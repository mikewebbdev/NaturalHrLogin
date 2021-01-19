-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 19, 2021 at 01:42 PM
-- Server version: 5.7.31
-- PHP Version: 7.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `naturalhr`
--

-- --------------------------------------------------------

--
-- Table structure for table `nhrusers`
--

DROP TABLE IF EXISTS `nhrusers`;
CREATE TABLE IF NOT EXISTS `nhrusers` (
  `userID` varchar(32) NOT NULL,
  `userFirst` varchar(11) NOT NULL,
  `userSecond` varchar(11) NOT NULL,
  `userEmail` varchar(255) NOT NULL,
  `userPass` varchar(255) NOT NULL,
  `userStatus` smallint(11) NOT NULL,
  `userRole` smallint(11) NOT NULL,
  `userRecovery` varchar(11) NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
