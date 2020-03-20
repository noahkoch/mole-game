<?php declare(strict_types=1);

require 'tests/test_setup.php';

use PHPUnit\Framework\TestCase;

final class GameTest extends TestCase {
  public function setUp() : void {
    TestSetup::reset_database();
  }

  public function testGenerateGameCode() : void {
    $this->assertNotNull(Game::generate_unique_game_code());
  }

  public function testAssignPlayers() : void {
    $game = new Game('8PGame');
    $game->start();

    $players = Player::all_for_game('8PGame');

    $assignments = array();

    while($row = $players->fetch_assoc()) {
      if(isset($assignments[$row['character_type']])) {
        $assignments[$row['character_type']]++;
      } else {
        $assignments[$row['character_type']] = 1;
      }
    }

    $this->assertEquals($assignments['mole'], 2);
  }
}
