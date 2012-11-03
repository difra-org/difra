SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `portfolio_users`
-- ----------------------------
DROP TABLE IF EXISTS `portfolio_users`;
CREATE TABLE `portfolio_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `linktext` text,
  `archive` tinyint(4) NOT NULL DEFAULT '0',
  `role` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `archive` (`archive`),
  CONSTRAINT `portfolio_users_fidk` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `portfolio_work_to_user`
-- ----------------------------
DROP TABLE IF EXISTS `portfolio_work_to_user`;
CREATE TABLE `portfolio_work_to_user` (
  `work_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  KEY `work_id` (`work_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_idk` FOREIGN KEY (`user_id`) REFERENCES `portfolio_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `work_idk` FOREIGN KEY (`work_id`) REFERENCES `portfolio_works` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `portfolio_works`
-- ----------------------------
DROP TABLE IF EXISTS `portfolio_works`;
CREATE TABLE `portfolio_works` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `name_link` varchar(255) NOT NULL,
  `url` text,
  `url_text` text,
  `release_date` date NOT NULL,
  `description` text,
  `software` text,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `name_link` (`name_link`),
  KEY `release_date` (`release_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
