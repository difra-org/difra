CREATE TABLE `users` (
	`id`         INT(11)    NOT NULL AUTO_INCREMENT,
	`email`      CHAR(250)  NOT NULL,
	`password`   CHAR(32)   NOT NULL,
	`active`     TINYINT(1) NOT NULL DEFAULT '1',
	`banned`     TINYINT(1) NOT NULL DEFAULT '0',
	`registered` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`logged`     TIMESTAMP  NOT NULL DEFAULT '0000-00-00 00:00:00',
	`info`       BLOB,
	`activation` CHAR(24) DEFAULT NULL,
	`moderator`  TINYINT(1) NOT NULL DEFAULT '0',
	`type`       CHAR(25) DEFAULT 'user',
	PRIMARY KEY (`id`),
	UNIQUE KEY `email` (`email`),
	KEY `active` (`active`),
	KEY `banned` (`banned`),
	KEY `activation` (`activation`),
	KEY `moderator` (`moderator`),
	KEY `type` (`type`)
)
	ENGINE =InnoDB
	DEFAULT CHARSET =utf8;

CREATE TABLE `users_recovers` (
	`id`             CHAR(24)   NOT NULL,
	`used`           TINYINT(1) NOT NULL DEFAULT '0',
	`user_id`        INT(11) DEFAULT NULL,
	`date_requested` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_used`      TIMESTAMP  NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`),
	KEY `used` (`used`),
	KEY `user_id` (`user_id`),
	CONSTRAINT `users_recovers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
		ON DELETE CASCADE
)
	ENGINE =InnoDB
	DEFAULT CHARSET =utf8;

CREATE TABLE `users_fields` (
	`id`    INT(11)  NOT NULL DEFAULT '0',
	`name`  CHAR(64) NOT NULL DEFAULT '',
	`value` CHAR(250) DEFAULT NULL,
	PRIMARY KEY (`id`, `name`),
	KEY `key_name_value` (`name`, `value`),
	CONSTRAINT `users_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`)
		ON DELETE CASCADE
)
	ENGINE =InnoDB
	DEFAULT CHARSET =utf8;

CREATE TABLE `users_sessions` (
	`id`         INT(11)          NOT NULL,
	`session_id` VARCHAR(48)      NOT NULL,
	`date`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`ip`         INT(11) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	KEY `id` (`id`),
	KEY `session_id` (`session_id`),
	CONSTRAINT `sessionToUser_ifk` FOREIGN KEY (`id`) REFERENCES `users` (`id`)
		ON DELETE CASCADE
)
	ENGINE =InnoDB
	DEFAULT CHARSET =utf8;
