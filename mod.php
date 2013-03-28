<?php
if (!file_exists("./config.php"))
{
header("Location: ./install.php");
}

session_start();
include("config.php");
include("version.php");
include("inc/common.php");
include("inc/common.caching.php");
include("inc/admin.common.php");
include("inc/admin.users.php");
include("inc/admin.bans.php");
include("inc/admin.caching.php");
include("inc/admin.boards.php");
include("inc/admin.boards.links.php");
include("inc/admin.boards.view.php");
include("inc/admin.posting.php");
include("inc/common.plugins.php");
include("lang/en/lang.mod.php");


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

	if ($type == 1) { generateNews($conn); }
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
	
	if ($type == 1) { generateNews($conn); }
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
</head>
<body>
<div id="doc">
<br /><br />
<?php
}
$conn = new mysqli($db_host, $db_username, $db_password, $db_database);
loadPlugins($conn);
switch ($path)
{
	case "/":
		if ((isset($_SESSION['logged'])) && ($_SESSION['logged']==1))
		{
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Mitsuba</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<frameset cols="200px,*" frameborder="1" border="1" bordercolor="#800">
<frame src="./mod.php?/nav" id="nav">
<frame src="./mod.php?/announcements" name="main" id="main">
<noframes>
<h1>Mitsuba</h1>
<p>This page uses frames!</p>
</noframes>
</frameset>
</html>
		<?php
		} else {
			?>

<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/log_in']; ?></h2></div>
<div class="boxcontent">
<form action="?/login" method="POST">
<center><?php echo $lang['mod/username']; ?>: <input type="text" name="username" /> | <?php echo $lang['mod/password']; ?>: <input type="password" name="password" /> <input type="submit" value="<?php echo $lang['mod/log_in']; ?>" /></center>
</form>
</div>
</div>
</div>
		<?php
		}
		break;
	case "/login":
		if ((!empty($_POST['username'])) && (!empty($_POST['password'])))
		{
			$username = $conn->real_escape_string($_POST['username']);
			$password = hash("sha512", $_POST['password']);
			$result = $conn->query("SELECT * FROM users WHERE username='".$username."'");
			if ($result->num_rows == 1)
			{
				$data = $result->fetch_assoc();
				if ($data['password'] == $password)
				{
					$_SESSION['logged']=1;
					$_SESSION['id']=$data['id'];
					$_SESSION['username']=$username;
					$_SESSION['type']=$data['type'];
					$_SESSION['boards']=$data['boards'];
					header("Location: ./mod.php");
				} else {
					die($lang['mod/bad_password']);
				}
			} else {
				die($lang['mod/bad_password']);
			}
		} else {
			die($lang['mod/error']);
		}
		break;
	case "/logout":
		session_destroy();
		header("Location: ./mod.php");
		break;
	case "/nav":
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
<li><?php echo $lang['mod/privileges']; ?><b><?php if ($_SESSION['type']==2) { echo $lang['mod/administrator']; } elseif ($_SESSION['type']==1) { echo $lang['mod/moderator']; } elseif ($_SESSION['type']==0) { echo $lang['mod/janitor']; } else { echo $lang['mod/faggot']; } ?></b></li>
<li><a href="?/logout" target="_top"><?php echo $lang['mod/logout']; ?></a></li>
</ul>
<h2><span class="coll" onclick="toggle(this,'gen');" title="Toggle Category">&minus;</span><?php echo $lang['mod/general']; ?></h2>
<div id="gen" style="">
<ul>
<li><a href="?/announcements" target="main"><?php echo $lang['mod/announcements']; ?></a></li>
<li><a href="?/news" target="main"><?php echo $lang['mod/news']; ?></a></li>
<li><a href="?/bans" target="main"><?php echo $lang['mod/banlist']; ?></a></li>
<li><a href="?/reports" target="main"><?php echo $lang['mod/report_queue']; ?> (<?php echo $reports; ?>)</a></li>
<li><a href="?/notes" target="main"><?php echo $lang['mod/notes']; ?></a></li>
<li><a href="?/ipnotes" target="main"><?php echo $lang['mod/ip_notes']; ?></a></li>
<li><a href="?/recent/posts" target="main"><?php echo $lang['mod/recent_posts']; ?></a></li>
<li><a href="?/recent/files" target="main"><?php echo $lang['mod/recent_images']; ?></a></li>
<?php
echo runHooks("menu", null);
if ($_SESSION['type'] >= 1)
{
?>
<li><a href="?/ban_requests" target="main"><?php echo $lang['mod/ban_requests']; ?> (<?php echo $breqs; ?>)</a></li>
<li><a href="?/announcements/add" target="main"><?php echo $lang['mod/new_announcement']; ?></a></li>
<li><a href="?/news/add" target="main"><?php echo $lang['mod/add_news']; ?></a></li>
<li><a href="?/bans/add" target="main"><?php echo $lang['mod/add_ban']; ?></a></li>
<li><a href="?/locked" target="main"><?php echo $lang['mod/locked']; ?></a></li>
<li><a href="?/sticky" target="main"><?php echo $lang['mod/sticky']; ?></a></li>
<li><a href="?/appeals" target="main"><?php echo $lang['mod/appeals']; ?> (<?php echo $appeals; ?>)</a></li>
<?php
}
?>
</ul></div>
<h2><span class="coll" onclick="toggle(this,'acc');" title="Toggle Category">&minus;</span><?php echo $lang['mod/account']; ?></h2>
<div id="acc" style="">
<ul>
<li><a href="?/password" target="main"><?php echo $lang['mod/change_password']; ?></a></li>
<li><a href="?/inbox" target="main"><?php echo $lang['mod/inbox']; ?> (<?php echo $pms; ?>)</a></li>
<li><a href="?/inbox/new" target="main"><?php echo $lang['mod/send_message']; ?></a></li>
</ul></div>
<?php
if ($_SESSION['type'] >= 2)
{
?>
<h2><span class="coll" onclick="toggle(this,'adm');" title="Toggle Category">&minus;</span><?php echo $lang['mod/administration']; ?></h2>
<div id="adm" style="">
<ul>
<li><a href="?/config" target="main"><?php echo $lang['mod/configuration']; ?></a></li>
<li><a href="?/boards" target="main"><?php echo $lang['mod/manage_boards']; ?></a></li>
<li><a href="?/links" target="main"><?php echo $lang['mod/manage_board_links']; ?></a></li>
<li><a href="?/users" target="main"><?php echo $lang['mod/manage_users']; ?></a></li>
<li><a href="?/whitelist" target="main"><?php echo $lang['mod/manage_whitelist']; ?></a></li>
<li><a href="?/news/manage" target="main"><?php echo $lang['mod/manage_news_entries']; ?></a></li>
<li><a href="?/announcements/manage" target="main"><?php echo $lang['mod/manage_announcements']; ?></a></li>
<li><a href="?/bbcodes" target="main"><?php echo $lang['mod/manage_bbcodes']; ?></a></li>
<li><a href="?/embeds" target="main"><?php echo $lang['mod/manage_embeds']; ?></a></li>
<li><a href="?/styles" target="main"><?php echo $lang['mod/manage_styles']; ?></a></li>
<li><a href="?/wordfilter" target="main"><?php echo $lang['mod/manage_wordfilter']; ?></a></li>
<li><a href="?/range" target="main"><?php echo $lang['mod/manage_range_bans']; ?></a></li>
<li><a href="?/message" target="main"><?php echo $lang['mod/global_message']; ?></a></li>
<li><a href="?/rebuild" target="main"><?php echo $lang['mod/rebuild_cache']; ?></a></li>
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
if (($_SESSION['boards'] != "*") && ($_SESSION['type'] != 2))
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
<?php
		break;
	case "/announcements":
?>

<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/announcements']; ?></h2></div>
<div class="boxcontent">
<?php
$result = $conn->query("SELECT * FROM announcements ORDER BY date DESC;");
while ($row = $result->fetch_assoc())
{
echo '<div class="content">';
echo '<h3><span class="newssub">'.$row['title'].' by '.$row['who'].' - '.date("d/m/Y @ H:i", $row['date']).'</span></span></h3>';
echo $row['text'];
echo '</div>';
}
?>
</div>
</div>
</div>
<?php
		break;
	case "/users":
	reqPermission(2);
	?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/new_user']; ?></h2></div>
<div class="boxcontent">
<form action="?/users/add" method="POST">
<?php echo $lang['mod/username']; ?>: <input type="text" name="username" /><br />
<?php echo $lang['mod/password']; ?>: <input type="password" name="password"/><br />
<?php echo $lang['mod/type']; ?>: <select name="type"><option value="0"><?php echo $lang['mod/janitor']; ?></option><option value="1"><?php echo $lang['mod/moderator']; ?></option><option value="2"><?php echo $lang['mod/administrator']; ?></option></select>

<br /><br />
<?php echo $lang['mod/boards']; ?>: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1/> <?php echo $lang['mod/all']; ?><br/>
<select name="boards[]" id="boardSelect" multiple>
<?php
$result = $conn->query("SELECT * FROM boards;");
while ($row = $result->fetch_assoc())
{
echo "<option onClick='document.getElementById(\"all\").checked=false;' value='",$row['short']."'>/".$row['short']."/ - ".$row['name']."</option>";
}
?>
</select><br />
<input type="submit" value="<?php echo $lang['mod/add_user']; ?>" />
</form>
</div>
</div>
</div><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/all_users']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td style="width: 30%;"><?php echo $lang['mod/username']; ?></td>
<td style="width: 20%;"><?php echo $lang['mod/type']; ?></td>
<td style="width: 30%;"><?php echo $lang['mod/boards']; ?></td>
<td style="width: 10%;"><?php echo $lang['mod/edit']; ?></td>
<td style="width: 10%;"><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM users;");
$usern = $result->num_rows;
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".$row['username']."</td>";
echo "<td>";
switch ($row['type'])
{
	case 0:
		echo $lang['mod/janitor'];
		break;
	case 1:
		echo $lang['mod/moderator'];
		break;
	case 2:
		echo $lang['mod/administrator'];
		break;
	default:
		echo $lang['mod/faggot'];
		break;
}
echo "</td>";
echo "<td>".$row['boards']."</td>";
echo "<td><a href='?/users/edit&id=".$row['id']."'>".$lang['mod/edit']."</a></td>";
if ($usern != 1)
{
echo "<td><a href='?/users/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></td>";
} else {
echo "<td></td>";
}
echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
<?php
		break;
	case "/announcements/add":
	reqPermission(1);
	if (empty($_POST['text']))
	{
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/new_announcement']; ?></h2></div>
<div class="boxcontent">
<form action="?/announcements/add" method="POST">
<?php echo $lang['mod/by']; ?>: <input type="text" name="who" value="<?php echo $_SESSION['username']; ?>" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title"/><br />
<?php echo $lang['mod/text']; ?>: <br />
<textarea name="text" cols="70" rows="10"></textarea>
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/your_entries']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/title']; ?></td>
<td><?php echo $lang['mod/date']; ?></td>
<td><?php echo $lang['mod/edit']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM announcements WHERE mod_id=".$_SESSION['id']." ORDER BY date DESC;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".$row['title']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['date'])."</td>";
echo "<td><a href='?/announcements/edit&b=".$row['id']."'>".$lang['mod/edit']."</a></td>";
echo "<td><a href='?/announcements/delete&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
	<?php
	} else {
		$text = processEntry($conn, $_POST['text']);
		$who = $_SESSION['username'];
		if (!empty($_POST['who'])) { $who = $_POST['who']; }
		$conn->query("INSERT INTO announcements (date, who, title, text, mod_id) VALUES (".time().", '".$who."', '".$conn->real_escape_string(htmlspecialchars($_POST['title']))."', '".$text."', ".$_SESSION['id'].");");
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/post_added']; ?></h2></div>
<div class="boxcontent"><a href="?/announcements"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
	}
		break;
		
	case "/announcements/edit":
	reqPermission(1);
	if ((isset($_GET['b'])) && (is_numeric($_GET['b'])))
	{
	$result = $conn->query("SELECT * FROM announcements WHERE id=".$_GET['b']);
	if ($result->num_rows != 0)
	{
	if (empty($_POST['text']))
	{
	$data = $result->fetch_assoc();
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/edit_announcement']; ?></h2></div>
<div class="boxcontent">
<form action="?/announcements/edit&b=<?php echo $_GET['b']; ?>" method="POST">
<?php echo $lang['mod/by']; ?>: <input type="text" name="who" value="<?php echo $data['who']; ?>" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" value="<?php echo $data['title']; ?>"/><br />
<?php echo $lang['mod/text']; ?>: <br />
<textarea name="text" cols="70" rows="10"><?php echo $data['text']; ?></textarea>
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div><br />
	<?php
	} else {
		if ($_SESSION['type']==2)
		{
		updateEntry($conn, 0, $_GET['b'], $_POST['who'], $_POST['title'], $_POST['text']);
		} else {
		updateEntry($conn, 0, $_GET['b'], $_POST['who'], $_POST['title'], $_POST['text'], 1);
		}
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/post_updated']; ?></h2></div>
<div class="boxcontent"><a href="?/announcements"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
		
	}
	} else {
	?>
	<meta http-equiv="refresh" content="0;URL='?/announcements'" />
	<?php
	}
	} else {
	?>
	<meta http-equiv="refresh" content="0;URL='?/announcements'" />
	<?php
	}
		break;
	case "/announcements/delete":
	reqPermission(1);
		if (isset($_GET['b']))
		{
			if ($_SESSION['type']==2)
			{
				deleteEntry($conn, 0, $_GET['b']);
			} else {
				deleteEntry($conn, 0, $_GET['b'], 1);
			}
	?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/post_deleted']; ?></h2></div>
<div class="boxcontent"><a href="?/announcements"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
		}
		break;
	case "/announcements/manage":
	reqPermission(2);
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/all_announcements']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/title']; ?></td>
<td><?php echo $lang['mod/date']; ?></td>
<td><?php echo $lang['mod/edit']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM announcements ORDER BY date DESC;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".$row['title']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['date'])."</td>";
echo "<td><a href='?/announcements/edit&b=".$row['id']."'>".$lang['mod/edit']."</a></td>";
echo "<td><a href='?/announcements/delete&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
<?php
		break;
	case "/news":
?>

<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/news']; ?></h2></div>
<div class="boxcontent">
<?php
$result = $conn->query("SELECT * FROM news ORDER BY date DESC;");
while ($row = $result->fetch_assoc())
{
echo '<div class="content">';
echo '<h3><span class="newssub">'.$row['title'].' by '.$row['who'].' - '.date("d/m/Y @ H:i", $row['date']).'</span></span></h3>';
echo $row['text'];
echo '</div>';
}
?>
</div>
</div>
</div>
<?php
		break;
	case "/news/add":
	reqPermission(1);
	if (empty($_POST['text']))
	{
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_news']; ?></h2></div>
<div class="boxcontent">
<form action="?/news/add" method="POST">
<?php echo $lang['mod/by']; ?>: <input type="text" name="who" value="<?php echo $_SESSION['username']; ?>" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title"/><br />
<?php echo $lang['mod/text']; ?>: <br />
<textarea name="text" cols="70" rows="10"></textarea>
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/your_entries']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/title']; ?></td>
<td><?php echo $lang['mod/date']; ?></td>
<td><?php echo $lang['mod/edit']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM news WHERE mod_id=".$_SESSION['id']." ORDER BY date DESC;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".$row['title']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['date'])."</td>";
echo "<td><a href='?/news/edit&b=".$row['id']."'>".$lang['mod/edit']."</a></td>";
echo "<td><a href='?/news/delete&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
echo "</td>";
}
generateNews($conn);
?>
</tbody>
</table>
</div>
</div>
</div>
	<?php
	} else {
		$text = processEntry($conn, $_POST['text']);
		$who = $_SESSION['username'];
		if (!empty($_POST['who'])) { $who = $_POST['who']; }
		$conn->query("INSERT INTO news (date, who, title, text, mod_id) VALUES (".time().", '".$who."', '".$conn->real_escape_string(htmlspecialchars($_POST['title']))."', '".$text."', ".$_SESSION['id'].");");
		generateNews($conn);
		
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/post_added']; ?></h2></div>
<div class="boxcontent"><a href="?/news"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
	}
		break;
	case "/news/edit":
	reqPermission(1);
		if ((isset($_GET['b'])) && (is_numeric($_GET['b'])))
	{
	$result = $conn->query("SELECT * FROM news WHERE id=".$_GET['b']);
	if ($result->num_rows != 0)
	{
	if (empty($_POST['text']))
	{
	$data = $result->fetch_assoc();
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/edit_news_entry']; ?></h2></div>
<div class="boxcontent">
<form action="?/news/edit&b=<?php echo $_GET['b']; ?>" method="POST">
<?php echo $lang['mod/by']; ?>: <input type="text" name="who" value="<?php echo $data['who']; ?>" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" value="<?php echo $data['title']; ?>"/><br />
<?php echo $lang['mod/text']; ?>: <br />
<textarea name="text" cols="70" rows="10"><?php echo $data['text']; ?></textarea>
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div><br />
	<?php
	} else {
		if ($_SESSION['type']==2)
		{
		updateEntry($conn, 1, $_GET['b'], $_POST['who'], $_POST['title'], $_POST['text']);
		} else {
		updateEntry($conn, 1, $_GET['b'], $_POST['who'], $_POST['title'], $_POST['text'], 1);
		}
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/post_updated']; ?></h2></div>
<div class="boxcontent"><a href="?/news"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
	}
	} else {
	?>
	<meta http-equiv="refresh" content="0;URL='?/news'" />
	<?php
	}
	} else {
	?>
	<meta http-equiv="refresh" content="0;URL='?/news'" />
	<?php
	}
		break;
	case "/news/delete":
	reqPermission(1);
	if (isset($_GET['b']))
	{
		if ($_SESSION['type']==2)
		{
			deleteEntry($conn, 1, $_GET['b']);
		} else {
			deleteEntry($conn, 1, $_GET['b'], 1);
		}
	?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/post_deleted']; ?></h2></div>
<div class="boxcontent"><a href="?/news"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
	}
		break;
	case "/news/manage":
	reqPermission(2);
		?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/all_news_entries']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/title']; ?></td>
<td><?php echo $lang['mod/date']; ?></td>
<td><?php echo $lang['mod/edit']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM news ORDER BY date DESC;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".$row['title']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['date'])."</td>";
echo "<td><a href='?/news/edit&b=".$row['id']."'>".$lang['mod/edit']."</a></td>";
echo "<td><a href='?/news/delete&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
<?php
		break;
	case "/boards/add":
		reqPermission(2);
		if ((!empty($_POST['short'])) && (!empty($_POST['name'])))
		{
			$spoilers = 0;
			if ((!empty($_POST['spoilers'])) && ($_POST['spoilers'] == 1))
			{
				$spoilers = 1;
			}
			$noname = 0;
			if ((!empty($_POST['noname'])) && ($_POST['noname'] == 1))
			{
				$noname = 1;
			}
			$ids = 0;
			if ((!empty($_POST['ids'])) && ($_POST['ids'] == 1))
			{
				$ids = 1;
			}
			$embeds = 0;
			if ((!empty($_POST['embeds'])) && ($_POST['embeds'] == 1))
			{
				$embeds = 1;
			}
			if (addBoard($conn, $_POST['short'], $_POST['name'], $_POST['des'], $_POST['msg'], $_POST['limit'], $spoilers, $noname, $ids, $embeds) > 0)
			{
				?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_created']; ?></h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			} else {
			?>
						<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_exists_mysql_error']; ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
			<?php
			}
		} else {
	?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/fill_all_fields']; ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
	<?php
		}
		break;
	case "/boards":
	reqPermission(2);
?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/create_new_board']; ?></h2></div>
<div class="boxcontent">
<form action="?/boards/add" method="POST">
<?php echo $lang['mod/board_directory']; ?>: <input type="text" name="short" maxlength=10 /><br />
<?php echo $lang['mod/board_name']; ?>: <input type="text" name="name" maxlength=40 /><br />
<?php echo $lang['mod/board_short']; ?>: <input type="text" name="des" maxlength=100 /><br />
<?php echo $lang['mod/board_msg']; ?>: <br /><textarea cols=70 rows=7 name="msg"></textarea><br />
<?php echo $lang['mod/board_limit']; ?>: <input type="text" name="limit" maxlength=9 value="0" /><br />
<?php echo $lang['mod/board_options']; ?>: <input type="checkbox" name="spoilers" value="1" /><?php echo $lang['mod/board_spoilers']; ?> <input type="checkbox" name="noname" value="1" /><?php echo $lang['mod/board_no_name']; ?> <input type="checkbox" name="ids" value="1" /><?php echo $lang['mod/board_ids']; ?><br />
<input type="checkbox" name="embeds" value="1" /><?php echo $lang['mod/board_embeds']; ?> <br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
<br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/manage_boards']; ?></h2></div>
<div class="boxcontent">
<?php echo $lang['mod/all_boards']; ?>: <br />
<table>
<thead>
<tr>
<td><?php echo $lang['mod/directory']; ?></td>
<td><?php echo $lang['mod/name']; ?></td>
<td><?php echo $lang['mod/description']; ?></td>
<td><?php echo $lang['mod/bump_limit']; ?></td>
<td><?php echo $lang['mod/message']; ?></td>
<td><?php echo $lang['mod/special']; ?></td>
<td><?php echo $lang['mod/edit']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
<td><?php echo $lang['mod/rebuild_cache']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM boards;");
while ($row = $result->fetch_assoc())
{
echo '<tr>';
echo "<td><a href='./".$row['short']."/'>/".$row['short']."/</a></td>";
echo "<td>".$row['name']."</td>";
echo "<td>".$row['des']."</td>";
echo "<td>".$row['bumplimit']."</td>";
if (!empty($row['message']))
{
echo "<td>".$lang['mod/yes']."</td>";
} else {
echo "<td>".$lang['mod/no']."</td>";
}
echo "<td>";
if ($row['spoilers']==1) { echo "<b>".$lang['mod/spoilers']."</b><br />"; }
if ($row['noname']==1) { echo "<b>".$lang['mod/noname']."</b><br />"; }
if ($row['ids']==1) { echo "<b>".$lang['mod/ids']."</b><br />"; }
if ($row['embeds']==1) { echo "<b>".$lang['mod/embeds']."</b><br />"; }
echo "</td>";
echo "<td><a href='?/boards/edit&board=".$row['short']."'>".$lang['mod/edit']."</a></td>";
echo "<td><a href='?/boards/delete&board=".$row['short']."'>".$lang['mod/delete']."</a></td>";
echo "<td><a href='?/boards/rebuild&board=".$row['short']."'>".$lang['mod/rebuild_cache']."</a></td>";
echo '</tr>';
}
?>
</tbody>
</table>
</div>
</div>
</div>
<?php
		break;
	case "/boards/rebuild":
	reqPermission(1);
		if ((!empty($_GET['board'])) && (isBoard($conn, $_GET['board'])))
		{
			rebuildBoardCache($conn, $_GET['board']);
		?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_cache_rebuilded']; ?></h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		} else {
		?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_not_found']; ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		}
		break;
	case "/boards/delete_yes":
	reqPermission(2);
		if (!empty($_GET['board']))
		{
			$board = $conn->real_escape_string($_GET['board']);
			if (isBoard($conn, $board))
			{
				deleteBoard($conn, $board);
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_deleted']; ?></h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			} else {
			
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_not_found']; ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			}
			
		}
		break;
	case "/boards/delete":
	reqPermission(2);
		if (!empty($_GET['board']))
		{
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php printf($lang['mod/want_delete_board'], $_GET['board']); ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/no_big']; ?></a> <a href="?/boards/delete_yes&board=<?php echo $_GET['board']; ?>"><?php echo $lang['mod/yes_big']; ?></a></div>
</div>
</div>
				<?php
		} else {
						?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_not_found']; ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		}
		break;
	case "/boards/update":
	reqPermission(2);
		if (isBoard($conn, $_GET['board']))
		{
			if (!empty($_POST['name']))
			{
				$spoilers = 0;
				if ((!empty($_POST['spoilers'])) && ($_POST['spoilers'] == 1))
				{
					$spoilers = 1;
				}
				$noname = 0;
				if ((!empty($_POST['noname'])) && ($_POST['noname'] == 1))
				{
					$noname = 1;
				}
				$ids = 0;
				if ((!empty($_POST['ids'])) && ($_POST['ids'] == 1))
				{
					$ids = 1;
				}
				$embeds = 0;
				if ((!empty($_POST['embeds'])) && ($_POST['embeds'] == 1))
				{
					$embeds = 1;
				}
				if (updateBoard($conn, $_GET['board'], $_POST['name'], $_POST['des'], $_POST['msg'], $_POST['limit'], $spoilers, $noname, $ids, $embeds))
				{
				?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_updated']; ?></h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
				} else {
				?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/some_error']; ?></h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
				}
			}
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_not_found']; ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		}
		break;
	case "/boards/move":
	reqPermission(2);
		if (isBoard($conn, $_GET['board']))
		{
			if (!empty($_POST['new']))
			{
				$result = moveBoard($conn, $_GET['board'], $_POST['new']);
				if($result == 1)
				{
				?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_moved']; ?></h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
				} elseif ($result == 0) {
				?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_not_found']; ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
				} elseif ($result == -1) {
				?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php printf($lang['mod/board_exists'], $_POST['new']); ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
				}
			}
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_not_found']; ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		}
		break;
	case "/boards/edit":
	reqPermission(2);
		if (isBoard($conn, $_GET['board']))
		{
			$data = getBoardData($conn, $_GET['board']);
			?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php printf($lang['mod/edit_board'], $_GET['board']); ?></h2></div>
<div class="boxcontent">
<form action="?/boards/update&board=<?php echo $_GET['board']; ?>" method="POST">
<?php echo $lang['mod/board_directory']; ?>: <input disabled type="text" name="short" maxlength=10 value="<?php echo $data['short']; ?>" /><br />
<?php echo $lang['mod/board_name']; ?>: <input type="text" name="name" maxlength=40 value="<?php echo $data['name']; ?>" /><br />
<?php echo $lang['mod/board_short']; ?>: <input type="text" name="des" maxlength=100 value="<?php echo $data['des']; ?>" /><br />
<?php echo $lang['mod/board_msg']; ?>: <br /><textarea cols=70 rows=7 name="msg"><?php echo $data['message']; ?></textarea><br />
<?php echo $lang['mod/board_limit']; ?>: <input type="text" name="limit" maxlength=9 value="<?php echo $data['bumplimit']; ?>" /><br />
<?php echo $lang['mod/board_options']; ?>: <input type="checkbox" name="spoilers" value="1" <?php if ($data['spoilers'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_spoilers']; ?> <input type="checkbox" name="noname" value="1" <?php if ($data['noname'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_no_name']; ?> <input type="checkbox" name="ids" value="1" <?php if ($data['ids'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_ids']; ?><br />
<input type="checkbox" name="embeds" value="1" <?php if ($data['embeds'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_embeds']; ?> <br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php printf($lang['mod/move_board'], $_GET['board']); ?></h2></div>
<div class="boxcontent">
<form action="?/boards/move&board=<?php echo $_GET['board']; ?>" method="POST">
<?php echo $lang['mod/new_dir']; ?>: <input type="text" name="new" maxlength=10 /><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
<?php
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_not_found']; ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		}
		break;
	case "/password":
		if ((!empty($_POST['old'])) && (!empty($_POST['new'])) && (!empty($_POST['new2'])))
		{
			if ($_POST['new']==$_POST['new2'])
			{
		
			$result = $conn->query("SELECT password FROM users WHERE id=".$_SESSION['id']);
			$row = $result->fetch_assoc();
				if ($row['password'] != hash("sha512", $_POST['old']))
				{
							?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/pwd_no_match']; ?></h2></div>
<div class="boxcontent"><a href="?/password"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
			<?php
				} else {
					$conn->query("UPDATE users SET password='".hash("sha512", $_POST['new'])."' WHERE id=".$_SESSION['id']);
				?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/pwd_updated']; ?></h2></div>
<div class="boxcontent"><a href="?/password"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
				}
			} else {
				?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/pwd_wrong']; ?></h2></div>
<div class="boxcontent"><a href="?/password"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
			<?php
			}
		} else {
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/pwd_change']; ?></h2></div>
<div class="boxcontent">
<form action="?/password" method="POST">
<?php echo $lang['mod/pwd_current']; ?>: <input type="password" name="old"><br />
<?php echo $lang['mod/pwd_new']; ?>: <input type="password" name="new"><br />
<?php echo $lang['mod/pwd_confirm']; ?>: <input type="password" name="new2"><br />
<input type="submit" value="Change password"><br />
</form>
</div>
</div>
</div>
		<?php
		}
		break;
	case "/rebuild":
	reqPermission(2);
	$config = getConfig($conn);
	?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/rebuild_cache']; ?></h2></div>
<div class="boxcontent">
<form action="?/cache" method="POST">
<input type="checkbox" name="links" value=1><?php echo $lang['mod/board_links']; ?></input><br />
<input type="checkbox" name="styles" value=1><?php echo $lang['mod/board_styles']; ?></input><br />
<input type="checkbox" name="boards" value=1><?php echo $lang['mod/all_boards']; ?></input><br />
<input type="checkbox" name="static" value=1><?php echo $lang['mod/all_static']; ?></input><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>"><br />
</form>
</div>
</div>
</div>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/rebuild_static']; ?></h2></div>
<div class="boxcontent">
<form action="?/static" method="POST">
<input type="checkbox" name="frontpage" value=1><?php echo $lang['mod/frontpage']; ?> (./<?php echo $config['frontpage_url']; ?>)</input><br />
<input type="checkbox" name="news" value=1><?php echo $lang['mod/news_page']; ?> (./<?php echo $config['news_url']; ?>)</input><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>"><br />
</form>
</div>
</div>
</div>
	<?php
		break;
	case "/cache":
		reqPermission(2);
		if ((!empty($_POST['links'])) && ($_POST['links']==1))
		{
			
			rebuildBoardLinks($conn);
		}
		
		if ((!empty($_POST['styles'])) && ($_POST['styles']==1))
		{
			rebuildStyles($conn);
		}
		
		
		if ((!empty($_POST['boards'])) && ($_POST['boards']==1))
		{
			$result = $conn->query("SELECT * FROM boards ORDER BY short ASC;");
			while ($row = $result->fetch_assoc())
			{
				rebuildBoardCache($conn, $row['short']);
			}
		}
		
		if ((!empty($_POST['static'])) && ($_POST['static']==1))
		{
			generateFrontpage($conn);
			generateNews($conn);
		}
		?>
					<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/rebuilding_done']; ?></h2></div>
<div class="boxcontent">
<a href="?/rebuild"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
		<?php
		break;
	case "/static":
	reqPermission(2);
		if ((!empty($_POST['frontpage'])) && ($_POST['frontpage']==1))
		{
			generateFrontpage($conn);
		}
		
		if ((!empty($_POST['news'])) && ($_POST['news']==1))
		{
			generateNews($conn);
		}
		?>
					<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/rebuilding_done']; ?></h2></div>
<div class="boxcontent">
<a href="?/rebuild"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
		<?php
		break;
	case '/links':
	reqPermission(2);
		?>
				<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/manage_board_links']; ?></h2></div>
<div class="boxcontent">
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<?php
echo getLinkTable($conn, -1);
?>
</div>
</div>
</div>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_link_category']; ?></h2></div>
<div class="boxcontent">
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<form action="?/links/category" method="POST">
<?php echo $lang['mod/name']; ?>: <input type="text" name="title" value="<?php echo $lang['mod/category']; ?>" /><input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
		break;
	case "/links/delete":
	reqPermission(2);
		if (!empty($_GET['i']))
		{
			$id = $conn->real_escape_string($_GET['i']);
			deleteBoardLink($conn, $id);
		}
		?>
		<meta http-equiv="refresh" content="0;URL='?/links'" />
		<?php
		break;
	case "/links/edit":
	reqPermission(2);
		if (isset($_GET['i']))
		{
			$id = $conn->real_escape_string($_GET['i']);
			$link = $conn->query("SELECT * FROM links WHERE id=".$id);
			if ($link->num_rows == 1)
			{
				$data = $link->fetch_assoc();
				if (empty($_POST['title']))
				{
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2></h2></div>
<div class="boxcontent">
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<form action="?/links/edit&i=<?php echo $id; ?>" method="POST">
<?php echo $lang['mod/short']; ?>: <input type="text" name="short" value="<?php echo $data['short']; ?>" /><br />
<?php echo $lang['mod/url']; ?>: <input type="text" name="url" value="<?php echo $data['url']; ?>" /><br />
<?php echo $lang['mod/url_thread']; ?>: <input type="text" name="url_thread" value="<?php echo $data['url_thread']; ?>" /><br />
<?php echo $lang['mod/url_index']; ?>: <input type="text" name="url_index" value="<?php echo $data['url_index']; ?>" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" value="<?php echo $data['title']; ?>" /><br />
<br /><input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
				} else {
					updateBoardLink($conn, $id, $_POST['url'], $_POST['url_thread'], $_POST['url_index'], $_POST['title'], $_POST['short']);
					
			?>
			<meta http-equiv="refresh" content="0;URL='?/links'" />
			<?php
				}
			} else {
			?>
			<meta http-equiv="refresh" content="0;URL='?/links'" />
			<?php
			}
		} else {
		?>
		<meta http-equiv="refresh" content="0;URL='?/links'" />
		<?php
		}
		break;
	case "/links/category":
	reqPermission(2);
		if (!empty($_POST['title']))
		{
			addLinkCategory($conn, $_POST['title']);
		}
		?>
		<meta http-equiv="refresh" content="0;URL='?/links'" />
		<?php
		break;
	case "/links/up":
	reqPermission(2);
		if (!empty($_GET['l']))
		{
			$id = $conn->real_escape_string($_GET['l']);
			moveUpCategory($conn, $id);
		}
		?>
		<meta http-equiv="refresh" content="0;URL='?/links'" />
		<?php
		break;
	case "/links/down":
	reqPermission(2);
		if (!empty($_GET['l']))
		{
			$id = $conn->real_escape_string($_GET['l']);
			moveDownCategory($conn, $id);
		}
		?>
		<meta http-equiv="refresh" content="0;URL='?/links'" />
		<?php
		break;
	case "/links/add":
	reqPermission(2);
		if (isset($_GET['p']))
		{
			$id = $conn->real_escape_string($_GET['p']);
			$cat = $conn->query("SELECT * FROM links WHERE url='' AND id=".$id);
			if ($cat->num_rows == 1)
			{
				if (empty($_POST['title']))
				{
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_link']; ?></h2></div>
<div class="boxcontent">
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<form action="?/links/add&p=<?php echo $id; ?>" method="POST">
<?php echo $lang['mod/short']; ?>: <input type="text" name="short" value="" /><br />
<?php echo $lang['mod/url']; ?>: <input type="text" name="url" value="../" /><br />
<?php echo $lang['mod/url_thread']; ?>: <input type="text" name="url_thread" value="../../" /><br />
<?php echo $lang['mod/url_index']; ?>: <input type="text" name="url_index" value="./" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" value="" /><br />
<br /><input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
				} else {
				//$parent, $url, $url_thread, $title, $short
					addBoardLink($conn, $id, $_POST['url'], $_POST['url_thread'], $_POST['url_index'],  $_POST['title'], $_POST['short']);
					
					?>
					<meta http-equiv="refresh" content="0;URL='?/links'" />
					<?php
				}
			} else {
			?>
			<meta http-equiv="refresh" content="0;URL='?/links'" />
			<?php
			}
		} else {
		?>
		<meta http-equiv="refresh" content="0;URL='?/links'" />
		<?php
		}
		break;
	case "/message":
	reqPermission(2);
		if (isset($_POST['message']))
		{
			updateConfig($conn, "global_message", $_POST['message']);
		?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/global_message_updated']; ?></h2></div>
<div class="boxcontent">
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<a href="?/message"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
		<?php
		} else {
		$config = getConfig($conn);
		$msg = $config['global_message'];
		
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/edit_global_message']; ?></h2></div>
<div class="boxcontent">
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
		<form action="?/message" method="POST">
		<textarea cols=70 rows=14 name="message"><?php echo $msg; ?></textarea><br />
		<input type="submit" value="<?php echo $lang['mod/submit']; ?>">
		</form>
		</div>
		</div>
		</div>
		</div>
		<?php
		}
		break;
		
	case "/bans":
	if ((isset($_GET['del'])) && ($_GET['del']==1))
	{
		reqPermission(1);
		if ((!empty($_GET['b'])) && (is_numeric($_GET['b'])))
		{
			$conn->query("DELETE FROM bans WHERE id=".$_GET['b']);
		}
	}
	?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/bans']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/ip']; ?></td>
<td><?php echo $lang['mod/reason']; ?></td>
<td><?php echo $lang['mod/staff_note']; ?></td>
<td><?php echo $lang['mod/created']; ?></td>
<td><?php echo $lang['mod/expires']; ?></td>
<td><?php echo $lang['mod/boards']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM bans ORDER BY created LIMIT 0, 15;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".$row['ip']."</td>";
echo "<td>".$row['reason']."</td>";
echo "<td>".$row['note']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['created'])."</td>";
if ($row['expires'] != 0)
{
echo "<td>".date("d/m/Y @ H:i", $row['expires'])."</td>";
} else {
echo "<td><b>never</b></td>";
}
echo "<td>".$row['boards']."</td>";
if ($_SESSION['type']>=1)
{
echo "<td><a href='?/bans&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
} else {
echo "<td></td>";
}
echo "</tr>";
}
?>
</tbody>
</table>
<?php printf($lang['mod/showing_bans'], 15); ?> <a href="?/bans/all"><?php echo $lang['mod/show_all']; ?></a> <a href="?/bans/recent&c=100"><?php printf($lang['mod/show_recent'], 100); ?></a>
</div>
</div>
</div>
<?php
		break;
	case "/bans/all":
	?>
	<div class="box-outer top-box">
	<div class="box-inner">
	<div class="boxbar"><h2><?php echo $lang['mod/all_bans']; ?></h2></div>
	<div class="boxcontent">
	<table>
	<thead>
	<tr>
	<td><?php echo $lang['mod/ip']; ?></td>
	<td><?php echo $lang['mod/reason']; ?></td>
	<td><?php echo $lang['mod/staff_note']; ?></td>
	<td><?php echo $lang['mod/created']; ?></td>
	<td><?php echo $lang['mod/expires']; ?></td>
	<td><?php echo $lang['mod/boards']; ?></td>
	<td><?php echo $lang['mod/delete']; ?></td>
	</tr>
	</thead>
	<tbody>
	<?php
	$result = $conn->query("SELECT * FROM bans ORDER BY created;");
	while ($row = $result->fetch_assoc())
	{
	echo "<tr>";
	echo "<td>".$row['ip']."</td>";
	echo "<td>".$row['reason']."</td>";
	echo "<td>".$row['note']."</td>";
	echo "<td>".date("d/m/Y @ H:i", $row['created'])."</td>";
	if ($row['expires'] != 0)
	{
	echo "<td>".date("d/m/Y @ H:i", $row['expires'])."</td>";
	} else {
	echo "<td><b>never</b></td>";
	}
	echo "<td>".$row['boards']."</td>";
	if ($_SESSION['type']>=1)
	{
	echo "<td><a href='?/bans&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
	} else {
	echo "<td></td>";
	}
	echo "</tr>";
	}
	?>
	</tbody>
	</table>
	</div>
	</div>
	</div>
	<?php
	break;
	case "/bans/recent":
	if ((!empty($_GET['c'])) && (is_numeric($_GET['c'])))
	{
	?>
	<div class="box-outer top-box">
	<div class="box-inner">
	<div class="boxbar"><h2><?php printf($lang['mod/recent_bans'], $_GET['c']); ?></h2></div>
	<div class="boxcontent">
	<table>
	<thead>
	<tr>
	<td><?php echo $lang['mod/ip']; ?></td>
	<td><?php echo $lang['mod/reason']; ?></td>
	<td><?php echo $lang['mod/staff_note']; ?></td>
	<td><?php echo $lang['mod/created']; ?></td>
	<td><?php echo $lang['mod/expires']; ?></td>
	<td><?php echo $lang['mod/boards']; ?></td>
	<td><?php echo $lang['mod/delete']; ?></td>
	</tr>
	</thead>
	<tbody>
	<?php
	$result = $conn->query("SELECT * FROM bans ORDER BY created LIMIT 0, ".$_GET['c'].";");
	while ($row = $result->fetch_assoc())
	{
	echo "<tr>";
	echo "<td>".$row['ip']."</td>";
	echo "<td>".$row['reason']."</td>";
	echo "<td>".$row['note']."</td>";
	echo "<td>".date("d/m/Y @ H:i", $row['created'])."</td>";
	if ($row['expires'] != 0)
	{
	echo "<td>".date("d/m/Y @ H:i", $row['expires'])."</td>";
	} else {
	echo "<td><b>never</b></td>";
	}
	echo "<td>".$row['boards']."</td>";
	if ($_SESSION['type']>=1)
	{
	echo "<td><a href='?/bans&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
	} else {
	echo "<td></td>";
	}
	echo "</tr>";
	}
	?>
	</tbody>
	</table>
	</div>
	</div>
	</div>
	<?php
	}
	break;
	case "/bans/add":
	if (empty($_GET['r']))
	{
	if (empty($_POST['ip']))
	{
		$ip = "";
		$post = "";
		$board = "";
		$postinfo = "";
		if ((!empty($_GET['p'])) && (!empty($_GET['b'])) && (is_numeric($_GET['p'])) && (isBoard($conn, $_GET['b'])))
		{
			$board = $conn->real_escape_string($_GET['b']);
			$post = $_GET['p'];
			//<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>
			$postdata = $conn->query("SELECT * FROM posts_".$board." WHERE id=".$post);
			if ($postdata->num_rows == 1)
			{
				$postinfo = $postdata->fetch_assoc();
				$ip = $postinfo['ip'];
			} else {
				$post = "";
				$board = "";
			}
		}
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php if ($_SESSION['type']>=1) { echo $lang['mod/add_ban']; } else { echo $lang['mod/add_ban_request']; } ?></h2></div>
<div class="boxcontent">
<form action="?/bans/add" method="POST">
<?php echo $lang['mod/ip']; ?>: <input type="text" name="ip" value="<?php echo $ip; ?>"/><br />
<?php echo $lang['mod/reason']; ?>: <input type="text" name="reason" /><br />
<?php echo $lang['mod/staff_note']; ?>: <input type="text" name="note" /><br />
<?php
if ($_SESSION['type']>=1) {
?>
<?php echo $lang['mod/expires_eg']; ?>: <input type="text" name="expires" /><br />
<br /><br />
<?php echo $lang['mod/boards']; ?>: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1/> <?php echo $lang['mod/all']; ?><br/>
<select name="boards[]" id="boardSelect" multiple>
<?php
$result = $conn->query("SELECT * FROM boards;");
while ($row = $result->fetch_assoc())
{
echo "<option value='",$row['short']."'>/".$row['short']."/ - ".$row['name']."</option>";
}
?>
</select><br />
<br />
<?php
}
if (!empty($postinfo))
{
?>
<input type="hidden" name="post" value="<?php echo $post; ?>" />
<input type="hidden" name="board" value="<?php echo $board; ?>" />
<?php
if ($_SESSION['type']>=1) {
if ((!empty($_GET['d'])) && ($_GET['d'] == 1))
{
?>
<input type="hidden" name="delete" value="1" /><b><?php echo $lang['mod/will_delete']; ?></b>
<?php
} else {
?>
<?php echo $lang['mod/append_text']; ?>: <input type="text" name="append_text" value='<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>' style="width: 400px;"/><input type="checkbox" name="append" value="1" checked=1/><?php echo $lang['mod/yes']; ?><br/>
<?php
}
}
}
?>
<br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
		} else {
		if (!filter_var($_POST['ip'], FILTER_VALIDATE_IP))
		{
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/ip_syntax_wrong']; ?></h2></div>
<div class="boxcontent"><a href="?/bans/add"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
</div>
</body>
</html>
		<?php
		exit;
		}
		$post = "";
		$board = "";
		$postinfo = "";
		if ((!empty($_POST['post'])) && (!empty($_POST['board'])) && (is_numeric($_POST['post'])) && (isBoard($conn, $_POST['board'])))
		{
			$board = $conn->real_escape_string($_POST['board']);
			$post = $_POST['post'];
			//<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>
			$postdata = $conn->query("SELECT * FROM posts_".$board." WHERE id=".$post);
			if ($postdata->num_rows == 0)
			{
				$post = "";
				$board = "";
			}
		}
		$boards = "";
		if ((!empty($_POST['all'])) && ($_POST['all']==1))
		{
			$boards = "*";
		} else {
			if (!empty($_POST['boards']))
			{
				foreach ($_POST['boards'] as $board)
				{
					$boards .= $board.",";
				}
			} else {
				$boards = "*";
			}
		}
		if ($boards != "*") { $boards = substr($boards, 0, strlen($boards) - 1); }
		$result = 0;
		$what = 1;
		if ($_SESSION['type'] == 0)
		{
			$append = 0;
			if ((!empty($_POST['delete'])) && ($_POST['delete']=="1"))
			{
				$append = 2;
			} else {
				if ((!empty($post)) && (!empty($_POST['append'])) && ($_POST['append'] == 1))
				{
					$append = 1;
				}
			}
			$result = addBanRequest($conn, $_POST['ip'], $_POST['reason'], $_POST['note'], $board, $post, $append);
			$what = 2;
		} else {
			$result = addBan($conn, $_POST['ip'], $_POST['reason'], $_POST['note'], $_POST['expires'], $boards);
			if ($result != -2)
			{
				if ((!empty($_POST['delete'])) && ($_POST['delete']=="1"))
				{
					deletePostMod($conn, $board, $post);
				} else {
					if ((!empty($post)) && (!empty($_POST['append'])) && ($_POST['append'] == 1))
					{
						appendToPost($conn, $board, $post, $_POST['append_text']);
					}
				}
			}
		}
		if (($what == 1) && ($result == 1))
		{
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_banned']; ?></h2></div>
<div class="boxcontent"><a href="?/bans"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		} elseif (($what == 2) && ($result == 1))
		{
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/request_sent']; ?></h2></div>
<div class="boxcontent"><a href="javascript:history.go(-2);"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/filled_wrong']; ?></h2></div>
<div class="boxcontent"><a href="javascript:history.back(-1);"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		}
		}
		} else {
			if (is_numeric($_GET['r']))
			{
				$req = $conn->query("SELECT * FROM ban_requests WHERE id=".$_GET['r']);
				if ($req->num_rows == 1)
				{
				$request = $req->fetch_assoc();
				$board = $request['board'];
				$post = $request['post'];
				//<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>
				$postdata = $conn->query("SELECT * FROM posts_".$board." WHERE id=".$post);
				if ($postdata->num_rows == 1)
				{
					$postinfo = $postdata->fetch_assoc();
					$ip = $postinfo['ip'];
				} else {
					$post = "";
					$board = "";
				}
					?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php if ($_SESSION['type']>=1) { echo $lang['mod/add_ban']; } else { echo $lang['mod/add_ban_request']; } ?></h2></div>
<div class="boxcontent">
<form action="?/bans/add" method="POST">
<?php echo $lang['mod/ip']; ?>: <input type="text" name="ip" value="<?php echo $ip; ?>"/><br />
<?php echo $lang['mod/reason']; ?>: <input type="text" name="reason" value="<?php echo $request['reason']; ?>"/><br />
<?php echo $lang['mod/staff_note']; ?>: <input type="text" name="note" value="<?php echo $request['note']; ?>"/><br />
<?php echo $lang['mod/expires_eg']; ?>: <input type="text" name="expires" /><br />
<br /><br />
<?php echo $lang['mod/boards']; ?>: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1/> <?php echo $lang['mod/all']; ?><br/>
<select name="boards[]" id="boardSelect" multiple>
<?php
$result = $conn->query("SELECT * FROM boards;");
while ($row = $result->fetch_assoc())
{
echo "<option value='",$row['short']."'>/".$row['short']."/ - ".$row['name']."</option>";
}
?>
</select><br />
<br />
<?php
if (!empty($postinfo))
{
?>
<input type="hidden" name="post" value="<?php echo $post; ?>" />
<input type="hidden" name="board" value="<?php echo $board; ?>" />
<?php
if ((!empty($_GET['d'])) && ($_GET['d'] == 1))
{
?>
<input type="hidden" name="delete" value="1" /><b><?php echo $lang['mod/will_delete']; ?></b>
<?php
} else {
?>
<?php echo $lang['mod/append_text']; ?>: <input type="text" name="append_text" value='<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>' style="width: 400px;"/><input type="checkbox" name="append" value="1" checked=1/><?php echo $lang['mod/yes']; ?><br/>
<?php
}
}
?>
<br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
				}
			}
		}
		break;
	case "/users/delete_yes":
	reqPermission(2);
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$id = $_GET['id'];
			if (isUser($conn, $id))
			{
				delUser($conn, $id);
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_deleted']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			} else {
			
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_not_exists']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			}
			
		}
		break;
	case "/users/delete":
	reqPermission(2);
		if (!empty($_GET['id']))
		{
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_want_delete']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/no_big']; ?></a> <a href="?/users/delete_yes&id=<?php echo $_GET['id']; ?>"><?php echo $lang['mod/yes_big']; ?></a></div>
</div>
</div>
				<?php
		} else {
						?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_not_exists']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		}
		break;
	case "/users/add":
	reqPermission(2);
		if ((!empty($_POST['username'])) && (!empty($_POST['password'])) && (is_numeric($_POST['type'])))
		{
			$type = $_POST['type'];
			if (empty($type)) { $type = 0; }
			$boards = "";
			if (((!empty($_POST['all'])) && ($_POST['all']==1)) || ($type == 2))
			{
				$boards = "*";
			} else {
				foreach ($_POST['boards'] as $board)
				{
					$boards .= $board.",";
				}
			}
			if ($boards != "*") { $boards = substr($boards, 0, strlen($boards) - 1); }
			$result = addUser($conn, $_POST['username'], $_POST['password'], $type, $boards);
			if ($result == 1)
			{
			?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_added']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			} else {
			?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_exists']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			}
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/fill_all_fields']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		}
		break;
	case "/users/edit":
		reqPermission(2);
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$id = $_GET['id'];
			if (isUser($conn, $id))
			{
				if ((!empty($_POST['username'])) && (is_numeric($_POST['type'])))
				{
					$type = $_POST['type'];
					if (empty($type)) { $type = 0; }
					$boards = "";
					if (((!empty($_POST['all'])) && ($_POST['all']==1)) || ($type == 2))
					{
						$boards = "*";
					} else {
						foreach ($_POST['boards'] as $board)
						{
							$boards .= $board.",";
						}
					}
					if ($boards != "*") { $boards = substr($boards, 0, strlen($boards) - 1); }
					updateUser($conn, $id, $_POST['username'], $_POST['password'], $_POST['type'], $boards);
					?>
					<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_updated']; ?></h2></div>
<div class="boxcontent">
<a href="?/users"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
					<?php
				} else {
					$result = $conn->query("SELECT * FROM users WHERE id=".$_GET['id']);
					$data = $result->fetch_assoc();
					$boards = $data['boards'];
					if ($data['boards'] != "*") { $board = explode(",", $data['boards']); }
		?>
				<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/edit_user']; ?></h2></div>
<div class="boxcontent">
<form action="?/users/edit&id=<?php echo $id; ?>" method="POST">
<?php echo $lang['mod/username']; ?>: <input type="text" name="username" value="<?php echo $data['username']; ?>"/><br />
<?php echo $lang['mod/password_leave_blank']; ?>: <input type="password" name="password"/><br />
<?php
$janitor = "";
$moderator = "";
$administrator = "";

switch ($data['type'])
{
	case 0:
		$janitor = " selected ";
		break;
	case 1:
		$moderator = " selected ";
		break;
	case 2:
		$administrator = " selected ";
		break;
}
?>
<?php echo $lang['mod/type']; ?>: <select name="type"><option value="0"<?php echo $janitor; ?>><?php echo $lang['mod/janitor']; ?></option><option value="1"<?php echo $moderator; ?>><?php echo $lang['mod/moderator']; ?></option><option value="2"<?php echo $administrator; ?>><?php echo $lang['mod/administrator']; ?></option></select>

<br /><br />
<?php
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
$result = $conn->query("SELECT * FROM boards;");
while ($row = $result->fetch_assoc())
{
$checked = "";
if ($boards !== "*")
{
	if (in_array($boards, $row['short']))
	{
		$checked = " checked ";
	}
}
echo "<option onClick='document.getElementById(\"all\").checked=false;' value='",$row['short']."'".$checked.">/".$row['short']."/ - ".$row['name']."</option>";
}
?>
</select><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div><br />
<?php
				}
			}
		}
		break;
	case "/notes":
	?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/your_notes']; ?></h2></div>
<div class="boxcontent">
<?php
$result = $conn->query("SELECT * FROM notes WHERE mod_id=".$_SESSION['id']." ORDER BY created DESC;");
while ($row = $result->fetch_assoc())
{
echo '<div class="content">';
echo '<h3><span class="newssub">'.date("d/m/Y @ H:i", $row['created']).'</span> <a href="?/notes/delete&id='.$row['id'].'">Delete</a></span></h3>';
echo $row['note'];
echo '</div>';
}
?>
</div>
</div>
</div><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_note']; ?></h2></div>
<div class="boxcontent">
<form action="?/notes/add" method="POST">
<textarea name="note" cols=70 rows=12></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
<?php
		break;
	case "/notes/add":
		if (!empty($_POST['note']))
		{
			$note = $conn->real_escape_string($_POST['note']);
			$conn->query("INSERT INTO notes (mod_id, note, created) VALUES (".$_SESSION['id'].", '".$note."', ".time().")");
		?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/note_added']; ?></h2></div>
<div class="boxcontent">
<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
<?php
		} else {
				?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/fill_all_fields']; ?></h2></div>
<div class="boxcontent">
<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
<?php
		}
		break;
	case "/notes/delete":
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$note = $conn->query("SELECT * FROM notes WHERE id=".$_GET['id']);
			if ($note->num_rows == 1)
			{
				$info = $note->fetch_assoc();
				if ($info['mod_id'] == $_SESSION['id'])
				{
					$conn->query("DELETE FROM notes WHERE id=".$_GET['id']);
					?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/note_deleted']; ?></h2></div>
<div class="boxcontent">
<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
<?php
				} else {
				?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/error']; ?></h2></div>
<div class="boxcontent">
<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
<?php
				}
			} else {
			?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/error']; ?></h2></div>
<div class="boxcontent">
<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
<?php
			}
		} else {
				?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/error']; ?></h2></div>
<div class="boxcontent">
<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
<?php
		}
		break;
	case "/board":
		if ((!empty($_GET['b'])) && (isBoard($conn, $_GET['b'])))
		{
			canBoard($_GET['b']);
			$board = getBoardData($conn, $_GET['b']);
			$mode = "page";
			$page = 0;
			if ((!empty($_GET['p'])) && (is_numeric($_GET['p'])) && ($_GET['p'] >= 0) && ($_GET['p'] <= 15))
			{
				$page = $_GET['p'];
				showView($conn, $_GET['b'], 0, $page);
			} elseif ((!empty($_GET['t'])) && (is_numeric($_GET['t'])))
			{
				$mode = "thread";
				$page = $_GET['t'];
				showView($conn, $_GET['b'], 1, $page);
			} else {
			
				showView($conn, $_GET['b'], 0, 0);
			}
			
			
		}
		break;
	case "/board/action":
		if (!empty($_POST['mode']))
		{
		if ((!empty($_POST['board'])) || (isBoard($conn, $_POST['board'])))
		{
			canBoard($_POST['board']);
		}
		$mode = $_POST['mode'];
		switch($mode)
		{
			case "regist":
				$filename = null;
				if ((empty($_POST['board'])) || (!isBoard($conn, $_POST['board'])))
				{
				?>
				
	<html>
	<head>
	<title><?php echo $lang['mod/error']; ?></title>
	</head>
	<body>
				<?php
					echo "<center><h1>".$lang['mod/no_board']."</h1></center></body></html>";
					exit;
				}
				$board = $_POST['board'];
				canBoard($board);
				?>
	<html>
	<head>
	<title><?php echo $lang['mod/updating_index']; ?></title>
	</head>
	<body>
	<center><h1><?php echo $lang['mod/updating_index']; ?></h1></center>
				<?php
				
				$md5 = "";
				$bdata = getBoardData($conn, $_POST['board']);
				if ((!empty($_POST['embed'])) && (!empty($_FILES['upfile']['tmp_name'])))
				{
					echo "<center><h1>".$lang['mod/choose_one']."</h1></center></body></html>";
					exit;
				}
				if (!empty($_POST['embed']))
				{
					$embed_table = array();
					$result = $conn->query("SELECT * FROM embeds;");
					while ($row = $result->fetch_assoc())
					{
						$embed_table[] = $row;
					}
					if ((isEmbed($_POST['embed'], $embed_table)) && ($bdata['embeds']==1))
					{
						$filename = "embed:".$_POST['embed'];
					} else {
						echo "<center><h1>".$lang['mod/embed_not_supported']."</h1></center></body></html>";
						exit;
					}
				} else {
					if ((empty($_FILES['upfile']['tmp_name'])) && (!empty($_FILES['upfile']['name'])))
					{
						echo "<h1>".$lang['mod/file_too_big']."</h1></body></html>";
						exit;
					}
					if (!empty($_FILES['upfile']['tmp_name']))
					{
						$target_path = "./".$board."/src/";
						$fileid = time() . mt_rand(10000000, 999999999);
						$ext = pathinfo($_FILES['upfile']['name'], PATHINFO_EXTENSION);
						$filename = $fileid . "." . $ext; 
						$target_path .= $filename;
						$file_size = $_FILES['upfile']['size'];
						if ($file_size > 2097152)
						{
							echo "<h1>".$lang['mod/file_too_big']."</h1></body></html>";
							exit;
						}
						if (!isImage($_FILES['upfile']['tmp_name']))
						{
							echo "<h1>".$lang['mod/file_not_img']."</h1></body></html>";
							exit;
						}
						$md5 = md5_file($_FILES['upfile']['tmp_name']);
						if(move_uploaded_file($_FILES['upfile']['tmp_name'], $target_path)) {
							printf($lang['mod/file_uploaded'], basename( $_FILES['upfile']['name']));
						} else {
							echo $lang['mod/upload_error'];
							$filename = "";
						}
					}
				}
				$name = "Anonymous";
				if (!empty($_POST['name'])) { $name = $_POST['name']; }
				$resto = 0;
				if (isset($_POST['resto'])) { $resto = $_POST['resto']; }
				$password = "";
				if (empty($_POST['pwd']))
				{
					if (isset($_COOKIE['password']))
					{
						$password = $_COOKIE['password'];
					} else {
						$password = randomPassword();
					}
				} else {
					$password = $_POST['pwd'];
				}
				if (substr($filename, 0, 6) != "embed:")
				{
					if (!empty($_FILES['upfile']['tmp_name']))
					{
						if ($resto != 0)
						{
							if (thumb($board, $fileid.".".$ext, 125) < 0)
							{
								echo "<h1>".$lang['no_thumb']."</h1></body></html>"; exit;
							}
						} else {
							if (thumb($board, $fileid.".".$ext) < 0)
							{
								echo "<h1>".$lang['no_thumb']."</h1></body></html>"; exit;
							}
						}
					}
				}
				setcookie("password", $password, time() + 86400*256);
				$capcode = 0;
				$raw = 0;
				$sticky = 0;
				$lock = 0;
				$nolimit = 0;
				if ((!empty($_POST['nolimit'])) && ($_POST['nolimit']==1))
				{
					$nolimit = 1;
				}
				if ((!empty($_POST['capcode'])) && ($_POST['capcode']==1))
				{
					$capcode = $_SESSION['type'];
				}
				if ((!empty($_POST['raw'])) && ($_POST['raw']==1))
				{
					$raw = 1;
				}
				if ((!empty($_POST['sticky'])) && ($_POST['sticky']==1))
				{
					$sticky = 1;
				}
				if ((!empty($_POST['lock'])) && ($_POST['lock']==1))
				{
					$lock = 1;
				}
				$spoiler = 0;
				if ((!empty($_POST['spoiler'])) && ($_POST['spoiler'] == 1) && ($bdata['spoilers'] == 1) && (substr($filename, 0, 6) != "embed:"))
				{
					$spoiler = 1;
				}
				$embed = 0;
				if (substr($filename, 0, 6) != "embed:")
				{
					$fname = $_FILES['upfile']['name'];
					$filename = "";
					if (empty($_FILES['upfile']['tmp_name']))
					{
						$fname = "";
					} else {
						$filename = $fileid.".".$ext;
					}
				} else {
					$embed = 1;
					$fname = "embed";
				}
				$is = addPostMod($conn, $_POST['board'], $name, $_POST['email'], $_POST['sub'], $_POST['com'], $password, $filename, $fname, $resto, $md5, $spoiler, $embed, $capcode, $raw, $sticky, $lock, $nolimit);
				if ($is == -16)
				{
					echo "<h1>".$lang['mod/board_not_found']."</h1></body></html>"; exit;
				}
				break;
			case "usrform":
				if (!empty($_POST['delete']))
				{
					$onlyimgdel = 0;
					if (empty($_POST['board']))
					{
						echo "<h1>".$lang['mod/no_board']."</h1></body></html>";
						exit;
					}
					$board = $_POST['board'];
					if ((isset($_POST['onlyimgdel']) && ($_POST['onlyimgdel'] == "on"))) { $onlyimgdel = 1; }
					foreach ($_POST as $key => $value)
					{
						if ($value == "delete")
						{
							$done = deletePostMod($conn, $_POST['board'], $key, $onlyimgdel);
							if ($done == -1) {
								printf($lang['mod/post_bad_password'], $key);
								echo "<br />";
							} elseif ($done == -2) {
								printf($lang['mod/post_not_found'], $key);
								echo "<br />";
							} elseif ($done == -3) {
								printf($lang['mod/post_no_image'], $key);
								echo "<br />";
							} elseif ($done == 1) {
								printf($lang['mod/post_deleted_image'], $key);
								echo "<br />";
							} elseif ($done == 2) {
								printf($lang['mod/post_deleted'], $key);
								echo "<br />";
							}
							if ($done == -16)
							{
								echo "<h1>".$lang['mod/board_not_found']."</h1></body></html>"; exit;
							}
						}
					}
					echo '<meta http-equiv="refresh" content="2;URL='."'?/board&b=".$_POST['board']."'".'">';
				}
				break;
		}
	?>
	</body>
	</html>
	<?php
	}
		break;
	case "/reports":
	if ((!empty($_GET['cl'])) && ($_GET['cl']==1))
	{
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$conn->query("DELETE FROM reports WHERE id=".$_GET['id']);
		}
	}
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/reports']; ?></h2></div>
<div class="boxcontent">
<?php
if ($_SESSION['type'] >= 1)
{
?>
<a href="?/reports/clear_all"><?php echo $lang['mod/clear_all']; ?></a>
<?php
}
?>
<table>
<thead>
<tr>
<td><?php echo $lang['mod/post']; ?></td>
<td><?php echo $lang['mod/file']; ?></td>
<td><?php echo $lang['mod/comment']; ?></td>
<td><?php echo $lang['mod/reason']; ?></td>
<td><?php echo $lang['mod/reporter_ip']; ?></td>
<td><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php
		require_once( "./jbbcode/Parser.php" );
		$parser = new JBBCode\Parser();
		$bbcode = $conn->query("SELECT * FROM bbcodes;");
		
		while ($row = $bbcode->fetch_assoc())
		{
			$parser->addBBCode($row['name'], $row['code']);
		}
		$result = $conn->query("SELECT * FROM reports ORDER BY created DESC");
		while ($row = $result->fetch_assoc())
		{
			$post = $conn->query("SELECT * FROM posts_".$row['board']." WHERE id=".$row['reported_post']);
			if ($post->num_rows == 0)
			{
				$conn->query("DELETE FROM reports WHERE id=".$row['id']);
				continue;
			}
			$pdata = $post->fetch_assoc();
			$resto = $pdata['id'];
			if ($pdata['resto'] != 0)
			{
				$resto = $pdata['resto'];
			}
			echo "<tr>";
			echo "<td><a href='?/board&b=".$row['board']."&t=".$resto."#p".$row['reported_post']."'>/".$row['board']."/".$row['reported_post']."</a></td>";
			if (!empty($pdata['filename']))
			{
				if ($pdata['filename'] == "deleted")
				{
					echo "<td><img src='./img/deleted.gif' /></td>";
				} elseif (substr($pdata['filename'], 0, 8) == "spoiler:") {
					echo "<td><a href='./".$row['board']."/src/".substr($pdata['filename'], 8)." target='_blank'><img src='./".$row['board']."/src/thumb/".substr($pdata['filename'], 8)."' /></a></td>";
				} elseif (substr($pdata['filename'], 0, 6) == "embed:") {
					echo "<td><a href='".substr($pdata['filename'], 6)."'>Embed</a></td>";
				} else {
					echo "<td><a href='./".$row['board']."/src/".$pdata['filename']." target='_blank'><img src='./".$row['board']."/src/thumb/".$pdata['filename']."' /></a></td>";
				}
			} else {
				echo "<td></td>";
			}
			if ($pdata['raw'] == 0)
			{
				echo "<td>".processComment($row['board'], $conn, $pdata['comment'], $parser, 2)."</td>";
			} elseif ($pdata['raw'] == 2)
			{
				echo "<td>".processComment($row['board'], $conn, $pdata['comment'], $parser, 2, 0)."</td>";
			} else {
				echo "<td>".$pdata['comment']."</td>";
			}
			echo "<td>".$row['reason']."</td>";
			echo "<td>".$row['reporter_ip']."</td>";
			echo "<td>[ <a href='?/reports&cl=1&id=".$row['id']."'>C</a> ] [ <a href='?/bans/add&b=".$row['board']."&p=".$row['reported_post']."'>B</a> "; 
			if ($_SESSION['type']>=1)
			{
				echo "/ <a href='?/bans/add&b=".$row['board']."&p=".$row['reported_post']."&d=1'>&</a> / <a href='?/delete_post&b=".$row['board']."&p=".$row['reported_post']."'>D</a> / <a href='?/delete_post&b=".$row['board']."&p=".$row['reported_post']."&f=1'>F</a> ]"; 
				echo "[ <a href='?/info&ip=".$pdata['ip']."'>N</a> ]</td>";
			} else {
				echo "]</td>";
			}
			echo "</tr>";
		}
		?>
		</tbody>
		</table>
		</div>
		</div>
		</div>
		<script type="text/javascript">parent.nav.location.reload();</script>
		<?php
		break;
	case "/reports/clear_all_yes":
		reqPermission(1);
		$conn->query("TRUNCATE TABLE reports;");
		?>
		<meta http-equiv="refresh" content="0;URL='?/reports'" />
		<?php
		break;
	case "/reports/clear_all":
		reqPermission(1);
		?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/want_clear_reports']; ?></h2></div>
<div class="boxcontent"><a href="?/reports"><?php echo $lang['mod/no_big']; ?></a> <a href="?/reports/clear_all_yes"><?php echo $lang['mod/yes_big']; ?></a></div>
</div>
</div>
		<?php
		break;
	case "/delete_post":
		reqPermission(1);
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$f = "";
			if ((!empty($_GET['f'])) && ($_GET['f'] == 1))
			{
				$f = "&f=1";
			}
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/want_delete_post']; ?></h2></div>
<div class="boxcontent"><a href="javascript:history.back(-1);"><?php echo $lang['mod/no_big']; ?></a> <a href="?/delete_post/yes&b=<?php echo $_GET['b']; ?>&p=<?php echo $_GET['p'].$f; ?>"><?php echo $lang['mod/yes_big']; ?></a></div>
</div>
</div>
		<?php
		}
		break;
	case "/delete_post/yes":
		reqPermission(1);
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$imageonly = 0;
			canBoard($_GET['b']);
			if ((!empty($_GET['f'])) && ($_GET['f'] == 1))
			{
				$imageonly = 1;
			}
			deletePostMod($conn, $_GET['b'], $_GET['p'], $imageonly);
			if ($imageonly == 1)
			{
			?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/file_deleted']; ?></h2></div>
<div class="boxcontent"><a href="?/board&b=<?php echo $_GET['b']; ?>"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
			} else {
			?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/post_deleted_short']; ?></h2></div>
<div class="boxcontent"><a href="?/board&b=<?php echo $_GET['b']; ?>"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
		}
		} else {
		
		}
		break;
	case "/info":
		if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php printf($lang['mod/ip_info'], $_GET['ip']); ?></h2></div>
<div class="boxcontent">
<?php
if ($_SESSION['type']>=1)
{
?>
<a href="?/search/ip&ip=<?php echo $_GET['ip']; ?>"><?php echo $lang['mod/search_ip']; ?></a><br />
<?php
}
?>
<b><?php printf($lang['mod/recent_bans_ip'], 15); ?></b>
<table>
<thead>
<tr>
<td><?php echo $lang['mod/ip']; ?></td>
<td><?php echo $lang['mod/reason']; ?></td>
<td><?php echo $lang['mod/staff_note']; ?></td>
<td><?php echo $lang['mod/created']; ?></td>
<td><?php echo $lang['mod/expires']; ?></td>
<td><?php echo $lang['mod/boards']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM bans WHERE ip='".$_GET['ip']."' ORDER BY created LIMIT 0, 15;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".$row['ip']."</td>";
echo "<td>".$row['reason']."</td>";
echo "<td>".$row['note']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['created'])."</td>";
if ($row['expires'] != 0)
{
echo "<td>".date("d/m/Y @ H:i", $row['expires'])."</td>";
} else {
echo "<td><b>never</b></td>";
}
echo "<td>".$row['boards']."</td>";
if ($_SESSION['type']>=1)
{
echo "<td><a href='?/bans&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
} else {
echo "<td></td>";
}
echo "</tr>";
}
?>
</tbody>
</table>
<br />
<b><?php echo $lang['mod/notes_ip']; ?></b>
<br />
<table>
<thead>
<td><?php echo $lang['mod/created']; ?></td>
<td><?php echo $lang['mod/note']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM ip_notes WHERE ip='".$_GET['ip']."';");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".date("d/m/Y(D)H:i:s", $row['created'])."</td>";
echo "<td>".$row['text']."</td>";
echo "<td><a href='?/ipnotes/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></td>";
echo "</td>";
}
?>
</tbody>
</table>
</div>
</div>
</div><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_note']; ?></h2></div>
<div class="boxcontent">
<form action="?/ipnotes/add&ip=<?php echo $_GET['ip']; ?>" method="POST">
<textarea name="note" cols=70 rows=12></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
		}
		break;
	case "/ipnotes":
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/recent_ip_notes']; ?></h2></div>
<div class="boxcontent">
	<table>
<thead>
<td><?php echo $lang['mod/created']; ?></td>
<td><?php echo $lang['mod/note']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM ip_notes LIMIT 0, 15;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".date("d/m/Y(D)H:i:s", $row['created'])."</td>";
echo "<td>".$row['text']."</td>";
echo "<td><a href='?/ipnotes/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></td>";
echo "</tr>";
}
?>
</tbody>
</table>
<?php printf($lang['mod/showing_notes'], 15); ?> <a href="?/ipnotes/all"><?php echo $lang['mod/show_all']; ?></a>
</div>
</div>
</div><br />
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_ip_note']; ?></h2></div>
<div class="boxcontent">
<form action="?/ipnotes/add" method="POST">
<?php echo $lang['mod/ip']; ?>: <input type="text" name="ip" /><br />
<textarea name="note" cols=70 rows=12></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
	<?php
		break;
	case "/ipnotes/all":
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/all_ip_notes']; ?></h2></div>
<div class="boxcontent">
	<table>
<thead>
<td><?php echo $lang['mod/created']; ?></td>
<td><?php echo $lang['mod/note']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM ip_notes;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".date("d/m/Y(D)H:i:s", $row['created'])."</td>";
echo "<td>".$row['text']."</td>";
if ($_SESSION['type']>=1)
{
echo "<td><a href='?/ipnotes/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></td>";
} else {
echo "<td></td>";
}
echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
<?php
	break;
	case "/ipnotes/add":
		$ip = "";
		if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
			$ip = $_GET['ip'];
		}
		if ((!empty($_POST['ip'])) && (filter_var($_POST['ip'], FILTER_VALIDATE_IP)))
		{
			$ip = $_POST['ip'];
		}
		
		if (empty($ip))
		{
		?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/no_ip']; ?></h2></div>
<div class="boxcontent"><a href="?/ipnotes"><?php echo $lang['mod/back']; ?></a></div>
</div></div>

			<?php
		} else {
			if ((!empty($ip)) && (!empty($_POST['note'])))
			{
				$note = processEntry($conn, $_POST['note']);
				$conn->query("INSERT INTO ip_notes (ip, text, created, mod_id) VALUES ('".$ip."', '".$note."', ".time().", ".$_SESSION['id'].")");
				?>
				<div class="box-outer top-box">
	<div class="box-inner">
	<div class="boxbar"><h2><?php echo $lang['mod/ip_note_added']; ?></h2></div>
	<div class="boxcontent"><a href="?/ipnotes"><?php echo $lang['mod/back']; ?></a></div>
	</div></div>

				<?php
			}
		}
		if (empty($_POST['note']))
		{
		?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/fill_all_fields']; ?></h2></div>
<div class="boxcontent"><a href="?/ipnotes"><?php echo $lang['mod/back']; ?></a></div>
</div></div>

			<?php
		}
		break;
	case "/ipnotes/delete":
		reqPermission(1);
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$conn->query("DELETE FROM ip_notes WHERE id=".$_GET['id']);
		}
		?>
		<meta http-equiv="refresh" content="0;URL='?/ipnotes'" />
		<?php
		break;
	case "/sticky/toggle":
		if ((!empty($_GET['b'])) && (!empty($_GET['t'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['t'])))
		{
			canBoard($_GET['b']);
			$result = $conn->query("SELECT * FROM posts_".$_GET['b']." WHERE id=".$_GET['t']." AND resto=0");
			if ($result->num_rows == 1)
			{
				$pdata = $result->fetch_assoc();
				if ($pdata['sticky'] == 1)
				{
					$conn->query("UPDATE posts_".$_GET['b']." SET sticky=0 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/unstickied']; ?></h2></div>
<meta http-equiv="refresh" content="1;URL='?/board&b=<?php echo $_GET['b']."&t=".$_GET['t']; ?>'" />
</div>
</div>
		<?php
				} else {
					$conn->query("UPDATE posts_".$_GET['b']." SET sticky=1 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/stickied']; ?></h2></div>
<meta http-equiv="refresh" content="1;URL='?/board&b=<?php echo $_GET['b']."&t=".$_GET['t']; ?>'" />
</div>
</div>
		<?php
				}
			} else {
			?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/thread_not_found']; ?></h2></div>
</div>
</div>
		<?php
			}
		} else {
		
		}
		break;
	case "/locked/toggle":
		if ((!empty($_GET['b'])) && (!empty($_GET['t'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['t'])))
		{
			canBoard($_GET['b']);
			$result = $conn->query("SELECT * FROM posts_".$_GET['b']." WHERE id=".$_GET['t']." AND resto=0");
			if ($result->num_rows == 1)
			{
				$pdata = $result->fetch_assoc();
				if ($pdata['locked'] == 1)
				{
					$conn->query("UPDATE posts_".$_GET['b']." SET locked=0 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/unlocked']; ?></h2></div>
<meta http-equiv="refresh" content="1;URL='?/board&b=<?php echo $_GET['b']."&t=".$_GET['t']; ?>'" />
</div>
</div>
		<?php
				} else {
					$conn->query("UPDATE posts_".$_GET['b']." SET locked=1 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/locked']; ?></h2></div>
<meta http-equiv="refresh" content="1;URL='?/board&b=<?php echo $_GET['b']."&t=".$_GET['t']; ?>'" />
</div>
</div>
		<?php
				}
			} else {
			?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/thread_not_found']; ?></h2></div>
</div>
</div>
		<?php
			}
		} else {
		
		}
		break;
	case "/antibump/toggle":
		if ((!empty($_GET['b'])) && (!empty($_GET['t'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['t'])))
		{
			canBoard($_GET['b']);
			$result = $conn->query("SELECT * FROM posts_".$_GET['b']." WHERE id=".$_GET['t']." AND resto=0");
			if ($result->num_rows == 1)
			{
				$pdata = $result->fetch_assoc();
				if ($pdata['sage'] == 1)
				{
					$conn->query("UPDATE posts_".$_GET['b']." SET sage=0 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/ab_off']; ?></h2></div>
<meta http-equiv="refresh" content="1;URL='?/board&b=<?php echo $_GET['b']."&t=".$_GET['t']; ?>'" />
</div>
</div>
		<?php
				} else {
					$conn->query("UPDATE posts_".$_GET['b']." SET sage=1 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/ab_on']; ?></h2></div>
<meta http-equiv="refresh" content="1;URL='?/board&b=<?php echo $_GET['b']."&t=".$_GET['t']; ?>'" />
</div>
</div>
		<?php
				}
			} else {
			?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/thread_not_found']; ?></h2></div>
</div>
</div>
		<?php
			}
		} else {
		
		}
		break;
	case "/locked":
		?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/locked']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/id']; ?></td>
<td><?php echo $lang['mod/comment']; ?></td>
<td><?php echo $lang['mod/unlock']; ?></td>
</tr>
</thead>
<tbody>
	<?php
	require_once( "./jbbcode/Parser.php" );
	$parser = new JBBCode\Parser();
	$bbcode = $conn->query("SELECT * FROM bbcodes;");
	
	while ($row = $bbcode->fetch_assoc())
	{
		$parser->addBBCode($row['name'], $row['code']);
	}
	$boards = $conn->query("SELECT * FROM boards ORDER BY short ASC;");
	while ($row = $boards->fetch_assoc())
	{
		$threads = $conn->query("SELECT * FROM posts_".$row['short']." WHERE locked=1 AND resto=0 ORDER BY lastbumped DESC;");
		while ($thread = $threads->fetch_assoc())
		{
			echo "<tr>";
			echo "<td><a href='?/board&b=".$row['short']."&t=".$thread['id']."#p".$thread['id']."'>/".$row['short']."/".$thread['id']."</a></td>";
			if ($thread['raw'] == 0)
			{
				echo "<td>".processComment($row['short'], $conn, $thread['comment'], $parser, 2)."</td>";
			} elseif ($thread['raw'] == 2)
			{
				echo "<td>".processComment($row['short'], $conn, $thread['comment'], $parser, 2, 0)."</td>";
			} else {
				echo "<td>".$thread['comment']."</td>";
			}
			echo "<td><a href='?/locked/toggle&b=".$row['short']."&t=".$thread['id']."'>".$lang['mod/unlock']."</a></td>";
			echo "</tr>";
		}
	}
	?>
</tbody>
</table>
</div>
</div>
</div>
		<?php
		break;
	case "/sticky":
		?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/sticky']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/id']; ?></td>
<td><?php echo $lang['mod/comment']; ?></td>
<td><?php echo $lang['mod/unstick']; ?></td>
</tr>
</thead>
<tbody>
	<?php
	require_once( "./jbbcode/Parser.php" );
	$parser = new JBBCode\Parser();
	$bbcode = $conn->query("SELECT * FROM bbcodes;");
	
	while ($row = $bbcode->fetch_assoc())
	{
		$parser->addBBCode($row['name'], $row['code']);
	}
	$boards = $conn->query("SELECT * FROM boards ORDER BY short ASC;");
	while ($row = $boards->fetch_assoc())
	{
		$threads = $conn->query("SELECT * FROM posts_".$row['short']." WHERE sticky=1 AND resto=0 ORDER BY lastbumped DESC;");
		while ($thread = $threads->fetch_assoc())
		{
			echo "<tr>";
			echo "<td><a href='?/board&b=".$row['short']."&t=".$thread['id']."#p".$thread['id']."'>/".$row['short']."/".$thread['id']."</a></td>";
			if ($thread['raw'] == 0)
			{
				echo "<td>".processComment($row['short'], $conn, $thread['comment'], $parser, 2)."</td>";
			} elseif ($thread['raw'] == 2)
			{
				echo "<td>".processComment($row['short'], $conn, $thread['comment'], $parser, 2, 0)."</td>";
			} else {
				echo "<td>".$thread['comment']."</td>";
			}
			echo "<td><a href='?/sticky/toggle&b=".$row['short']."&t=".$thread['id']."'>".$lang['mod/unstick']."</a></td>";
			echo "</tr>";
		}
	}
	?>
</tbody>
</table>
</div>
</div>
</div>
		<?php
		break;
	case "/appeals":
		reqPermission(1);
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/appeals']; ?></h2></div>
<div class="boxcontent">
<a href="?/appeals/clear_all"><?php echo $lang['mod/clear_all']; ?></a>
<table>
<thead>
<tr>
<td><?php echo $lang['mod/ip']; ?></td>
<td><?php echo $lang['mod/ban_reason']; ?></td>
<td><?php echo $lang['mod/staff_note']; ?></td>
<td><?php echo $lang['mod/days_left']; ?></td>
<td><?php echo $lang['mod/e_mail']; ?></td>
<td><?php echo $lang['mod/appeal_text']; ?></td>
<td><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php
$appeals = $conn->query("SELECT * FROM appeals;");
while ($row = $appeals->fetch_assoc())
{
	if ($row['rangeban'] == 0)
	{
		$bandata = $conn->query("SELECT * FROM bans WHERE id=".$row['ban_id']);
	} else {
		$bandata = $conn->query("SELECT * FROM rangebans WHERE id=".$row['ban_id']);
	}
	if ($bandata->num_rows == 1)
	{
		$ban = $bandata->fetch_assoc();
		echo "<tr>";
		if ($row['rangeban'] == 0)
		{
			echo "<td>".$ban['ip']."</td>";
		} else {
			echo "<td>".$ban['start_ip']." - ".$ban['end_ip']." ( ".$row['ip']." )</td>";
		}
		if ($ban['expires'] != 0)
		{
			$left = floor($ban['expires'] - time()/(60*60*24));
		} else {
			$left = -1;
		}
		echo "<td>".$ban['reason']."</td>";
		echo "<td>".$ban['note']."</td>";
		if ($left = -1)
		{
			echo "<td><b>".$lang['mod/permaban']."</b></td>";
		} else {
			echo "<td>".$left." days</td>";
		}
		echo "<td>".$row['email']."</td>";
		echo "<td>".$row['msg']."</td>";
		echo "<td> [ <a href='?/appeals/clear&id=".$row['id']."'>C</a> / <a href='?/bans&del=1&b=".$ban['id']."'>U</a> ]</td>";
		echo "</tr>";
	} else {
		$conn->query("DELETE FROM appeals WHERE id=".$row['id']);
	}
}
?>
</tbody>
</div>
</div>
</div>
<script type="text/javascript">parent.nav.location.reload();</script>
		<?php
		break;
	case "/appeals/clear":
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$conn->query("DELETE FROM appeals WHERE id=".$_GET['id']);
			?>
			<meta http-equiv="refresh" content="0;URL='?/appeals'" />
			<?php
		}
		break;
	case "/appeals/clear_all_yes":
		reqPermission(1);
		$conn->query("TRUNCATE TABLE appeals;");
		?>
		<meta http-equiv="refresh" content="0;URL='?/appeals'" />
		<?php
		break;
	case "/appeals/clear_all":
		reqPermission(1);
		?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/want_clear_appeals']; ?></h2></div>
<div class="boxcontent"><a href="?/appeals"><?php echo $lang['mod/no_big']; ?></a> <a href="?/appeals/clear_all_yes"><?php echo $lang['mod/yes_big']; ?></a></div>
</div>
</div>
		<?php
		break;
	case "/config":
		$config = getConfig($conn);
		?>
				<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/configuration']; ?></h2></div>
<div class="boxcontent">
<a href="?/config/reset">Reset config</a>
		<form action="?/config/update" method="POST">
		<?php echo $lang['mod/frontpage_style']; ?>: <select name="frontpage_style">
		<option value="0" <?php if ($config['frontpage_style'] == 0) { echo "selected"; } ?>>Kusaba X</option>
		<option value="1" <?php if ($config['frontpage_style'] == 1) { echo "selected"; } ?>>4chan</option></select><br />
		<?php echo $lang['mod/frontpage_url']; ?>: <input type="text" name="frontpage_url" value="<?php echo $config['frontpage_url']; ?>" /><br />
		<?php echo $lang['mod/frontpage_menu_url']; ?>: <input type="text" name="frontpage_menu_url"  value="<?php echo $config['frontpage_menu_url']; ?>" /><br />
		<?php echo $lang['mod/news_url']; ?>: <input type="text" name="news_url" value="<?php echo $config['news_url']; ?>" /><br />
		<?php echo $lang['mod/sitename']; ?>: <input type="text" name="sitename" value="<?php echo $config['sitename']; ?>"  /><br />
		<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
		</form>
		</div>
		</div>
		</div>
		<?php
		break;
	case "/inbox":
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/inbox']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<td><?php echo $lang['mod/title']; ?></td>
<td><?php echo $lang['mod/date']; ?></td>
<td><?php echo $lang['mod/from']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</thead>
<tbody>
		<?php
		$pms = $conn->query("SELECT users.username, pm.* FROM pm LEFT JOIN users ON pm.from_user=users.id WHERE pm.to_user=".$_SESSION['id']." ORDER BY pm.created DESC");
		while ($row = $pms->fetch_assoc())
		{
			echo "<tr>";
			if ($row['read_msg']==0)
			{
				echo "<td><b><a href='?/inbox/read&id=".$row['id']."'>".$row['title']."</a></b></td>";
			} else {
				echo "<td><a href='?/inbox/read&id=".$row['id']."'>".$row['title']."</a></td>";
			}
			echo "<td>".date("d/m/Y @ H:i", $row['created'])."</td>";
			echo "<td>".$row['username']."</td>";
			echo "<td><a href='?/inbox/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></td>";
			echo "</tr>";
		}
		?>
		</tbody>
		</div></div></div>
		<?php
		//created, from, to, title, text, read
		break;
	case "/inbox/new":
		if ((!empty($_POST['to'])) && (!empty($_POST['title'])) && (!empty($_POST['text'])))
		{
			$result = $conn->query("SELECT * FROM users WHERE username='".$conn->real_escape_string($_POST['to'])."'");
			if ($result->num_rows == 1)
			{
				$row = $result->fetch_assoc();
				$text = processEntry($conn, $_POST['text']);
				$title = $conn->real_escape_string($_POST['title']);
				$conn->query("INSERT INTO pm (created, from_user, to_user, title, text, read_msg) VALUES (".time().", ".$_SESSION['id'].", ".$row['id'].", '".$title."', '".$text."', 0)");
			?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/msg_sent']; ?></h2></div>
<div class="boxcontent"><a href="?/inbox/new"><?php echo $lang['mod/back']; ?></a></div>
</div></div>
			<?php
			} else {
			?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_not_found']; ?></h2></div>
<div class="boxcontent"><a href="?/inbox/new"><?php echo $lang['mod/back']; ?></a></div>
</div></div>
			<?php
			}
		} else {
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/send_message']; ?></h2></div>
<div class="boxcontent">
<form action="?/inbox/new" method="POST">
<?php echo $lang['mod/to']; ?>: <input type="text" name="to" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" /><br />
<?php echo $lang['mod/text']; ?>:<br />
<textarea name="text" cols=40 rows=9></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
		}
		break;
	case "/inbox/read":
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
		$result = $conn->query("SELECT users.username, pm.* FROM pm LEFT JOIN users ON pm.from_user=users.id WHERE pm.to_user=".$_SESSION['id']." AND pm.id=".$_GET['id']);
		if ($result->num_rows == 1)
			{
				$row = $result->fetch_assoc();
				if ($row['read_msg'] != 1)
				{
					$conn->query("UPDATE pm SET read_msg=1 WHERE id=".$_GET['id']);
				}
				?>
				<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/read_msg']; ?></h2></div>
<div class="boxcontent">
<?php echo $lang['mod/from']; ?>: <b><?php echo $row['username']; ?></b><br />
<?php echo $lang['mod/title']; ?>: <b><?php echo $row['title']; ?></b><br />
<?php echo $lang['mod/text']; ?>:<br />
<?php echo $row['text']; ?><br />
</div>
</div>
</div>
<script type="text/javascript">parent.nav.location.reload();</script>
				<?php
			}
		}
		break;
	case "/inbox/delete":
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$conn->query("DELETE FROM pm WHERE id=".$_GET['id']." AND to_user=".$_SESSION['id']);
			?>
			<script type="text/javascript">parent.nav.location.reload();</script>
			<meta http-equiv="refresh" content="0;URL='?/inbox'" />
			<?php
		}
		break;
	case "/log":
		break;
	case "/log/all":
		break;
	case "/search/ip":
		reqPermission(1);
		if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
			?>
			<div class="box-outer top-box">
			<div class="box-inner">
			<div class="boxbar"><h2><?php printf($lang['mod/showing_posts'], $_GET['ip']); ?></h2></div>
			<div class="boxcontent">
			<a href="?/delete_posts&ip=<?php echo $_GET['ip']; ?>"><?php echo $lang['mod/delete_ip']; ?></a>
			<table>
			<thead>
			<tr>
			<td><?php echo $lang['mod/name']; ?></td>
			<td><?php echo $lang['mod/e_mail']; ?></td>
			<td><?php echo $lang['mod/date']; ?></td>
			<td><?php echo $lang['mod/comment']; ?></td>
			<td><?php echo $lang['mod/subject']; ?></td>
			<td><?php echo $lang['mod/file']; ?></td>
			<td><?php echo $lang['mod/delete']; ?></td>
			</tr>
			</thead>
			<tbody>
			<?php
			require_once( "./jbbcode/Parser.php" );
			$parser = new JBBCode\Parser();
			$bbcode = $conn->query("SELECT * FROM bbcodes;");
			
			while ($row = $bbcode->fetch_assoc())
			{
				$parser->addBBCode($row['name'], $row['code']);
			}
			$boards = $conn->query("SELECT * FROM boards ORDER BY short ASC");
			while ($board = $boards->fetch_assoc())
			{
				$posts = $conn->query("SELECT * FROM posts_".$board['short']." WHERE ip='".$_GET['ip']."'");
				while ($row = $posts->fetch_assoc())
				{
					echo "<tr><td>";
					
					$trip = "";
					if (!empty($row['trip']))
					{
						$trip = "<span class='postertrip'>!".$row['trip']."</span>";
					}
					if ($row['capcode'] == 1)
					{
						echo '<span class="nameBlock"><span class="name"><span style="color:#800080">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#800080">## Mod</span></span></span>';
					} elseif ($row['capcode'] == 2)
					{
						echo '<span class="nameBlock"><span class="name"><span style="color:#FF0000">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF0000">## Admin</span></span></span>';
					} elseif ($row['capcode'] == 3)
					{
						echo '<span class="nameBlock"><span class="name"><span style="color:#FF00FF">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF00FF">## Faggot</span></span></span>';
					} else {
						echo '<span class="nameBlock"><span class="name">'.$row['name'].'</span>'.$trip.'</span>';
					}
				
					echo "</td>";
					echo "<td>".$row['email']."</td>";
					echo "<td>".date("d/m/Y @ H:i", $row['date'])."</td>";
					if ($row['raw'] != 1)
					{
						if ($row['raw'] == 2)
						{
							$comment = processComment($board['short'], $conn, $row['comment'], $parser, 2, 0);
						} else {
							$comment = processComment($board['short'], $conn, $row['comment'], $parser, 2);
						}
					} else {
						$comment = $row['comment'];
					}
					echo "<td>".$comment."</td>";
					echo "<td>".$row['subject']."</td>";
					if (!empty($row['filename']))
					{
						
						if ($row['filename'] == "deleted")
						{
							echo "<td><img src='./img/deleted.gif' /></td>";
						} elseif (substr($row['filename'], 0, 8) == "spoiler:") {
							echo "<td><a href='./".$board['short']."/src/".substr($row['filename'], 8)."' target='_blank'><img src='./".$board['short']."/src/thumb/".substr($row['filename'], 8)."' /></a><br /><b>Spoiler image</b></td>";
						} elseif (substr($row['filename'], 0, 6) == "embed:") {
							echo "<td><a href='".substr($row['filename'], 6)."'>Embed</a></td>";
						} else {
							echo "<td><a href='./".$board['short']."/src/".$row['filename']."' target='_blank'><img src='./".$board['short']."/src/thumb/".$row['filename']."' /></a></td>";
						}
					} else {
						echo "<td></td>";
					}
					echo '<td>[<a href="?/delete_post&b='.$board['short'].'&p='.$row['id'].'">D</a>] [<a href="?/delete_post&b='.$board['short'].'&p='.$row['id'].'&f=1">F</a>] [<a href="?/bans/add&b='.$board['short'].'&p='.$row['id'].'">B</a>]</td>';
				}
			}
			?>
			</tbody>
			</table>
			</div>
			</div></div>
			<?php
		}
		break;
	case "/delete_posts":
		if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
	?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php printf($lang['mod/want_delete_ip'], $_GET['ip']); ?></h2></div>
<div class="boxcontent"><a href="?/info&ip=<?php echo $_GET['ip']; ?>"><?php echo $lang['mod/no_big']; ?></a> <a href="?/delete_posts/yes&ip=<?php echo $_GET['ip']; ?>"><?php echo $lang['mod/yes_big']; ?></a></div>
</div>
</div>
		<?php
		}
		break;
	case "/delete_posts/yes":
		if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
			$boards = $conn->query("SELECT * FROM boards ORDER BY short ASC");
			while ($board = $boards->fetch_assoc())
			{
				$threads = $conn->query("SELECT * FROM posts_".$board['short']." WHERE ip='".$_GET['ip']."' AND resto=0");
				while ($row = $threads->fetch_assoc())
				{
					$conn->query("DELETE FROM posts_".$board['short']." WHERE resto=".$row['id']);
					if ($row['resto'] == 0)
					{
						unlink("./".$board['short']."/res/".$row['id'].".html");
					}
				}
				$conn->query("DELETE FROM posts_".$board['short']." WHERE ip='".$_GET['ip']."'");
				rebuildBoardCache($conn, $row['short']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/posts_deleted']; ?></h2></div>
<div class="boxcontent"><a href="?/info&ip=<?php echo $_GET['ip']; ?>"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
			}
		}
		break;
	case "/recent/posts":
		if ((!empty($_GET['max'])) && (is_numeric($_GET['max'])))
		{
			$max = $_GET['max'];
		} else {
			$max = 50;
		}
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php printf($lang['mod/recent_n_posts'], $max); ?></h2></div>
<div class="boxcontent">
			<?php echo $lang['mod/show_recent_none']; ?>: <a href="?/recent/posts">50</a> <a href="?/recent/posts&max=100">100</a> <a href="?/recent/posts&max=250">250</a> <a href="?/recent/posts&max=500">500</a>
<table>
			<thead>
			<tr>
			<td style="width: 10%;"><?php echo $lang['mod/post']; ?></td>
			<td style="width: 10%;"><?php echo $lang['mod/name']; ?></td>
			<td style="width: 10%;"><?php echo $lang['mod/e_mail']; ?></td>
			<td style="width: 10%;"><?php echo $lang['mod/date']; ?></td>
			<td style="width: 25%;"><?php echo $lang['mod/comment']; ?></td>
			<td style="width: 15%;"><?php echo $lang['mod/subject']; ?></td>
			<td style="width: 10%;"><?php echo $lang['mod/file']; ?></td>
			<td style="width: 10%;"><?php echo $lang['mod/delete']; ?></td>
			</tr>
			</thead>
			<tbody>
			<?php
			$boards = $conn->query("SELECT * FROM boards ORDER BY short ASC");
			$post_array = array();
			$num = 0;
			
			while ($board = $boards->fetch_assoc())
			{
				$posts = $conn->query("SELECT * FROM posts_".$board['short']." ORDER BY date DESC LIMIT 0, ".$max);
				while ($row = $posts->fetch_assoc())
				{
					$post_array[$num] = $row;
					$post_array[$num]['board'] = $board['short'];
					$num++;
				}
			}
			$dates = array();
			require_once( "./jbbcode/Parser.php" );
			$parser = new JBBCode\Parser();
			$bbcode = $conn->query("SELECT * FROM bbcodes;");
			
			while ($row = $bbcode->fetch_assoc())
			{
				$parser->addBBCode($row['name'], $row['code']);
			}
			foreach ($post_array as $key => $row)
			{
				$dates[$key] = $row['date'];
			}
			array_multisort($dates, SORT_DESC, $post_array);
			if (count($post_array) < $max)
			{
				$max = count($post_array);
			}
			for ($i = 0; $i < $max; $i++)
			{
				$row = $post_array[$i];
				echo "<tr><td>";
				$resto = $row['resto'];
				$op = 0;
				if ($row['resto'] == 0) { $resto = $row['id']; $op = 1; }
				echo "<a href='?/board&b=".$row['board']."&t=".$resto."'>/".$row['board']."/".$row['id']."</a> ";
				if ($op == 1) { echo "<b>OP</b>"; }
				echo "</td><td>";
				$trip = "";
				if (!empty($row['trip']))
				{
					$trip = "<span class='postertrip'>!".$row['trip']."</span>";
				}
				if ($row['capcode'] == 1)
				{
					echo '<span class="nameBlock"><span class="name"><span style="color:#800080">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#800080">## Mod</span></span></span>';
				} elseif ($row['capcode'] == 2)
				{
					echo '<span class="nameBlock"><span class="name"><span style="color:#FF0000">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF0000">## Admin</span></span></span>';
				} elseif ($row['capcode'] == 3)
				{
					echo '<span class="nameBlock"><span class="name"><span style="color:#FF00FF">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF00FF">## Faggot</span></span></span>';
				} else {
					echo '<span class="nameBlock"><span class="name">'.$row['name'].'</span>'.$trip.'</span>';
				}
			
				echo "</td>";
				echo "<td>".$row['email']."</td>";
				echo "<td>".date("d/m/Y @ H:i", $row['date'])."</td>";
				if ($row['raw'] != 1)
				{
					if ($row['raw'] == 2)
					{
						$comment = processComment($row['board'], $conn, $row['comment'], $parser, 2, 0);
					} else {
						$comment = processComment($row['board'], $conn, $row['comment'], $parser, 2);
					}
				} else {
					$comment = $row['comment'];
				}
				echo "<td>".$comment."</td>";
				echo "<td>".$row['subject']."</td>";
				if (!empty($row['filename']))
				{
					if ($row['filename'] == "deleted")
					{
						echo "<td><img src='./img/deleted.gif' /></td>";
					} elseif (substr($row['filename'], 0, 8) == "spoiler:") {
						echo "<td><a href='./".$row['board']."/src/".substr($row['filename'], 8)."' target='_blank'><img src='./".$row['board']."/src/thumb/".substr($row['filename'], 8)."' /></a><br /><b>Spoiler image</b></td>";
					} elseif (substr($row['filename'], 0, 6) == "embed:") {
						echo "<td><a href='".substr($row['filename'], 6)."'>Embed</a></td>";
					} else {
						echo "<td><a href='./".$row['board']."/src/".$row['filename']."' target='_blank'><img src='./".$row['board']."/src/thumb/".$row['filename']."' /></a></td>";
					}
				} else {
					echo "<td></td>";
				}
				echo '<td>[<a href="?/delete_post&b='.$row['board'].'&p='.$row['id'].'">D</a>] [<a href="?/delete_post&b='.$row['board'].'&p='.$row['id'].'&f=1">F</a>] [<a href="?/bans/add&b='.$row['board'].'&p='.$row['id'].'">B</a>]</td>';
			}
			?>
			</tbody>
			</table>
</div>
</div>
</div>
		<?php
		break;
	case "/recent/files":
	
		if ((!empty($_GET['max'])) && (is_numeric($_GET['max'])))
		{
			$max = $_GET['max'];
		} else {
			$max = 50;
		}
		?>
			<div class="box-outer top-box">
			<div class="box-inner">
			<div class="boxbar"><h2><?php printf($lang['mod/recent_n_posts_images'], $max); ?></h2></div>
			<div class="boxcontent">
			<?php echo $lang['mod/show_recent_none']; ?>: <a href="?/recent/files">50</a> <a href="?/recent/files&max=100">100</a> <a href="?/recent/files&max=250">250</a> <a href="?/recent/files&max=500">500</a> 
			<table>
			<thead>
			<tr>
			<td style="width: 10%;"><?php echo $lang['mod/post']; ?></td>
			<td style="width: 10%;"><?php echo $lang['mod/name']; ?></td>
			<td style="width: 10%;"><?php echo $lang['mod/e_mail']; ?></td>
			<td style="width: 10%;"><?php echo $lang['mod/date']; ?></td>
			<td style="width: 25%;"><?php echo $lang['mod/comment']; ?></td>
			<td style="width: 15%;"><?php echo $lang['mod/subject']; ?></td>
			<td style="width: 10%;"><?php echo $lang['mod/file']; ?></td>
			<td style="width: 10%;"><?php echo $lang['mod/delete']; ?></td>
			</tr>
			</thead>
			<tbody>
			<?php
			$boards = $conn->query("SELECT * FROM boards ORDER BY short ASC");
			$post_array = array();
			$num = 0;
			while ($board = $boards->fetch_assoc())
			{
				$posts = $conn->query("SELECT * FROM posts_".$board['short']." WHERE filename != '' ORDER BY date DESC LIMIT 0, ".$max);
				while ($row = $posts->fetch_assoc())
				{
					$post_array[$num] = $row;
					$post_array[$num]['board'] = $board['short'];
					$num++;
				}
			}
			$dates = array();
			require_once( "./jbbcode/Parser.php" );
			$parser = new JBBCode\Parser();
			$bbcode = $conn->query("SELECT * FROM bbcodes;");
			
			while ($row = $bbcode->fetch_assoc())
			{
				$parser->addBBCode($row['name'], $row['code']);
			}
			foreach ($post_array as $key => $row)
			{
				$dates[$key] = $row['date'];
			}
			array_multisort($dates, SORT_DESC, $post_array);
			if (count($post_array) < $max)
			{
				$max = count($post_array);
			}
			for ($i = 0; $i < $max; $i++)
			{
				$row = $post_array[$i];
				echo "<tr><td>";
				$resto = $row['resto'];
				$op = 0;
				if ($row['resto'] == 0) { $resto = $row['id']; $op = 1; }
				echo "<a href='?/board&b=".$row['board']."&t=".$resto."'>/".$row['board']."/".$row['id']."</a> ";
				if ($op == 1) { echo "<b>OP</b>"; }
				echo "</td><td>";
				$trip = "";
				if (!empty($row['trip']))
				{
					$trip = "<span class='postertrip'>!".$row['trip']."</span>";
				}
				if ($row['capcode'] == 1)
				{
					echo '<span class="nameBlock"><span class="name"><span style="color:#800080">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#800080">## Mod</span></span></span>';
				} elseif ($row['capcode'] == 2)
				{
					echo '<span class="nameBlock"><span class="name"><span style="color:#FF0000">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF0000">## Admin</span></span></span>';
				} elseif ($row['capcode'] == 3)
				{
					echo '<span class="nameBlock"><span class="name"><span style="color:#FF00FF">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF00FF">## Faggot</span></span></span>';
				} else {
					echo '<span class="nameBlock"><span class="name">'.$row['name'].'</span>'.$trip.'</span>';
				}
			
				echo "</td>";
				echo "<td>".$row['email']."</td>";
				echo "<td>".date("d/m/Y @ H:i", $row['date'])."</td>";
				if ($row['raw'] != 1)
				{
					if ($row['raw'] == 2)
					{
						$comment = processComment($row['board'], $conn, $row['comment'], $parser, 2, 0);
					} else {
						$comment = processComment($row['board'], $conn, $row['comment'], $parser, 2);
					}
				} else {
					$comment = $row['comment'];
				}
				echo "<td>".$comment."</td>";
				echo "<td>".$row['subject']."</td>";
				if (!empty($row['filename']))
				{
					if ($row['filename'] == "deleted")
					{
						echo "<td><img src='./img/deleted.gif' /></td>";
					} elseif (substr($row['filename'], 0, 8) == "spoiler:") {
						echo "<td><a href='./".$row['board']."/src/".substr($row['filename'], 8)."' target='_blank'><img src='./".$row['board']."/src/thumb/".substr($row['filename'], 8)."' /></a><br /><b>Spoiler image</b></td>";
					} elseif (substr($row['filename'], 0, 6) == "embed:") {
						echo "<td><a href='".substr($row['filename'], 6)."'>Embed</a></td>";
					} else {
						echo "<td><a href='./".$row['board']."/src/".$row['filename']."' target='_blank'><img src='./".$row['board']."/src/thumb/".$row['filename']."' /></a></td>";
					}
				} else {
					echo "<td></td>";
				}
				echo '<td>[<a href="?/delete_post&b='.$row['board'].'&p='.$row['id'].'">D</a>] [<a href="?/delete_post&b='.$row['board'].'&p='.$row['id'].'&f=1">F</a>] [<a href="?/bans/add&b='.$row['board'].'&p='.$row['id'].'">B</a>]</td>';
			}
			?>
			</tbody>
			</table>
</div>
</div>
</div>
		<?php
		break;
		case "/ban_requests":
		reqPermission(1);
		if ((isset($_GET['del'])) && ($_GET['del']==1))
		{
			if ((!empty($_GET['b'])) && (is_numeric($_GET['b'])))
			{
				$conn->query("DELETE FROM ban_requests WHERE id=".$_GET['b']);
			}
		}
	?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/ban_requests']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/ip']; ?></td>
<td><?php echo $lang['mod/reason']; ?></td>
<td><?php echo $lang['mod/staff_note']; ?>/td>
<td><?php echo $lang['mod/created']; ?></td>
<td><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM ban_requests ORDER BY created DESC LIMIT 0, 15;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".$row['ip']."</td>";
echo "<td>".$row['reason']."</td>";
echo "<td>".$row['note']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['created'])."</td>";

$post_r = $conn->query("SELECT * FROM posts_".$row['board']." WHERE id=".$row['post']);
if ($post_r->num_rows == 1)
{
$post = $post_r->fetch_assoc();
$resto = $post['resto'];
if ($resto == 0) { $resto = $post['id']; }
echo "<td>[ <a href='?/ban_requests&del=1&b=".$row['id']."'>C</a> / <a href='?/bans/add&r=".$row['id']."'>B</a> / <a href='?/board&b=".$row['board']."&t=".$resto."#p".$post['id']."'>P</a> ]</td>";
} else {
echo "<td>[ <a href='?/ban_requests&del=1&b=".$row['id']."'>C</a> / <a href='?/bans/add&r=".$row['id']."'>B</a> ]</td>";
}

echo "</tr>";
}
?>
</tbody>
</table>
<?php printf($lang['mod/showing_requests'], 15); ?> <a href="?/ban_requests/all"><?php echo $lang['mod/show_all']; ?></a>
</div>
</div>
</div>
<script type="text/javascript">parent.nav.location.reload();</script>
<?php
		break;
	case "/ban_requests/all":
	reqPermission(1);
	?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/ban_requests']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/ip']; ?></td>
<td><?php echo $lang['mod/reason']; ?></td>
<td><?php echo $lang['mod/staff_note']; ?>/td>
<td><?php echo $lang['mod/created']; ?></td>
<td><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM ban_requests ORDER BY created DESC");
while ($row = $result->fetch_assoc())
{

$post_r = $conn->query("SELECT * FROM posts_".$row['board']." WHERE id=".$row['post']);
if ($post_r->num_rows == 0)
{
	$conn->query("DELETE FROM reports WHERE id=".$row['id']);
	continue;
}
$post = $post_r->fetch_assoc();

echo "<tr>";
echo "<td>".$row['ip']."</td>";
echo "<td>".$row['reason']."</td>";
echo "<td>".$row['note']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['created'])."</td>";
$resto = $post['resto'];
if ($resto == 0) { $resto = $post['id']; }
echo "<td>[ <a href='?/ban_requests&del=1&b=".$row['id']."'>C</a> / <a href='?/bans/add&r=".$row['id']."'>B</a> / <a href='?/board&b=".$row['board']."&t=".$resto."#p".$post['id']."'>P</a> ]</td>";
echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
<script type="text/javascript">parent.nav.location.reload();</script>
<?php
		break;
	case "/bbcodes":
		reqPermission(2);
		$name = "";
		$code = "";
		if ((!empty($_POST['mode'])) && ($_POST['mode'] == "add"))
		{
			if (empty($_POST['name'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $name = $_POST['name']; }
			if (empty($_POST['code'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $code = $_POST['code']; }
			if (!preg_match("/^[a-zA-Z0-9]*$/", $_POST['name']))
			{ echo "<b style='color: red;'>".$lang['mod/name_error']."</b>"; }
			else {
				$name = $conn->real_escape_string($_POST['name']);
				$code = $conn->real_escape_string($_POST['code']);
				$conn->query("INSERT INTO bbcodes (name, code) VALUES ('".$name."', '".$code."');");
				$name = "";
				$code = "";
			}
		} elseif ((!empty($_POST['mode'])) && ($_POST['mode'] == "edit") && (!empty($_POST['name2']))) {
			
			if (empty($_POST['name'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $name = $_POST['name']; }
			if (empty($_POST['code'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $code = $_POST['code']; }
			if (!preg_match("/^[a-zA-Z0-9]*$/", $_POST['name']))
			{ echo "<b style='color: red;'>".$lang['mod/name_error']."</b>"; }
			else {
				$name = $conn->real_escape_string($_POST['name']);
				$name2 = $conn->real_escape_string($_POST['name2']);
				$code = $conn->real_escape_string($_POST['code']);
				$conn->query("UPDATE bbcodes SET name='".$name."', code='".$code."' WHERE name='".$name2."';");
			}
			$name = "";
			$code = "";
		}

		if ((!empty($_GET['d'])) && ($_GET['d'] == 1) && (!empty($_GET['n'])))
		{
			$n = $conn->real_escape_string($_GET['n']);
			$conn->query("DELETE FROM bbcodes WHERE name='".$n."'");
		}
		?>
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/manage_bbcodes']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/bbcode']; ?></td>
<td><?php echo $lang['mod/html_code']; ?></td>
<td><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM bbcodes ORDER BY name ASC");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".$row['name']."</td>";
echo "<td>".htmlspecialchars($row['code'])."</td>";
echo "<td><a href='?/bbcodes&d=1&n=".$row['name']."'>".$lang['mod/edit']."</a> <a href='?/bbcodes/edit&n=".$row['name']."'>".$lang['mod/delete']."</a></td>";
echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
<br /><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_bbcode']; ?></h2></div>
<div class="boxcontent">
<form action="?/bbcodes" method="POST">
<input type="hidden" name="mode" value="add">
<?php echo $lang['mod/bbcode']; ?>: <input type="text" name="name" value="<?php echo $name; ?>"/><br />
<?php echo $lang['mod/html_code']; ?>: <textarea cols=40 rows=9 name="code"><?php echo $code; ?></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
		break;
	case "/bbcodes/edit":
		reqPermission(2);
		if (!empty($_GET['n']))
		{
		$result = $conn->query("SELECT * FROM bbcodes WHERE name='".$conn->real_escape_string($_GET['n'])."'");
		if ($result->num_rows == 1)
		{
		$binfo = $result->fetch_assoc();
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/edit_bbcode']; ?></h2></div>
<div class="boxcontent">
<form action="?/bbcodes" method="POST">
<input type="hidden" name="mode" value="edit">
<input type="hidden" name="name2" value="<?php echo $conn->real_escape_string($_GET['n']); ?>">
<?php echo $lang['mod/bbcode']; ?>: <input type="text" name="name" value="<?php echo $binfo['name']; ?>"/><br />
<?php echo $lang['mod/html_code']; ?>:<textarea cols=40 rows=9 name="code"><?php echo $binfo['code']; ?>"</textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
		}
		}
		break;
	case "/wordfilter":
		reqPermission(2);
		$search = "";
		$replace = "";
		if ((!empty($_POST['mode'])) && ($_POST['mode'] == "add"))
		{
			if (empty($_POST['search'])) { echo "<b style='color: red;'>Please fill search field!</b>"; } else { $search = $_POST['search']; }
			if (empty($_POST['replace'])) { echo "<b style='color: red;'>Please fill replace field!</b>"; } else { $replace = $_POST['replace']; }
			$search = $conn->real_escape_string($_POST['search']);
			$replace = $conn->real_escape_string($_POST['replace']);
			$conn->query("INSERT INTO wordfilter (`search`, `replace`, `active`) VALUES ('".$search."', '".$replace."', 1);");
			$search = "";
			$replace = "";
		} elseif ((!empty($_POST['mode'])) && ($_POST['mode'] == "edit") && (!empty($_POST['id']))) {
			
			if (empty($_POST['search'])) { echo "<b style='color: red;'>Please fill search field!</b>"; } else { $search = $_POST['search']; }
			if (empty($_POST['replace'])) { echo "<b style='color: red;'>Please fill replace field!</b>"; } else { $replace = $_POST['replace']; }
			$search = $conn->real_escape_string($_POST['search']);
			$id = $_POST['id'];
			if (!is_numeric($id)) { echo "<b style='color: red;'>Don't try to fool me!</b>"; }
			$replace = $conn->real_escape_string($_POST['replace']);
			$conn->query("UPDATE wordfilter SET `search`='".$search."', `replace`='".$replace."' WHERE id=".$id);
			$search = "";
			$replace = "";
		}

		if ((!empty($_GET['d'])) && ($_GET['d'] == 1) && (!empty($_GET['n'])))
		{
			$n = $conn->real_escape_string($_GET['n']);
			if (!is_numeric($n)) { echo "<b style='color: red;'>Don't try to fool me!</b>"; }
			$conn->query("DELETE FROM wordfilter WHERE id=".$n);
		}
		?>
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Wordfilter</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>Search</td>
<td>Replace</td>
<td>Actions</td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM wordfilter ORDER BY search ASC");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".htmlspecialchars($row['search'])."</td>";
echo "<td>".htmlspecialchars($row['replace'])."</td>";
echo "<td><a href='?/wordfilter&d=1&n=".$row['id']."'>Delete</a> <a href='?/wordfilter/edit&n=".$row['id']."'>Edit</a></td>";
echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
<br /><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Add wordfilter</h2></div>
<div class="boxcontent">
<form action="?/wordfilter" method="POST">
<input type="hidden" name="mode" value="add">
Search: <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"/><br />
Replace: <input type="text" name="replace" value="<?php echo htmlspecialchars($replace); ?>"/><br />
<input type="submit" value="Add" />
</form>
</div>
</div>
</div>
		<?php
		break;
	case "/wordfilter/edit":
		reqPermission(2);
		if (!empty($_GET['n']))
		{
		$result = $conn->query("SELECT * FROM wordfilter WHERE id=".$conn->real_escape_string($_GET['n']));
		if ($result->num_rows == 1)
		{
		$info = $result->fetch_assoc();
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Edit wordfilter</h2></div>
<div class="boxcontent">
<form action="?/wordfilter" method="POST">
<input type="hidden" name="mode" value="edit">
<input type="hidden" name="id" value="<?php echo $_GET['n']; ?>">
Search: <input type="text" name="search" value="<?php echo htmlspecialchars($info['search']); ?>"/><br />
Replace: <input type="text" name="replace" value="<?php echo htmlspecialchars($info['replace']); ?>"/><br />
<input type="submit" value="Update" />
</form>
</div>
</div>
</div>
		<?php
		}
		}
		break;
	case "/embeds":
		reqPermission(2);
		$name = "";
		$code = "";
		$regex = "";
		if ((!empty($_POST['mode'])) && ($_POST['mode'] == "add"))
		{
			if (empty($_POST['name'])) { echo "<b style='color: red;'>Please fill name field!</b>"; } else { $name = $_POST['name']; }
			if (empty($_POST['code'])) { echo "<b style='color: red;'>Please fill code field!</b>"; } else { $code = $_POST['code']; }
			if (empty($_POST['regex'])) { echo "<b style='color: red;'>Please fill code field!</b>"; } else { $regex = $_POST['regex']; }
			if (!preg_match("/^[a-zA-Z0-9]*$/", $_POST['name']))
			{ echo "<b style='color: red;'>Name must consist of alphanumeric characters and it may not contain spaces!</b>"; }
			else {
				$name = $conn->real_escape_string($_POST['name']);
				$regex = $conn->real_escape_string($_POST['regex']);
				$code = $conn->real_escape_string($_POST['code']);
				$conn->query("INSERT INTO embeds (name, regex, code) VALUES ('".$name."', '".$regex."', '".$code."');");
				$name = "";
				$code = "";
			}
		} elseif ((!empty($_POST['mode'])) && ($_POST['mode'] == "edit") && (!empty($_POST['name2']))) {
			
			if (empty($_POST['name'])) { echo "<b style='color: red;'>Please fill name field!</b>"; } else { $name = $_POST['name']; }
			if (empty($_POST['regex'])) { echo "<b style='color: red;'>Please regex name field!</b>"; } else { $regex = $_POST['regex']; }
			if (empty($_POST['code'])) { echo "<b style='color: red;'>Please fill code field!</b>"; } else { $code = $_POST['code']; }
			if (!preg_match("/^[a-zA-Z0-9]*$/", $_POST['name']))
			{ echo "<b style='color: red;'>Name must consist of alphanumeric characters and it may not contain spaces!</b>"; }
			else {
				$name = $conn->real_escape_string($_POST['name']);
				$name2 = $conn->real_escape_string($_POST['name2']);
				$regex = $conn->real_escape_string($_POST['regex']);
				$code = $conn->real_escape_string($_POST['code']);
				$conn->query("UPDATE embeds SET name='".$name."', code='".$code."', regex='".$regex."' WHERE name='".$name2."';");
			}
			$name = "";
			$code = "";
		}

		if ((!empty($_GET['d'])) && ($_GET['d'] == 1) && (!empty($_GET['n'])))
		{
			$n = $conn->real_escape_string($_GET['n']);
			$conn->query("DELETE FROM embeds WHERE name='".$n."'");
		}
		?>
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Embeds</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>BBCode</td>
<td>Regex</td>
<td>Actions</td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM embeds ORDER BY name ASC");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".$row['name']."</td>";
echo "<td>".htmlspecialchars($row['regex'])."</td>";
echo "<td><a href='?/embeds&d=1&n=".$row['name']."'>Delete</a> <a href='?/embeds/edit&n=".$row['name']."'>Edit</a></td>";
echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
<br /><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Add embed</h2></div>
<div class="boxcontent">
<form action="?/embeds" method="POST">
<input type="hidden" name="mode" value="add">
Name: <input type="text" name="name" value="<?php echo $name; ?>"/><br />
Regex: <input type="text" name="regex" value="<?php echo $regex; ?>"/><br />
HTML Code: <textarea cols=40 rows=9 name="code"><?php echo $code; ?></textarea><br />
<input type="submit" value="Add" />
</form>
</div>
</div>
</div>
		<?php
		break;
	case "/embeds/edit":
		reqPermission(2);
		if (!empty($_GET['n']))
		{
		$result = $conn->query("SELECT * FROM embeds WHERE name='".$conn->real_escape_string($_GET['n'])."'");
		if ($result->num_rows == 1)
		{
		$binfo = $result->fetch_assoc();
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Edit embed</h2></div>
<div class="boxcontent">
<form action="?/embeds" method="POST">
<input type="hidden" name="mode" value="edit">
<input type="hidden" name="name2" value="<?php echo $conn->real_escape_string($_GET['n']); ?>">
Name: <input type="text" name="name" value="<?php echo $binfo['name']; ?>"/><br />
Regex: <input type="text" name="regex" value="<?php echo $binfo['regex']; ?>"/><br />
HTML Code: <textarea cols=40 rows=9 name="code"><?php echo $binfo['code']; ?></textarea><br />
<input type="submit" value="Update" />
</form>
</div>
</div>
</div>
		<?php
		}
		}
		break;
	case "/styles":
		reqPermission(2);
		$search = "";
		$replace = "";
		if ((!empty($_POST['mode'])) && ($_POST['mode'] == "upload"))
		{
			$shouldnt = 0;
			if (empty($_POST['name'])) { echo "<b style='color: red;'>Please fill name field!</b>"; $shouldnt = 1; }
			if (empty($_FILES['upfile']['tmp_name'])) { echo "<b style='color: red;'>No file! ;_;</b>"; $shouldnt = 1; }
			if (!$shouldnt)
			{
				$name = $conn->real_escape_string($_POST['name']);
				$filename = strtolower(preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $_FILES['upfile']['name']));
				if(move_uploaded_file($_FILES['upfile']['tmp_name'], "./styles/".$filename)) {
					$conn->query("INSERT INTO styles (`name`, `path`, `path_thread`, `path_index`, `default`) VALUES ('".$name."', '../styles/".$filename."', '../../styles/".$filename."', './styles/".$filename."', 0);");
					echo "<b style='color: green;'>Upload done!</b>";
				}
			}
		}
		
		if ((!empty($_GET['def'])) && ($_GET['def'] == 1) && (!empty($_GET['n'])))
		{
			$n = $conn->real_escape_string($_GET['n']);
			if (!is_numeric($n)) { echo "<b style='color: red;'>Don't try to fool me!</b>"; }
			$conn->query("UPDATE styles SET `default`=0");
			$conn->query("UPDATE styles SET `default`=1 WHERE id=".$n);
		}

		if ((!empty($_GET['d'])) && ($_GET['d'] == 1) && (!empty($_GET['n'])))
		{
			$n = $conn->real_escape_string($_GET['n']);
			if (!is_numeric($n)) { echo "<b style='color: red;'>Don't try to fool me!</b>"; }
			$conn->query("DELETE FROM styles WHERE id=".$n);
		}
		
		if ((!empty($_GET['f'])) && ($_GET['f'] == 1) && (!empty($_GET['n'])))
		{
			$n = $conn->real_escape_string($_GET['n']);
			if (!is_numeric($n)) { echo "<b style='color: red;'>Don't try to fool me!</b>"; }
			$result = $conn->query("SELECT * FROM styles WHERE id=".$n);
			$row = $result->fetch_assoc();
			unlink($row['path_index']);
			$conn->query("DELETE FROM styles WHERE id=".$n);
		}
		?>
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Stylesheets</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>Name</td>
<td>File</td>
<td>Actions</td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM styles ORDER BY name ASC");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".htmlspecialchars($row['name']);
if ($row['default'] == 1) { echo " ( <b>default</b> )"; }
echo "</td>";
echo "<td><a href='".htmlspecialchars($row['path_index'])."' target='_blank'>Show file</a></td>";
echo "<td><a href='?/styles&f=1&n=".$row['id']."'>Delete</a>(<a href='?/styles&d=1&n=".$row['id']."'>No file</a>)";
if ($row['default'] == 0)
{
	echo " <a href='?/styles&def=1&n=".$row['id']."'>Make default</a>";
}
echo "</td>";
echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
<br /><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Upload stylesheet</h2></div>
<div class="boxcontent">
<form action="?/styles" method="POST" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="2097152">
<input type="hidden" name="mode" value="upload">
File: <input id="postFile" name="upfile" type="file"><br />
Name: <input type="text" name="name"/><br />
<input type="submit" value="Upload" />
</form>
</div>
</div>
</div>
		<?php
		
		break;
	case "/edit_post":
		reqPermission(2);
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$result = $conn->query("SELECT * FROM posts_".$_GET['b']." WHERE id=".$_GET['p']);
			if ($result->num_rows == 1)
			{
			$row = $result->fetch_assoc();
			?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Edit post</h2></div>
<div class="boxcontent">
			<form action="?/save_post" method="POST">
			<input type="hidden" name="b" value="<?php echo $_GET['b']; ?>" />
			<input type="hidden" name="p" value="<?php echo $_GET['p']; ?>" />
			Text: <textarea cols="50" rows="7" name="text"><?php echo $row['comment']; ?></textarea><br />
			Options: <input type="checkbox" name="raw" value="1" <?php if ($row['raw'] == 1) { echo "checked='checked'"; }?> />Raw HTML<br />
			<input type="submit" value="Update!" />
			</form>
</div>
</div>
</div>
			<?php
			} else {
			
			}
		} else {
		
		}
		break;
	case "/save_post":
		reqPermission(2);
		if ((!empty($_POST['b'])) && (!empty($_POST['p'])) && (isBoard($conn, $_POST['b'])) && (is_numeric($_POST['p'])) && (!empty($_POST['text'])))
		{
			$result = $conn->query("SELECT * FROM posts_".$_POST['b']." WHERE id=".$_POST['p']);
			if ($result->num_rows == 1)
			{
				$row = $result->fetch_assoc();
				$raw = 0;
				if ((isset($_POST['raw'])) && ($_POST['raw'] == 1))
				{
					$raw = 1;
				}
				$conn->query("UPDATE posts_".$_POST['b']." SET comment='".preprocessComment($conn, $_POST['text'])."', raw=".$raw." WHERE id=".$_POST['p']);
				$resto = $row['resto'];
				if ($row['resto'] == 0)
				{
					generateView($conn, $_POST['b'], $row['id']);
					$resto = $row['id'];
				} else {
					generateView($conn, $_POST['b'], $row['resto']);
				}
				generateView($conn, $_POST['b']);
				?>
				<div class="box-outer top-box">
	<div class="box-inner">
	<div class="boxbar"><h2>Post updated successfully</h2></div>
	</div>
	</div>
	</div>
	<meta http-equiv="refresh" content="2;URL='?/board&b=<?php echo $_POST['b']; ?>&t=<?php echo $resto; ?>#p<?php echo $row['id']; ?>'" />
				<?php
			}
		}
		break;
	case "/api/get_post":
		reqPermission(2);
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$result = $conn->query("SELECT * FROM posts_".$_GET['b']." WHERE id=".$_GET['p']);
			if ($result->num_rows == 1)
			{
				$row = $result->fetch_assoc();
				echo json_encode(array('comment' => htmlspecialchars($row['comment']), 'raw' => $row['raw'], 'id' => $row['id']));
			} else {
				echo json_encode(array('error' => 404));
			}
		} else {
			echo json_encode(array('error' => 404));
		}
		break;
	case "/api/update_post":
		reqPermission(2);
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$result = $conn->query("SELECT * FROM posts_".$_GET['b']." WHERE id=".$_GET['p']);
			if ($result->num_rows == 1)
			{
				$row = $result->fetch_assoc();
				$raw = 0;
				if ((isset($_POST['raw'])) && ($_POST['raw'] == 1))
				{
					$raw = 1;
				}
				$conn->query("UPDATE posts_".$_GET['b']." SET comment='".preprocessComment($conn, $_POST['comment'])."', raw=".$raw." WHERE id=".$_GET['p']);
				$resto = $row['resto'];
				if ($row['resto'] == 0)
				{
					generateView($conn, $_GET['b'], $row['id']);
					$resto = $row['id'];
				} else {
					generateView($conn, $_GET['b'], $row['resto']);
				}
				generateView($conn, $_GET['b']);
			}
		} else {
			echo json_encode(array('error' => 404));
		}
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