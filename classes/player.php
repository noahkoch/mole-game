<?php

class Player {
  public $character_type;
  public $died;
  public $finished;
  public $position;
  public $team;
  public $user_id;
  public $game_code;

  const WIN_AT = 4; # Zero-based indexing, this is the fifth "step".

  const TEAMS = array('runners', 'moles');

  const SPECIAL_RUNNERS = array('captain', 'sore loser', 'coach');

  const CHARACTERS = array(
    'mole' => array(
      'before_huddle_powers' => true,
      'required_participants' => 6,
      'max' => 4,
      'team' => 1,
      'description' => 'You are secretly on the other team. But shhhh don\'t let anyone on to that. You and you fellow moles will open their eyes before each huddle to pick two people to move back 1 step. You can also move yourselves back to throw everyone off.' ,
    ),
    'coach' => array(
      'description' => 'Before each huddle, you get to choose one participant to move one step ahead.' ,
      'before_huddle_powers' => true,
      'required_participants' => 6,
      'max' => 1,
      'team' => 0 
    ),
    'runner' => array(
      'description' => "You're just happy to be here! Vote on who gets to step ahead during each huddle.",
      'before_huddle_powers' => false,
      'required_participants' => 6,
      'max' => 100,
      'team' => 0 
    ),
    'captain' => array(
      'description' => 'Before each huddle, you get to view one participants card.',
      'before_huddle_powers' => true,
      'required_participants' => 8,
      'max' => 1,
      'team' => 0 
    ),
    'sore loser' => array(
      'description' => 'Before each huddle, if you choose, you can move one participant back one step.', 
      'before_huddle_powers' => true,
      'required_participants' => 6,
      'max' => 1,
      'team' => 0 
    )
  );

  public function __construct($user_id, $game_code) {
    $query = DB::query("SELECT * FROM players WHERE user_id = '{$user_id}' AND game_code = '{$game_code}';");
    if($query->num_rows == 1) {
      $row = $query->fetch_assoc();
      $this->user_id = $row['user_id'];
      $this->game_code = $row['game_code'];
      $this->character_type = $row['character_type'];
      $this->finished = $row['finished'];
      $this->died = $row['died'];
      $this->position = $row['position'];
      //$this->team = Player::TEAMS[$row['team']];
    }
  }

  public function exists() {
    return !!$this->user_id;
  }

  public function is_captain() {
    return $this->character_type == 'captain';
  }

  public function toggle_reveal_to_captain() {
      DB::query("UPDATE players SET revealed_to_captain = !revealed_to_captain WHERE user_id = '{$this->user_id}' AND game_code = '{$this->game_code}'");
  }

  public function move_forward() {
    if($this->position >= Player::WIN_AT) { return; }

    $this->position = $this->position + 1;
    if($this->position >= Player::WIN_AT) {
      DB::query("UPDATE players SET position = position + 1, finished = true WHERE user_id = '{$this->user_id}' AND game_code = '{$this->game_code}'");
    } else {
      DB::query("UPDATE players SET position = position + 1, died = false WHERE user_id = '{$this->user_id}' AND game_code = '{$this->game_code}'");
    }
  }

  public function move_back() {
    if($this->position < 0) { return; }

    $this->position = $this->position - 1;
    if($this->position < 0) {
      DB::query("UPDATE players SET position = position - 1, died = true WHERE user_id = '{$this->user_id}' AND game_code = '{$this->game_code}'");
    } else {
      DB::query("UPDATE players SET position = position - 1, finished = false WHERE user_id = '{$this->user_id}' AND game_code = '{$this->game_code}'");
    }
  }

  public static function all_for_game($game_code) {
    return DB::query("SELECT * FROM players INNER JOIN users ON players.user_id = users.user_id WHERE game_code = '{$game_code}' ORDER BY users.user_id");
  }

  public static function join_game($user_id, $game_code) {
    DB::query("INSERT INTO players (user_id, game_code) VALUES ('{$user_id}', '{$game_code}')");
    return new Player($user_id, $game_code);
  }

  public static function how_many_moles($number_of_players) {
    if($number_of_players < 9) {
      return 2;
    } else if($number_of_players < 12) {
      return 3;
    } else {
      return 4;
    }
  }
}
