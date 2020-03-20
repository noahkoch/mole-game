<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

ini_set('session.gc_maxlifetime', 259200);

// each client should remember their session id for EXACTLY 72 hours
session_set_cookie_params(259200);

session_start();

require 'database/db.php';
require 'vendor/autoload.php';

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

function current_user() {
  $user = new User($_SESSION);
  return $user;
}
