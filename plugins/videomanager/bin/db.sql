CREATE TABLE `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `video` varchar(42) NOT NULL,
  `site` varchar(90) DEFAULT NULL,
  `name` text NOT NULL,
  `original_file` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `thumbs` tinyint(4) NOT NULL DEFAULT '0',
  `length` tinyint(4) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `hasPoster` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `video_key` (`video`),
  KEY `site_key` (`site`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;