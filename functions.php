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

function current_user() {
  $user = new User($_SESSION);
  return $user;
}
