-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.14-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             11.0.0.5919
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table tagme.betelgeuse
CREATE TABLE IF NOT EXISTS `betelgeuse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `project_id` int(11) NOT NULL DEFAULT 0,
  `post_id` int(11) NOT NULL DEFAULT 0,
  `timestamp` int(11) NOT NULL DEFAULT 0,
  `old_tags` varchar(5000) NOT NULL DEFAULT '',
  `new_tags` varchar(5000) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `FK_betelgeuse_user` (`user_id`),
  KEY `FK_betelgeuse_project` (`project_id`),
  CONSTRAINT `FK_betelgeuse_project` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`),
  CONSTRAINT `FK_betelgeuse_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table tagme.comment
CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `added_on` int(11) NOT NULL DEFAULT 0,
  `edited_on` int(11) NOT NULL DEFAULT 0,
  `content` mediumtext NOT NULL DEFAULT '',
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `FK_comment_project` (`project_id`),
  KEY `FK_comment_user` (`user_id`),
  CONSTRAINT `FK_comment_project` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`),
  CONSTRAINT `FK_comment_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table tagme.project
CREATE TABLE IF NOT EXISTS `project` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `meta` varchar(16) DEFAULT NULL,
  `user` int(11) NOT NULL DEFAULT 0,
  `desc` varchar(255) NOT NULL DEFAULT '',
  `text` varchar(10000) DEFAULT NULL,
  `tags` longtext DEFAULT NULL CHECK (json_valid(`tags`)),
  `optmode` int(1) DEFAULT 0,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `is_private` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`project_id`) USING BTREE,
  UNIQUE KEY `id` (`project_id`) USING BTREE,
  KEY `FK_projects_users` (`user`) USING BTREE,
  CONSTRAINT `FK_project_user` FOREIGN KEY (`user`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table tagme.project_changes
CREATE TABLE IF NOT EXISTS `project_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `changes` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `FK_project_changes_project` (`project_id`),
  KEY `FK_project_changes_user` (`user_id`),
  CONSTRAINT `FK_project_changes_project` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`),
  CONSTRAINT `FK_project_changes_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table tagme.user
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(32) DEFAULT NULL,
  `rank` tinyint(1) NOT NULL DEFAULT 0,
  `strikes` int(11) NOT NULL DEFAULT 0,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`) USING BTREE,
  UNIQUE KEY `userID` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table tagme.user_tokens
CREATE TABLE IF NOT EXISTS `user_tokens` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `token` char(32) DEFAULT NULL,
  PRIMARY KEY (`user_id`) USING BTREE,
  UNIQUE KEY `userID` (`user_id`) USING BTREE,
  CONSTRAINT `FK_tokens_users` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
