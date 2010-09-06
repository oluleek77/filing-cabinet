-- phpMyAdmin SQL Dump
-- version 2.11.3deb1ubuntu1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 21, 2009 at 09:24 AM
-- Server version: 5.0.51
-- PHP Version: 5.2.4-2ubuntu5.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `filingcabinet`
--

-- --------------------------------------------------------

--
-- Table structure for table `Files`
--

CREATE TABLE IF NOT EXISTS `Files` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `filename` varchar(100) collate utf8_unicode_ci NOT NULL,
  `type` varchar(50) collate utf8_unicode_ci NOT NULL default 'unknown',
  `size` double NOT NULL default '0',
  `uploaded` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `downloads` bigint(20) unsigned NOT NULL default '0',
  `owner` varchar(50) collate utf8_unicode_ci NOT NULL,
  `permissions` smallint(6) NOT NULL default '0',
  `next_file_id` int(10) default NULL,
  PRIMARY KEY  (`id`),
  KEY `filename` (`filename`),
  KEY `owner` (`owner`),
  KEY `next_file_id` (`next_file_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Table structure for table `Labels`
--

CREATE TABLE IF NOT EXISTS `Labels` (
  `file_id` int(8) NOT NULL,
  `label_name` varchar(100) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`file_id`,`label_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Sequence`
--

CREATE TABLE IF NOT EXISTS `Sequence` (
  `file_id` int(8) NOT NULL,
  `next_file_id` int(8) NOT NULL,
  PRIMARY KEY  (`file_id`,`next_file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE IF NOT EXISTS `Users` (
  `username` varchar(50) collate utf8_unicode_ci NOT NULL,
  `password` varchar(50) collate utf8_unicode_ci NOT NULL,
  `email` varchar(250) character set ascii NOT NULL,
  `activationHash` varchar(150) collate utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`username`)
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `activationHash` (`activationHash`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
