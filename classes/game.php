<?php

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
    $length = 4;
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
    DB::query("UPDATE players SET character_type = null WHERE game_code = '{$this->code}'");

    $query = DB::query("SELECT * FROM players WHERE game_code = '{$this->code}' ORDER BY rand()");

    $number_of_moles = Player::how_many_moles($query->num_rows);

    $assigned_players = array('mole' => array(), 'runner' => array());

    foreach (range(1, $number_of_moles) as $index) {
      $row = $query->fetch_assoc();
      $assigned_players['mole'][] = $row['user_id'];
    }

    foreach(Player::SPECIAL_RUNNERS as $special_runner) {
      $row = $query->fetch_assoc();
      $assigned_players[$special_runner] = array($row['user_id']);
    }

    while($row = $query->fetch_assoc()) {
      $assigned_players['runner'][] = $row['user_id'];
    }

    foreach($assigned_players as $character_type => $users) {
      foreach($users as $user_id) {
        DB::query("UPDATE players SET position = 1, died = false, finished = false, revealed_to_captain = false, character_type = '{$character_type}' WHERE game_code = '{$this->code}' AND user_id = '{$user_id}'");
      }
    }
  }
}
