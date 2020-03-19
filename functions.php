<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
}

class Game {
  public $code;
  public $completed;
  public $has_started;

  public function __construct($code) {
    $query = DB::query("SELECT * FROM games WHERE game_code = {$code};")
    $row = $query->fetch_result();

    if($row) {
      $this->code = $code;
      $this->has_started = $row['has_started'];
      $this->owner = $row['owner'];
    }
  }

  public static function create_new_game() {
  }

  public static function generate_unique_game_code() {
    $length = 5;
    $game_code = null;

    while($game_code !== null) {
      $game_code = substr(str_shuffle("ABCDEFGHJKMNPQRSTUVWXYZ"), 0, $length);

      if(DB::query("SELECT * FROM games WHERE code = {$game_code}"))->num_rows == 1) {
        $game_code = null;
      }
    }
  }

  public function assign_players() {
    $query = DB::query("SELECT * FROM players WHERE game_code = {$this->game_code}");
    $number_of_players = $query->num_rows;
    $number_of_moles = $query->num_rows($query->num_rows);

    $assigned_players = array();

    while($row = $query->fetch_row) {
      $player_assigned_to_character = false;

      while(!$player_assigned_to_character) {
        $character = array_rand(array_keys(Player::CHARACTERS));
        $character_settings = Player::CHARACTERS[$character]

        if($character_settings['required_participants'] >= $number_of_players && (!isset($assigned_players[$character]) || $character_settings['max'] > count($assigned_players[$character]))) {
          if($charachter == 'mole' && (!isset($assigned_players[$character]) || $number_of_moles > count($assigned_players['mole']))) {
            $player_assigned_to_character = true;
          } else if($character !== 'mole') {
            $player_assigned_to_character = true;
          }

          if($player_assigned_to_character) {
            if(!isset($assigned_players[$character])) {
              $assigned_players[$character] = array();
            }

            $assigned_players[$character][] = $row['user_id'];

            DB::query("UPDATE players WHERE game_code = {$this->game_code} AND user_id = {$row['user_id']} SET character_type = {$character}");
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

  const TEAMS = array('runners', 'moles');

  const CHARACTERS = array(
    'mole' => array(
      'before_huddle_powers' => true,
      'required_participants' => 6,
      'max' => 4,
      'team' => 1
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

    public function __construct($user_id, $game_code) {
      $query = DB::query("SELECT * FROM players WHERE user_id = {$user_id} AND game_code = {$user_id};")
      $row = $query->fetch_result();
      $this->character_type = $row['character_type'];
      $this->finished = $row['finished'];
      $this->died = $row['died'];
      $this->position = $row['position'];
      $this->team = TEAMS[$row['position']];
    }
  );

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
