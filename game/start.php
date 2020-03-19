<head>
  <title>Mole Game</title>
</head>

<body>
  <?php
    require "../functions.php";
    $game = Game::create_new_game(current_user()->user_id);
    header("Location: play.php?code={$game->code}");
    return;
  ?>

</body>
