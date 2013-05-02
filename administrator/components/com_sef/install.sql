CREATE TABLE IF NOT EXISTS `#__sefurls` (
  `id` int(11) NOT NULL auto_increment,
  `cpt` int(11) NOT NULL default '0',
  `sefurl` varchar(255) NOT NULL,
  `origurl` varchar(255) NOT NULL,
  `Itemid` varchar(20) default NULL,
  `metadesc` varchar(255) default '',
  `metakey` varchar(255) default '',
  `metatitle` varchar(255) default '',
  `metalang` varchar(30) default '',
  `metarobots` varchar(30) default '',
  `metagoogle` varchar(30) default '',
  `canonicallink` varchar(255) default '',
  `dateadd` date NOT NULL default '0000-00-00',
  `priority` int(11) NOT NULL DEFAULT '0',
  `trace` text DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `sefurl` (`sefurl`),
  KEY `origurl` (`origurl`, `Itemid`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `#__sefexts` (
  `id` int(11) NOT NULL auto_increment,
  `file` varchar(100) NOT NULL,
  `filters` text,
  `params` text,
  `title` varchar(255),
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `#__sefmoved` (
  `id` int(11) NOT NULL auto_increment,
  `old` varchar(255) NOT NULL,
  `new` varchar(255) NOT NULL,
  `lastHit` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `old` (`old`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `#__sefexttexts` (
  `id` int(11) NOT NULL auto_increment,
  `extension` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `value` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
