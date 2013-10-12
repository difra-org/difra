CREATE TABLE `sape` (
  `key` varchar(1000) DEFAULT NULL,
  `value` varchar(5000) DEFAULT NULL,
  KEY `key` (`key`(255)) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8;