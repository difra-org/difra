--
-- This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
-- Copyright © A-Jam Studio
-- License: http://ajamstudio.com/difra/license
--

CREATE TABLE `config` (
  `config` longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vault` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` longblob,
  PRIMARY KEY (`id`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;