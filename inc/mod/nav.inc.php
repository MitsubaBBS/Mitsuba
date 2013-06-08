<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$reports = $conn->query("SELECT * FROM reports;");
	$reports = $reports->num_rows;
	$appeals = $conn->query("SELECT * FROM appeals;");
	$appeals = $appeals->num_rows;
	$breqs = $conn->query("SELECT * FROM ban_requests;");
	$breqs = $breqs->num_rows;
	$pms = $conn->query("SELECT * FROM pm WHERE to_user=".$_SESSION['id']." AND read_msg=0");
	$pms = $pms->num_rows;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Mitsuba Navigation</title>
<meta http-equiv="refresh" content="180" />
<link rel="stylesheet" href="./styles/menu.css" />
<script type="text/javascript">
function toggle(button,area) {
	var tog=document.getElementById(area);
	if(tog.style.display)	{
		tog.style.display="";
	}	else {
		tog.style.display="none";
	}
	button.innerHTML=(tog.style.display)?'+':'&minus;';
	createCookie('nav_show_'+area, tog.style.display?'0':'1', 365);
}
</script>
</head>
<body>
<ul>
<li><?php echo $lang['mod/logged_in_as']; ?><b><?php echo $_SESSION['username']; ?></b></li>
<li><?php echo $lang['mod/privileges']; ?><b><?php if ($_SESSION['type']==3) { echo $lang['mod/administrator']; } elseif ($_SESSION['type']==2) { echo $lang['mod/moderator']; } elseif ($_SESSION['type']==1) { echo $lang['mod/janitor']; } else { echo $lang['mod/faggot']; } ?></b></li>
<li><a href="?/logout" target="_top"><?php echo $lang['mod/logout']; ?></a></li>
</ul>
<h2><span class="coll" onclick="toggle(this,'gen');" title="Toggle Category">&minus;</span><?php echo $lang['mod/general']; ?></h2>
<div id="gen" style="">
<ul>
<li><a href="?/announcements" target="main"><?php echo $lang['mod/announcements']; ?></a></li>
<li><a href="?/news" target="main"><?php echo $lang['mod/news']; ?></a></li>
<li><a href="?/notes" target="main"><?php echo $lang['mod/notes']; ?></a></li>
<li><a href="?/ipnotes" target="main"><?php echo $lang['mod/ip_notes']; ?></a></li>
<li><a href="?/recent/posts" target="main"><?php echo $lang['mod/recent_posts']; ?></a></li>
<li><a href="?/recent/files" target="main"><?php echo $lang['mod/recent_images']; ?></a></li>
<li><a href="?/reports" target="main"><?php echo $lang['mod/report_queue']; ?> (<?php echo $reports; ?>)</a></li>
<li><a href="?/bans" target="main"><?php echo $lang['mod/banlist']; ?></a></li>
<li><a href="?/warnings" target="main"><?php echo $lang['mod/warnings']; ?></a></li>
<?php
echo runHooks("menu", null);
if ($_SESSION['type'] >= 2)
{
?>
<li><a href="?/whitelist" target="main"><?php echo $lang['mod/manage_whitelist']; ?></a></li>
<li><a href="?/bans/add" target="main"><?php echo $lang['mod/add_ban']; ?></a></li>
<li><a href="?/warnings/add" target="main"><?php echo $lang['mod/add_warning']; ?></a></li>
<li><a href="?/ban_requests" target="main"><?php echo $lang['mod/ban_requests']; ?> (<?php echo $breqs; ?>)</a></li>
<li><a href="?/appeals" target="main"><?php echo $lang['mod/appeals']; ?> (<?php echo $appeals; ?>)</a></li>
<li><a href="?/announcements/add" target="main"><?php echo $lang['mod/new_announcement']; ?></a></li>
<li><a href="?/news/add" target="main"><?php echo $lang['mod/add_news']; ?></a></li>
<li><a href="?/locked" target="main"><?php echo $lang['mod/locked']; ?></a></li>
<li><a href="?/sticky" target="main"><?php echo $lang['mod/sticky']; ?></a></li>
<?php
}
?>
</ul></div>
<h2><span class="coll" onclick="toggle(this,'acc');" title="Toggle Category">&minus;</span><?php echo $lang['mod/account']; ?></h2>
<div id="acc" style="">
<ul>
<li><a href="?/inbox" target="main"><?php echo $lang['mod/inbox']; ?> (<?php echo $pms; ?>)</a></li>
<li><a href="?/outbox" target="main"><?php echo $lang['mod/outbox']; ?></a></li>
<li><a href="?/inbox/new" target="main"><?php echo $lang['mod/send_message']; ?></a></li>
<li><a href="?/password" target="main"><?php echo $lang['mod/change_password']; ?></a></li>
</ul></div>
<?php
if ($_SESSION['type'] >= 3)
{
?>
<h2><span class="coll" onclick="toggle(this,'adm');" title="Toggle Category">&minus;</span><?php echo $lang['mod/administration']; ?></h2>
<div id="adm" style="">
<ul>
<li><a href="?/config" target="main"><?php echo $lang['mod/configuration']; ?></a></li>
<li><a href="?/boards" target="main"><?php echo $lang['mod/manage_boards']; ?></a></li>
<li><a href="?/pages" target="main"><?php echo $lang['mod/pages']; ?></a></li>
<li><a href="?/links" target="main"><?php echo $lang['mod/manage_board_links']; ?></a></li>
<li><a href="?/users" target="main"><?php echo $lang['mod/manage_users']; ?></a></li>
<li><a href="?/news/manage" target="main"><?php echo $lang['mod/manage_news_entries']; ?></a></li>
<li><a href="?/announcements/manage" target="main"><?php echo $lang['mod/manage_announcements']; ?></a></li>
<li><a href="?/bbcodes" target="main"><?php echo $lang['mod/manage_bbcodes']; ?></a></li>
<li><a href="?/embeds" target="main"><?php echo $lang['mod/manage_embeds']; ?></a></li>
<li><a href="?/styles" target="main"><?php echo $lang['mod/manage_styles']; ?></a></li>
<li><a href="?/wordfilter" target="main"><?php echo $lang['mod/manage_wordfilter']; ?></a></li>
<li><a href="?/spamfilter" target="main"><?php echo $lang['mod/manage_spamfilter']; ?></a></li>
<li><a href="?/range" target="main"><?php echo $lang['mod/manage_range_bans']; ?></a></li>
<li><a href="?/message" target="main"><?php echo $lang['mod/global_message']; ?></a></li>
<li><a href="?/rebuild" target="main"><?php echo $lang['mod/rebuild_cache']; ?></a></li>
<li><a href="?/cleaner" target="main"><?php echo $lang['mod/cleaner']; ?></a></li>
<li><a href="?/log" target="main"><?php echo $lang['mod/action_log']; ?></a></li>
</ul></div>
<?php
}
?>
<h2><span class="coll" onclick="toggle(this,'brd');" title="Toggle Category">&minus;</span><?php echo $lang['mod/boards']; ?></h2>
<div id="brd" style="">
<ul>
<?php
$result = $conn->query("SELECT * FROM boards ORDER BY short ASC;");
if (($_SESSION['boards'] != "*") && ($_SESSION['type'] != 3))
{
$boards = explode(",", $_SESSION['boards']);
} else {
$boards = "*";
}
while ($row = $result->fetch_assoc())
{
if (($boards == "*") || (in_array($row['short'], $boards)))
{
echo '<li><a href="?/board&b='.$row['short'].'" target="main">/'.$row['short'].'/ - '.$row['name'].'</a></li>';
}
}
?>
</ul></div>
</body>
</html>