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
