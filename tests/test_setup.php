<?php

class TestSetup {

  public static function reset_database() {
    $result = DB::query(file_get_contents(__DIR__ . '/../database/test_seed.sql'));
  }

}
