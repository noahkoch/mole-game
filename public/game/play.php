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

    tr.dq td.not-reached, tr.dq:nth-child(2n) td.not-reached {
      background: red;
    }

    td.dq {
      color: red; 
    }

    td.finished {
      color: green; 
    }
  </style>
</head>

<body>
  <?php
    require "../../functions.php";
    $game_code = $_GET['code'];

    $game      = new Game($game_code);


    if(!$game->code) {
      header('Location: /');
      return;
    }

    $user      = current_user()->user_id;

    if(!$user) {
      header('Location: /');
      return;
    }

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

    if(isset($_POST['reassign_players']) && $is_owner) {
      $game->assign_players();
      header("Location: /game/play.php?code={$game_code}");
      return;
    }

    if(isset($_POST['move_back']) && $is_owner) {
      $player    = new Player($_POST['user_id'], $game_code);
      $player->move_back();
      header("Location: /game/play.php?code={$game_code}");
      return;
    }

    if(isset($_POST['move_forward']) && $is_owner) {
      $player    = new Player($_POST['user_id'], $game_code);
      $player->move_forward();
      header("Location: /game/play.php?code={$game_code}");
      return;
    }

    if(isset($_POST['toggle_reveal_to_captain']) && $is_owner) {
      $player    = new Player($_POST['user_id'], $game_code);
      $player->toggle_reveal_to_captain();
      header("Location: /game/play.php?code={$game_code}");
      return;
    }

    if(isset($_POST['name_override']) && $is_owner) {
      $user = new User(array('user_id' => $_POST['user_id'], 'username' => $_POST['name_override']));
      $user->override_name($_POST['name_override']);
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
        <i>Once at least 5 players have joined, we can kick this thing off.</i>
        <?php if($players_query->num_rows >= 5): ?>
          <form method="POST">
            <input type="submit" name="start_game" value="Start the Game">
          </form>
        <?php endif; ?>
      <?php endif; ?>
      <ul>
        <?php while($row = $players_query->fetch_assoc()): ?>
          <li>
            <?php if($row['name_override']): ?>
              <?= $row['username'] . " (" . $row['name_override'] . ")"; ?>
            <?php else: ?>
              <?= $row['username']; ?>
            <?php endif; ?>
          </li>
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
    <form method="POST">
      <input type="submit" name="reassign_players" value="Re-assign players" >
    </form>
  <?php elseif($player->exists()): ?>
    <b><?= "You are a " . $player->character_type; ?></b>
    <p>Description: <?= Player::CHARACTERS[$player->character_type]['description']; ?></p>
  <?php else: ?>
    <b>This game has already started, but you can follow along.</b>
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
          <th> Revealed to Captain </th>  
          <th> - </th>  
          <th> &nbsp; </th>  
          <th> + </th>  
        <?php else: ?>
          <th> &nbsp; </th>  
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php # ZOINKS! This is not a safe query! ?>
      <?php $query = Player::all_for_game($game_code); ?>
      <?php $id = 0; ?>
      <?php while($row = $query->fetch_assoc()): ?>
        <?php $id++; ?>
        <?php
          $class = "";
          if($row['died'] == 1) {
            $class = "dq";
          } else if($row['finished']) {
            $class = "finished";
          }
        ?>
        <tr class="<?= $class; ?>"> 
          <td class="<?= $class; ?>">
            [<?= $id; ?>]&nbsp; 
            <?php if($row['name_override']): ?>
              <?= $row['username'] . " (" . $row['name_override'] . ")"; ?>
            <?php else: ?>
              <?= $row['username']; ?>
            <?php endif; ?>
            <?php if($is_owner): ?>
              <form method="POST">
                <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                <input type="text" name="name_override" value="<?= $row['name_override']; ?>">
              </form>
            <?php endif; ?>
          </td>
          <td class="<?= $row['position'] >= 0 ? 'reached' : 'not-reached'; ?>">&nbsp;</td>
          <td class="<?= $row['position'] >= 1 ? 'reached' : 'not-reached'; ?>">&nbsp;</td>
          <td class="<?= $row['position'] >= 2 ? 'reached' : 'not-reached'; ?>">&nbsp;</td>
          <td class="<?= $row['position'] >= 3 ? 'reached' : 'not-reached'; ?>">&nbsp;</td>
          <td class="<?= $row['position'] >= 4 ? 'reached' : 'not-reached'; ?>">&nbsp;</td>
          <?php if($is_owner): ?>
            <td><?= $row['character_type']; ?></td>  
            <td>
              <form method="POST">
                <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                <?php if(!$row['revealed_to_captain']): ?>
                  <input type="submit" name="toggle_reveal_to_captain" value="Reveal">
                <?php else: ?>
                  <input type="submit" name="toggle_reveal_to_captain" value="Hide">
                <?php endif; ?>
              </form>
            </td>  
            <td> 
              <form method="POST">
                <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                <input type="submit" name="move_back" value="<<<--">
              </form>
            </td>  
            <td> &nbsp; </td>  
            <td>
              <form method="POST">
                <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                <input type="submit" name="move_forward" value="-->>>">
              </form>
            </td>  
          <?php else: ?>
            <td>
              <?php if($row['died'] || ($row['revealed_to_captain'] && $player->is_captain())): ?>
                <?= $row['character_type']; ?>
              <?php endif; ?>
            </td>  
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
  
  <p>
    Each "heat" or round will have a pre-huddle and a huddle. Regular "Runners" will keep their eyes closed for the entire pre-huddle. During the pre-huddle, the other participants will open their eyes when called upon. The moles choose 2 participants whome they would like to move back one step each. The coach gets to move one participant forward up to one step; they can also choose not to move anyone. The sore loser gets to move one person back one step; they can choose to not move anyone. The captain can know the character of one particpant.  
  </p>

  <p>
    During the huddle, everyone will open their eyes. Based on the movements, they will try to determine who the moles are and vote to move one person back one or forward one step. If a vote ends in a tie, no one moves back.
  </p>

  <p>Disqualified players are not allowed to vote, but players who have finished the race can still vote.</p>

  <p>
    The game ends when either one team has all participants move to the finish line or one team has all participants disqualified. Tie breakers are decided by the team with the fewest disqualifications. If it is still tied, the moles win. 
  </p>

  <p>
    Characters are revealed when a player is disqualified.
  </p>

</body>
