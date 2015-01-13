CREATE TABLE `cms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` char(250) DEFAULT NULL,
  `hidden` tinyint(1) DEFAULT '0',
  `title` text,
  `body` mediumtext,
  PRIMARY KEY (`id`),
  KEY `tag` (`tag`) USING HASH,
  KEY `hidden` (`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(250) DEFAULT NULL,
  `description` char(250) DEFAULT NULL,
  `maxdepth` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu` int(11) NOT NULL,
  `position` int(11) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `visible` tinyint(1) DEFAULT '1',
  `page` int(11) DEFAULT NULL,
  `link` varchar(2048) DEFAULT NULL,
  `link_label` char(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_id` (`menu`),
  KEY `page_id` (`page`),
  KEY `position` (`position`),
  CONSTRAINT `cms_menu_items_ibfk_1` FOREIGN KEY (`menu`) REFERENCES `cms_menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cms_menu_items_ibfk_2` FOREIGN KEY (`page`) REFERENCES `cms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_snippets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(250) NOT NULL,
  `description` char(250) DEFAULT NULL,
  `text` text,
  PRIMARY KEY (`id`),
  KEY `name` (`name`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
