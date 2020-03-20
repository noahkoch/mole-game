<?php
class DB {
  public static $instance;
  private $mysqli;

  public function __construct() {
    $this->mysqli = new mysqli(
      "side-projects-mysql-do-user-1607355-0.a.db.ondigitalocean.com",
      "mole_test",
      "my4fvlrh9o5jaxww",
      "mole_game_test",
      25060
    );
  }

  public static function query($query) {
    if(!self::$instance) {
      self::$instance = new DB;
    }
    return self::$instance->mysqli->query($query);
  }
}
