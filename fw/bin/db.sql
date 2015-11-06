CREATE TABLE `config` (
  `config` LONGBLOB
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `vault` (
  `id`      INT(11)   NOT NULL AUTO_INCREMENT,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data`    LONGBLOB,
  PRIMARY KEY (`id`),
  KEY `created` (`created`) USING BTREE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
