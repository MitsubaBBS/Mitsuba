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
  `reason` text NOT NULL,
  `note` text NOT NULL,
  `created` int(30) NOT NULL,
  `expires` int(30) NOT NULL,
  `appeal` int(30) NOT NULL,
  `boards` text NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `ban_requests` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `mod_id` int(10) NOT NULL,
  `reason` varchar(80) NOT NULL,
  `note` text NOT NULL,
  `created` int(30) NOT NULL,
  `board` varchar(10) NOT NULL,
  `post` int(20) NOT NULL,
  `append` int(1) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `bbcodes` (
  `name` varchar(100) NOT NULL,
  `code` text NOT NULL,
  UNIQUE KEY `name` (`name`)
);

CREATE TABLE IF NOT EXISTS `boards` (
  `short` varchar(10) NOT NULL,
  `name` varchar(40) NOT NULL,
  `des` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `bumplimit` int(9) NOT NULL,
  `spoilers` int(1) NOT NULL,
  `noname` int(1) NOT NULL,
  `ids` int(1) NOT NULL,
  `embeds` int(1) NOT NULL,
  `bbcode` int(1) NOT NULL,
  `time_between_posts` int(20) NOT NULL,
  `time_between_threads` int(20) NOT NULL,
  `time_to_delete` int(20) NOT NULL,
  `filesize` int(20) NOT NULL,
  `pages` int(4) NOT NULL,
  `hidden` int(1) NOT NULL,
  `nodup` int(1) NOT NULL,
  `maxchars` int(8) NOT NULL,
  `multifile` int(2) NOT NULL,
  `anonymous` varchar(60) NOT NULL,
  `extensions` text NOT NULL,
  PRIMARY KEY (`short`)
);

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  UNIQUE KEY `name` (`name`)
);

CREATE TABLE IF NOT EXISTS `embeds` (
  `name` varchar(50) NOT NULL,
  `regex` varchar(100) NOT NULL,
  `code` text NOT NULL,
  UNIQUE KEY `name` (`name`)
);

CREATE TABLE IF NOT EXISTS `extensions` (
  `ext` varchar(8) NOT NULL,
  `name` varchar(100) NOT NULL,
  `mimetype` varchar(100) NOT NULL,
  `image` text NOT NULL,
  `default` int(1) NOT NULL,
  UNIQUE KEY `mimetype` (`mimetype`)
);

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

CREATE TABLE IF NOT EXISTS `pages` (
  `name` varchar(60) NOT NULL,
  `title` varchar(200) NOT NULL,
  `text` text NOT NULL,
  `raw` int(1) NOT NULL,
  PRIMARY KEY (`name`)
);

CREATE TABLE IF NOT EXISTS `pm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(30) NOT NULL,
  `from_user` int(10) NOT NULL,
  `to_user` int(10) NOT NULL,
  `title` varchar(70) NOT NULL,
  `text` text NOT NULL,
  `read_msg` int(1) NOT NULL,
  `resto` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `posts` (
  `board` varchar(10) NOT NULL,
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `date` int(30) NOT NULL,
  `name` varchar(60) NOT NULL,
  `trip` varchar(30) NOT NULL,
  `strip` varchar(11) NOT NULL,
  `poster_id` varchar(8) NOT NULL,
  `email` varchar(60) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `password` varchar(100) NOT NULL,
  `orig_filename` text NOT NULL,
  `filename` text NOT NULL,
  `resto` int(20) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `lastbumped` int(20) NOT NULL,
  `filehash` text NOT NULL,
  `orig_filesize` text NOT NULL,
  `filesize` text NOT NULL,
  `imagesize` text NOT NULL,
  `mimetype` text NOT NULL,
  `t_w` text NOT NULL,
  `t_h` text NOT NULL,
  `sticky` int(1) NOT NULL,
  `sage` int(1) NOT NULL,
  `locked` int(1) NOT NULL,
  `capcode` int(1) NOT NULL,
  `raw` int(1) NOT NULL,
  `cc_color` varchar(12) NOT NULL,
  `cc_text` varchar(50) NOT NULL,
  PRIMARY KEY (`board`, `id`)
) ENGINE=MyISAM;

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

CREATE TABLE IF NOT EXISTS `spamfilter` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `search` varchar(100) NOT NULL,
  `reason` varchar(100) NOT NULL,
  `boards` text NOT NULL,
  `expires` varchar(90) NOT NULL,
  `active` int(1) NOT NULL,
  `regex` int(1) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `styles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `path` varchar(60) NOT NULL,
  `path_thread` varchar(60) NOT NULL,
  `path_index` varchar(60) NOT NULL,
  `default` int(1) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `tripcodes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `hash` varchar(11) NOT NULL,
  `replace` varchar(30) NOT NULL,
  `secure` int (1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`, `secure`)
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

CREATE TABLE IF NOT EXISTS `warnings` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `mod_id` int(10) NOT NULL,
  `reason` text NOT NULL,
  `note` text NOT NULL,
  `created` int(30) NOT NULL,
  `shown` int(1) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `whitelist` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `mod_id` int(10) NOT NULL,
  `note` text NOT NULL,
  `nolimits` int(1) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `wordfilter` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `search` varchar(100) NOT NULL,
  `replace` varchar(100) NOT NULL,
  `boards` text NOT NULL,
  `active` int(1) NOT NULL,
  `regex` int(1) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `bbcodes` (`name`, `code`) VALUES
('spoiler', '<s>{param}</s>');

INSERT INTO `config` (`name`, `value`) VALUES
('boardLinks', ''),
('boardLinks_thread', ''),
('boardLinks_index', ''),
('frontpage_menu_url', 'menu.html'),
('frontpage_style', '0'),
('frontpage_url', 'index.html'),
('global_message', ''),
('news_url', 'news.html'),
('sitename', 'Mitsuba'),
('enable_api', '0'),
('enable_rss', '0'),
('caching_mode', '0'),
('overboard_enabled', '0'),
('overboard_boards', ''),
('overboard_name', '*');

INSERT INTO `embeds` (`name`, `regex`, `code`) VALUES
('dailymotion', '/http(s)?:\\/\\/(www\\.)?dailymotion\\.com\\/video\\/([^&]+)/', '<iframe width="%1$s" height=""%1$s" src="http://www.dailymotion.com/embed/video/"%4$s" frameborder="0" allowfullscreen></iframe>'),
('liveleak', '/http(s)?:\\/\\/(www\\.)?liveleak\\.com\\/view\\?i=([^&]+)/', '<iframe width="%1$s" height="%1$s" src="http://www.liveleak.com/e/%4$s" frameborder="0" allowfullscreen></iframe>'),
('vimeo', '/http(s)?:\\/\\/(www\\.)?vimeo\\.com\\/([0-9]+)/', '<iframe width="%1$s" height="%1$s" src="http://player.vimeo.com/video/%4$s" frameborder="0" allowfullscreen></iframe>'),
('youtube', '/http(s)?:\\/\\/(www\\.)?youtube\\.com\\/watch\\?v=([^&]+)/', '<iframe width="%1$s" height="%1$s" src="http://www.youtube.com/embed/%4$s" frameborder="0" allowfullscreen></iframe>'),
('youtu.be', '/http(s)?:\\/\\/(www\\.)?youtu\\.be\\/([^&]+)/', '<iframe width="%1$s" height="%1$s" src="http://www.youtube.com/embed/%4$s" frameborder="0" allowfullscreen></iframe>');

INSERT INTO `extensions` (`ext`, `name`, `mimetype`, `image`, `default`) VALUES
('jpg', 'JPEG Image', 'image/jpeg', '1', 1),
('png', 'PNG Image', 'image/png', '1', 1),
('gif', 'GIF Image', 'image/gif', '1', 1),
('mp3', 'MP3 Audio File', 'audio/mpeg', '0', 0),
('mp3', 'MP3 Audio File', 'audio/mp3', '0', 0),
('mp3', 'MP3 Audio File', 'audio/mpg', '0', 0),
('wav', 'WAV Audio File', 'audio/wav', '0', 0),
('mp3', 'MP3 Audio File', 'audio/x-mpeg', '0', 0),
('mp3', 'MP3 Audio File', 'audio/x-mp3', '0', 0),
('mp3', 'MP3 Audio File', 'audio/x-mpg', '0', 0),
('wav', 'WAV Audio File', 'audio/x-wav', '0', 0),
('swf', 'Flash Application', 'application/x-shockwave-flash', '0', 0),
('mp4', 'MP4 Video File', 'video/mp4', '0', 0),
('mpg', 'MPG Video File', 'video/mpeg', '0', 0),
('webm', 'WEBM Video File', 'video/webm', '0', 0),
('avi', 'AVI Video File', 'video/avi', '0', 0),
('mkv', 'Matroska Video File', 'video/x-matroska', '0', 0);

INSERT INTO `styles` (`name`, `path`, `path_thread`, `path_index`, `default`) VALUES 
('Yotsuba', '../styles/stylesheet.css', '../../styles/stylesheet.css', './styles/stylesheet.css', '1'),
('Yotsuba Blue', '../styles/yotsubablue.css', '../../styles/yotsubablue.css', './styles/yotsubablue.css', '0');