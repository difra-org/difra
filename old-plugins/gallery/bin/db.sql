CREATE TABLE `gallery_albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(250) DEFAULT NULL,
  `description` mediumtext,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `gallery_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album` int(11) DEFAULT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `album` (`album`),
  CONSTRAINT `gallery_photos_ibfk_1` FOREIGN KEY (`album`) REFERENCES `gallery_albums` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
