<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$permissions = $mitsuba->admin->listPermissions();
$menu_categories = array();
$menu_items = array();
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
	'name' => '?/announcements',
	'url' => $lang['mod/announcements'],
	'show' => $mitsuba->admin->checkPermission("announcements.view", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/news',
	'url' => $lang['mod/news'],
	'show' => $mitsuba->admin->checkPermission("news.view", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/notes',
	'url' => $lang['mod/notes'],
	'show' => $mitsuba->admin->checkPermission("notes.view", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/ipnotes',
	'url' => $lang['mod/ip_notes'],
	'show' => $mitsuba->admin->checkPermission("ipnotes.view", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/recent/posts',
	'url' => $lang['mod/recent_posts'],
	'show' => $mitsuba->admin->checkPermission("recent.posts", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/recent/files',
	'url' => $lang['mod/recent_images'],
	'show' => $mitsuba->admin->checkPermission("recent.files", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/reports',
	'url' => $lang['mod/report_queue'].' ('.$reports.')',
	'show' => $mitsuba->admin->checkPermission("reports.view", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/bans',
	'url' => $lang['mod/banlist'],
	'show' => $mitsuba->admin->checkPermission("bans.view", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/warnings',
	'url' => $lang['mod/warnings'],
	'show' => $mitsuba->admin->checkPermission("warnings.view", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/whitelist',
	'url' => $lang['mod/manage_whitelist'],
	'show' => $mitsuba->admin->checkPermission("whitelist.view", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/bans/add',
	'url' => $lang['mod/add_ban'],
	'show' => $mitsuba->admin->checkPermission("bans.add", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/warnings/add',
	'url' => $lang['mod/add_warning'],
	'show' => $mitsuba->admin->checkPermission("warnings.add", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/ban_requests',
	'url' => $lang['mod/ban_requests'].' ('.$breqs.')',
	'show' => $mitsuba->admin->checkPermission("requests.view", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/appeals',
	'url' => $lang['mod/appeals'].' ('.$appeals.')',
	'show' => $mitsuba->admin->checkPermission("appeals.view", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/rangebans',
	'url' => $lang['mod/manage_range_bans'],
	'show' => $mitsuba->admin->checkPermission("rangebans.view", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/rangebans/add',
	'url' => $lang['mod/add_range_ban'],
	'show' => $mitsuba->admin->checkPermission("rangebans.add", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/announcements/add',
	'url' => $lang['mod/new_announcement'],
	'show' => $mitsuba->admin->checkPermission("announcements.add", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/news/add',
	'url' => $lang['mod/add_news'],
	'show' => $mitsuba->admin->checkPermission("news.add", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/locked',
	'url' => $lang['mod/locked'],
	'show' => $mitsuba->admin->checkPermission("post.closed", $permissions)
);
$menu['gen']['children'][] = array(
	'name' => '?/sticky',
	'url' => $lang['mod/sticky'],
	'show' => $mitsuba->admin->checkPermission("post.sticky", $permissions)
);
$menu['acc']['children'][] = array(
	'name' => '?/inbox',
	'url' => $lang['mod/inbox'].' ('.$pms.')',
	'show' => $mitsuba->admin->checkPermission("user.inbox", $permissions)
);
$menu['acc']['children'][] = array(
	'name' => '?/outbox',
	'url' => $lang['mod/outbox'],
	'show' => $mitsuba->admin->checkPermission("user.inbox", $permissions)
);
$menu['acc']['children'][] = array(
	'name' => '?/inbox/new',
	'url' => $lang['mod/send_message'],
	'show' => $mitsuba->admin->checkPermission("user.inbox", $permissions)
);
$menu['acc']['children'][] = array(
	'name' => '?/password',
	'url' => $lang['mod/change_password'],
	'show' => $mitsuba->admin->checkPermission("user.change_password", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/config',
	'url' => $lang['mod/configuration'],
	'show' => $mitsuba->admin->checkPermission("config.view", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/boards',
	'url' => $lang['mod/manage_boards'],
	'show' => $mitsuba->admin->checkPermission("boards.view", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/ads',
	'url' => $lang['mod/manage_ads'],
	'show' => $mitsuba->admin->checkPermission("ads.list", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/pages',
	'url' => $lang['mod/manage_pages'],
	'show' => $mitsuba->admin->checkPermission("pages.view", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/links',
	'url' => $lang['mod/manage_board_links'],
	'show' => $mitsuba->admin->checkPermission("links.view", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/users',
	'url' => $lang['mod/manage_users'],
	'show' => $mitsuba->admin->checkPermission("users.view", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/news/manage',
	'url' => $lang['mod/manage_news_entries'],
	'show' => $mitsuba->admin->checkPermission("news.manage", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/announcements/manage',
	'url' => $lang['mod/manage_announcements'],
	'show' => $mitsuba->admin->checkPermission("announcements.manage", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/bbcodes',
	'url' => $lang['mod/manage_bbcodes'],
	'show' => $mitsuba->admin->checkPermission("bbcodes.view", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/embeds',
	'url' => $lang['mod/manage_embeds'],
	'show' => $mitsuba->admin->checkPermission("embeds.view", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/styles',
	'url' => $lang['mod/manage_styles'],
	'show' => $mitsuba->admin->checkPermission("styles.view", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/wordfilter',
	'url' => $lang['mod/manage_wordfilter'],
	'show' => $mitsuba->admin->checkPermission("wordfilter.view", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/spamfilter',
	'url' => $lang['mod/manage_spamfilter'],
	'show' => $mitsuba->admin->checkPermission("spamfilter.view", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/message',
	'url' => $lang['mod/global_message'],
	'show' => $mitsuba->admin->checkPermission("config.global_message", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/rebuild',
	'url' => $lang['mod/rebuild_cache'],
	'show' => $mitsuba->admin->checkPermission("config.rebuild", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/cleaner',
	'url' => $lang['mod/cleaner'],
	'show' => $mitsuba->admin->checkPermission("config.cleaner", $permissions)
);
$menu['adm']['children'][] = array(
	'name' => '?/log',
	'url' => $lang['mod/action_log'],
	'show' => $mitsuba->admin->checkPermission("logs.view", $permissions)
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
				'name' => '/'.$row['short'].'/ - '.$row['name'],
				'url' => '?/board&b='.$row['short'],
				'show' => true
			);
		} else {
			$menu['brd']['children'][] = array(
				'name' => '/'.$row['short'].'/ - '.$row['name'],
				'url' => './'.$row['short'].'/',
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