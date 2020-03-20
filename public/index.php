<head>
  <title>Mole Game</title>
</head>

<body>
  <?php
    $permit_unauthenticated = true;
    require "../functions.php";

    if (isset($_POST['username']) && !current_user()->exists()) {
      $_SESSION['username'] = $_POST['username'];
      $_SESSION['user_id'] = User::generate_user_id();
    }

    current_user()->save();
  ?>

  <h1> Welcome to the Mole Game </h1>

  <?php if(current_user()->exists()): ?>
    <?= "Welcome ". current_user()->username . "!"; ?>
    <br><br>

    <b>Join a Game</b>
    <form method="GET" action="game/play.php">
      <input type="text" name="code"> 
      <br>
      <input type="submit" value="Start">
    </form>

    <b>Start a Game (You will be the ref)</b>
    <form method="POST" action="game/start.php">
      <input type="submit" value="Start">
    </form>
  <?php else: ?>
    <h3>Please enter your REAL name</h3>
    <b>DO NOT ENTER A GAME CODE HERE</b>
    <form method="POST">
      <input type="text" name="username" placeholder="Enter your real name"> 
      <br>
      <input type="submit" value="Start">
    </form>
  <?php endif; ?>
</body>
