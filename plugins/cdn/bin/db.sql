CREATE TABLE `cdn_hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(255) NOT NULL,
  `port` int(11) NOT NULL DEFAULT '1080',
  `status` varchar(15) NOT NULL DEFAULT 'nottested',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `failed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked` timestamp NULL DEFAULT NULL,
  `selected` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `host_key` (`host`,`port`) USING HASH,
  KEY `status_key` (`status`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cdn_hosts_work` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(255) NOT NULL,
  `port` int(11) NOT NULL DEFAULT '1080',
  `status` char(15) NOT NULL DEFAULT 'nottested',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `failed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked` timestamp NULL DEFAULT NULL,
  `selected` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `host_key` (`host`,`port`) USING HASH,
  KEY `status_key` (`status`) USING HASH
) ENGINE=MEMORY DEFAULT CHARSET=utf8;