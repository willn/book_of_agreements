
DROP TABLE IF EXISTS `agreements`;
CREATE TABLE `agreements` (
  `id` int(6) NOT NULL auto_increment,
  `title` text,
  `summary` text,
  `full` text,
  `background` text,
  `comments` text,
  `processnotes` text,
  `cid` smallint(6) default NULL,
  `date` date default NULL,
  `surpassed_by` bigint(1) NOT NULL default '0',
  `expired` bigint(1) NOT NULL default '0',
  `world_public` tinyint(4) default '0',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `fti_prop` (`title`,`summary`,`full`,`background`,`comments`,`processnotes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `committees`;
CREATE TABLE `committees` (
  `cid` smallint(6) NOT NULL auto_increment,
  `parent` smallint(6) default NULL,
  `cmty` varchar(40) default NULL,
  `listname` varchar(20) default 'none',
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `minutes`;
CREATE TABLE `minutes` (
  `m_id` int(11) NOT NULL auto_increment,
  `notes` varchar(255) default NULL,
  `agenda` text,
  `content` text,
  `cid` smallint(6) default NULL,
  `date` date default NULL,
  PRIMARY KEY  (`m_id`),
  FULLTEXT KEY `fti_mins` (`notes`,`agenda`,`content`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

