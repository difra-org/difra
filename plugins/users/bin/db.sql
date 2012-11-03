CREATE TABLE `users` (
	  `id` int(11) NOT NULL auto_increment,
	  `email` char(250) NOT NULL,
	  `password` char(32) NOT NULL,
	  `active` tinyint(1) NOT NULL DEFAULT '1',
	  `banned` tinyint(1) NOT NULL DEFAULT '0',
	  `registered` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `logged` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `info` blob,
	  `activation` char(24) default NULL,
	  `moderator` tinyint(1) NOT NULL DEFAULT '0',
	  PRIMARY KEY  (`id`),
	  UNIQUE KEY `email` (`email`),
	  KEY `active` (`active`),
	  KEY `banned` (`banned`),
	  KEY `activation` (`activation`),
	  KEY `moderator` (`moderator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_recovers` (
	  `id` char(24) NOT NULL,
	  `used` tinyint(1) NOT NULL DEFAULT '0',
	  `user_id` int(11) DEFAULT NULL,
	  `date_requested` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `date_used` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	  PRIMARY KEY  (`id`),
	  KEY `used` (`used`),
	  KEY `user_id` (`user_id`),
	  CONSTRAINT `users_recovers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_fields` (
	  `id` int(11) NOT NULL default '0',
	  `name` char(64) NOT NULL default '',
	  `value` char(250) default NULL,
	  PRIMARY KEY  (`id`,`name`),
	  KEY `key_name_value` (`name`,`value`),
	  CONSTRAINT `users_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(48) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `sessionToUser_ifk` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


