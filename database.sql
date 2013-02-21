CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` int(30) NOT NULL,
  `who` varchar(40) NOT NULL,
  `title` varchar(80) NOT NULL,
  `text` text NOT NULL,
  `mod_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `appeals` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `created` int(30) NOT NULL,
  `ban_id` int(30) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `msg` text NOT NULL,
  `email` varchar(70) NOT NULL,
  `rangeban` int(1) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `bans` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `mod_id` int(10) NOT NULL,
  `reason` varchar(80) NOT NULL,
  `note` text NOT NULL,
  `created` int(30) NOT NULL,
  `expires` int(30) NOT NULL,
  `boards` text NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `boards` (
  `short` varchar(10) NOT NULL,
  `name` varchar(40) NOT NULL,
  `des` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `bumplimit` int(9) NOT NULL,
  PRIMARY KEY (`short`)
);

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  UNIQUE KEY `name` (`name`)
);

INSERT INTO `config` (`name`, `value`) VALUES
('boardLinks', ''),
('boardLinks_thread', ''),
('frontpage_menu_url', 'menu.html'),
('frontpage_style', '0'),
('frontpage_url', 'index.html'),
('global_message', ''),
('news_url', 'news.html'),
('sitename', 'Mitsuba'),
('styles', ''),
('styles_thread', '');

CREATE TABLE IF NOT EXISTS `ip_notes` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `text` text NOT NULL,
  `created` int(30) NOT NULL,
  `mod_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `links` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `parent` int(5) NOT NULL,
  `url` varchar(100) NOT NULL,
  `url_thread` varchar(100) NOT NULL,
  `url_index` varchar(100) NOT NULL,
  `title` varchar(40) NOT NULL,
  `short` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `date` int(30) NOT NULL,
  `event` varchar(300) NOT NULL,
  `mod_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` int(30) NOT NULL,
  `who` varchar(40) NOT NULL,
  `title` varchar(80) NOT NULL,
  `text` text NOT NULL,
  `mod_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `mod_id` int(10) NOT NULL,
  `note` text NOT NULL,
  `created` int(30) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `pm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(30) NOT NULL,
  `from_user` int(10) NOT NULL,
  `to_user` int(10) NOT NULL,
  `title` varchar(70) NOT NULL,
  `text` text NOT NULL,
  `read_msg` int(1) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `rangebans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(30) NOT NULL,
  `expires` int(30) NOT NULL,
  `start_ip` int(50) NOT NULL,
  `end_ip` int(50) NOT NULL,
  `reason` text NOT NULL,
  `mod_id` int(10) NOT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporter_ip` varchar(50) NOT NULL,
  `reported_post` int(20) NOT NULL,
  `reason` text NOT NULL,
  `created` int(30) NOT NULL,
  `board` varchar(6) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `styles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `path` varchar(60) NOT NULL,
  `path_thread` varchar(60) NOT NULL,
  `default` int(1) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL,
  `password` varchar(130) NOT NULL,
  `type` int(1) NOT NULL,
  `boards` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
);

CREATE TABLE IF NOT EXISTS `wordfilter` (
  `search` varchar(100) NOT NULL,
  `replace` varchar(100) NOT NULL,
  `active` int(1) NOT NULL
);
