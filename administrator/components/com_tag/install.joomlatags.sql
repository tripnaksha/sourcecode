CREATE TABLE IF NOT EXISTS `#__tag_term` (
  `id` int(10) unsigned NOT NULL auto_increment,  
  `name` varchar(255) NOT NULL default '',
  `description` text,
  `weight` tinyint(4) NOT NULL default '0',
  `hits` int(10) NOT NULL default '0',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),  
  KEY `id_tag` (`id`,`name`),
  KEY `id_hits` (`id`,`hits`),
  KEY `term_hits` (`hits`),
  KEY `term_created` (`created`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `#__tag_term_content` (
  `tid` int(10) unsigned NOT NULL default '0',
  `cid` int(10) unsigned NOT NULL default '0',  
  PRIMARY KEY  (`tid`,`cid`),
  KEY `cid` (`cid`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
