
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `badistes` (
  `id` int(11) NOT NULL auto_increment,
  `pseudo` text NOT NULL,
  `mail_addr` text NOT NULL,
  `mot2passe` text NOT NULL,
  `statut` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `date2bad` (
  `id_date` int(11) NOT NULL auto_increment,
  `date` date NOT NULL default '0000-00-00',
  `C1` int(11) NOT NULL default '0',
  `C2` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `resa` (
  `id_res` int(11) NOT NULL auto_increment,
  `id_j` int(11) NOT NULL default '0',
  `date` date NOT NULL default '0000-00-00',
  `creneau` tinyint(4) NOT NULL default '0',
  `ext` tinyint(1) NOT NULL default '0',
  `nom` text NOT NULL,
  `rien` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id_res`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;