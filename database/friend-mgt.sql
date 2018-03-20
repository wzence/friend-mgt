-- phpMyAdmin SQL Dump
-- version 4.4.1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost:8889
-- Generation Time: Mar 20, 2018 at 09:16 AM
-- Server version: 5.5.42
-- PHP Version: 5.6.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `friend-mgt`
--

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `email_address` varchar(256) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `email_address`) VALUES
(1, 'andy@example.com'),
(2, 'john@example.com'),
(3, 'sandy@example.com'),
(4, 'alvin@example.com'),
(5, 'bill@example.com'),
(6, 'jonathan@example.com'),
(7, 'victor@example.com'),
(8, 'brandon@example.com'),
(9, 'victor@example.com'),
(10, 'brandon@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `user_relationship`
--

CREATE TABLE `user_relationship` (
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `block` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_relationship`
--

INSERT INTO `user_relationship` (`user_id`, `friend_id`, `block`) VALUES
(1, 2, 'N'),
(1, 3, 'N'),
(1, 4, 'N'),
(1, 9, 'N'),
(1, 10, 'N'),
(2, 4, 'N'),
(2, 5, 'N'),
(2, 6, 'N'),
(2, 9, 'N'),
(5, 2, 'N'),
(7, 2, 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `user_subscribe_update`
--

CREATE TABLE `user_subscribe_update` (
  `user_id` int(11) NOT NULL,
  `subscribed_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_subscribe_update`
--

INSERT INTO `user_subscribe_update` (`user_id`, `subscribed_user_id`) VALUES
(1, 2),
(5, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_relationship`
--
ALTER TABLE `user_relationship`
  ADD UNIQUE KEY `relationship_index` (`user_id`,`friend_id`),
  ADD KEY `friend_id_foreign_key` (`friend_id`);

--
-- Indexes for table `user_subscribe_update`
--
ALTER TABLE `user_subscribe_update`
  ADD UNIQUE KEY `subscription_index` (`user_id`,`subscribed_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_relationship`
--
ALTER TABLE `user_relationship`
  ADD CONSTRAINT `friend_id_foreign_key` FOREIGN KEY (`friend_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_id_foreign_key` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_subscribe_update`
--
ALTER TABLE `user_subscribe_update`
  ADD CONSTRAINT `subcribed_user_id_index` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_id_index` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
