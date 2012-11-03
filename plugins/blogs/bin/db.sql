CREATE TABLE `blogs` (
	  `id` int(11) NOT NULL auto_increment,
	  `user` int(11) default NULL,
	  `group` int(11) default NULL,
	  `name` char(250) default NULL,
	  PRIMARY KEY  (`id`),
	  UNIQUE KEY `user` (`user`),
	  UNIQUE KEY `group` (`group`),
	  KEY `id_group` (`id`,`group`),
	  CONSTRAINT `blogs_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
	  CONSTRAINT `blogs_ibfk_2` FOREIGN KEY (`group`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `blogs_comments` (
	  `id` int(11) NOT NULL auto_increment,
	  `user` int(11) default NULL,
	  `parent_id` int(11) NOT NULL COMMENT 'References blogs.id',
	  `reply_id` int(11) default NULL COMMENT 'References this.id for threaded comments',
	  `text` varchar(1024) default NULL,
	  `deleted` tinyint(1) default '0',
	  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
	  PRIMARY KEY  (`id`),
	  KEY `user` (`user`),
	  KEY `parent_id` (`parent_id`),
	  KEY `reply_id` (`reply_id`),
	  CONSTRAINT `blogs_comments_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	  CONSTRAINT `blogs_comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	  CONSTRAINT `blogs_comments_ibfk_3` FOREIGN KEY (`reply_id`) REFERENCES `blogs_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `blogs_friends` (
  `user` int(11) NOT NULL DEFAULT '0',
  `blog` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user`,`blog`),
  KEY `blogs_friends_ibfk_2` (`blog`),
  CONSTRAINT `blogs_friends_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blogs_friends_ibfk_2` FOREIGN KEY (`blog`) REFERENCES `blogs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `blogs_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `title` char(250) DEFAULT NULL,
  `link` char(250) DEFAULT NULL,
  `preview` mediumtext,
  `text` longtext,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `visible` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `key_blog_date` (`blog`,`date`),
  KEY `key_blog_visible_date` (`blog`,`visible`,`date`),
  CONSTRAINT `blogs_posts_ibfk_1` FOREIGN KEY (`blog`) REFERENCES `blogs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blogs_posts_ibfk_2` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `blogs_stat` (
  `post_id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `count` int(11) NOT NULL,
  UNIQUE KEY `key_post_date` (`post_id`,`date`),
  KEY `post_id` (`post_id`),
  KEY `group_id` (`group_id`),
  KEY `date` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `postid_fk1` FOREIGN KEY (`post_id`) REFERENCES `blogs_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `groups` (
	`id` INT (11) NOT NULL AUTO_INCREMENT,
	`name` char(250) NOT NULL,
	`domain` char(63) NOT NULL,
	`owner` int(11) NOT NULL,
	PRIMARY KEY  (`id`),
	UNIQUE KEY `domain` (`domain`),
	UNIQUE KEY `name` (`name`),
	KEY `owner` (`owner`),
	CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `groups_fields` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` char(64) NOT NULL DEFAULT '',
  `value` text,
  PRIMARY KEY (`id`,`name`),
  KEY `key_name_value` (`name`,`value`(8)),
  CONSTRAINT `groups_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `groups_users` (
	  `group` int(11) NOT NULL,
	  `user` int(11) NOT NULL,
	  `role` char(60) default NULL,
	  `confirmed` tinyint(1) default '0',
	  `comment` char(250) default NULL,
	  UNIQUE KEY `key_group_user` (`group`,`user`),
	  KEY `key_group` (`group`),
	  KEY `key_user` (`user`),
	  KEY `key_user_confirmed_group` (`user`,`confirmed`,`group`),
	  CONSTRAINT `groups_users_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
	  CONSTRAINT `groups_users_ibfk_2` FOREIGN KEY (`group`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

