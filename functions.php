<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('session.gc_maxlifetime', 259200);

// each client should remember their session id for EXACTLY 72 hours
session_set_cookie_params(259200);

session_start();

require 'database/setup.php';

class User {
  public $user_id;
  public $username;

  public function __construct($session) {
    if(isset($session['user_id']) && isset($session['username'])) {
      $this->user_id = $session['user_id'];
      $this->username = $session['username'];
    }
  }

  public static function generate_user_id() {
    return time() . '-' . rand(10000, 1000000000000);
  }

  public function exists() {
    return $this->user_id !== null;
  }

  public function override_name($override) {
    DB::query("UPDATE users SET name_override = '{$override}' WHERE user_id = '{$this->user_id}';");
  }

  public function save() {
    $query = DB::query("SELECT * FROM users WHERE user_id = '{$this->user_id}';");
    if($query->num_rows == 0) {
      DB::query("INSERT INTO users (user_id, username) VALUES ('{$this->user_id}', '{$this->username}');");
    }
  }
}

class Game {
  public $code;
  public $completed;
  public $has_started;

  public function __construct($code) {
    $query = DB::query("SELECT * FROM games WHERE game_code = '{$code}';");
    if($query->num_rows > 0) {
      $row = $query->fetch_assoc();

      $this->code = $code;
      $this->has_started = $row['has_started'];
      $this->owner = $row['owner'];
    }
  }

  public static function create_new_game($user_id) {
    $code = Game::generate_unique_game_code();

    if(DB::query("INSERT INTO games (owner, game_code) VALUES ('{$user_id}', '{$code}');")){
      return new Game($code);
    } else {
      return new Game(null);
    }
  }

  public static function generate_unique_game_code() {
    $length = 6;
    $game_code = null;

    while($game_code == null) {
      $game_code = substr(str_shuffle("ABCDEFGHJKMNPQRSTUVWXYZ"), 0, $length);

      if(DB::query("SELECT * FROM games WHERE game_code = '{$game_code}'")->num_rows == 1) {
        $game_code = null;
      }
    }

    return $game_code;
  }

  public function start() {
    $this->assign_players();
    DB::query("UPDATE games SET has_started = TRUE WHERE game_code = '{$this->code}'");
  }

  public function assign_players() {
    $query = DB::query("SELECT * FROM players WHERE game_code = '{$this->code}'");
    $number_of_players = $query->num_rows;
    $number_of_moles = Player::how_many_moles($query->num_rows);

    $assigned_players = array();

    while($row = $query->fetch_assoc()) {
      $player_assigned_to_character = false;

      while(!$player_assigned_to_character) {
        $character_offset = array_rand(array_keys(Player::CHARACTERS));
        $character = array_keys(Player::CHARACTERS)[$character_offset];
        $character_settings = Player::CHARACTERS[$character];

        if($character_settings['required_participants'] <= $number_of_players && (!isset($assigned_players[$character]) || $character_settings['max'] > count($assigned_players[$character]))) {
          if($character == 'mole' && (!isset($assigned_players[$character]) || $number_of_moles > count($assigned_players['mole']))) {
            $player_assigned_to_character = true;
          } else if($character !== 'mole') {
            $player_assigned_to_character = true;
          }

          if($player_assigned_to_character) {
            if(!isset($assigned_players[$character])) {
              $assigned_players[$character] = array();
            }

            $assigned_players[$character][] = $row['user_id'];

            DB::query("UPDATE players SET position = 1, died = false, finished = false, revealed_to_captain = false, character_type = '{$character}' WHERE game_code = '{$this->code}' AND user_id = '{$row['user_id']}'");
          }
        }
      }

    }
  }
}

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

function current_user() {
  $user = new User($_SESSION);
  return $user;
}
