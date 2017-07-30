CREATE TABLE IF NOT EXISTS `settingsb` (
  `id` int(11) NOT NULL auto_increment,
  `sitename` varchar(255) NOT NULL,
  `tagline` varchar(255) NOT NULL,
  `siteaddress` varchar(255) NOT NULL,
  `supportemail` varchar(255) NOT NULL,
  `abouttext` text NOT NULL,
  `adscript` text NOT NULL,
  `sitenote` text NOT NULL,
  `theme` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
