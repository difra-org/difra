SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `radio_channels`
-- ----------------------------
DROP TABLE IF EXISTS `radio_channels`;
CREATE TABLE `radio_channels` (
`id` INT (11) NOT NULL AUTO_INCREMENT ,
`mount` VARCHAR (100) NOT NULL ,
`name` VARCHAR (255) NOT NULL ,
`description` TEXT ,
`genre` VARCHAR (255) DEFAULT NULL ,
`siteDescription` TEXT ,
`url` TEXT NOT NULL ,
`bitrate` INT (11) NOT NULL DEFAULT '192' ,
`reencode` TINYINT (4) NOT NULL DEFAULT '1' ,
`samplerate` INT (11) NOT NULL DEFAULT '44100' ,
`port` INT (11) NOT NULL DEFAULT '8000' ,
`password` TEXT NOT NULL ,
`debug` TINYINT (4) NOT NULL DEFAULT '1' ,
`onLine` TINYINT (4) NOT NULL DEFAULT '0' ,
`hostname` TEXT NOT NULL ,
`minArtistInQuery` INT (11) NOT NULL DEFAULT '30' ,
`minSongInQuery` INT (11) NOT NULL DEFAULT '60' ,
`tracksCount` INT (11) NOT NULL DEFAULT '3' ,
PRIMARY KEY (`id`) ,
UNIQUE KEY `mountPointKey` (`mount`) ,
KEY `debugKey` (`debug`) ,
KEY `onLineKey` (`onLine`)) ENGINE = InnoDB DEFAULT CHARSET = utf8;


-- ----------------------------
-- Table structure for `radio_playlist`
-- ----------------------------
DROP TABLE IF EXISTS `radio_playlist`;
CREATE TABLE `radio_playlist` (
`position` TINYINT (4) NOT NULL ,
`channel` VARCHAR (25) NOT NULL ,
`id` INT (11) NOT NULL ,
`group_id` INT (11) NOT NULL ,
`filename` VARCHAR (255) NOT NULL ,
`title` VARCHAR (255) NOT NULL ,
`duration` INT (11) NOT NULL ,
PRIMARY KEY (`position` , `channel`) ,
KEY `channel_key` (`channel`) ,
KEY `track_id_key` (`id`) ,
KEY `artist_id_key` (`group_id`) USING HASH ,
KEY `position_key` (`position`)) ENGINE = MEMORY DEFAULT CHARSET = utf8;

-- ----------------------------
-- Table structure for `radio_temp_data`
-- ----------------------------
DROP TABLE IF EXISTS `radio_temp_data`;
CREATE TABLE `radio_temp_data` (
`channel` VARCHAR (25) NOT NULL ,
`name` VARCHAR (25) NOT NULL ,
`data` VARCHAR (1024) DEFAULT NULL ,
PRIMARY KEY (`channel` , `name`) ,
KEY `channel` (`channel`) ,
KEY `name` (`name`)) ENGINE = MEMORY DEFAULT CHARSET = utf8;

-- ----------------------------
-- Table structure for `radio_tracks`
-- ----------------------------
DROP TABLE IF EXISTS `radio_tracks`;
CREATE TABLE `radio_tracks` (
`id` INT (11) NOT NULL ,
`group_id` INT (11) DEFAULT NULL ,
`channel` VARCHAR (25) NOT NULL ,
`lastPlayed` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' , `played` INT (11) NOT NULL DEFAULT '0' ,
`weight` FLOAT NOT NULL DEFAULT '50' ,
`plays` FLOAT NOT NULL DEFAULT '0' ,
`duration` INT (11) NOT NULL ,
`requested` INT (11) NOT NULL DEFAULT '0' ,
`lastRequest` TIMESTAMP NULL DEFAULT NULL ,
`lastPlayedArtist` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' ,
UNIQUE KEY `id_channel_key` (`id` , `channel`) ,
KEY `channel_key` (`channel`) ,
KEY `id_key` (`id`) ,
KEY `group_id_key` (`group_id`) ,
CONSTRAINT `idtogroup_fkey` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ,
CONSTRAINT `idtotrack_fkey` FOREIGN KEY (`id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE ,
CONSTRAINT `mountPoint_fkey` FOREIGN KEY (`channel`) REFERENCES `radio_channels` (`mount`) ON DELETE CASCADE ON UPDATE CASCADE)
ENGINE = InnoDB DEFAULT CHARSET = utf8;
