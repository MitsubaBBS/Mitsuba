CREATE TABLE IF NOT EXISTS `ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `board` varchar(10) NOT NULL,
  `position` varchar(10) NOT NULL,
  `text` text NOT NULL,
  `show` int(1) NOT NULL,
  PRIMARY KEY (`id`)
);

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `ban` (`ban_id`, `rangeban`)
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
  `seen` int(1) NOT NULL,
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
  `type` varchar(60) NOT NULL,
  `name` varchar(100) NOT NULL,
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
  `unlisted` int(1) NOT NULL,
  `nodup` int(1) NOT NULL,
  `nofile` int(1) NOT NULL,
  `maxchars` int(8) NOT NULL,
  `multifile` int(2) NOT NULL,
  `anonymous` varchar(60) NOT NULL,
  `extensions` text NOT NULL,
  `catalog` int(1) NOT NULL,
  `captcha` int(1) NOT NULL,
  `overboard_boards` text NOT NULL,
  `allow_replies` int(1) NOT NULL,
  `file_replies` int(1) NOT NULL,
  `links` text NOT NULL,
  `files` int(4) NOT NULL,
  PRIMARY KEY (`short`)
);

CREATE TABLE IF NOT EXISTS `bruteforce_tries` (
  `ip` varchar(50) NOT NULL,
  `mod_id` int(10) NOT NULL,
  `tries` int(30) NOT NULL,
  `lasttry` int(30) NOT NULL,
  PRIMARY KEY (`ip`)
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

CREATE TABLE IF NOT EXISTS `group_permissions` (
  `gid` int(10) NOT NULL,
  `pid` int(10) NOT NULL,
  UNIQUE KEY `rule` (`gid`, `pid`)
);

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `capcode` varchar(60) NOT NULL,
  `capcode_style` varchar(60) NOT NULL,
  `capcode_icon` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
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
  `relative` int(1) NOT NULL,
  `title` varchar(40) NOT NULL,
  `short` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `link` (
  `name` varchar(50) NOT NULL,
  `regex` varchar(200) NOT NULL,
  `parser` varchar(200) NOT NULL,
  UNIQUE KEY `name` (`name`)
);

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `date` int(30) NOT NULL,
  `event` varchar(300) NOT NULL,
  `mod_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `module_boardconfig` (
  `namespace` varchar(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `default_value` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY `name` (`namespace`, `name`)
);

CREATE TABLE IF NOT EXISTS `module_classes` (
  `namespace` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `file` varchar(200) NOT NULL,
  `class` varchar(200) NOT NULL,
  PRIMARY KEY `name` (`name`)
);

CREATE TABLE IF NOT EXISTS `module_config` (
  `namespace` varchar(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `default_value` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY `name` (`namespace`, `name`)
);

CREATE TABLE IF NOT EXISTS `module_events` (
  `namespace` varchar(100) NOT NULL,
  `event` varchar(200) NOT NULL,
  `file` varchar(200) NOT NULL,
  `class` varchar(200) NOT NULL,
  `method` varchar(200) NOT NULL,
  PRIMARY KEY `namespace` (`namespace`, `event`)
);

CREATE TABLE IF NOT EXISTS `module_fields` (
  `namespace` varchar(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `type` varchar(200) NOT NULL,
  PRIMARY KEY `namespace` (`namespace`, `name`, `type`)
);

CREATE TABLE IF NOT EXISTS `module_pages` (
  `namespace` varchar(100) NOT NULL,
  `url` varchar(200) NOT NULL,
  `file` varchar(200) NOT NULL,
  `class` varchar(200) NOT NULL,
  `method` varchar(200) NOT NULL,
  PRIMARY KEY `url` (`url`)
);

CREATE TABLE IF NOT EXISTS `modules` (
  `namespace` varchar(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `author` varchar(100) NOT NULL,
  `version` varchar(50) NOT NULL,
  PRIMARY KEY `namespace` (`namespace`)
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

CREATE TABLE IF NOT EXISTS `permissions_categories` (
  `id` int(10) NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
);

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(10) NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` varchar(60) NOT NULL,
  `category` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
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
  `raw` int(1) NOT NULL,
  `capcode_style` varchar(60) NOT NULL,
  `capcode_text` varchar(60) NOT NULL,
  `capcode_icon` varchar(100) NOT NULL,
  `deleted` int(30) NOT NULL,
  PRIMARY KEY (`board`, `id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `rangebans` (
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
  `relative` int(1) NOT NULL,
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
  `salt` varchar(20) NOT NULL,
  `group` int(10) NOT NULL,
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
  `seen` int(1) NOT NULL,
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
('boardLinks_board', ''),
('boardLinks_thread', ''),
('boardLinks', ''),
('frontpage_menu_url', 'menu.html'),
('frontpage_style', 'kusabalike.php'),
('frontpage_url', 'index.html'),
('global_message', ''),
('news_url', 'news.html'),
('sitename', 'Mitsuba'),
('enable_api', '0'),
('enable_rss', '0'),
('enable_meny', '0'),
('keep_hours', '6'),
('caching_mode', '0');

INSERT INTO `embeds` (`name`, `regex`, `code`) VALUES
('dailymotion', '/http(s)?:\\/\\/(www\\.)?dailymotion\\.com\\/video\\/([^&]+)/', '<iframe width="%1$s" height=""%1$s" src="http://www.dailymotion.com/embed/video/"%4$s" frameborder="0" allowfullscreen></iframe>'),
('liveleak', '/http(s)?:\\/\\/(www\\.)?liveleak\\.com\\/view\\?i=([^&]+)/', '<iframe width="%1$s" height="%1$s" src="http://www.liveleak.com/e/%4$s" frameborder="0" allowfullscreen></iframe>'),
('vimeo', '/http(s)?:\\/\\/(www\\.)?vimeo\\.com\\/([0-9]+)/', '<iframe width="%1$s" height="%1$s" src="http://player.vimeo.com/video/%4$s" frameborder="0" allowfullscreen></iframe>'),
('youtube', '/http(s)?:\\/\\/(www\\.)?youtube\\.com\\/watch\\?v=([^&]+)/', '<iframe width="%1$s" height="%1$s" src="http://www.youtube.com/embed/%4$s" frameborder="0" allowfullscreen></iframe>'),
('youtu.be', '/http(s)?:\\/\\/(www\\.)?youtu\\.be\\/([^&]+)/', '<iframe width="%1$s" height="%1$s" src="http://www.youtube.com/embed/%4$s" frameborder="0" allowfullscreen></iframe>');

INSERT INTO `extensions` (`ext`, `name`, `mimetype`, `image`, `default`) VALUES
('jpg', 'JPEG Image', 'image/jpeg', 1, 1),
('png', 'PNG Image', 'image/png', 1, 1),
('gif', 'GIF Image', 'image/gif', 1, 1),
('mp3', 'MP3 Audio File', 'audio/mpeg', 0, 0),
('mp3', 'MP3 Audio File', 'audio/mp3', 0, 0),
('mp3', 'MP3 Audio File', 'audio/mpg', 0, 0),
('wav', 'WAV Audio File', 'audio/wav', 0, 0),
('mp3', 'MP3 Audio File', 'audio/x-mpeg', 0, 0),
('mp3', 'MP3 Audio File', 'audio/x-mp3', 0, 0),
('mp3', 'MP3 Audio File', 'audio/x-mpg', 0, 0),
('wav', 'WAV Audio File', 'audio/x-wav', 0, 0),
('swf', 'Flash Application', 'application/x-shockwave-flash', 0, 0),
('mp4', 'MP4 Video File', 'video/mp4', 0, 0),
('mpg', 'MPG Video File', 'video/mpeg', 0, 0),
('webm', 'WEBM Video File', 'video/webm', 0, 0),
('avi', 'AVI Video File', 'video/avi', 0, 0),
('mkv', 'Matroska Video File', 'video/x-matroska', 0, 0);

INSERT INTO `groups` (`id`, `name`, `capcode`, `capcode_style`, `capcode_icon`) VALUES
(1, 'Janitor', '', '', ''),
(2, 'Moderator', 'Mod', 'color:#800080', './img/mod.png'),
(3, 'Administrator', 'Admin', 'color:#FF0000', './img/admin.png'),
(4, 'Disabled', '', '', '');

INSERT INTO `permissions_categories` (`id`, `name`, `description`) VALUES
(0, 'ads', 'Advertisements'),
(1, 'announcements', 'Announcements'),
(2, 'appeals', 'Appeals'),
(3, 'bans', 'Bans'),
(4, 'bbcodes', 'BBCodes'),
(5, 'boards', 'Boards'),
(6, 'config', 'Configuration'),
(7, 'embeds', 'Embed management'),
(8, 'info', 'IP information'),
(9, 'ipnotes', 'IP Notes'),
(10, 'links', 'Links'),
(11, 'logs', 'Logs'),
(12, 'news', 'News'),
(13, 'notes', 'Notes'),
(14, 'post', 'Post management'),
(15, 'range', 'Range bans'),
(16, 'recent', 'Recent'),
(17, 'reports', 'Reports'),
(18, 'requests', 'Requests'),
(19, 'search', 'Search'),
(20, 'spamfilter', 'Spamfilter'),
(21, 'styles', 'Styles'),
(22, 'user', 'Account'),
(23, 'users', 'Users'),
(24, 'warnings', 'Warnings'),
(25, 'whitelist', 'Whitelist'),
(26, 'wordfilter', 'Wordfilter'),
(27, 'pages', 'Pages'),
(28, 'modules', 'Modules'),
(29, 'module', 'Module permissions');

INSERT INTO `permissions` (`id`, `name`, `description`, `category`) VALUES
(0, 'ads.add', 'Create advertisements', 0),
(1, 'ads.delete', 'Delete advertisements', 0),
(2, 'ads.list', 'List advertisements', 0),
(3, 'ads.update', 'Update advertisements', 0),
(4, 'announcements.add', 'Add announcements', 1),
(5, 'announcements.delete', 'Delete announcements', 1),
(6, 'announcements.delete.own', 'Delete own announcements', 1),
(7, 'announcements.manage', 'Manage announcements', 1),
(8, 'announcements.update', 'Update announcements', 1),
(9, 'announcements.update.own', 'Update own annoucements', 1),
(10, 'announcements.view', 'View announcements', 1),
(11, 'appeals.clear.all', 'Clear all appeals', 2),
(12, 'appeals.clear.single', 'Clear single appeal', 2),
(13, 'appeals.view', 'View appeals', 2),
(14, 'bans.add', 'Add bans', 3),
(15, 'bans.delete', 'Delete bans', 3),
(16, 'bans.view', 'View bans', 3),
(17, 'bbcodes.add', 'Add BBCodes', 4),
(18, 'bbcodes.delete', 'Delete BBCodes', 4),
(19, 'bbcodes.edit', 'Edit BBCodes', 4),
(20, 'bbcodes.view', 'View BBCodes', 4),
(21, 'boards.add', 'Add boards', 5),
(22, 'boards.delete', 'Delete boards', 5),
(23, 'boards.move', 'Move boards', 5),
(24, 'boards.rebuild', 'Rebuild boards', 5),
(25, 'boards.update', 'Update boards', 5),
(26, 'boards.view', 'View boards', 5),
(27, 'config.cleaner', 'Use cleaner', 6),
(28, 'config.extras', 'Use config extras', 6),
(29, 'config.global_message', 'Change global message', 6),
(30, 'config.reset', 'Reset configuration', 6),
(31, 'config.rebuild', 'Rebuild cache', 6),
(32, 'config.update', 'Update configuration', 6),
(33, 'config.view', 'View configuration', 6),
(34, 'embeds.add', 'Add embeds', 7),
(35, 'embeds.delete', 'Delete embeds', 7),
(36, 'embeds.edit', 'Update embeds', 7),
(37, 'embeds.view', 'View embeds', 7),
(38, 'info.view', 'View IP information', 8),
(39, 'ipnotes.add', 'Add IP notes', 9),
(40, 'ipnotes.delete', 'Delete IP notes', 9),
(41, 'ipnotes.view', 'View IP notes', 9),
(42, 'links.add', 'Add board links', 10),
(43, 'links.delete', 'Delete board links', 10),
(44, 'links.move', 'Change board links position', 10),
(45, 'links.update', 'Update board links', 10),
(46, 'links.view', 'View board links', 10),
(47, 'logs.view', 'View action logs', 11),
(48, 'news.add', 'Add news', 12),
(49, 'news.delete', 'Delete news', 12),
(50, 'news.delete.own', 'Delete own news', 12),
(51, 'news.manage', 'Manage news', 12),
(52, 'news.update', 'Update news', 12),
(53, 'news.update.own', 'Update own news', 12),
(54, 'news.view', 'View news', 12),
(55, 'notes.add', 'Add notes', 13),
(56, 'notes.delete', 'Delete notes', 13),
(57, 'notes.view', 'View notes', 13),
(58, 'post.antibump', 'Enable antibump on posts', 14),
(59, 'post.capcode', 'Use capcode', 14),
(60, 'post.customcapcode', 'Use custom capcode', 14),
(61, 'post.closed', 'Close threads', 14),
(62, 'post.delete.ip', 'Delete posts from IP', 14),
(63, 'post.delete.single', 'Delete single post', 14),
(64, 'post.edit', 'Edit posts', 14),
(65, 'post.sticky', 'Sticky threads', 14),
(66, 'post.viewip', 'View IP', 14),
(67, 'range.add', 'Add range bans', 15),
(68, 'range.delete', 'Delete range bans', 15),
(69, 'range.view', 'View range bans', 15),
(70, 'recent.files', 'View recent files', 16),
(71, 'recent.posts', 'View recent posts', 16),
(72, 'reports.clear.all', 'Clear all reports', 17),
(73, 'reports.clear.multiple', 'Clear multiple reports (D_WTIP, D_WTR)', 17),
(74, 'reports.clear.single', 'Clear single report', 17),
(75, 'reports.view', 'View reporst', 17),
(76, 'requests.delete', 'Delete ban requests', 18),
(77, 'requests.view', 'View ban requests', 18),
(78, 'search.ip', 'Search by IP', 19),
(79, 'search.text', 'Search by text', 19),
(80, 'spamfilter.add', 'Add spamfilter', 20),
(81, 'spamfilter.delete', 'Delete spamfilter', 20),
(82, 'spamfilter.update', 'Update spamfilter', 20),
(83, 'spamfilter.view', 'View spamfilter', 20),
(84, 'styles.delete', 'Delete styles', 21),
(85, 'styles.update', 'Update styles', 21),
(86, 'styles.upload', 'Upload styles', 21),
(87, 'styles.view', 'View styles', 21),
(88, 'user.change_password', 'Change own password', 22),
(89, 'user.inbox', 'Inbox', 22),
(90, 'user.login', 'Log in', 22),
(91, 'users.add', 'Add users', 23),
(92, 'users.delete', 'Delete users', 23),
(93, 'users.update', 'Update users', 23),
(94, 'users.view', 'View users', 23),
(95, 'warnings.add', 'Add warnings', 24),
(96, 'warnings.delete', 'Delete warnings', 24),
(97, 'warnings.view', 'View warnings', 24),
(98, 'whitelist.add', 'Add whitelist', 25),
(99, 'whitelist.delete', 'Delete whitelist', 25),
(100, 'whitelist.view', 'View whitelist', 25),
(101, 'wordfilter.add', 'Add wordfilter', 26),
(102, 'wordfilter.delete', 'Delete wordfilter', 26),
(103, 'wordfilter.update', 'Update wordfilter', 26),
(104, 'wordfilter.view', 'View wordfilter', 26),
(105, 'bans.add.request', 'Add ban requests', 3),
(106, 'post.ignorespamfilter', 'Ignore spamfilter', 14),
(107, 'post.ignorebumplimit', 'Ignore bumplimit', 14),
(108, 'post.ignoresizelimit', 'Ignore size limit', 14),
(109, 'post.raw', 'Raw HTML', 14),
(110, 'post.nofile', 'No file', 14),
(111, 'post.fakeid', 'Fake ID', 14),
(112, 'post.ignorespamlimits', 'Ignore spamlimits', 14),
(113, 'post.ignorenoname', 'Ignore noname', 14),
(114, 'post.ignorenodup', 'Ignore nodup', 14),
(115, 'post.ignorecaptcha', 'Ignore CAPTCHA', 14),
(116, 'pages.view', 'View pages', 27),
(117, 'pages.update', 'Update pages', 27),
(118, 'pages.delete', 'Delete pages', 27),
(119, 'pages.add', 'Add pages', 27),
(120, 'modules.view', 'View modules', 28),
(121, 'modules.upload', 'Upload modules', 28),
(122, 'modules.install', 'Install modules', 28),
(123, 'modules.uninstall', 'Uninstall modules', 28),
(124, 'modules.delete', 'Delete modules', 28),
(125, 'modules.config', 'Configure modules', 28);

INSERT INTO `group_permissions` (`gid`, `pid`) VALUES
(1, 10),
(1, 14),
(1, 38),
(1, 41),
(1, 54),
(1, 66),
(1, 70),
(1, 71),
(1, 75),
(1, 88),
(1, 89),
(1, 90),
(1, 105),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 38),
(2, 39),
(2, 4),
(2, 40),
(2, 41),
(2, 48),
(2, 50),
(2, 51),
(2, 53),
(2, 54),
(2, 55),
(2, 56),
(2, 57),
(2, 58),
(2, 59),
(2, 6),
(2, 61),
(2, 62),
(2, 63),
(2, 65),
(2, 66),
(2, 67),
(2, 69),
(2, 7),
(2, 70),
(2, 71),
(2, 72),
(2, 73),
(2, 74),
(2, 75),
(2, 76),
(2, 77),
(2, 78),
(2, 88),
(2, 89),
(2, 9),
(2, 90),
(2, 95),
(2, 96),
(2, 97),
(2, 105),
(2, 106),
(2, 107),
(2, 108),
(2, 109),
(2, 110),
(2, 111),
(2, 112),
(2, 113),
(2, 114),
(2, 115),
(3, 0),
(3, 1),
(3, 10),
(3, 100),
(3, 101),
(3, 102),
(3, 103),
(3, 104),
(3, 105),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(3, 15),
(3, 16),
(3, 17),
(3, 18),
(3, 19),
(3, 2),
(3, 20),
(3, 21),
(3, 22),
(3, 23),
(3, 24),
(3, 25),
(3, 26),
(3, 27),
(3, 28),
(3, 29),
(3, 3),
(3, 30),
(3, 31),
(3, 32),
(3, 33),
(3, 34),
(3, 35),
(3, 36),
(3, 37),
(3, 38),
(3, 39),
(3, 4),
(3, 40),
(3, 41),
(3, 42),
(3, 43),
(3, 44),
(3, 45),
(3, 46),
(3, 47),
(3, 48),
(3, 49),
(3, 5),
(3, 50),
(3, 51),
(3, 52),
(3, 53),
(3, 54),
(3, 55),
(3, 56),
(3, 57),
(3, 58),
(3, 59),
(3, 6),
(3, 60),
(3, 61),
(3, 62),
(3, 63),
(3, 64),
(3, 65),
(3, 66),
(3, 67),
(3, 68),
(3, 69),
(3, 7),
(3, 70),
(3, 71),
(3, 72),
(3, 73),
(3, 74),
(3, 75),
(3, 76),
(3, 77),
(3, 78),
(3, 8),
(3, 80),
(3, 81),
(3, 82),
(3, 83),
(3, 84),
(3, 85),
(3, 86),
(3, 87),
(3, 88),
(3, 89),
(3, 9),
(3, 90),
(3, 91),
(3, 92),
(3, 93),
(3, 94),
(3, 95),
(3, 96),
(3, 97),
(3, 98),
(3, 99),
(3, 106),
(3, 107),
(3, 108),
(3, 109),
(3, 110),
(3, 111),
(3, 112),
(3, 113),
(3, 114),
(3, 115),
(3, 116),
(3, 117),
(3, 118),
(3, 119),
(3, 120),
(3, 121),
(3, 122),
(3, 123),
(3, 124),
(3, 125);

INSERT INTO `styles` (`name`, `path`, `relative`, `default`) VALUES 
('Mitsuba', './styles/mitsuba.css', 1, 1),
('Mitsuba Blue', './styles/mitsubablue.css', 1, 0);

INSERT INTO `link` (`name`, `regex`, `parser`) VALUES
('dailymotion', '/http(s)?:\\/\\/(www\\.)?dailymotion\\.com\\/video\\/([^&]+)/', 'dailymotion.php'),
('liveleak', '/http(s)?:\\/\\/(www\\.)?liveleak\\.com\\/view\\?i=([^&]+)/', 'liveleak.php'),
('vimeo', '/http(s)?:\\/\\/(www\\.)?vimeo\\.com\\/([0-9]+)/', 'vimeo.php'),
('youtube', '/http(s)?:\\/\\/(www\\.)?youtube\\.com\\/watch\\?v=([^&]+)/', 'youtube.php'),
('youtu.be', '/http(s)?:\\/\\/(www\\.)?youtu\\.be\\/([^&]+)/', 'youtube.php'),
('rapidshare_link', '/http(s)?:\\/\\/rapidshare.com\\/files\\/([0-9]+)\\/(\w+)/', 'rapidshare.php');