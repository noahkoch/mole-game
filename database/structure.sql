create table users (
  user_id varchar(255) NOT NULL CHECK (user_id <> ''),
  username varchar(255) NOT NULL CHECK (username <> ''),
  name_override varchar(255) NULL,
  PRIMARY KEY(user_id)
); 

create table games (
  game_code varchar(55) NOT NULL,
  owner varchar(255) NOT NULL CHECK (owner <> ''),
  completed boolean DEFAULT FALSE,
  has_started boolean DEFAULT FALSE,
  PRIMARY KEY(game_code)
); 

create table players (
  game_code varchar(55) NOT NULL,
  user_id VARCHAR(255) NOT NULL CHECK (user_id <> ''),
  character_type varchar(55) NULL,
  finished boolean DEFAULT FALSE,
  died boolean DEFAULT FALSE,
  position INT(2) DEFAULT 1 NOT NULL,
  team INT(1) NULL,
  revealed_to_captain BOOLEAN DEFAULT FALSE,
  PRIMARY KEY(game_code, user_id)
); 
