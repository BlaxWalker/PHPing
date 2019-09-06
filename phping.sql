-- --------------------------------------------------------
-- Host:                         idapp22
-- Server version:               5.7.25-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for phping
CREATE DATABASE IF NOT EXISTS `phping` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `phping`;

-- Dumping structure for table phping.ip
CREATE TABLE IF NOT EXISTS `ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `priority` tinyint(1) NOT NULL,
  `lastupdate` datetime NOT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `remarks` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='monitor level:\r\n0: not monitored\r\n1: first priority\r\n2: less priority\r\n3: lesser priority\r\n4: lesser and lesser priority\r\n5: and so on....';

-- Data exporting was unselected.
-- Dumping structure for table phping.log
CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_id` int(11) DEFAULT NULL,
  `time` datetime NOT NULL,
  `duration` smallint(6) DEFAULT NULL,
  `result` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_log_ip` (`ip_id`),
  CONSTRAINT `FK_log_ip` FOREIGN KEY (`ip_id`) REFERENCES `ip` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
