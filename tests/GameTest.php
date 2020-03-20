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

  public function testStart() : void {
    $game = new Game('8PGame');
    $this->assertEquals(0, $game->has_started);
    $game->start();

    $game = new Game('8PGame');
    $this->assertEquals(true, $game->has_started);
  }

  public function testAssignPlayers() : void {
    $game = new Game('8PGame');
    $game->assign_players();

    $players = Player::all_for_game('8PGame');

    $assignments = array();

    while($row = $players->fetch_assoc()) {
      if(isset($assignments[$row['character_type']])) {
        $assignments[$row['character_type']]++;
      } else {
        $assignments[$row['character_type']] = 1;
      }
    }

    $this->assertEquals(2, $assignments['mole']);
    $this->assertGreaterThanOrEqual(2, $assignments['runner']);
    $this->assertEquals(1, $assignments['coach']);
    $this->assertEquals(1, $assignments['captain']);
    $this->assertEquals(1, $assignments['sore loser']);
  }
}
