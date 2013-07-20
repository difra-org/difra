CREATE TABLE `catalog_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link` char(250) DEFAULT NULL,
  `name` char(250) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `visible` tinyint(1) DEFAULT '1',
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visible` (`visible`),
  KEY `parent` (`parent`),
  KEY `position` (`position`),
  KEY `link` (`link`),
  CONSTRAINT `catalog_categories_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `catalog_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `catalog_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` int(11) NOT NULL,
  `name` char(250) NOT NULL,
  `visible` int(1) DEFAULT '1',
  `price` float DEFAULT NULL,
  `sale` float DEFAULT NULL,
  `link` char(250) DEFAULT NULL,
  `shortdesc` text,
  `description` mediumtext,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `link` (`link`),
  CONSTRAINT `catalog_items_ibfk_1` FOREIGN KEY (`category`) REFERENCES `catalog_categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `catalog_ext_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(255) DEFAULT NULL,
  `description` char(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `catalog_ext` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(250) DEFAULT NULL,
  `group` int(11) DEFAULT NULL,
  `visible` int(1) DEFAULT '1',
  `position` int(11) DEFAULT NULL,
  `set` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `position` (`position`),
  KEY `group` (`group`),
  CONSTRAINT `catalog_ext_ibfk_1` FOREIGN KEY (`group`) REFERENCES `catalog_ext_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `catalog_ext_sets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ext` int(11) DEFAULT NULL,
  `name` char(250) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ext` (`ext`),
  KEY `position` (`position`),
  CONSTRAINT `catalog_ext_sets_ibfk_1` FOREIGN KEY (`ext`) REFERENCES `catalog_ext` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `catalog_items_ext` (
  `item` int(11) NOT NULL DEFAULT '0',
  `ext` int(11) NOT NULL DEFAULT '0',
  `setvalue` int(11) DEFAULT NULL,
  `value` char(250) DEFAULT NULL,
  KEY `ext` (`ext`),
  KEY `setvalue` (`setvalue`),
  KEY `item` (`item`),
  CONSTRAINT `catalog_items_ext_ibfk_1` FOREIGN KEY (`item`) REFERENCES `catalog_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `catalog_items_ext_ibfk_2` FOREIGN KEY (`ext`) REFERENCES `catalog_ext` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `catalog_items_ext_ibfk_3` FOREIGN KEY (`setvalue`) REFERENCES `catalog_ext_sets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `catalog_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item` int(11) DEFAULT NULL,
  `main` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `item` (`item`),
  CONSTRAINT `catalog_images_ibfk_1` FOREIGN KEY (`item`) REFERENCES `catalog_items` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
CREATE TABLE `catalog_cart` (
  `user` int(11) NOT NULL,
  `cart` blob,
  PRIMARY KEY (`user`),
  UNIQUE KEY `user` (`user`),
  CONSTRAINT `catalog_cart_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `catalog_favourites` (
  `user` int(11) NOT NULL,
  `favourites` blob,
  PRIMARY KEY (`user`),
  UNIQUE KEY `user` (`user`),
  CONSTRAINT `catalog_favourites_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `catalog_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) DEFAULT NULL,
  `user_email` char(250) DEFAULT NULL,
  `cost` float NOT NULL,
  `weight` float NOT NULL DEFAULT '0',
  `delivery_cost` float NOT NULL DEFAULT '0',
  `comment` text,
  `locked` tinyint(1) DEFAULT '0',
  `wait_arrival` tinyint(1) DEFAULT '0',
  `paid` tinyint(1) NOT NULL DEFAULT '0',
  `packed` tinyint(1) NOT NULL DEFAULT '0',
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `cart` text,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `closed` (`closed`),
  CONSTRAINT `catalog_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `catalog_orders_data` (
  `order_id` int(11) DEFAULT NULL,
  `name` char(250) DEFAULT NULL,
  `value` char(250) DEFAULT NULL,
  KEY `order_id` (`order_id`),
  KEY `name` (`name`),
  CONSTRAINT `catalog_orders_data_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `catalog_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `catalog_orders_data_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `catalog_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

*/