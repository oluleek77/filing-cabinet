-- phpMyAdmin SQL Dump
-- version 2.11.3deb1ubuntu1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 09, 2009 at 11:28 AM
-- Server version: 5.0.51
-- PHP Version: 5.2.4-2ubuntu5.3
--
-- Database that goes with filing-cabinet project
-- http://code.google.com/p/filing-cabinet
--

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
  `filename` varchar(100) character set utf8 collate utf8_bin NOT NULL,
  `type` varchar(50) NOT NULL default 'unknown',
  `size` double NOT NULL default '0',
  `uploaded` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `owner` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL,
  `permissions` smallint(6) NOT NULL default '0',
  `next_file_id` int(10) default NULL,
  PRIMARY KEY  (`id`),
  KEY `filename` (`filename`),
  KEY `owner` (`owner`),
  KEY `next_file_id` (`next_file_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `Labels`
--

CREATE TABLE IF NOT EXISTS `Labels` (
  `file_id` int(8) NOT NULL,
  `label_name` varchar(100) character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`file_id`,`label_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE IF NOT EXISTS `Users` (
  `username` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL,
  `password` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL,
  `email` varchar(250) character set ascii default NULL,
  PRIMARY KEY  (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
