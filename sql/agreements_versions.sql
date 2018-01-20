CREATE TABLE `agreements_versions` (
  `vers_id` int(6) NOT NULL auto_increment,
  `updated_date` datetime default NULL,
  `agr_version_num` int(6) NOT NULL,
  `diff_comment` text,
  `agr_id` int(6) NOT NULL,
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
  PRIMARY KEY  (`vers_id`),
  FULLTEXT KEY `fti_prop`
	(`title`,`summary`,`full`,`background`,`comments`,`processnotes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
