<?php
if (!file_exists("./config.php"))
{
header("Location: ./install.php");
}

define("IN_MOD", TRUE);

session_start();

if ((!empty($_SESSION['logged'])) && (!empty($_SESSION['cookie_set'])) && ($_SESSION['cookie_set']==2))
{
	setcookie('in_mod', $_SESSION['type'], 0);
	$_SESSION['cookie_set']=1;
}
include("config.php");
include("version.php");
include("inc/strings/mod.strings.php");
include("inc/strings/imgboard.strings.php");
include("inc/strings/log.strings.php");
include("inc/common.php");
include("inc/common.caching.php");
include("inc/common.posting.php");
include("inc/admin.common.php");
include("inc/admin.users.php");
include("inc/admin.bans.php");
include("inc/admin.caching.php");
include("inc/admin.boards.php");
include("inc/admin.boards.links.php");
include("inc/common.plugins.php");

function getBoardList($conn, $boards = "")
{
	global $lang;
	if ($boards == "*")
	{
	?>
	<?php echo $lang['mod/boards']; ?>: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1 checked/> <?php echo $lang['mod/all']; ?><br/>
	<select name="boards[]" id="boardSelect" multiple style="display: none;">
	<?php
	} else {
	?>
	<?php echo $lang['mod/boards']; ?>: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1/> <?php echo $lang['mod/all']; ?><br/>
	<select name="boards[]" id="boardSelect" multiple>
	<?php
	}
	?>
	<?php
	if (($boards != "*") && ($boards != "")) { $boards = substr($boards, 0, strlen($boards) - 1); }
	$result = $conn->query("SELECT * FROM boards;");
	while ($row = $result->fetch_assoc())
	{
	$checked = "";
	if (($boards !== "*") && ($boards !== ""))
	{
		if (in_array($boards, $row['short']))
		{
			$checked = " checked ";
		}
	}
	echo "<option onClick='document.getElementById(\"all\").checked=false;' value='".$row['short']."'".$checked.">/".$row['short']."/ - ".$row['name']."</option>";
	}
	?>
	</select>
	<?php
}

function logAction($conn, $text)
{
	$conn->query("DELETE FROM log WHERE date<".(time()-(60*60*24*7)));
	$text = $conn->real_escape_string($text);
	$conn->query("INSERT INTO log (date, event, mod_id) VALUES (".time().", '".$text."', ".$_SESSION['id'].")");
}

function deleteEntry($conn, $type, $id, $validate_id = 0)
{
	if (!is_numeric($id))
	{
		return -1;
	}
	$table = "";
	if ($type == 0) { $table = "announcements"; }
	if ($type == 1) { $table = "news"; }
	
	if ($validate_id == 1)
	{
		$result = $conn->query("SELECT * FROM ".$table." WHERE id=".$id);
		$entry = $result->fetch_assoc();
		if ($entry['mod_id'] == $_SESSION['id'])
		{
			$conn->query("DELETE FROM ".$table." WHERE id=".$id);
		}
	} else {
		$conn->query("DELETE FROM ".$table." WHERE id=".$id);
	}

	if ($type == 1) { $cacher->generateNews(); }
}

function updateEntry($conn, $type, $id, $who, $title, $text, $validate_id = 0)
{
	if (!is_numeric($id))
	{
		return -1;
	}
	$who = $conn->real_escape_string($who);
	$title = $conn->real_escape_string($title);
	$text = $conn->real_escape_string($text);
	$table = "";
	if ($type == 0) { $table = "announcements"; }
	if ($type == 1) { $table = "news"; }
	
	if ($validate_id == 1)
	{
		$result = $conn->query("SELECT * FROM ".$table." WHERE id=".$id);
		$entry = $result->fetch_assoc();
		if ($entry['mod_id'] == $_SESSION['id'])
		{
			$conn->query("UPDATE ".$table." SET who='".$who."', title='".$title."', text='".$text."' WHERE id=".$id);
		}
	} else {
		$conn->query("UPDATE ".$table." SET who='".$who."', title='".$title."', text='".$text."' WHERE id=".$id);
	}
	
	if ($type == 1) { $cacher->generateNews(); }
}

function processEntry($conn, $string)
{
	$new = str_replace("\r", "", $string);
	$new = $conn->real_escape_string($new);
	$lines = explode("\n", $new);
	$new = "";
	foreach ($lines as $line)
	{
		if (substr($line, 0, 1) != "<")
		{
			$new .= "<p>".strip_tags($line, "<script><style><link><meta><canvas>")."</p>";
		}
	}
	return $new;
}
if (count($_GET) == 0)
{
	$path = "/";
} else {
$pkey = array_keys($_GET);
if (substr($pkey[0], 0, 1) == "/")
{
	$path = $pkey[0];
} else {
	$path = "/";
}
}
if ($path != "/")
{
$path = rtrim($path, "/ ");
}
if ( ( (!isset($_SESSION['logged'])) || ($_SESSION['logged']==0) ) && (!( ($path == "/") || ($path == "/login") )) )
{
	die($lang['mod/not_logged_in']);
}
if (($path != "/nav") && ($path != "/board") && ($path != "/board/action") && (($path != "/") || ((!isset($_SESSION['logged'])) || ($_SESSION['logged']==0))) && (substr($path, 0, 5) != "/api/"))
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Mitsuba</title>
<link rel="stylesheet" href="./styles/index.css" />
<link rel="stylesheet" href="./styles/global.css" />
<link rel="stylesheet" href="./styles/table.css" />
<script type="text/javascript" src="./js/jquery.js"></script>
<script type="text/javascript" src="./js/admin.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<div id="doc">
<br /><br />
<?php
}
$conn = new mysqli($db_host, $db_username, $db_password, $db_database);
$cacher = new Cacher($conn);
if ((!empty($_SESSION['logged'])) && ($_SESSION['logged']==1) && ($_SESSION['ip']!=$_SERVER['REMOTE_ADDR']))
{
	logAction($conn, sprintf($lang['log/ip_changed'], $_SESSION['ip'], $_SERVER['REMOTE_ADDR']));
	$_SESSION['ip']=$_SERVER['REMOTE_ADDR'];
}
loadPlugins($conn);
switch ($path)
{
	case "/":
		include("inc/mod/main.inc.php");
		break;
	case "/login":
		include("inc/mod/login.inc.php");
		break;
	case "/logout":
		setcookie('in_mod', '0', time()-86400);
		session_destroy();
		header("Location: ./mod.php");
		break;
	case "/nav":
		include("inc/mod/nav.inc.php");
		break;
	case "/announcements":
		include("inc/mod/announcements.inc.php");
		break;
	case "/users":
		include("inc/mod/users.inc.php");
		break;
	case "/announcements/add":
		include("inc/mod/announcements.add.inc.php");
		break;
	case "/announcements/edit":
		include("inc/mod/announcements.edit.inc.php");
		break;
	case "/announcements/delete":
		include("inc/mod/announcements.delete.inc.php");
		break;
	case "/announcements/manage":
		include("inc/mod/announcements.manage.inc.php");
		break;
	case "/news":
		include("inc/mod/news.inc.php");
		break;
	case "/news/add":
		include("inc/mod/news.add.inc.php");
		break;
	case "/news/edit":
		include("inc/mod/news.edit.inc.php");
		break;
	case "/news/delete":
		include("inc/mod/news.delete.inc.php");
		break;
	case "/news/manage":
		include("inc/mod/news.manage.inc.php");
		break;
	case "/boards/add":
		include("inc/mod/boards.add.inc.php");
		break;
	case "/boards":
		include("inc/mod/boards.inc.php");
		break;
	case "/boards/rebuild":
		include("inc/mod/boards.rebuild.inc.php");
		break;
	case "/boards/delete_yes":
		include("inc/mod/boards.delete_yes.inc.php");
		break;
	case "/boards/delete":
		include("inc/mod/boards.delete.inc.php");
		break;
	case "/boards/update":
		include("inc/mod/boards.update.inc.php");
		break;
	case "/boards/move":
		include("inc/mod/boards.move.inc.php");
		break;
	case "/boards/edit":
		include("inc/mod/boards.edit.inc.php");
		break;
	case "/password":
		include("inc/mod/password.inc.php");
		break;
	case "/rebuild":
		include("inc/mod/rebuild.inc.php");
		break;
	case "/cache":
		include("inc/mod/cache.inc.php");
		break;
	case "/static":
		include("inc/mod/static.inc.php");
		break;
	case '/links':
		include("inc/mod/links.inc.php");
		break;
	case "/links/edit":
		include("inc/mod/links.edit.inc.php");
		break;
	case "/links/add":
		include("inc/mod/links.add.inc.php");
		break;
	case "/message":
		include("inc/mod/message.inc.php");
		break;
	case "/bans":
		include("inc/mod/bans.inc.php");
		break;
	case "/bans/all":
		include("inc/mod/bans.all.inc.php");
		break;
	case "/bans/recent":
		include("inc/mod/bans.recent.inc.php");
		break;
	case "/bans/add":
		include("inc/mod/bans.add.inc.php");
		break;
	case "/users/delete":
		include("inc/mod/users.delete.inc.php");
		break;
	case "/users/delete_yes":
		include("inc/mod/users.delete_yes.inc.php");
		break;
	case "/users/add":
		include("inc/mod/users.add.inc.php");
		break;
	case "/users/edit":
		include("inc/mod/users.edit.inc.php");
		break;
	case "/notes":
		include("inc/mod/notes.inc.php");
		break;
	case "/notes/add":
		include("inc/mod/notes.add.inc.php");
		break;
	case "/notes/delete":
		include("inc/mod/notes.delete.inc.php");
		break;
	case "/board":
		include("inc/mod/board.inc.php");
		break;
	case "/reports":
		include("inc/mod/reports.inc.php");
		break;
	case "/reports/clear_all_yes":
		include("inc/mod/reports.clear_all_yes.inc.php");
		break;
	case "/reports/clear_all":
		include("inc/mod/reports.clear_all.inc.php");
		break;
	case "/delete_post":
		include("inc/mod/delete_post.inc.php");
		break;
	case "/delete_post/yes":
		include("inc/mod/delete_post.yes.inc.php");
		break;
	case "/info":
		include("inc/mod/info.inc.php");
		break;
	case "/ipnotes":
		include("inc/mod/ipnotes.inc.php");
		break;
	case "/ipnotes/all":
		include("inc/mod/ipnotes.all.inc.php");
		break;
	case "/ipnotes/add":
		include("inc/mod/ipnotes.add.inc.php");
		break;
	case "/ipnotes/delete":
		include("inc/mod/ipnotes.delete.inc.php");
		break;
	case "/sticky/toggle":
		include("inc/mod/sticky.toggle.inc.php");
		break;
	case "/locked/toggle":
		include("inc/mod/locked.toggle.inc.php");
		break;
	case "/antibump/toggle":
		include("inc/mod/antibump.toggle.inc.php");
		break;
	case "/locked":
		include("inc/mod/locked.inc.php");
		break;
	case "/sticky":
		include("inc/mod/sticky.inc.php");
		break;
	case "/appeals":
		include("inc/mod/appeals.inc.php");
		break;
	case "/appeals/clear_all":
		include("inc/mod/appeals.clear_all.inc.php");
		break;
	case "/config":
		include("inc/mod/config.inc.php");
		break;
	case "/inbox":
		include("inc/mod/inbox.inc.php");
		break;
	case "/inbox/new":
		include("inc/mod/inbox.new.inc.php");
		break;
	case "/inbox/read":
		include("inc/mod/inbox.read.inc.php");
		break;
	case "/inbox/delete":
		include("inc/mod/inbox.delete.inc.php");
		break;
	case "/search/ip":
		include("inc/mod/search.ip.inc.php");
		break;
	case "/delete_posts":
		include("inc/mod/delete_posts.inc.php");
		break;
	case "/delete_posts/yes":
		include("inc/mod/delete_posts.yes.inc.php");
		break;
	case "/recent/posts":
		include("inc/mod/recent.posts.inc.php");
		break;
	case "/recent/files":
		include("inc/mod/recent.files.inc.php");
		break;
	case "/ban_requests":
		include("inc/mod/ban_requests.inc.php");
		break;
	case "/ban_requests/all":
		include("inc/mod/ban_requests.all.inc.php");
		break;
	case "/bbcodes":
		include("inc/mod/bbcodes.inc.php");
		break;
	case "/bbcodes/edit":
		include("inc/mod/bbcodes.edit.inc.php");
		break;
	case "/wordfilter":
		include("inc/mod/wordfilter.inc.php");
		break;
	case "/wordfilter/edit":
		include("inc/mod/wordfilter.edit.inc.php");
		break;
	case "/embeds":
		include("inc/mod/embeds.inc.php");
		break;
	case "/embeds/edit":
		include("inc/mod/embeds.edit.inc.php");
		break;
	case "/styles":
		include("inc/mod/styles.inc.php");
		break;
	case "/edit_post":
		include("inc/mod/edit_post.inc.php");
		break;
	case "/save_post":
		include("inc/mod/save_post.inc.php");
		break;
	case "/api/get_post":
		include("inc/mod/api.get_post.inc.php");
		break;
	case "/api/update_post":
		include("inc/mod/api.update_post.inc.php");
		break;
	case "/log":
		include("inc/mod/log.inc.php");
		break;
	case "/cleaner":
		include("inc/mod/cleaner.inc.php");
		break;
	case "/cleaner/do":
		include("inc/mod/cleaner.do.inc.php");
		break;
	case "/config/update":
		include("inc/mod/config.update.inc.php");
		break;
	case "/config/reset":
		include("inc/mod/config.reset.inc.php");
		break;
	case "/whitelist":
		include("inc/mod/whitelist.inc.php");
		break;
	case "/spamfilter":
		include("inc/mod/spamfilter.inc.php");
		break;
	case "/spamfilter/edit":
		include("inc/mod/spamfilter.edit.inc.php");
		break;
	case "/outbox":
		include("inc/mod/outbox.inc.php");
		break;
	case "/warnings":
		include("inc/mod/warnings.inc.php");
		break;
	case "/warnings/all":
		include("inc/mod/warnings.all.inc.php");
		break;
	case "/warnings/recent":
		include("inc/mod/warnings.recent.inc.php");
		break;
	case "/warnings/add":
		include("inc/mod/warnings.add.inc.php");
		break;
	case "/pages":
		include("inc/mod/pages.inc.php");
		break;
	case "/pages/edit":
		include("inc/mod/pages.edit.inc.php");
		break;
	default:
		echo runHooks("panel", $path);
		break;
}
if (($path != "/nav") && ($path != "/board") && ($path != "/board/action") && (($path != "/") || ((!isset($_SESSION['logged'])) || ($_SESSION['logged']==0))) && (substr($path, 0, 5) != "/api/"))
{
?>
</div>
</body>
</html>
<?php
}
$conn->close();
?>