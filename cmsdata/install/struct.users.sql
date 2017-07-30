CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `theme` varchar(255) NOT NULL default 'none',
  `posts` int(11) NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `subscriber` tinyint(1) NOT NULL default '0',
  `mod` tinyint(1) NOT NULL,
  `editor` tinyint(1) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `joined` int(11) NOT NULL,
  `lastlogin` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user` (`user`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;