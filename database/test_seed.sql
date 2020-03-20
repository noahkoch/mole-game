DELETE FROM users;

DELETE FROM games;

DELETE FROM players;

INSERT INTO users (username, user_id) VALUES ('user1', '1'), ('user2', '2'), ('user3', '3'), ('user4', '4'), ('user5', '5'), ('user6', '6'), ('user7', '7'), ('user8', '8'), ('user9', '9'), ('user10', '10'), ('user11', '11'), ('user12', '12'), ('user13', '13');

INSERT INTO games (game_code, owner, has_started) VALUES ('8PGame', '1', false);

INSERT INTO players (game_code, user_id) VALUES ('8PGame', '2'), ('8PGame', '3'), ('8PGame', '4'), ('8PGame', '5'), ('8PGame', '6'), ('8PGame', '7'), ('8PGame', '8'), ('8PGame', '10');
