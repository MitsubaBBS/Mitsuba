<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$menu = array();
$reports = $conn->query("SELECT * FROM reports;")->num_rows;
$appeals = $conn->query("SELECT * FROM appeals;")->num_rows;
$breqs = $conn->query("SELECT * FROM ban_requests;")->num_rows;
$pms = $conn->query("SELECT * FROM pm WHERE to_user=".$_SESSION['id']." AND read_msg=0")->num_rows;

$menu['gen'] = array(
	'name' => $lang['mod/general'],
	'children' => array()
);
$menu['acc'] = array(
	'name' => $lang['mod/account'],
	'children' => array()
);
$menu['adm'] = array(
	'name' => $lang['mod/administration'],
	'children' => array()
);
$menu['brd'] = array(
	'name' => $lang['mod/boards'],
	'children' => array()
);

$menu['gen']['children'][] = array(
	'url' => '?/announcements',
	'name' => $lang['mod/announcements'],
	'show' => $mitsuba->admin->checkPermission("announcements.view")
);
$menu['gen']['children'][] = array(
	'url' => '?/news',
	'name' => $lang['mod/news'],
	'show' => $mitsuba->admin->checkPermission("news.view")
);
$menu['gen']['children'][] = array(
	'url' => '?/notes',
	'name' => $lang['mod/notes'],
	'show' => $mitsuba->admin->checkPermission("notes.view")
);
$menu['gen']['children'][] = array(
	'url' => '?/ipnotes',
	'name' => $lang['mod/ip_notes'],
	'show' => $mitsuba->admin->checkPermission("ipnotes.view")
);
$menu['gen']['children'][] = array(
	'url' => '?/recent/posts',
	'name' => $lang['mod/recent_posts'],
	'show' => $mitsuba->admin->checkPermission("recent.posts")
);
$menu['gen']['children'][] = array(
	'url' => '?/recent/files',
	'name' => $lang['mod/recent_images'],
	'show' => $mitsuba->admin->checkPermission("recent.files")
);
$menu['gen']['children'][] = array(
	'url' => '?/reports',
	'name' => $lang['mod/report_queue'].' ('.$reports.')',
	'show' => $mitsuba->admin->checkPermission("reports.view")
);
$menu['gen']['children'][] = array(
	'url' => '?/bans',
	'name' => $lang['mod/banlist'],
	'show' => $mitsuba->admin->checkPermission("bans.view")
);
$menu['gen']['children'][] = array(
	'url' => '?/warnings',
	'name' => $lang['mod/warnings'],
	'show' => $mitsuba->admin->checkPermission("warnings.view")
);
$menu['gen']['children'][] = array(
	'url' => '?/whitelist',
	'name' => $lang['mod/manage_whitelist'],
	'show' => $mitsuba->admin->checkPermission("whitelist.view")
);
$menu['gen']['children'][] = array(
	'url' => '?/bans/add',
	'name' => $lang['mod/add_ban'],
	'show' => $mitsuba->admin->checkPermission("bans.add")
);
$menu['gen']['children'][] = array(
	'url' => '?/warnings/add',
	'name' => $lang['mod/add_warning'],
	'show' => $mitsuba->admin->checkPermission("warnings.add")
);
$menu['gen']['children'][] = array(
	'url' => '?/ban_requests',
	'name' => $lang['mod/ban_requests'].' ('.$breqs.')',
	'show' => $mitsuba->admin->checkPermission("requests.view")
);
$menu['gen']['children'][] = array(
	'url' => '?/appeals',
	'name' => $lang['mod/appeals'].' ('.$appeals.')',
	'show' => $mitsuba->admin->checkPermission("appeals.view")
);
$menu['gen']['children'][] = array(
	'url' => '?/rangebans',
	'name' => $lang['mod/manage_range_bans'],
	'show' => $mitsuba->admin->checkPermission("range.view")
);
$menu['gen']['children'][] = array(
	'url' => '?/rangebans/add',
	'name' => $lang['mod/add_range_ban'],
	'show' => $mitsuba->admin->checkPermission("range.add")
);
$menu['gen']['children'][] = array(
	'url' => '?/announcements/add',
	'name' => $lang['mod/new_announcement'],
	'show' => $mitsuba->admin->checkPermission("announcements.add")
);
$menu['gen']['children'][] = array(
	'url' => '?/news/add',
	'name' => $lang['mod/add_news'],
	'show' => $mitsuba->admin->checkPermission("news.add")
);
$menu['gen']['children'][] = array(
	'url' => '?/locked',
	'name' => $lang['mod/locked'],
	'show' => $mitsuba->admin->checkPermission("post.closed")
);
$menu['gen']['children'][] = array(
	'url' => '?/sticky',
	'name' => $lang['mod/sticky'],
	'show' => $mitsuba->admin->checkPermission("post.sticky")
);
$menu['acc']['children'][] = array(
	'url' => '?/inbox',
	'name' => $lang['mod/inbox'].' ('.$pms.')',
	'show' => $mitsuba->admin->checkPermission("user.inbox")
);
$menu['acc']['children'][] = array(
	'url' => '?/outbox',
	'name' => $lang['mod/outbox'],
	'show' => $mitsuba->admin->checkPermission("user.inbox")
);
$menu['acc']['children'][] = array(
	'url' => '?/inbox/new',
	'name' => $lang['mod/send_message'],
	'show' => $mitsuba->admin->checkPermission("user.inbox")
);
$menu['acc']['children'][] = array(
	'url' => '?/password',
	'name' => $lang['mod/change_password'],
	'show' => $mitsuba->admin->checkPermission("user.change_password")
);
$menu['adm']['children'][] = array(
	'url' => '?/config',
	'name' => $lang['mod/configuration'],
	'show' => $mitsuba->admin->checkPermission("config.view")
);
$menu['adm']['children'][] = array(
	'url' => '?/boards',
	'name' => $lang['mod/manage_boards'],
	'show' => $mitsuba->admin->checkPermission("boards.view")
);
$menu['adm']['children'][] = array(
	'url' => '?/ads',
	'name' => $lang['mod/manage_ads'],
	'show' => $mitsuba->admin->checkPermission("ads.list")
);
$menu['adm']['children'][] = array(
	'url' => '?/pages',
	'name' => $lang['mod/manage_pages'],
	'show' => $mitsuba->admin->checkPermission("pages.view")
);
$menu['adm']['children'][] = array(
	'url' => '?/links',
	'name' => $lang['mod/manage_board_links'],
	'show' => $mitsuba->admin->checkPermission("links.view")
);
$menu['adm']['children'][] = array(
	'url' => '?/users',
	'name' => $lang['mod/manage_users'],
	'show' => $mitsuba->admin->checkPermission("users.view")
);
$menu['adm']['children'][] = array(
	'url' => '?/news/manage',
	'name' => $lang['mod/manage_news_entries'],
	'show' => $mitsuba->admin->checkPermission("news.manage")
);
$menu['adm']['children'][] = array(
	'url' => '?/announcements/manage',
	'name' => $lang['mod/manage_announcements'],
	'show' => $mitsuba->admin->checkPermission("announcements.manage")
);
$menu['adm']['children'][] = array(
	'url' => '?/bbcodes',
	'name' => $lang['mod/manage_bbcodes'],
	'show' => $mitsuba->admin->checkPermission("bbcodes.view")
);
$menu['adm']['children'][] = array(
	'url' => '?/embeds',
	'name' => $lang['mod/manage_embeds'],
	'show' => $mitsuba->admin->checkPermission("embeds.view")
);
$menu['adm']['children'][] = array(
	'url' => '?/styles',
	'name' => $lang['mod/manage_styles'],
	'show' => $mitsuba->admin->checkPermission("styles.view")
);
$menu['adm']['children'][] = array(
	'url' => '?/wordfilter',
	'name' => $lang['mod/manage_wordfilter'],
	'show' => $mitsuba->admin->checkPermission("wordfilter.view")
);
$menu['adm']['children'][] = array(
	'url' => '?/spamfilter',
	'name' => $lang['mod/manage_spamfilter'],
	'show' => $mitsuba->admin->checkPermission("spamfilter.view")
);
$menu['adm']['children'][] = array(
	'url' => '?/modules',
	'name' => $lang['mod/manage_modules'],
	'show' => $mitsuba->admin->checkPermission("modules.view")
);
$menu['adm']['children'][] = array(
	'url' => '?/message',
	'name' => $lang['mod/global_message'],
	'show' => $mitsuba->admin->checkPermission("config.global_message")
);
$menu['adm']['children'][] = array(
	'url' => '?/rebuild',
	'name' => $lang['mod/rebuild_cache'],
	'show' => $mitsuba->admin->checkPermission("config.rebuild")
);
$menu['adm']['children'][] = array(
	'url' => '?/cleaner',
	'name' => $lang['mod/cleaner'],
	'show' => $mitsuba->admin->checkPermission("config.cleaner")
);
$menu['adm']['children'][] = array(
	'url' => '?/log',
	'name' => $lang['mod/action_log'],
	'show' => $mitsuba->admin->checkPermission("logs.view")
);

$result = $conn->query("SELECT * FROM boards ORDER BY short ASC;");
if ($_SESSION['boards'] != "%")
{
	$boards = explode(",", $_SESSION['boards']);
} else {
	$boards = "%";
}
while ($row = $result->fetch_assoc())
{
	if (($boards == "%") || (in_array($row['short'], $boards)))
	{
		if ($row['hidden']==1)
		{
			$menu['brd']['children'][] = array(
				'url' => '?/board&b='.$row['short'],
				'name' => '/'.$row['short'].'/ - '.$row['name'],
				'show' => true
			);
		} else {
			$menu['brd']['children'][] = array(
				'url' => './'.$row['short'].'/',
				'name' => '/'.$row['short'].'/ - '.$row['name'],
				'show' => true
			);
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Mitsuba Navigation</title>
<meta http-equiv="refresh" content="180" />
<?php
$first_default = 1;
$styles = $conn->query("SELECT * FROM styles ORDER BY `default` DESC");
while ($row = $styles->fetch_assoc())
{
	if ($first_default == 1)
	{
		echo '<link rel="stylesheet" id="switch" href="'.$mitsuba->getPath($row['path'], "index", $row['relative']).'">';
		$first_default = 0;
	}
	echo '<link rel="alternate stylesheet" style="text/css" href="'.$mitsuba->getPath($row['path'], "index", $row['relative']).'" title="'.$row['name'].'">';
}
?>
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
<script type='text/javascript' src='./js/style.js'></script>
</head>
<body id="menu">
<ul>
<li><?php echo $lang['mod/logged_in_as']; ?><b><?php echo $_SESSION['username']; ?></b></li>
<li><?php echo $lang['mod/privileges']; ?><b><?php echo $_SESSION['group_name'] ?></b></li>
<li><a href="?/logout" target="_top"><?php echo $lang['mod/logout']; ?></a></li>
</ul>
<?php
foreach ($menu as $key => $category) {
	?>
<h2><span class="coll" onclick="toggle(this,'<?php echo $key; ?>');" title="Toggle Category">&minus;</span><?php echo $category['name']; ?></h2>
<div id="<?php echo $key; ?>">
<ul>
	<?php
		foreach ($category['children'] as $item)
		{
			if ($item['show'])
			{
				echo '<li><a href="'.$item['url'].'" target="main">'.$item['name'].'</a></li>';
			}
		}
	?>
</ul></div>
<?php
}
?>
</body>
</html>