<?php

if(!$_SESSION['user']) {
  header('/');
  return;
}
