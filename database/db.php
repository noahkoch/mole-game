<?php
require "database_config.php";

class DB {
  public static $instance;
  private $mysqli;

  public function __construct() {
    if($GLOBALS["db_env"] == "test") {
      $config = TEST_DB_CONFIG;
    } else {
      $config = PROD_DB_CONFIG;
    }

    $this->mysqli = new mysqli(
      $config['host'],
      $config['username'],
      $config['password'],
      $config['database'],
      $config['port']
    );
  }

  public static function query($query) {
    if(!self::$instance) {
      self::$instance = new DB;
    }
    return self::$instance->mysqli->query($query);
  }
}
