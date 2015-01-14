CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(512) NOT NULL,
  `title` text NOT NULL,
  `pubDate` timestamp NULL DEFAULT NULL,
  `viewDate` timestamp NULL DEFAULT NULL,
  `stopDate` timestamp NULL DEFAULT NULL,
  `source` text,
  `sourceURL` text,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `important` tinyint(1) NOT NULL DEFAULT '0',
  `body` mediumtext NOT NULL,
  `announcement` text,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `newslink_key` (`link`(255)),
  KEY `newsvisible_key` (`visible`),
  KEY `newsimportant_key` (`important`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;