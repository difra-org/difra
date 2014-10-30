CREATE TABLE `announcements_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(250) NOT NULL,
  `categoryText` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_key` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `announcements_additionals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `alias` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias_key` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `group` int(11) DEFAULT NULL,
  `location` int(11) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `link` char(255) NOT NULL,
  `title` text NOT NULL,
  `description` text,
  `shortDescription` text NOT NULL,
  `fromEventDate` DATE NULL DEFAULT NULL,
  `eventDate` DATE NULL DEFAULT NULL,
  `beginDate` DATE NULL DEFAULT NULL,
  `endDate` DATE NULL DEFAULT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `priority` int(11) NOT NULL DEFAULT '50',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exported` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_akey` (`user`),
  KEY `group_key` (`group`),
  KEY `visible_key` (`visible`),
  KEY `category_key` (`category`),
  KEY `location_key` (`location`),
  KEY `datesort` (`visible`,`endDate`,`fromEventDate`,`eventDate`,`beginDate`,`priority`),
  KEY `announce_exported_key` (`exported`),
  CONSTRAINT `user_afkey` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `announcements_additionals_data` (
  `announce_id` int(11) NOT NULL,
  `additional_id` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`announce_id`,`additional_id`),
  KEY `announce_id_key` (`announce_id`),
  KEY `additional_id_key` (`additional_id`),
  CONSTRAINT `additional_fkey` FOREIGN KEY (`additional_id`) REFERENCES `announcements_additionals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `announce_fkey` FOREIGN KEY (`announce_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `announcements_schedules` (
  `announce_id` int(11) NOT NULL,
  `schedule` text,
  PRIMARY KEY (`announce_id`),
  CONSTRAINT `announce_fkey2` FOREIGN KEY (`announce_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `anouncements_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `locationData` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
