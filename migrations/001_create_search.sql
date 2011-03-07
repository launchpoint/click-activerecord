CREATE TABLE IF NOT EXISTS `search` (
  `id` int(11) NOT NULL auto_increment,
  `model_name` varchar(255) NOT NULL,
  `record_id` int(11) NOT NULL,
  `search_text` longtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `model_name` (`model_name`),
  FULLTEXT KEY `ft` (`search_text`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;
