<head>
  <title>Mole Game</title>
</head>

<body>
  <?php
    require "../functions.php";
    $game_code = $_GET['code'];
    $game      = new Game($game_code);


    if(!$game->code) {
      header('Location: /');
      return;
    }

    $user      = current_user()->user_id;
    $is_owner  = $user == $game->owner;
    if(!$is_owner) {
      $player    = new Player(current_user()->user_id, $game_code);
    }

    if(isset($_POST['join'])) {
      $player = Player::join_game(current_user()->user_id, $game_code);
    }
  ?>

  <h1>Game</h1>
  <h2><?= current_user()->username; ?></h2>
  <?php if(!$game->has_started): ?>
    <?php if($is_owner || $player->exists()): ?>
      Waiting for game to start -- Invite others with code "<?= $game_code; ?>".
      <?php if($is_owner): ?>
        <b> (You're the ref) </b>
      <?php endif; ?>
      <ul>
        <?php while($row = Player::all_for_game($game_code)->fetch_assoc()): ?>
          <li><?= $row['user_id']; ?></li>
        <?php endwhile; ?>
      </ul>
    <?php else: ?>
      <form method="POST">
        <input type="submit" name="join" value="Join the game">
      </form>
    <?php endif; ?>
    <?php return; ?>
  <?php endif; ?>


  <?php if($is_owner): ?>
    <b> You're the ref </b>
  <?php else: ?>
    <b><?= "You are a " . $player->character_type; ?></b>
  <?php endif; ?>

  <table> 
    <thead>
      <tr>
        <th>Player</th>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5 -- FINISH!</th>
      </tr>
    </thead>
    <tbody>
      <?php # ZOINKS! This is not a safe query! ?>
      <?php while($row = Player::all_for_game($game_code)->fetch_assoc()): ?>
        <tr> 
          <td><?= $row['user_id']; ?></td>
          <td><?= $row['position'] >= 1; ?></td>
          <td><?= $row['position'] >= 2; ?></td>
          <td><?= $row['position'] >= 3; ?></td>
          <td><?= $row['position'] >= 4; ?></td>
          <td><?= $row['position'] >= 5; ?></td>
        </tr>
      <?php endwhile; ?>
    <tbody>
  </table>

</body>
