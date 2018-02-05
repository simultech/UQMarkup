# ************************************************************
# UQMarkup Database
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table Activities
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Activities`;

CREATE TABLE `Activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_role_users_id` int(11) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `submission_id` int(11) DEFAULT NULL,
  `meta` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `SUBMISSION_INDEX` (`submission_id`),
  KEY `STATE_INDEX` (`state_id`),
  KEY `STATE AND SUBMISSION` (`submission_id`,`state_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Adminusers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Adminusers`;

CREATE TABLE `Adminusers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email` int(11) DEFAULT NULL,
  `super` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Aggregatedmarks
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Aggregatedmarks`;

CREATE TABLE `Aggregatedmarks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) DEFAULT NULL,
  `marks` longtext,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Assignedstudents
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Assignedstudents`;

CREATE TABLE `Assignedstudents` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `courseroleuser_id` int(11) DEFAULT NULL,
  `marker_id` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Attachments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Attachments`;

CREATE TABLE `Attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) DEFAULT NULL,
  `path` text,
  `title` text,
  `type` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Course_Role_Users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Course_Role_Users`;

CREATE TABLE `Course_Role_Users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Courses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Courses`;

CREATE TABLE `Courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coursecode` text,
  `uid` text,
  `name` text,
  `shadowcode` text,
  `semester` int(11) DEFAULT '2',
  `year` int(11) DEFAULT '2012',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Logs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Logs`;

CREATE TABLE `Logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `logtype_id` int(11) DEFAULT NULL,
  `submission_id` int(11) DEFAULT NULL,
  `interaction` text,
  `meta` longtext,
  `platform` text,
  `version` text,
  `sessionhash` varchar(255) DEFAULT NULL,
  `runhash` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `USER_ID` (`user_id`),
  KEY `SUBMISSION_ID` (`submission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Logtypes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Logtypes`;

CREATE TABLE `Logtypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Marks
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Marks`;

CREATE TABLE `Marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) DEFAULT NULL,
  `ruberic_id` int(11) DEFAULT NULL,
  `value` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Projects
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Projects`;

CREATE TABLE `Projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `name` text,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `description` longtext,
  `created` datetime DEFAULT NULL,
  `option_group_project` int(11) DEFAULT '0',
  `option_multiple_markers` int(11) DEFAULT '0',
  `option_downloadable` int(11) DEFAULT '0',
  `option_autopublish` int(11) DEFAULT '0',
  `option_disable_autoassign` int(11) DEFAULT '0',
  `option_gradescaling` float DEFAULT '1',
  `option_gradeprecision` int(11) DEFAULT '0',
  `option_gradeshown` int(1) DEFAULT '1',
  `option_gradefunction` text,
  `option_gradeusefunction` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Roles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Roles`;

CREATE TABLE `Roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Rubrics
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Rubrics`;

CREATE TABLE `Rubrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `name` text,
  `type` text,
  `section` text,
  `meta` longtext,
  `order` int(11) DEFAULT '99',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Rubrics_copy
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Rubrics_copy`;

CREATE TABLE `Rubrics_copy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `name` text,
  `type` text,
  `section` text,
  `meta` longtext,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table States
# ------------------------------------------------------------

DROP TABLE IF EXISTS `States`;

CREATE TABLE `States` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Submissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Submissions`;

CREATE TABLE `Submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table surveyresults
# ------------------------------------------------------------

DROP TABLE IF EXISTS `surveyresults`;

CREATE TABLE `surveyresults` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question_id` text NOT NULL,
  `answer` text,
  `survey_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Tags`;

CREATE TABLE `Tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `name` text,
  `color` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Users`;

CREATE TABLE `Users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uqid` text,
  `name` text,
  `email` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `termsagreed` int(11) DEFAULT '0',
  `pastfeedback` longtext,
  `futurefeedback` longtext,
  PRIMARY KEY (`id`),
  KEY `UQID_INDEX` (`uqid`(12))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Versions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Versions`;

CREATE TABLE `Versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) DEFAULT NULL,
  `path` text,
  `meta` longtext,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
