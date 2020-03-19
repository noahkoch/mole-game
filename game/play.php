<head>
  <title>Mole Game</title>
  <style type="text/css">
    table {
      width: 100%;
    } 

    tr:nth-child(2n) td{
      background: #f7f7f7;
    }

    td.reached, tr:nth-child(2n) td.reached{
      background: green;
    }

    td.not-reached, tr:nth-child(2n) td.not-reached {
      background: orange;
    }
  </style>
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
      header("Location: /game/play.php?code={$game_code}");
      return;
    }

    if(isset($_POST['start_game']) && $is_owner) {
      $game->start();
      header("Location: /game/play.php?code={$game_code}");
      return;
    }
  ?>

  <h1>Mole</h1>
  <h2><?= current_user()->username; ?></h2>
  <?php if(!$game->has_started): ?>
    <?php if($is_owner || $player->exists()): ?>
      <?php $players_query = Player::all_for_game($game_code); ?>
      Waiting for game to start -- Invite others with code "<?= $game_code; ?>".
      <?php if($is_owner): ?>
        <b> (You're the ref) </b>
        <br>
        <i>Once at least 6 players have joined, we can kick this thing off.</i>
        <?php if($players_query->num_rows >= 6): ?>
          <form method="POST">
            <input type="submit" name="start_game" value="Start the Game">
          </form>
        <?php endif; ?>
      <?php endif; ?>
      <ul>
        <?php while($row = $players_query->fetch_assoc()): ?>
          <li><?= $row['username']; ?></li>
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
        <?php if($is_owner): ?>
          <th> Character </th>  
          <th> - </th>  
          <th> &nbsp; </th>  
          <th> + </th>  
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php # ZOINKS! This is not a safe query! ?>
      <?php $query = Player::all_for_game($game_code); ?>
      <?php while($row = $query->fetch_assoc()): ?>
        <tr> 
          <td><?= $row['username']; ?></td>
          <td class="<?= $row['position'] >= 0 ? 'reached' : 'not-reached'; ?>">&nbsp;</td>
          <td class="<?= $row['position'] >= 1 ? 'reached' : 'not-reached'; ?>">&nbsp;</td>
          <td class="<?= $row['position'] >= 2 ? 'reached' : 'not-reached'; ?>">&nbsp;</td>
          <td class="<?= $row['position'] >= 3 ? 'reached' : 'not-reached'; ?>">&nbsp;</td>
          <td class="<?= $row['position'] >= 4 ? 'reached' : 'not-reached'; ?>">&nbsp;</td>
          <?php if($is_owner): ?>
            <th><?= $row['character_type']; ?></th>  
            <th> - </th>  
            <th> &nbsp; </th>  
            <th> + </th>  
          <?php endif; ?>
        </tr>
      <?php endwhile; ?>
    <tbody>
  </table>

  <hr>
  <br>
  <b>Rules</b>
  <p>
    Moles win if: all moles get to the finish line or all runners are disqualified. 
    <br>
    Runners win if: all runners get to the finish line or all moles are disqualified.
  </p>

  <p>
    If a player reaches the finish line they cannot be disqualified and if a player is disqualified they are out of the game. 
  </p>

</body>
