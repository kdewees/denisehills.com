-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 17, 2013 at 11:15 PM
-- Server version: 5.5.29
-- PHP Version: 5.4.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `denisehills`
--

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '0',
  `user_agent` varchar(50) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ci_sessions`
--

INSERT INTO `ci_sessions` (`session_id`, `ip_address`, `user_agent`, `last_activity`, `user_data`) VALUES
('e5f749c610c1814ffc760eeb76c3245f', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) App', 1374035862, 'a:3:{s:13:"admin_user_id";s:1:"1";s:15:"admin_logged_in";b:1;s:11:"admin_roles";s:0:"";}');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `uri_title` varchar(50) NOT NULL,
  `event_beginning_date` date NOT NULL,
  `event_end_date` date NOT NULL,
  `event_beginning_time` time NOT NULL,
  `event_end_time` time NOT NULL,
  `description` text NOT NULL,
  `cluster` int(11) NOT NULL,
  `display_school_wide` enum('Yes','No') NOT NULL,
  `link` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE `forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `uri_title` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `submit_button_text` varchar(25) NOT NULL,
  `thank_you_page_id` int(11) NOT NULL,
  `description` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `forms`
--

INSERT INTO `forms` (`id`, `name`, `uri_title`, `email`, `submit_button_text`, `thank_you_page_id`, `description`) VALUES
(1, 'Send Us A Message', 'send-us-a-message', 'info@electdenisehills.com', 'Send', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `form_fields`
--

CREATE TABLE `form_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `label` varchar(150) NOT NULL,
  `type` enum('text','textarea','select','checkbox') NOT NULL,
  `instructions` varchar(255) NOT NULL,
  `options` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `form_fields`
--

INSERT INTO `form_fields` (`id`, `form_id`, `label`, `type`, `instructions`, `options`) VALUES
(1, 1, 'Your Name', 'text', '', ''),
(2, 1, 'Your E-mail Address', 'text', '', ''),
(3, 1, 'Your Message to Us', 'textarea', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(150) NOT NULL,
  `page_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `menu_id` int(11) NOT NULL DEFAULT '1',
  `image` varchar(25) NOT NULL,
  `ordinal` int(11) NOT NULL,
  `section` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_id` (`menu_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `description` varchar(100) NOT NULL,
  `uri_title` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `name`, `description`, `uri_title`) VALUES
(1, 'Header Menu', 'on the top of every page', 'header-menu');

-- --------------------------------------------------------

--
-- Table structure for table `pageparts`
--

CREATE TABLE `pageparts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `description` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `uri_title` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `pageparts`
--

INSERT INTO `pageparts` (`id`, `name`, `description`, `content`, `uri_title`) VALUES
(10, 'footer_left', 'footer address', '<h3>Contact Our Campaign</h3>\n<ul>\n<li><div class="icon location">Friends of Denise Hills<br>14 Sherwood Drive<br>Tuscaloosa Alabama&nbsp;35401</div></li>\n<li><div class="icon email"><a href="mailto: info@electdenisehills.com">info@electdenisehills.com</a></div></li>\n</ul>', 'footer_left'),
(11, 'footer_middle', 'the middle spot in the footer', '<h3>Send Us a Message</h3>', 'footer_middle'),
(12, 'footer_right', 'the far right spot on the footer', '<h3>Find Us On the Web...</h3><ul>\n<li><div class="icon facebook"><a href="https://www.facebook.com/DeniseHills.TCS.BOE" target="_blank">Facebook</a></div></li>\n</ul>', 'footer_right'),
(14, 'home1', 'first text section on home page', '<h1>A Letter From Denise</h1>Our children''s education is an investment not only in their future, but ours as well. Children do not have a voice in the electoral process, so we have a responsibility to speak up for what''s best for them.<br><br>A strong foundation in core skills, such as reading, math, science, and civics, is essential to success. "Learning to read" in early grades should progress to "reading to learn" in later grades. The critical thinking skills gained through science, technology, and math education are key to effective problem-solving in later life. Whether our public schools are preparing students for employment immediately after graduation, or for further education at a college or university, or for additional vocational training, the foundations built through an effective public education ensure the success and vitality of our community.<br><br>When we came to Tuscaloosa in 2003, I had not heard positive things about the city schools. I was concerned when my oldest child started Kindergarten that he would not gain the skills necessary to succeed. Therefore, I decided that my mission should be ensuring not only success for my child, but for all children in the Tuscaloosa City Schools. <br><br>I have been a member of the PTA at the schools my children attend, and joined the PTA board. After attending a national PTA conference, I became inspired to volunteer for the Tuscaloosa City PTA Council, where I quickly became president, and also served on the Alabama State PTA Board. I was in this position for the April 27, 2011, tornado, and was grateful to the outpouring of support from across the nation. I was able to pass this support on to affected students and families throughout the city. I attended numerous Board of Education meetings throughout this time, and was impressed with how the BOE came together to speak for all the children throughout the city.<br><br>I have been amazed at the difference I have seen within the schools in the past few years, particularly since the BOE hired our new superintendent. Teachers seem to have faith that the BOE and the superintendent will support them in what''s best for our children. Does this mean the work is done? Far from it! I see the hard work and dedication of the BOE in the past few years as the beginning of a long journey. It takes time to change a mindset, but we are on that path. We need to continue to speak for our children, for their future and ours.', 'home1'),
(15, 'home2', 'photo of denise and family on front page', '<img src="/images/denise_hills_and_family.jpg" width="298" height="385" alt="Denise Hills &amp; Family">', 'home2');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) DEFAULT NULL,
  `uri_title` varchar(150) NOT NULL,
  `description` varchar(150) DEFAULT NULL,
  `content` text,
  `form_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `section` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uri_title` (`uri_title`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title`, `uri_title`, `description`, `content`, `form_id`, `menu_id`, `section`) VALUES
(1, 'Denise Hills - "A Mother''s Voice for Every Child"', 'denise-hills-a-mothers-voice-for-every-child', 'This page is a place-holder for the CMS software. Please do not delete this page! The parts of this page can be edited in "List Page Parts".', 'home', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `slides`
--

CREATE TABLE `slides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `image` varchar(100) NOT NULL,
  `promotion_id` int(11) NOT NULL,
  `heading` varchar(50) NOT NULL,
  `text` varchar(220) NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `uri_title` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `promotion_id` (`promotion_id`,`date_start`,`date_end`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `slides`
--

INSERT INTO `slides` (`id`, `name`, `image`, `promotion_id`, `heading`, `text`, `date_start`, `date_end`, `uri_title`) VALUES
(8, 'mothers in parade pushing strollers', 'lucas_baby_strollers.jpg', 0, '', '', '2013-07-15', '2014-07-15', 'mothers-in-parade-pushing-strollers'),
(9, 'giving a speech about initiative', 'lucas_initiative_speech.jpg', 0, '', '', '2013-07-15', '2014-07-15', 'giving-a-speech-about-initiative'),
(10, 'labor day parade', 'lucas_labor_day_parade.jpg', 0, '', '', '2013-07-15', '2014-07-15', 'labor-day-parade'),
(11, 'with loretta lynn', 'lucas_lynn.jpg', 0, '', '', '2013-07-15', '2014-07-15', 'with-loretta-lynn'),
(12, 'walk to remember', 'lucas_walk_to_remember.jpg', 0, '', '', '2013-07-15', '2014-07-15', 'walk-to-remember');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(100) NOT NULL,
  `roles` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`,`password`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `username`, `password`, `roles`) VALUES
(1, 'Katie', 'Dewees', 'katie@deweesdesigns.com', 'kates77', 'b1dc113bc0a59603da5bdadb2481a475', ''),
(5, 'No', 'One', '', '', 'd41d8cd98f00b204e9800998ecf8427e', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
