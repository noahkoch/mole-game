<head>
  <title>Mole Game</title>
</head>

<body>
  <?php
    require "../functions.php";
    $game_code = $_GET['code'];
    $game      = new Game($game_code);

    if(!$game->code) {
      header('/');
      return;
    }

    $user      = current_user()->user_id;
    $player    = new Player(current_user()->user_id, $game_code);
    $is_owner  = $user == $game->owner;
  ?>

  <h1>Game</h1>
  <h2><?= current_user()->username; ?></h2>

  <?php if(!$game->has_started): ?>
    Waiting for game to start -- Invite others with code <?= $game_code; ?>.
    <?php return; ?>
  <?php endif; >


  <?php if(!$is_owner): ?>
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
      <?php while($row = DB::query("SELECT *.players FROM players WHERE game_code = {$game_code}")): ?>
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
