DELETE FROM users;
DELETE FROM games;
DELETE FROM players;

INSERT INTO users (username, user_id)
VALUES
  ('user1', '1'),
  ('user2', '2'),
  ('user3', '3'),
  ('user4', '4'),
  ('user5', '5'),
  ('user6', '6'),
  ('user7', '7'),
  ('user8', '8'),
  ('user9', '9'),
  ('user10', '10'),
  ('user11', '11'),
  ('user12', '12'),
  ('user13', '13');

INSERT INTO games (game_code, owner, has_started)
VALUES
  ('8PGame', 'user1', false);

INSERT INTO players (game_code, user_id)
VALUES
  ('8PGame', 'user2'),
  ('8PGame', 'user3'),
  ('8PGame', 'user4'),
  ('8PGame', 'user5'),
  ('8PGame', 'user6'),
  ('8PGame', 'user7'),
  ('8PGame', 'user8'),
  ('8PGame', 'user9');
