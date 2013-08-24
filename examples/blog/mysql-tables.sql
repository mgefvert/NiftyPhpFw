-- MySQL dump 10.13  Distrib 5.5.8, for Win32 (x86)
--
-- Host: localhost    Database: example
-- ------------------------------------------------------
-- Server version	5.5.8

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `blog_comments`
--

DROP TABLE IF EXISTS `blog_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog_comments` (
  `bc_id` int(11) NOT NULL AUTO_INCREMENT,
  `bc_entry` int(11) NOT NULL,
  `bc_createdUTC` datetime NOT NULL,
  `bc_ip` varchar(32) DEFAULT NULL,
  `bc_text` text NOT NULL,
  `bc_signed` varchar(255) DEFAULT NULL,
  `bc_email` varchar(255) NOT NULL,
  PRIMARY KEY (`bc_id`),
  KEY `bc_entry` (`bc_entry`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_comments`
--

LOCK TABLES `blog_comments` WRITE;
/*!40000 ALTER TABLE `blog_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_entries`
--

DROP TABLE IF EXISTS `blog_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog_entries` (
  `b_id` int(11) NOT NULL AUTO_INCREMENT,
  `b_createdUTC` datetime NOT NULL,
  `b_title` varchar(255) NOT NULL,
  `b_text` longtext NOT NULL,
  `b_user` int(11) NOT NULL,
  PRIMARY KEY (`b_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_entries`
--

LOCK TABLES `blog_entries` WRITE;
/*!40000 ALTER TABLE `blog_entries` DISABLE KEYS */;
INSERT INTO `blog_entries` VALUES (61,'2011-05-05 14:11:37','My First Blog Post','Hi everyone,<div><br></div><div>This is the first blog post written in this little example system.</div><div><br></div><div>A blog is only one of many, many things that can be written in Nifty PHP Framework. In fact, Nifty provides only a couple of classes to build upon, but using these classes allows you to write beautiful PHP code. There are handy tools available for escaping output, security features, handling form submission and lots of many other things - and in added to that, we\'ve given you a complete Model - View - Controller pattern to work with (but very loosely enforced - you can write code any way you want).</div><div><br></div><div>The framework itself isn\'t very large - by the latest count, the code takes up just under 300 kb of space, but with this, you have the possibility to write enterprise-level apps (and we have!).</div><div><br></div><div>So, check out this demonstration. All the code specific for this application is in the /app folder.</div><div><br></div><div>Take care!</div>',1);
/*!40000 ALTER TABLE `blog_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_settings`
--

DROP TABLE IF EXISTS `blog_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog_settings` (
  `bs_key` varchar(20) NOT NULL,
  `bs_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`bs_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_settings`
--

LOCK TABLES `blog_settings` WRITE;
/*!40000 ALTER TABLE `blog_settings` DISABLE KEYS */;
INSERT INTO `blog_settings` VALUES ('title','A Nifty Blog Example'),('url','http://localhost/');
/*!40000 ALTER TABLE `blog_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `u_id` int(11) NOT NULL AUTO_INCREMENT,
  `u_username` varchar(40) NOT NULL,
  `u_fullname` varchar(80) NOT NULL,
  `u_email` varchar(255) DEFAULT NULL,
  `u_password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`u_id`),
  KEY `u_username` (`u_username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','Administrator','admin@localhost','$1$H}drKZ]A$px9E7Ed9iov4YNYCDlslw0');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-05-05 11:31:33
