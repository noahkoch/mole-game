<?php

class TestSetup {

  public static function reset_database() {
    $seed = file_get_contents(__DIR__ . '/../database/test_seed.sql');
    foreach(array_filter(explode("\n", $seed)) as $query) {
      $result = DB::query($query);
    }
  }

}
