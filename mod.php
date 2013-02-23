<?php
if (!file_exists("./config.php"))
{
header("Location: ./install.php");
}

session_start();
include("config.php");
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
		$result = mysqli_query($conn, "SELECT * FROM ".$table." WHERE id=".$id);
		$entry = mysqli_fetch_assoc($result);
		if ($entry['mod_id'] == $_SESSION['id'])
		{
			mysqli_query($conn, "DELETE FROM ".$table." WHERE id=".$id);
		}
	} else {
		mysqli_query($conn, "DELETE FROM ".$table." WHERE id=".$id);
	}

	if ($type == 1) { generateNews($conn); }
}

function updateEntry($conn, $type, $id, $who, $title, $text, $validate_id = 0)
{
	if (!is_numeric($id))
	{
		return -1;
	}
	$who = mysqli_real_escape_string($conn, $who);
	$title = mysqli_real_escape_string($conn, $title);
	$text = mysqli_real_escape_string($conn, $text);
	$table = "";
	if ($type == 0) { $table = "announcements"; }
	if ($type == 1) { $table = "news"; }
	
	if ($validate_id == 1)
	{
		$result = mysqli_query("SELECT * FROM ".$table." WHERE id=".$id);
		$entry = mysqli_fetch_assoc($result);
		if ($entry['mod_id'] == $_SESSION['id'])
		{
			mysqli_query($conn, "UPDATE ".$table." SET who='".$who."', title='".$title."', text='".$text."' WHERE id=".$id);
		}
	} else {
		mysqli_query($conn, "UPDATE ".$table." SET who='".$who."', title='".$title."', text='".$text."' WHERE id=".$id);
	}
	
	if ($type == 1) { generateNews($conn); }
}

function processEntry($conn, $string)
{
	$new = str_replace("\r", "", $string);
	$new = mysqli_real_escape_string($conn, $new);
	$lines = explode("\n", $new);
	$new = "";
	foreach ($lines as $line)
	{
		if (substr($line, 0, 1) != "<")
		{
			$new .= "<p>".strip_tags($line, "<script><style><link><meta><iframe><frame><canvas>")."</p>";
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
	die("NOT LOGGED IN!");
}
if (($path != "/nav") && ($path != "/board") && ($path != "/board/action") && (($path != "/") || ((!isset($_SESSION['logged'])) || ($_SESSION['logged']==0))))
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
$conn = mysqli_connect($db_host, $db_username, $db_password, $db_database);
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
<div class="boxbar"><h2>Log in</h2></div>
<div class="boxcontent">
<form action="?/login" method="POST">
<center>Username: <input type="text" name="username" /> | Password: <input type="password" name="password" /> <input type="submit" value="Log in!" /></center>
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
			$username = mysqli_real_escape_string($conn, $_POST['username']);
			$password = hash("sha512", $_POST['password']);
			$result = mysqli_query($conn, "SELECT * FROM users WHERE username='".$username."'");
			if (mysqli_num_rows($result) == 1)
			{
				$data = mysqli_fetch_assoc($result);
				if ($data['password'] == $password)
				{
					$_SESSION['logged']=1;
					$_SESSION['id']=$data['id'];
					$_SESSION['username']=$username;
					$_SESSION['type']=$data['type'];
					$_SESSION['boards']=$data['boards'];
					header("Location: ./mod.php");
				} else {
					die("BAD PASSWORD OR USERNAME");
				}
			} else {
				die("BAD PASSWORD OR USERNAME");
			}
		} else {
			die("ERROR");
		}
		break;
	case "/logout":
		session_destroy();
		header("Location: ./mod.php");
		break;
	case "/nav":
	$reports = mysqli_query($conn, "SELECT * FROM reports;");
	$reports = mysqli_num_rows($reports);
	$appeals = mysqli_query($conn, "SELECT * FROM appeals;");
	$appeals = mysqli_num_rows($appeals);
	$breqs = mysqli_query($conn, "SELECT * FROM ban_requests;");
	$breqs = mysqli_num_rows($breqs);
	$pms = mysqli_query($conn, "SELECT * FROM pm WHERE to_user=".$_SESSION['id']." AND read_msg=0");
	$pms = mysqli_num_rows($pms);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Mitsuba Navigation</title>
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
<li><a href="?/logout" target="_top">[Log out]</a></li>
</ul>
<h2><span class="coll" onclick="toggle(this,'gen');" title="Toggle Category">&minus;</span>General</h2>
<div id="gen" style="">
<ul>
<li><a href="?/announcements" target="main">Announcements</a></li>
<li><a href="?/news" target="main">News</a></li>
<li><a href="?/bans" target="main">Banlist</a></li>
<li><a href="?/reports" target="main">Report queue (<?php echo $reports; ?>)</a></li>
<li><a href="?/notes" target="main">Notes</a></li>
<li><a href="?/ipnotes" target="main">IP notes</a></li>
<li><a href="?/recent/posts" target="main">Recent posts</a></li>
<li><a href="?/recent/files" target="main">Recently uploaded images</a></li>
<?php
if ($_SESSION['type'] >= 1)
{
?>
<li><a href="?/ban_requests" target="main">Ban requests (<?php echo $breqs; ?>)</a></li>
<li><a href="?/announcements/add" target="main">New announcement</a></li>
<li><a href="?/news/add" target="main">Add news entry</a></li>
<li><a href="?/bans/add" target="main">Add new ban</a></li>
<li><a href="?/locked" target="main">Locked threads</a></li>
<li><a href="?/sticky" target="main">Sticky threads</a></li>
<li><a href="?/appeals" target="main">Ban appeals (<?php echo $appeals; ?>)</a></li>
<?php
}
?>
</ul></div>
<h2><span class="coll" onclick="toggle(this,'acc');" title="Toggle Category">&minus;</span>Your account</h2>
<div id="acc" style="">
<ul>
<li><a href="?/password" target="main">Change password</a></li>
<li><a href="?/inbox" target="main">Inbox (<?php echo $pms; ?>)</a></li>
<li><a href="?/inbox/new" target="main">Send message</a></li>
</ul></div>
<?php
if ($_SESSION['type'] >= 2)
{
?>
<h2><span class="coll" onclick="toggle(this,'adm');" title="Toggle Category">&minus;</span>Administration</h2>
<div id="adm" style="">
<ul>
<li><a href="?/config" target="main">Configuration</a></li>
<li><a href="?/boards" target="main">Manage boards</a></li>
<li><a href="?/links" target="main">Manage board links</a></li>
<li><a href="?/users" target="main">Manage users</a></li>
<li><a href="?/whitelist" target="main">Manage whitelist</a></li>
<li><a href="?/news/manage" target="main">Manage news entries</a></li>
<li><a href="?/announcements/manage" target="main">Manage announcements</a></li>
<li><a href="?/bbcodes" target="main">Manage BBCodes</a></li>
<li><a href="?/embeds" target="main">Manage embeds</a></li>
<li><a href="?/styles" target="main">Manage styles</a></li>
<li><a href="?/range" target="main">Manage range bans</a></li>
<li><a href="?/message" target="main">Global message</a></li>
<li><a href="?/rebuild" target="main">Rebuild cache</a></li>
<li><a href="?/log" target="main">Action log</a></li>
</ul></div>
<?php
}
?>
<h2><span class="coll" onclick="toggle(this,'brd');" title="Toggle Category">&minus;</span>Boards</h2>
<div id="brd" style="">
<ul>
<?php
$result = mysqli_query($conn, "SELECT * FROM boards ORDER BY short ASC;");
if (($_SESSION['boards'] != "*") && ($_SESSION['type'] != 2))
{
$boards = explode(",", $_SESSION['boards']);
} else {
$boards = "*";
}
while ($row = mysqli_fetch_assoc($result))
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
<div class="boxbar"><h2>Announcements</h2></div>
<div class="boxcontent">
<?php
$result = mysqli_query($conn, "SELECT * FROM announcements ORDER BY date DESC;");
while ($row = mysqli_fetch_assoc($result))
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
<div class="boxbar"><h2>New user</h2></div>
<div class="boxcontent">
<form action="?/users/add" method="POST">
Username: <input type="text" name="username" /><br />
Password: <input type="password" name="password"/><br />
Type: <select name="type"><option value="0">Janitor</option><option value="1">Moderator</option><option value="2">Administrator</option></select>

<br /><br />
Boards: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1/> All<br/>
<select name="boards[]" id="boardSelect" multiple>
<?php
$result = mysqli_query($conn, "SELECT * FROM boards;");
while ($row = mysqli_fetch_assoc($result))
{
echo "<option onClick='document.getElementById(\"all\").checked=false;' value='",$row['short']."'>/".$row['short']."/ - ".$row['name']."</option>";
}
?>
</select><br />
<input type="submit" value="Add user!" />
</form>
</div>
</div>
</div><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>All users</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>Username</td>
<td>Type</td>
<td>Boards</td>
<td>Edit</td>
<td>Delete</td>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM users;");
$usern = mysqli_num_rows($result);
while ($row = mysqli_fetch_assoc($result))
{
echo "<tr>";
echo "<td>".$row['username']."</td>";
echo "<td>";
switch ($row['type'])
{
	case 0:
		echo "Janitor";
		break;
	case 1:
		echo "Moderator";
		break;
	case 2:
		echo "Administrator";
		break;
	default:
		echo "Faggot";
		break;
}
echo "</td>";
echo "<td>".$row['boards']."</td>";
echo "<td><a href='?/users/edit&id=".$row['id']."'>Edit</a></td>";
if ($usern != 1)
{
echo "<td><a href='?/users/delete&id=".$row['id']."'>Delete</a></td>";
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
<div class="boxbar"><h2>New announcement</h2></div>
<div class="boxcontent">
<form action="?/announcements/add" method="POST">
By: <input type="text" name="who" value="<?php echo $_SESSION['username']; ?>" /><br />
Title: <input type="text" name="title"/><br />
Text: <br />
<textarea name="text" cols="70" rows="10"></textarea>
<input type="submit" value="Add new announcement" />
</form>
</div>
</div>
</div><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Your announcements</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>Title</td>
<td>Date</td>
<td>Edit</td>
<td>Delete</td>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM announcements WHERE mod_id=".$_SESSION['id']." ORDER BY date DESC;");
while ($row = mysqli_fetch_assoc($result))
{
echo "<td>".$row['title']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['date'])."</td>";
echo "<td><a href='?/announcements/edit&b=".$row['id']."'>Edit</a></td>";
echo "<td><a href='?/announcements/delete&b=".$row['id']."'>Delete</a></td>";
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
		mysqli_query($conn, "INSERT INTO announcements (date, who, title, text, mod_id) VALUES (".time().", '".$who."', '".mysqli_real_escape_string($conn, htmlspecialchars($_POST['title']))."', '".$text."', ".$_SESSION['id'].");");
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Post added!</h2></div>
<div class="boxcontent"><a href="?/announcements">[ BACK ]</a></div>
</div>
</div>
		<?php
	}
		break;
		
	case "/announcements/edit":
	reqPermission(1);
	if ((isset($_GET['b'])) && (is_numeric($_GET['b'])))
	{
	$result = mysqli_query($conn, "SELECT * FROM announcements WHERE id=".$_GET['b']);
	if (mysqli_num_rows($result) != 0)
	{
	if (empty($_POST['text']))
	{
	$data = mysqli_fetch_assoc($result);
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Edit announcement</h2></div>
<div class="boxcontent">
<form action="?/announcements/edit&b=<?php echo $_GET['b']; ?>" method="POST">
By: <input type="text" name="who" value="<?php echo $data['who']; ?>" /><br />
Title: <input type="text" name="title" value="<?php echo $data['title']; ?>"/><br />
Text: <br />
<textarea name="text" cols="70" rows="10"><?php echo $data['text']; ?></textarea>
<input type="submit" value="Update announcement" />
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
<div class="boxbar"><h2>Entry updated!</h2></div>
<div class="boxcontent"><a href="?/announcements">[ BACK ]</a></div>
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
<div class="boxbar"><h2>Entry deleted!</h2></div>
<div class="boxcontent"><a href="?/announcements">[ BACK ]</a></div>
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
<div class="boxbar"><h2>All announcements</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>Title</td>
<td>Date</td>
<td>Edit</td>
<td>Delete</td>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM announcements ORDER BY date DESC;");
while ($row = mysqli_fetch_assoc($result))
{
echo "<td>".$row['title']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['date'])."</td>";
echo "<td><a href='?/announcements/edit&b=".$row['id']."'>Edit</a></td>";
echo "<td><a href='?/announcements/delete&b=".$row['id']."'>Delete</a></td>";
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
<div class="boxbar"><h2>News</h2></div>
<div class="boxcontent">
<?php
$result = mysqli_query($conn, "SELECT * FROM news ORDER BY date DESC;");
while ($row = mysqli_fetch_assoc($result))
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
<div class="boxbar"><h2>New news entry</h2></div>
<div class="boxcontent">
<form action="?/news/add" method="POST">
By: <input type="text" name="who" value="<?php echo $_SESSION['username']; ?>" /><br />
Title: <input type="text" name="title"/><br />
Text: <br />
<textarea name="text" cols="70" rows="10"></textarea>
<input type="submit" value="Add new entry" />
</form>
</div>
</div>
</div><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Your entries</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>Title</td>
<td>Date</td>
<td>Edit</td>
<td>Delete</td>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM news WHERE mod_id=".$_SESSION['id']." ORDER BY date DESC;");
while ($row = mysqli_fetch_assoc($result))
{
echo "<td>".$row['title']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['date'])."</td>";
echo "<td><a href='?/news/edit&b=".$row['id']."'>Edit</a></td>";
echo "<td><a href='?/news/delete&b=".$row['id']."'>Delete</a></td>";
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
		mysqli_query($conn, "INSERT INTO news (date, who, title, text, mod_id) VALUES (".time().", '".$who."', '".mysqli_real_escape_string($conn, htmlspecialchars($_POST['title']))."', '".$text."', ".$_SESSION['id'].");");
		generateNews($conn);
		
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Post added!</h2></div>
<div class="boxcontent"><a href="?/news">[ BACK ]</a></div>
</div>
</div>
		<?php
	}
		break;
	case "/news/edit":
	reqPermission(1);
		if ((isset($_GET['b'])) && (is_numeric($_GET['b'])))
	{
	$result = mysqli_query($conn, "SELECT * FROM news WHERE id=".$_GET['b']);
	if (mysqli_num_rows($result) != 0)
	{
	if (empty($_POST['text']))
	{
	$data = mysqli_fetch_assoc($result);
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Edit news entry</h2></div>
<div class="boxcontent">
<form action="?/news/edit&b=<?php echo $_GET['b']; ?>" method="POST">
By: <input type="text" name="who" value="<?php echo $data['who']; ?>" /><br />
Title: <input type="text" name="title" value="<?php echo $data['title']; ?>"/><br />
Text: <br />
<textarea name="text" cols="70" rows="10"><?php echo $data['text']; ?></textarea>
<input type="submit" value="Update entry" />
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
<div class="boxbar"><h2>Entry updated!</h2></div>
<div class="boxcontent"><a href="?/news">[ BACK ]</a></div>
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
<div class="boxbar"><h2>Entry deleted!</h2></div>
<div class="boxcontent"><a href="?/news">[ BACK ]</a></div>
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
<div class="boxbar"><h2>All news entries</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>Title</td>
<td>Date</td>
<td>Edit</td>
<td>Delete</td>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM news ORDER BY date DESC;");
while ($row = mysqli_fetch_assoc($result))
{
echo "<td>".$row['title']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['date'])."</td>";
echo "<td><a href='?/news/edit&b=".$row['id']."'>Edit</a></td>";
echo "<td><a href='?/news/delete&b=".$row['id']."'>Delete</a></td>";
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
<div class="boxbar"><h2>Board created!</h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards">[ BACK ]</a></div>
</div>
</div>
				<?php
			} else {
			?>
						<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>MySQL error or board exists!</h2></div>
<div class="boxcontent"><a href="?/boards">[ BACK ]</a></div>
</div>
</div>
			<?php
			}
		} else {
	?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Please, fill all fields!</h2></div>
<div class="boxcontent"><a href="?/boards">[ BACK ]</a></div>
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
<div class="boxbar"><h2>Create new board</h2></div>
<div class="boxcontent">
<form action="?/boards/add" method="POST">
Board directory (without /'s): <input type="text" name="short" maxlenght=10 /><br />
Board name: <input type="text" name="name" maxlenght=40 /><br />
Board short description (optional): <input type="text" name="des" maxlenght=100 /><br />
Board message (optional): <br /><textarea cols=70 rows=7 name="msg"></textarea><br />
Board bumplimit (optional, 0 for no limit): <input type="text" name="limit" maxlenght=9 value="0" /><br />
Board special options: <input type="checkbox" name="spoilers" value="1" />Allow image spoilers <input type="checkbox" name="noname" value="1" />No name field (forced anonymity) <input type="checkbox" name="ids" value="1" />Poster IDs<br />
<input type="checkbox" name="embeds" value="1" />Allow embeds <br />
<input type="submit" value="Create new board" />
</form>
</div>
</div>
</div>
<br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Manage boards</h2></div>
<div class="boxcontent">
All boards: <br />
<table>
<thead>
<tr>
<td>Directory</td>
<td>Name</td>
<td>Description</td>
<td>Bump limit</td>
<td>Message</td>
<td>Special</td>
<td>Edit</td>
<td>Delete</td>
<td>Rebuild cache</td>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM boards;");
while ($row = mysqli_fetch_assoc($result))
{
echo '<tr>';
echo "<td><a href='./".$row['short']."/'>/".$row['short']."/</a></td>";
echo "<td>".$row['name']."</td>";
echo "<td>".$row['des']."</td>";
echo "<td>".$row['bumplimit']."</td>";
if (!empty($row['message']))
{
echo "<td>Yes</td>";
} else {
echo "<td>No</td>";
}
echo "<td>";
if ($row['spoilers']==1) { echo "<b>Spoilers</b><br />"; }
if ($row['noname']==1) { echo "<b>No name</b><br />"; }
if ($row['ids']==1) { echo "<b>Poster IDs</b><br />"; }
if ($row['embeds']==1) { echo "<b>Embeds</b><br />"; }
echo "</td>";
echo "<td><a href='?/boards/edit&board=".$row['short']."'>Edit</a></td>";
echo "<td><a href='?/boards/delete&board=".$row['short']."'>Delete</a></td>";
echo "<td><a href='?/boards/rebuild&board=".$row['short']."'>Rebuild cache</a></td>";
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
<div class="boxbar"><h2>Board's cache rebuilded!</h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards">[ BACK ]</a></div>
</div>
</div>
				<?php
		} else {
		?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Board not found!</h2></div>
<div class="boxcontent"><a href="?/boards">[ BACK ]</a></div>
</div>
</div>
				<?php
		}
		break;
	case "/boards/delete_yes":
	reqPermission(2);
		if (!empty($_GET['board']))
		{
			$board = mysqli_real_escape_string($conn, $_GET['board']);
			if (isBoard($conn, $board))
			{
				deleteBoard($conn, $board);
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Board deleted!</h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards">[ BACK ]</a></div>
</div>
</div>
				<?php
			} else {
			
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>This board does not exist!</h2></div>
<div class="boxcontent"><a href="?/boards">[ BACK ]</a></div>
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
<div class="boxbar"><h2>Do you want to delete /<?php echo $_GET['board']; ?>/?</h2></div>
<div class="boxcontent"><a href="?/boards">[ NO ]</a> <a href="?/boards/delete_yes&board=<?php echo $_GET['board']; ?>">[ YES ]</a></div>
</div>
</div>
				<?php
		} else {
						?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>This board does not exist!</h2></div>
<div class="boxcontent"><a href="?/boards">[ BACK ]</a></div>
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
<div class="boxbar"><h2>Board updated successfully!</h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards">[ BACK ]</a></div>
</div>
</div>
				<?php
				} else {
				?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Some sort of error happened ;_;</h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards">[ BACK ]</a></div>
</div>
</div>
				<?php
				}
			}
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>This board does not exist!</h2></div>
<div class="boxcontent"><a href="?/boards">[ BACK ]</a></div>
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
<div class="boxbar"><h2>Board moved successfully!</h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards">[ BACK ]</a></div>
</div>
</div>
				<?php
				} elseif ($result == 0) {
				?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>This board does not exist!</h2></div>
<div class="boxcontent"><a href="?/boards">[ BACK ]</a></div>
</div>
</div>
				<?php
				} elseif ($result == -1) {
				?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>The board /<?php echo $_POST['new']; ?>/ does exist!</h2></div>
<div class="boxcontent"><a href="?/boards">[ BACK ]</a></div>
</div>
</div>
				<?php
				}
			}
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>This board does not exist!</h2></div>
<div class="boxcontent"><a href="?/boards">[ BACK ]</a></div>
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
<div class="boxbar"><h2>Edit board /<?php echo $_GET['board']; ?>/</h2></div>
<div class="boxcontent">
<form action="?/boards/update&board=<?php echo $_GET['board']; ?>" method="POST">
Board directory (without /'s): <input disabled type="text" name="short" maxlenght=10 value="<?php echo $data['short']; ?>" /><br />
Board name: <input type="text" name="name" maxlenght=40 value="<?php echo $data['name']; ?>" /><br />
Board short description (optional): <input type="text" name="des" maxlenght=100 value="<?php echo $data['des']; ?>" /><br />
Board message (optional): <br /><textarea cols=70 rows=7 name="msg"><?php echo $data['message']; ?></textarea><br />
Board bumplimit (optional, 0 for no limit): <input type="text" name="limit" maxlenght=9 value="<?php echo $data['bumplimit']; ?>" /><br />
Board special options: <input type="checkbox" name="spoilers" value="1" <?php if ($data['spoilers'] == 1) { echo "checked "; } ?> />Allow image spoilers <input type="checkbox" name="noname" value="1" <?php if ($data['noname'] == 1) { echo "checked "; } ?> />No name field (forced anonymity) <input type="checkbox" name="ids" value="1" <?php if ($data['ids'] == 1) { echo "checked "; } ?> />Poster IDs<br />
<input type="checkbox" name="embeds" value="1" <?php if ($data['embeds'] == 1) { echo "checked "; } ?> />Allow embeds <br />
<input type="submit" value="Update board info!" />
</form>
</div>
</div>
</div><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Move board /<?php echo $_GET['board']; ?>/</h2></div>
<div class="boxcontent">
<form action="?/boards/move&board=<?php echo $_GET['board']; ?>" method="POST">
New board directory (without /'s): <input type="text" name="new" maxlenght=10 /><br />
<input type="submit" value="Move board!" />
</form>
</div>
</div>
</div>
<?php
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>This board does not exist!</h2></div>
<div class="boxcontent"><a href="?/boards">[ BACK ]</a></div>
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
		
			$result = mysqli_query($conn, "SELECT password FROM users WHERE id=".$_SESSION['id']);
			$row = mysqli_fetch_assoc($result);
				if ($row['password'] != hash("sha512", $_POST['old']))
				{
							?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Password does not match with the password in database!</h2></div>
<div class="boxcontent"><a href="?/password">[ BACK ]</a></div>
</div>
</div>
			<?php
				} else {
					mysqli_query($conn, "UPDATE users SET password='".hash("sha512", $_POST['new'])."' WHERE id=".$_SESSION['id']);
				?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Password updated!</h2></div>
<div class="boxcontent"><a href="?/password">[ BACK ]</a></div>
</div>
</div>
				<?php
				}
			} else {
				?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Passwords don't match!</h2></div>
<div class="boxcontent"><a href="?/password">[ BACK ]</a></div>
</div>
</div>
			<?php
			}
		} else {
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Change your password</h2></div>
<div class="boxcontent">
<form action="?/password" method="POST">
Current password: <input type="password" name="old"><br />
New password: <input type="password" name="new"><br />
Confirm new password: <input type="password" name="new2"><br />
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
<div class="boxbar"><h2>Rebuild cache</h2></div>
<div class="boxcontent">
<form action="?/cache" method="POST">
<input type="checkbox" name="links" value=1>Board links</input><br />
<input type="checkbox" name="styles" value=1>Board styles</input><br />
<input type="checkbox" name="boards" value=1>All boards</input><br />
<input type="checkbox" name="static" value=1>All static pages</input><br />
<input type="submit" value="Rebuild"><br />
</form>
</div>
</div>
</div>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Rebuild static pages</h2></div>
<div class="boxcontent">
<form action="?/static" method="POST">
<input type="checkbox" name="frontpage" value=1>Frontpage (./<?php echo $config['frontpage_url']; ?>)</input><br />
<input type="checkbox" name="news" value=1>News page (./<?php echo $config['news_url']; ?>)</input><br />
<input type="submit" value="Rebuild"><br />
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
			$result = mysqli_query($conn, "SELECT * FROM boards ORDER BY short ASC;");
			while ($row = mysqli_fetch_assoc($result))
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
<div class="boxbar"><h2>Rebuilding done</h2></div>
<div class="boxcontent">
<a href="?/rebuild">[ BACK ]</a>
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
<div class="boxbar"><h2>Rebuilding done</h2></div>
<div class="boxcontent">
<a href="?/rebuild">[ BACK ]</a>
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
<div class="boxbar"><h2>Manage board links</h2></div>
<div class="boxcontent">
<b>You'll have to <a href="?/rebuild">rebuild board cache</a> after modifying settings here.</b><br />
<?php
echo getLinkTable($conn, -1);
?>
</div>
</div>
</div>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Add link category</h2></div>
<div class="boxcontent">
<b>You'll have to <a href="?/rebuild">rebuild board cache</a> after modifying settings here.</b><br />
<form action="?/links/category" method="POST">
Title: <input type="text" name="title" value="Category" /><input type="submit" value="Add category!" />
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
			$id = mysqli_real_escape_string($conn, $_GET['i']);
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
			$id = mysqli_real_escape_string($conn, $_GET['i']);
			$link = mysqli_query($conn, "SELECT * FROM links WHERE id=".$id);
			if (mysqli_num_rows($link) == 1)
			{
				$data = mysqli_fetch_assoc($link);
				if (empty($_POST['title']))
				{
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Edit link</h2></div>
<div class="boxcontent">
<b>You'll have to <a href="?/rebuild">rebuild board cache</a> after updating link.</b><br />
<form action="?/links/edit&i=<?php echo $id; ?>" method="POST">
Short: <input type="text" name="short" value="<?php echo $data['short']; ?>" /><br />
URL (board index): <input type="text" name="url" value="<?php echo $data['url']; ?>" /><br />
URL in-thread (optional): <input type="text" name="url_thread" value="<?php echo $data['url_thread']; ?>" /><br />
URL on frontpage (optional): <input type="text" name="url_index" value="<?php echo $data['url_index']; ?>" /><br />
Title: <input type="text" name="title" value="<?php echo $data['title']; ?>" /><br />
<br /><input type="submit" value="Update link!" />
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
			$id = mysqli_real_escape_string($conn, $_GET['l']);
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
			$id = mysqli_real_escape_string($conn, $_GET['l']);
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
			$id = mysqli_real_escape_string($conn, $_GET['p']);
			$cat = mysqli_query($conn, "SELECT * FROM links WHERE url='' AND id=".$id);
			if (mysqli_num_rows($cat) == 1)
			{
				if (empty($_POST['title']))
				{
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Add link</h2></div>
<div class="boxcontent">
<b>You'll have to <a href="?/rebuild">rebuild board cache</a> after adding link.</b><br />
<form action="?/links/add&p=<?php echo $id; ?>" method="POST">
Short: <input type="text" name="short" value="" /><br />
URL: <input type="text" name="url" value="../" /><br />
URL in-thread (optional): <input type="text" name="url_thread" value="../../" /><br />
URL on frontpage (optional): <input type="text" name="url_index" value="./" /><br />
Title: <input type="text" name="title" value="" /><br />
<br /><input type="submit" value="Add link!" />
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
			echo mysqli_error($conn);
		?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Global message updated</h2></div>
<div class="boxcontent">
<b>You'll have to <a href="?/rebuild">rebuild board cache</a> after updating message.</b><br />
<a href="?/message">[ BACK ]</a>
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
<div class="boxbar"><h2>Edit global message</h2></div>
<div class="boxcontent">
<b>You'll have to <a href="?/rebuild">rebuild board cache</a> after updating message.</b><br />
		<form action="?/message" method="POST">
		<textarea cols=70 rows=14 name="message"><?php echo $msg; ?></textarea><br />
		<input type="submit" value="Update">
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
			mysqli_query($conn, "DELETE FROM bans WHERE id=".$_GET['b']);
		}
	}
	?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Bans</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>IP</td>
<td>Reason</td>
<td>Staff note</td>
<td>Created</td>
<td>Expires</td>
<td>Boards</td>
<td>Delete</td>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM bans ORDER BY created LIMIT 0, 15;");
while ($row = mysqli_fetch_assoc($result))
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
echo "<td><a href='?/bans&del=1&b=".$row['id']."'>Delete</a></td>";
} else {
echo "<td></td>";
}
echo "</tr>";
}
?>
</tbody>
</table>
Showing recent 15 bans. <a href="?/bans/all">Show all</a> <a href="?/bans/recent&c=100">Show recent 100</a>
</div>
</div>
</div>
<?php
		break;
	case "/bans/all":
	?>
	<div class="box-outer top-box">
	<div class="box-inner">
	<div class="boxbar"><h2>All bans</h2></div>
	<div class="boxcontent">
	<table>
	<thead>
	<tr>
	<td>IP</td>
	<td>Reason</td>
	<td>Staff note</td>
	<td>Created</td>
	<td>Expires</td>
	<td>Boards</td>
	<td>Delete</td>
	</tr>
	</thead>
	<tbody>
	<?php
	$result = mysqli_query($conn, "SELECT * FROM bans ORDER BY created;");
	while ($row = mysqli_fetch_assoc($result))
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
	echo "<td><a href='?/bans&del=1&b=".$row['id']."'>Delete</a></td>";
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
	<div class="boxbar"><h2>All bans</h2></div>
	<div class="boxcontent">
	<table>
	<thead>
	<tr>
	<td>IP</td>
	<td>Reason</td>
	<td>Staff note</td>
	<td>Created</td>
	<td>Expires</td>
	<td>Boards</td>
	<td>Delete</td>
	</tr>
	</thead>
	<tbody>
	<?php
	$result = mysqli_query($conn, "SELECT * FROM bans ORDER BY created LIMIT 0, ".$_GET['c'].";");
	while ($row = mysqli_fetch_assoc($result))
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
	echo "<td><a href='?/bans&del=1&b=".$row['id']."'>Delete</a></td>";
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
			$board = mysqli_real_escape_string($conn, $_GET['b']);
			$post = $_GET['p'];
			//<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>
			$postdata = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE id=".$post);
			if (mysqli_num_rows($postdata) == 1)
			{
				$postinfo = mysqli_fetch_assoc($postdata);
				$ip = $postinfo['ip'];
			} else {
				$post = "";
				$board = "";
			}
		}
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Add ban <?php if ($_SESSION['type']==0) { echo "request"; } ?></h2></div>
<div class="boxcontent">
<form action="?/bans/add" method="POST">
IP: <input type="text" name="ip" value="<?php echo $ip; ?>"/><br />
Reason: <input type="text" name="reason" /><br />
Staff note: <input type="text" name="note" /><br />
<?php
if ($_SESSION['type']>=1) {
?>
Expires (e.g. 1d, 20s): <input type="text" name="expires" /><br />
<br /><br />
Boards: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1/> All<br/>
<select name="boards[]" id="boardSelect" multiple>
<?php
$result = mysqli_query($conn, "SELECT * FROM boards;");
while ($row = mysqli_fetch_assoc($result))
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
<input type="hidden" name="delete" value="1" /><b>POST WILL BE DELETED</b>
<?php
} else {
?>
Append text to post: <input type="text" name="append_text" value='<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>' style="width: 400px;"/><input type="checkbox" name="append" value="1" checked=1/>Yes<br/>
<?php
}
}
}
?>
<br />
<input type="submit" value="<?php if ($_SESSION['type']==0) { echo "Add request"; } else { echo "Ban"; } ?>" />
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
<div class="boxbar"><h2>IP syntax wrong!</h2></div>
<div class="boxcontent"><a href="?/bans/add">[ BACK ]</a></div>
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
			$board = mysqli_real_escape_string($conn, $_POST['board']);
			$post = $_POST['post'];
			//<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>
			$postdata = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE id=".$post);
			if (mysqli_num_rows($postdata) == 0)
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
<div class="boxbar"><h2>User banned!</h2></div>
<div class="boxcontent"><a href="?/bans">[ BACK ]</a></div>
</div>
</div>
				<?php
		} elseif (($what == 2) && ($result == 1))
		{
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Request added!</h2></div>
<div class="boxcontent"><a href="javascript:history.go(-2);">[ BACK ]</a></div>
</div>
</div>
				<?php
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Form filled wrong</h2></div>
<div class="boxcontent"><a href="javascript:history.back(-1);">[ BACK ]</a></div>
</div>
</div>
				<?php
		}
		}
		} else {
			if (is_numeric($_GET['r']))
			{
				$req = mysqli_query($conn, "SELECT * FROM ban_requests WHERE id=".$_GET['r']);
				if (mysqli_num_rows($req) == 1)
				{
				$request = mysqli_fetch_assoc($req);
				$board = $request['board'];
				$post = $request['post'];
				//<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>
				$postdata = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE id=".$post);
				if (mysqli_num_rows($postdata) == 1)
				{
					$postinfo = mysqli_fetch_assoc($postdata);
					$ip = $postinfo['ip'];
				} else {
					$post = "";
					$board = "";
				}
					?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Add ban <?php if ($_SESSION['type']==0) { echo "request"; } ?></h2></div>
<div class="boxcontent">
<form action="?/bans/add" method="POST">
IP: <input type="text" name="ip" value="<?php echo $ip; ?>"/><br />
Reason: <input type="text" name="reason" value="<?php echo $request['reason']; ?>"/><br />
Staff note: <input type="text" name="note" value="<?php echo $request['note']; ?>"/><br />
Expires (e.g. 1d, 20s): <input type="text" name="expires" /><br />
<br /><br />
Boards: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1/> All<br/>
<select name="boards[]" id="boardSelect" multiple>
<?php
$result = mysqli_query($conn, "SELECT * FROM boards;");
while ($row = mysqli_fetch_assoc($result))
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
<input type="hidden" name="delete" value="1" /><b>POST WILL BE DELETED</b>
<?php
} else {
?>
Append text to post: <input type="text" name="append_text" value='<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>' style="width: 400px;"/><input type="checkbox" name="append" value="1" checked=1/>Yes<br/>
<?php
}
}
?>
<br />
<input type="submit" value="Ban" />
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
<div class="boxbar"><h2>User deleted!</h2></div>
<div class="boxcontent"><a href="?/users">[ BACK ]</a></div>
</div>
</div>
				<?php
			} else {
			
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>This user does not exist!</h2></div>
<div class="boxcontent"><a href="?/users">[ BACK ]</a></div>
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
<div class="boxbar"><h2>Do you want to delete this user?</h2></div>
<div class="boxcontent"><a href="?/users">[ NO ]</a> <a href="?/users/delete_yes&id=<?php echo $_GET['id']; ?>">[ YES ]</a></div>
</div>
</div>
				<?php
		} else {
						?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>This user does not exist!</h2></div>
<div class="boxcontent"><a href="?/users">[ BACK ]</a></div>
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
<div class="boxbar"><h2>User added!</h2></div>
<div class="boxcontent"><a href="?/users">[ BACK ]</a></div>
</div>
</div>
				<?php
			} else {
			?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>User already exists!</h2></div>
<div class="boxcontent"><a href="?/users">[ BACK ]</a></div>
</div>
</div>
				<?php
			}
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Please, fill all fields!</h2></div>
<div class="boxcontent"><a href="?/users">[ BACK ]</a></div>
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
<div class="boxbar"><h2>User updated</h2></div>
<div class="boxcontent">
<a href="?/users">[ BACK ]</a>
</div>
</div>
</div>
					<?php
				} else {
					$result = mysqli_query($conn, "SELECT * FROM users WHERE id=".$_GET['id']);
					$data = mysqli_fetch_assoc($result);
					$boards = $data['boards'];
					if ($data['boards'] != "*") { $board = explode(",", $data['boards']); }
		?>
				<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Edit user</h2></div>
<div class="boxcontent">
<form action="?/users/edit&id=<?php echo $id; ?>" method="POST">
Username: <input type="text" name="username" value="<?php echo $data['username']; ?>"/><br />
Password (leave blank to not change): <input type="password" name="password"/><br />
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
Type: <select name="type"><option value="0"<?php echo $janitor; ?>>Janitor</option><option value="1"<?php echo $moderator; ?>>Moderator</option><option value="2"<?php echo $administrator; ?>>Administrator</option></select>

<br /><br />
<?php
if ($boards == "*")
{
?>
Boards: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1 checked/> All<br/>
<select name="boards[]" id="boardSelect" multiple style="display: none;">
<?php
} else {
?>
Boards: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1/> All<br/>
<select name="boards[]" id="boardSelect" multiple>
<?php
}
?>
<?php
$result = mysqli_query($conn, "SELECT * FROM boards;");
while ($row = mysqli_fetch_assoc($result))
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
<input type="submit" value="Update user!" />
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
<div class="boxbar"><h2>Your notes</h2></div>
<div class="boxcontent">
<?php
$result = mysqli_query($conn, "SELECT * FROM notes WHERE mod_id=".$_SESSION['id']." ORDER BY created DESC;");
while ($row = mysqli_fetch_assoc($result))
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
<div class="boxbar"><h2>Add note</h2></div>
<div class="boxcontent">
<form action="?/notes/add" method="POST">
<textarea name="note" cols=70 rows=12></textarea><br />
<input type="submit" value="Add note!" />
</form>
</div>
</div>
</div>
<?php
		break;
	case "/notes/add":
		if (!empty($_POST['note']))
		{
			$note = mysqli_real_escape_string($conn, $_POST['note']);
			mysqli_query($conn, "INSERT INTO notes (mod_id, note, created) VALUES (".$_SESSION['id'].", '".$note."', ".time().")");
		?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Note added</h2></div>
<div class="boxcontent">
<a href="?/notes">[ BACK ]</a>
</div>
</div>
</div>
<?php
		} else {
				?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Please, fill all fields!</h2></div>
<div class="boxcontent">
<a href="?/notes">[ BACK ]</a>
</div>
</div>
</div>
<?php
		}
		break;
	case "/notes/delete":
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$note = mysqli_query($conn, "SELECT * FROM notes WHERE id=".$_GET['id']);
			if (mysqli_num_rows($note) == 1)
			{
				$info = mysqli_fetch_assoc($note);
				if ($info['mod_id'] == $_SESSION['id'])
				{
					mysqli_query($conn, "DELETE FROM notes WHERE id=".$_GET['id']);
					?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Note deleted!</h2></div>
<div class="boxcontent">
<a href="?/notes">[ BACK ]</a>
</div>
</div>
</div>
<?php
				} else {
				?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Error</h2></div>
<div class="boxcontent">
<a href="?/notes">[ BACK ]</a>
</div>
</div>
</div>
<?php
				}
			} else {
			?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Error</h2></div>
<div class="boxcontent">
<a href="?/notes">[ BACK ]</a>
</div>
</div>
</div>
<?php
			}
		} else {
				?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Error</h2></div>
<div class="boxcontent">
<a href="?/notes">[ BACK ]</a>
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
	<title>Error</title>
	</head>
	<body>
				<?php
					echo "<center><h1>No board selected!</h1></center></body></html>";
					exit;
				}
				$board = $_POST['board'];
				canBoard($board);
				?>
	<html>
	<head>
	<title>Updating index</title>
	</head>
	<body>
	<center><h1>Updating Index...</h1></center>
				<?php
				
				$md5 = "";
				$bdata = getBoardData($conn, $_POST['board']);
				if ((!empty($_POST['embed'])) && (!empty($_FILES['upfile']['tmp_name'])))
				{
					echo "<center><h1>Choose one: image or embed! ;_;</h1></center></body></html>";
					exit;
				}
				if (!empty($_POST['embed']))
				{
					$embed_table = array();
					$result = mysqli_query($conn, "SELECT * FROM embeds;");
					while ($row = mysqli_fetch_assoc($result))
					{
						$embed_table[] = $row;
					}
					if ((isEmbed($_POST['embed'], $embed_table)) && ($bdata['embeds']==1))
					{
						$filename = "embed:".$_POST['embed'];
					} else {
						echo "<center><h1>Embed not supported!</h1></center></body></html>";
						exit;
					}
				} else {
					if ((empty($_FILES['upfile']['tmp_name'])) && (!empty($_FILES['upfile']['name'])))
					{
						echo "<h1>File size too big!</h1></body></html>";
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
							echo "<h1>File size too big!</h1></body></html>";
							exit;
						}
						if (!isImage($_FILES['upfile']['tmp_name']))
						{
							echo "<h1>File is not an image!</h1></body></html>";
							exit;
						}
						$md5 = md5_file($_FILES['upfile']['tmp_name']);
						if(move_uploaded_file($_FILES['upfile']['tmp_name'], $target_path)) {
							echo "The file ".basename( $_FILES['upfile']['name'])." has been uploaded";
						} else {
							echo "There was an error uploading the file, please try again!";
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
								echo "<h1>Could not create thumbnail!</h1></body></html>"; exit;
							}
						} else {
							if (thumb($board, $fileid.".".$ext) < 0)
							{
								echo "<h1>Could not create thumbnail!</h1></body></html>"; exit;
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
					echo "<h1>This board does not exist!</h1></body></html>"; exit;
				}
				break;
			case "usrform":
				if (!empty($_POST['delete']))
				{
					$onlyimgdel = 0;
					if (empty($_POST['board']))
					{
						echo "<h1>No board selected!</h1></body></html>";
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
								echo "Bad password for post ".$key.".<br />";
							} elseif ($done == -2) {
								echo "Post ".$key." not found.<br />";
							} elseif ($done == -3) {
								echo "Post ".$key." has no image.<br />";
							} elseif ($done == 1) {
								echo "Deleted image from post ".$key.".<br />";
							} elseif ($done == 2) {
								echo "Deleted post ".$key.".<br />";
							}
							if ($done == -16)
							{
								echo "<h1>This board does not exist!</h1></body></html>"; exit;
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
			mysqli_query($conn, "DELETE FROM reports WHERE id=".$_GET['id']);
		}
	}
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Reports</h2></div>
<div class="boxcontent">
<?php
if ($_SESSION['type'] >= 1)
{
?>
<a href="?/reports/clear_all">Clear all</a>
<?php
}
?>
<table>
<thead>
<tr>
<td>Post</td>
<td>File</td>
<td>Comment</td>
<td>Reason</td>
<td>Reporter IP</td>
<td>Action</td>
</tr>
</thead>
<tbody>
<?php
		$result = mysqli_query($conn, "SELECT * FROM reports ORDER BY created DESC");
		while ($row = mysqli_fetch_assoc($result))
		{
			$post = mysqli_query($conn, "SELECT * FROM posts_".$row['board']." WHERE id=".$row['reported_post']);
			if (mysqli_num_rows($post) == 0)
			{
				mysqli_query($conn, "DELETE FROM reports WHERE id=".$row['id']);
			}
			$pdata = mysqli_fetch_assoc($post);
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
				echo "<td>".processComment($row['board'], $conn, $pdata['comment'], 2)."</td>";
			} elseif ($pdata['raw'] == 2)
			{
				echo "<td>".processComment($row['board'], $conn, $pdata['comment'], 2, 0)."</td>";
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
		mysqli_query($conn, "TRUNCATE TABLE reports;");
		?>
		<meta http-equiv="refresh" content="0;URL='?/reports'" />
		<?php
		break;
	case "/reports/clear_all":
		reqPermission(1);
		?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Do you want to clear all reports?</h2></div>
<div class="boxcontent"><a href="?/reports">[ NO ]</a> <a href="?/reports/clear_all_yes">[ YES ]</a></div>
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
<div class="boxbar"><h2>Do you want to delete this post?</h2></div>
<div class="boxcontent"><a href="javascript:history.back(-1);">[ NO ]</a> <a href="?/delete_post/yes&b=<?php echo $_GET['b']; ?>&p=<?php echo $_GET['p'].$f; ?>">[ YES ]</a></div>
</div>
</div>
		<?php
		}
		break;
	case "/delete_post/yes":
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
<div class="boxbar"><h2>File deleted</h2></div>
<div class="boxcontent"><a href="?/board&b=<?php echo $_GET['b']; ?>">[ BACK ]</a></div>
</div>
</div>
		<?php
			} else {
			?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Post deleted</h2></div>
<div class="boxcontent"><a href="?/board&b=<?php echo $_GET['b']; ?>">[ BACK ]</a></div>
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
<div class="boxbar"><h2>Information about <?php echo $_GET['ip']; ?></h2></div>
<div class="boxcontent">
<a href="?/search/ip&ip=<?php echo $_GET['ip']; ?>">Search for posts by this IP</a><br />
<b>Recent 15 bans for this IP:</b>
<table>
<thead>
<tr>
<td>IP</td>
<td>Reason</td>
<td>Staff note</td>
<td>Created</td>
<td>Expires</td>
<td>Boards</td>
<td>Delete</td>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM bans WHERE ip='".$_GET['ip']."' ORDER BY created LIMIT 0, 15;");
while ($row = mysqli_fetch_assoc($result))
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
echo "<td><a href='?/bans&del=1&b=".$row['id']."'>Delete</a></td>";
} else {
echo "<td></td>";
}
echo "</tr>";
}
?>
</tbody>
</table>
<br />
<b>Notes for this IP:</b>
<br />
<table>
<thead>
<td>Created</td>
<td>Note</td>
<td>Delete</td>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM ip_notes WHERE ip='".$_GET['ip']."';");
while ($row = mysqli_fetch_assoc($result))
{
echo "<tr>";
echo "<td>".date("d/m/Y(D)H:i:s", $row['created'])."</td>";
echo "<td>".$row['text']."</td>";
echo "<td><a href='?/ipnotes/delete&id=".$row['id']."'>Delete</a></td>";
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
<div class="boxbar"><h2>Add note</h2></div>
<div class="boxcontent">
<form action="?/ipnotes/add&ip=<?php echo $_GET['ip']; ?>" method="POST">
<textarea name="note" cols=70 rows=12></textarea><br />
<input type="submit" value="Add note!" />
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
<div class="boxbar"><h2>Latest IP notes</h2></div>
<div class="boxcontent">
	<table>
<thead>
<td>Created</td>
<td>Note</td>
<td>Delete</td>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM ip_notes LIMIT 0, 15;");
while ($row = mysqli_fetch_assoc($result))
{
echo "<tr>";
echo "<td>".date("d/m/Y(D)H:i:s", $row['created'])."</td>";
echo "<td>".$row['text']."</td>";
echo "<td><a href='?/ipnotes/delete&id=".$row['id']."'>Delete</a></td>";
echo "</tr>";
}
?>
</tbody>
</table>
Showing latest 15 notes. <a href="?/ipnotes/all">Show all</a>
</div>
</div>
</div><br />
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Add IP note</h2></div>
<div class="boxcontent">
<form action="?/ipnotes/add" method="POST">
IP: <input type="text" name="ip" /><br />
<textarea name="note" cols=70 rows=12></textarea><br />
<input type="submit" value="Add note!" />
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
<div class="boxbar"><h2>All IP notes</h2></div>
<div class="boxcontent">
	<table>
<thead>
<td>Created</td>
<td>Note</td>
<td>Delete</td>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM ip_notes;");
while ($row = mysqli_fetch_assoc($result))
{
echo "<tr>";
echo "<td>".date("d/m/Y(D)H:i:s", $row['created'])."</td>";
echo "<td>".$row['text']."</td>";
if ($_SESSION['type']>=1)
{
echo "<td><a href='?/ipnotes/delete&id=".$row['id']."'>Delete</a></td>";
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
		if ((!empty($ip)) && (!empty($_POST['note'])))
		{
			$note = processEntry($conn, $_POST['note']);
			mysqli_query($conn, "INSERT INTO ip_notes (ip, text, created, mod_id) VALUES ('".$ip."', '".$note."', ".time().", ".$_SESSION['id'].")");
			?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>IP note added</h2></div>
<div class="boxcontent"><a href="?/ipnotes">[ BACK ]</a></div>
</div></div>

			<?php
		}
		if (((empty($_GET['ip'])) || (!filter_var($_GET['ip'], FILTER_VALIDATE_IP))) || ((empty($_POST['ip'])) || (!filter_var($_POST['ip'], FILTER_VALIDATE_IP))))
		{
		?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>No IP provided or IP wrong</h2></div>
<div class="boxcontent"><a href="?/ipnotes">[ BACK ]</a></div>
</div></div>

			<?php
		}
		if (empty($_POST['note']))
		{
		?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Please, fill all fields</h2></div>
<div class="boxcontent"><a href="?/ipnotes">[ BACK ]</a></div>
</div></div>

			<?php
		}
		break;
	case "/ipnotes/delete":
		reqPermission(1);
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			mysqli_query($conn, "DELETE FROM ip_notes WHERE id=".$_GET['id']);
		}
		?>
		<meta http-equiv="refresh" content="0;URL='?/ipnotes'" />
		<?php
		break;
	case "/sticky/toggle":
		if ((!empty($_GET['b'])) && (!empty($_GET['t'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['t'])))
		{
			canBoard($_GET['b']);
			$result = mysqli_query($conn, "SELECT * FROM posts_".$_GET['b']." WHERE id=".$_GET['t']." AND resto=0");
			if (mysqli_num_rows($result) == 1)
			{
				$pdata = mysqli_fetch_assoc($result);
				if ($pdata['sticky'] == 1)
				{
					mysqli_query($conn, "UPDATE posts_".$_GET['b']." SET sticky=0 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Thread unstickied</h2></div>
<meta http-equiv="refresh" content="1;URL='?/board&b=<?php echo $_GET['b']."&t=".$_GET['t']; ?>'" />
</div>
</div>
		<?php
				} else {
					mysqli_query($conn, "UPDATE posts_".$_GET['b']." SET sticky=1 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Thread stickied</h2></div>
<meta http-equiv="refresh" content="1;URL='?/board&b=<?php echo $_GET['b']."&t=".$_GET['t']; ?>'" />
</div>
</div>
		<?php
				}
			} else {
			?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Thread not found</h2></div>
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
			$result = mysqli_query($conn, "SELECT * FROM posts_".$_GET['b']." WHERE id=".$_GET['t']." AND resto=0");
			if (mysqli_num_rows($result) == 1)
			{
				$pdata = mysqli_fetch_assoc($result);
				if ($pdata['locked'] == 1)
				{
					mysqli_query($conn, "UPDATE posts_".$_GET['b']." SET locked=0 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Thread unlocked</h2></div>
<meta http-equiv="refresh" content="1;URL='?/board&b=<?php echo $_GET['b']."&t=".$_GET['t']; ?>'" />
</div>
</div>
		<?php
				} else {
					mysqli_query($conn, "UPDATE posts_".$_GET['b']." SET locked=1 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Thread locked</h2></div>
<meta http-equiv="refresh" content="1;URL='?/board&b=<?php echo $_GET['b']."&t=".$_GET['t']; ?>'" />
</div>
</div>
		<?php
				}
			} else {
			?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Thread not found</h2></div>
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
			$result = mysqli_query($conn, "SELECT * FROM posts_".$_GET['b']." WHERE id=".$_GET['t']." AND resto=0");
			if (mysqli_num_rows($result) == 1)
			{
				$pdata = mysqli_fetch_assoc($result);
				if ($pdata['sage'] == 1)
				{
					mysqli_query($conn, "UPDATE posts_".$_GET['b']." SET sage=0 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Antibump off</h2></div>
<meta http-equiv="refresh" content="1;URL='?/board&b=<?php echo $_GET['b']."&t=".$_GET['t']; ?>'" />
</div>
</div>
		<?php
				} else {
					mysqli_query($conn, "UPDATE posts_".$_GET['b']." SET sage=1 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Antibump on</h2></div>
<meta http-equiv="refresh" content="1;URL='?/board&b=<?php echo $_GET['b']."&t=".$_GET['t']; ?>'" />
</div>
</div>
		<?php
				}
			} else {
			?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Thread not found</h2></div>
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
<div class="boxbar"><h2>Locked threads</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>ID</td>
<td>Comment</td>
<td>Unlock</td>
</tr>
</thead>
<tbody>
	<?php
	$boards = mysqli_query($conn, "SELECT * FROM boards ORDER BY short ASC;");
	while ($row = mysqli_fetch_assoc($boards))
	{
		$threads = mysqli_query($conn, "SELECT * FROM posts_".$row['short']." WHERE locked=1 AND resto=0 ORDER BY lastbumped DESC;");
		while ($thread = mysqli_fetch_assoc($threads))
		{
			echo "<tr>";
			echo "<td><a href='?/board&b=".$row['short']."&t=".$thread['id']."#p".$thread['id']."'>/".$row['short']."/".$thread['id']."</a></td>";
			if ($thread['raw'] == 0)
			{
				echo "<td>".processComment($row['short'], $conn, $thread['comment'], 2)."</td>";
			} elseif ($thread['raw'] == 2)
			{
				echo "<td>".processComment($row['short'], $conn, $thread['comment'], 2, 0)."</td>";
			} else {
				echo "<td>".$thread['comment']."</td>";
			}
			echo "<td><a href='?/locked/toggle&b=".$row['short']."&t=".$thread['id']."'>Unlock</a></td>";
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
<div class="boxbar"><h2>Sticky threads</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>ID</td>
<td>Comment</td>
<td>Unstick</td>
</tr>
</thead>
<tbody>
	<?php
	$boards = mysqli_query($conn, "SELECT * FROM boards ORDER BY short ASC;");
	while ($row = mysqli_fetch_assoc($boards))
	{
		$threads = mysqli_query($conn, "SELECT * FROM posts_".$row['short']." WHERE sticky=1 AND resto=0 ORDER BY lastbumped DESC;");
		while ($thread = mysqli_fetch_assoc($threads))
		{
			echo "<tr>";
			echo "<td><a href='?/board&b=".$row['short']."&t=".$thread['id']."#p".$thread['id']."'>/".$row['short']."/".$thread['id']."</a></td>";
			if ($thread['raw'] == 0)
			{
				echo "<td>".processComment($row['short'], $conn, $thread['comment'], 2)."</td>";
			} elseif ($thread['raw'] == 2)
			{
				echo "<td>".processComment($row['short'], $conn, $thread['comment'], 2, 0)."</td>";
			} else {
				echo "<td>".$thread['comment']."</td>";
			}
			echo "<td><a href='?/sticky/toggle&b=".$row['short']."&t=".$thread['id']."'>Unstick</a></td>";
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
<div class="boxbar"><h2>Ban appeals</h2></div>
<div class="boxcontent">
<a href="?/appeals/clear_all">Clear all</a>
<table>
<thead>
<tr>
<td>IP</td>
<td>Ban reason</td>
<td>Staff note</td>
<td>Days left</td>
<td>E-mail</td>
<td>Appeal text</td>
<td>Actions</td>
</tr>
</thead>
<tbody>
<?php
$appeals = mysqli_query($conn, "SELECT * FROM appeals;");
while ($row = mysqli_fetch_assoc($appeals))
{
	if ($row['rangeban'] == 0)
	{
		$bandata = mysqli_query($conn, "SELECT * FROM bans WHERE id=".$row['ban_id']);
	} else {
		$bandata = mysqli_query($conn, "SELECT * FROM rangebans WHERE id=".$row['ban_id']);
	}
	if (mysqli_num_rows($bandata) == 1)
	{
		$ban = mysqli_fetch_assoc($bandata);
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
			echo "<td><b>permaban</b></td>";
		} else {
			echo "<td>".$left." days</td>";
		}
		echo "<td>".$row['email']."</td>";
		echo "<td>".$row['msg']."</td>";
		echo "<td> [ <a href='?/appeals/clear&id=".$row['id']."'>C</a> / <a href='?/bans&del=1&b=".$ban['id']."'>U</a> ]</td>";
		echo "</tr>";
	} else {
		mysqli_query($conn, "DELETE FROM appeals WHERE id=".$row['id']);
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
			mysqli_query($conn, "DELETE FROM appeals WHERE id=".$_GET['id']);
			?>
			<meta http-equiv="refresh" content="0;URL='?/appeals'" />
			<?php
		}
		break;
	case "/appeals/clear_all_yes":
		reqPermission(1);
		mysqli_query($conn, "TRUNCATE TABLE appeals;");
		?>
		<meta http-equiv="refresh" content="0;URL='?/appeals'" />
		<?php
		break;
	case "/appeals/clear_all":
		reqPermission(1);
		?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Do you want to clear all appeals?</h2></div>
<div class="boxcontent"><a href="?/appeals">[ NO ]</a> <a href="?/appeals/clear_all_yes">[ YES ]</a></div>
</div>
</div>
		<?php
		break;
	case "/config":
		$config = getConfig($conn);
		?>
				<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Configuration</h2></div>
<div class="boxcontent">
<a href="?/config/reset">Reset config</a>
		<form action="?/config/update" method="POST">
		Frontpage style: <select name="frontpage_style">
		<option value="0" <?php if ($config['frontpage_style'] == 0) { echo "selected"; } ?>>Kusaba X</option>
		<option value="1" <?php if ($config['frontpage_style'] == 1) { echo "selected"; } ?>>4chan</option></select><br />
		Frontpage URL: <input type="text" name="frontpage_url" value="<?php echo $config['frontpage_url']; ?>" /><br />
		Frontpage menu URL: <input type="text" name="frontpage_menu_url"  value="<?php echo $config['frontpage_menu_url']; ?>" /><br />
		News URL: <input type="text" name="news_url" value="<?php echo $config['news_url']; ?>" /><br />
		Site name: <input type="text" name="sitename" value="<?php echo $config['sitename']; ?>"  /><br />
		<input type="submit" value="Update" />
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
<div class="boxbar"><h2>Inbox</h2></div>
<div class="boxcontent">
<table>
<thead>
<td>Title</td>
<td>Date</td>
<td>From</td>
<td>Delete</td>
</thead>
<tbody>
		<?php
		$pms = mysqli_query($conn, "SELECT users.username, pm.* FROM pm LEFT JOIN users ON pm.from_user=users.id WHERE pm.to_user=".$_SESSION['id']." ORDER BY pm.created DESC");
		while ($row = mysqli_fetch_assoc($pms))
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
			echo "<td><a href='?/inbox/delete&id=".$row['id']."'>Delete</a></td>";
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
			$result = mysqli_query($conn, "SELECT * FROM users WHERE username='".mysqli_real_escape_string($conn, $_POST['to'])."'");
			if (mysqli_num_rows($result) == 1)
			{
				$row = mysqli_fetch_assoc($result);
				$text = processEntry($conn, $_POST['text']);
				$title = mysqli_real_escape_string($conn, $_POST['title']);
				mysqli_query($conn, "INSERT INTO pm (created, from_user, to_user, title, text, read_msg) VALUES (".time().", ".$_SESSION['id'].", ".$row['id'].", '".$title."', '".$text."', 0)");
			?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Message sent</h2></div>
<div class="boxcontent"><a href="?/inbox/new">[ BACK ]</a></div>
</div></div>
			<?php
			} else {
			?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>User not found</h2></div>
<div class="boxcontent"><a href="?/inbox/new">[ BACK ]</a></div>
</div></div>
			<?php
			}
		} else {
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Send message</h2></div>
<div class="boxcontent">
<form action="?/inbox/new" method="POST">
To: <input type="text" name="to" /><br />
Title: <input type="text" name="title" /><br />
Text:<br />
<textarea name="text" cols=40 rows=9></textarea><br />
<input type="submit" value="Send" />
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
		$result = mysqli_query($conn, "SELECT users.username, pm.* FROM pm LEFT JOIN users ON pm.from_user=users.id WHERE pm.to_user=".$_SESSION['id']." AND pm.id=".$_GET['id']);
		if (mysqli_num_rows($result) == 1)
			{
				$row = mysqli_fetch_assoc($result);
				if ($row['read_msg'] != 1)
				{
					mysqli_query($conn, "UPDATE pm SET read_msg=1 WHERE id=".$_GET['id']);
				}
				?>
				<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Read message</h2></div>
<div class="boxcontent">
<form action="?/inbox/new" method="POST">
From: <b><?php echo $row['username']; ?></b><br />
Title: <b><?php echo $row['title']; ?></b><br />
Text:<br />
<?php echo $row['text']; ?><br />
</form>
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
			mysqli_query($conn, "DELETE FROM pm WHERE id=".$_GET['id']." AND to_user=".$_SESSION['id']);
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
		if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
			?>
			<div class="box-outer top-box">
			<div class="box-inner">
			<div class="boxbar"><h2>Showing posts by IP <?php echo $_GET['ip']; ?></h2></div>
			<div class="boxcontent">
			<a href="?/delete_posts&ip=<?php echo $_GET['ip']; ?>">Delete posts from this IP</a>
			<table>
			<thead>
			<tr>
			<td>Name</td>
			<td>Email</td>
			<td>Date</td>
			<td>Comment</td>
			<td>Subject</td>
			<td>File</td>
			<td>Delete</td>
			</tr>
			</thead>
			<tbody>
			<?php
			$boards = mysqli_query($conn, "SELECT * FROM boards ORDER BY short ASC");
			while ($board = mysqli_fetch_assoc($boards))
			{
				$posts = mysqli_query($conn, "SELECT * FROM posts_".$board['short']." WHERE ip='".$_GET['ip']."'");
				while ($row = mysqli_fetch_assoc($posts))
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
							$comment = processComment($board['short'], $conn, $row['comment'], 2, 0);
						} else {
							$comment = processComment($board['short'], $conn, $row['comment'], 2);
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
<div class="boxbar"><h2>Do you want to delete posts from IP <?php echo $_GET['ip']; ?>?</h2></div>
<div class="boxcontent"><a href="?/info&ip=<?php echo $_GET['ip']; ?>">[ NO ]</a> <a href="?/delete_posts&ip=<?php echo $_GET['ip']; ?>">[ YES ]</a></div>
</div>
</div>
		<?php
		}
		break;
	case "/delete_posts/yes":
		if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
			$boards = mysqli_query($conn, "SELECT * FROM boards ORDER BY short ASC");
			while ($board = mysqli_fetch_assoc($boards))
			{
				$threads = mysqli_query($conn, "SELECT * FROM posts_".$board['short']." WHERE ip=".$_GET['ip']."' AND resto=0");
				while ($row = mysqli_fetch_assoc($threads))
				{
					mysqli_query($conn, "DELETE FROM posts_".$board['short']." WHERE resto=".$row['id']);
					unlink("./".$board['short']."/res/".$row['id'].".html");
				}
				mysqli_query($conn, "DELETE FROM posts_".$board['short']." WHERE ip='".$_GET['ip']."'");
				rebuildBoardCache($conn, $row['short']);
				
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
<div class="boxbar"><h2>Recent <?php echo $max; ?> posts</h2></div>
<div class="boxcontent">
			Show recent: <a href="?/recent/posts">50</a> <a href="?/recent/posts&max=100">100</a> <a href="?/recent/posts&max=250">250</a> <a href="?/recent/posts&max=500">500</a>
<table>
			<thead>
			<tr>
			<td>Post</td>
			<td>Name</td>
			<td>Email</td>
			<td>Date</td>
			<td>Comment</td>
			<td>Subject</td>
			<td>File</td>
			<td>Delete</td>
			</tr>
			</thead>
			<tbody>
			<?php
			$boards = mysqli_query($conn, "SELECT * FROM boards ORDER BY short ASC");
			$post_array = array();
			$num = 0;
			
			while ($board = mysqli_fetch_assoc($boards))
			{
				$posts = mysqli_query($conn, "SELECT * FROM posts_".$board['short']." ORDER BY date DESC LIMIT 0, ".$max);
				while ($row = mysqli_fetch_assoc($posts))
				{
					$post_array[$num] = $row;
					$post_array[$num]['board'] = $board['short'];
					$num++;
				}
			}
			$dates = array();
			
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
						$comment = processComment($row['board'], $conn, $row['comment'], 2, 0);
					} else {
						$comment = processComment($row['board'], $conn, $row['comment'], 2);
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
			<div class="boxbar"><h2>Recent <?php echo $max; ?> posts with images</h2></div>
			<div class="boxcontent">
			Show recent: <a href="?/recent/files">50</a> <a href="?/recent/files&max=100">100</a> <a href="?/recent/files&max=250">250</a> <a href="?/recent/files&max=500">500</a> 
			<table>
			<thead>
			<tr>
			<td>Post</td>
			<td>Name</td>
			<td>Email</td>
			<td>Date</td>
			<td>Comment</td>
			<td>Subject</td>
			<td>File</td>
			<td>Delete</td>
			</tr>
			</thead>
			<tbody>
			<?php
			$boards = mysqli_query($conn, "SELECT * FROM boards ORDER BY short ASC");
			$post_array = array();
			$num = 0;
			while ($board = mysqli_fetch_assoc($boards))
			{
				$posts = mysqli_query($conn, "SELECT * FROM posts_".$board['short']." WHERE filename != '' ORDER BY date DESC LIMIT 0, ".$max);
				while ($row = mysqli_fetch_assoc($posts))
				{
					$post_array[$num] = $row;
					$post_array[$num]['board'] = $board['short'];
					$num++;
				}
			}
			$dates = array();
			
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
						$comment = processComment($row['board'], $conn, $row['comment'], 2, 0);
					} else {
						$comment = processComment($row['board'], $conn, $row['comment'], 2);
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
				mysqli_query($conn, "DELETE FROM ban_requests WHERE id=".$_GET['b']);
			}
		}
	?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Ban requests</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>IP</td>
<td>Reason</td>
<td>Staff note</td>
<td>Created</td>
<td>Actions</td>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM ban_requests ORDER BY created DESC LIMIT 0, 15;");
while ($row = mysqli_fetch_assoc($result))
{
echo "<tr>";
echo "<td>".$row['ip']."</td>";
echo "<td>".$row['reason']."</td>";
echo "<td>".$row['note']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['created'])."</td>";

$post_r = mysqli_query($conn, "SELECT * FROM posts_".$row['board']." WHERE id=".$row['post']);
if (mysqli_num_rows($post_r) == 1)
{
$post = mysqli_fetch_assoc($post_r);
$resto = $post['resto'];
if ($resto == 0) { $resto = $post['id']; }
echo "<td>[ <a href='?/ban_requests&del=1&b=".$row['id']."'>C</a> / <a href='?/bans/add&r=".$row['id']."'>B</a> / <a href='?/board&b=".$row['board']."&t=".$resto."#p".$row['id']."'>P</a> ]</td>";
} else {
echo "<td>[ <a href='?/ban_requests&del=1&b=".$row['id']."'>C</a> / <a href='?/bans/add&r=".$row['id']."'>B</a> ]</td>";
}

echo "</tr>";
}
?>
</tbody>
</table>
Showing recent 15 ban requests. <a href="?/ban_requests/all">Show all</a>
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
<div class="boxbar"><h2>Ban requests</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>IP</td>
<td>Reason</td>
<td>Staff note</td>
<td>Created</td>
<td>Actions</td>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM ban_requests ORDER BY created DESC");
while ($row = mysqli_fetch_assoc($result))
{
echo "<tr>";
echo "<td>".$row['ip']."</td>";
echo "<td>".$row['reason']."</td>";
echo "<td>".$row['note']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['created'])."</td>";

$post_r = mysqli_query($conn, "SELECT * FROM posts_".$row['board']." WHERE id=".$row['post']);
if (mysqli_num_rows($post_r) == 1)
{
$post = mysqli_fetch_assoc($post_r);
$resto = $post['resto'];
if ($resto == 0) { $resto = $post['id']; }
echo "<td>[ <a href='?/ban_requests&del=1&b=".$row['id']."'>C</a> / <a href='?/bans/add&r=".$row['id']."'>B</a> / <a href='?/board&b=".$row['board']."&t=".$resto."#p".$row['id']."'>P</a> ]</td>";
} else {
echo "<td>[ <a href='?/ban_requests&del=1&b=".$row['id']."'>C</a> / <a href='?/bans/add&r=".$row['id']."'>B</a> ]</td>";
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
	case "/bbcodes":
		reqPermission(2);
		$name = "";
		$code = "";
		if ((!empty($_POST['mode'])) && ($_POST['mode'] == "add"))
		{
			if (empty($_POST['name'])) { echo "<b style='color: red;'>Please fill name field!</b>"; } else { $name = $_POST['name']; }
			if (empty($_POST['code'])) { echo "<b style='color: red;'>Please fill code field!</b>"; } else { $code = $_POST['code']; }
			if (!preg_match("/^[a-zA-Z0-9]*$/", $_POST['name']))
			{ echo "<b style='color: red;'>Name must consist of alphanumeric characters and it may not contain spaces!</b>"; }
			else {
				$name = mysqli_real_escape_string($conn, $_POST['name']);
				$code = mysqli_real_escape_string($conn, $_POST['code']);
				mysqli_query($conn, "INSERT INTO bbcodes (name, code) VALUES ('".$name."', '".$code."');");
				$name = "";
				$code = "";
			}
		} elseif ((!empty($_POST['mode'])) && ($_POST['mode'] == "edit") && (!empty($_POST['name2']))) {
			
			if (empty($_POST['name'])) { echo "<b style='color: red;'>Please fill name field!</b>"; } else { $name = $_POST['name']; }
			if (empty($_POST['code'])) { echo "<b style='color: red;'>Please fill code field!</b>"; } else { $code = $_POST['code']; }
			if (!preg_match("/^[a-zA-Z0-9]*$/", $_POST['name']))
			{ echo "<b style='color: red;'>Name must consist of alphanumeric characters and it may not contain spaces!</b>"; }
			else {
				$name = mysqli_real_escape_string($conn, $_POST['name']);
				$name2 = mysqli_real_escape_string($conn, $_POST['name2']);
				$code = mysqli_real_escape_string($conn, $_POST['code']);
				mysqli_query($conn, "UPDATE bbcodes SET name='".$name."', code='".$code."' WHERE name='".$name2."';");
			}
			$name = "";
			$code = "";
		}

		if ((!empty($_GET['d'])) && ($_GET['d'] == 1) && (!empty($_GET['n'])))
		{
			$n = mysqli_real_escape_string($conn, $_GET['n']);
			mysqli_query($conn, "DELETE FROM bbcodes WHERE name='".$n."'");
		}
		?>
<b>You'll have to <a href="?/rebuild">rebuild board cache</a> after modifying settings here.</b><br />
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>BBCodes</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>BBCode</td>
<td>HTML Code</td>
<td>Actions</td>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM bbcodes ORDER BY name ASC");
while ($row = mysqli_fetch_assoc($result))
{
echo "<tr>";
echo "<td>".$row['name']."</td>";
echo "<td>".htmlspecialchars($row['code'])."</td>";
echo "<td><a href='?/bbcodes&d=1&n=".$row['name']."'>Delete</a> <a href='?/bbcodes/edit&n=".$row['name']."'>Edit</a></td>";
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
<div class="boxbar"><h2>Add BBCode</h2></div>
<div class="boxcontent">
<form action="?/bbcodes" method="POST">
<input type="hidden" name="mode" value="add">
Name: <input type="text" name="name" value="<?php echo $name; ?>"/><br />
HTML Code: <textarea cols=40 rows=9 name="code"><?php echo $code; ?></textarea><br />
<input type="submit" value="Add" />
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
		$result = mysqli_query($conn, "SELECT * FROM bbcodes WHERE name='".mysqli_real_escape_string($conn, $_GET['n'])."'");
		if (mysqli_num_rows($result) == 1)
		{
		$binfo = mysqli_fetch_assoc($result);
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Edit BBCode</h2></div>
<div class="boxcontent">
<form action="?/bbcodes" method="POST">
<input type="hidden" name="mode" value="edit">
<input type="hidden" name="name2" value="<?php echo mysqli_real_escape_string($conn, $_GET['n']); ?>">
Name: <input type="text" name="name" value="<?php echo $binfo['name']; ?>"/><br />
HTML Code:<textarea cols=40 rows=9 name="code"><?php echo $binfo['code']; ?>"</textarea><br />
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
				$name = mysqli_real_escape_string($conn, $_POST['name']);
				$regex = mysqli_real_escape_string($conn, $_POST['regex']);
				$code = mysqli_real_escape_string($conn, $_POST['code']);
				mysqli_query($conn, "INSERT INTO embeds (name, regex, code) VALUES ('".$name."', '".$regex."', '".$code."');");
				$name = "";
				$code = "";
			}
		} elseif ((!empty($_POST['mode'])) && ($_POST['mode'] == "edit") && (!empty($_POST['name2']))) {
			
			if (empty($_POST['name'])) { echo "<b style='color: red;'>Please fill name field!</b>"; } else { $name = $_POST['name']; }
			if (empty($_POST['regex'])) { echo "<b style='color: red;'>Please regex name field!</b>"; } else { $regex = $_POST['name']; }
			if (empty($_POST['code'])) { echo "<b style='color: red;'>Please fill code field!</b>"; } else { $code = $_POST['code']; }
			if (!preg_match("/^[a-zA-Z0-9]*$/", $_POST['name']))
			{ echo "<b style='color: red;'>Name must consist of alphanumeric characters and it may not contain spaces!</b>"; }
			else {
				$name = mysqli_real_escape_string($conn, $_POST['name']);
				$name2 = mysqli_real_escape_string($conn, $_POST['name2']);
				$regex = mysqli_real_escape_string($conn, $_POST['regex']);
				$code = mysqli_real_escape_string($conn, $_POST['code']);
				mysqli_query($conn, "UPDATE embeds SET name='".$name."', code='".$code."', regex='".$regex."' WHERE name='".$name2."';");
			}
			$name = "";
			$code = "";
		}

		if ((!empty($_GET['d'])) && ($_GET['d'] == 1) && (!empty($_GET['n'])))
		{
			$n = mysqli_real_escape_string($conn, $_GET['n']);
			mysqli_query($conn, "DELETE FROM embeds WHERE name='".$n."'");
		}
		?>
<b>You'll have to <a href="?/rebuild">rebuild board cache</a> after modifying settings here.</b><br />
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
$result = mysqli_query($conn, "SELECT * FROM embeds ORDER BY name ASC");
while ($row = mysqli_fetch_assoc($result))
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
Regex: <input type="text" name="code" value="<?php echo $regex; ?>"/><br />
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
		$result = mysqli_query($conn, "SELECT * FROM embeds WHERE name='".mysqli_real_escape_string($conn, $_GET['n'])."'");
		if (mysqli_num_rows($result) == 1)
		{
		$binfo = mysqli_fetch_assoc($result);
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Edit embed</h2></div>
<div class="boxcontent">
<form action="?/embeds" method="POST">
<input type="hidden" name="mode" value="edit">
<input type="hidden" name="name2" value="<?php echo mysqli_real_escape_string($conn, $_GET['n']); ?>">
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
}
if (($path != "/nav") && ($path != "/board") && ($path != "/board/action") && (($path != "/") || ((!isset($_SESSION['logged'])) || ($_SESSION['logged']==0))))
{
?>
</div>
</body>
</html>
<?php
}
mysqli_close($conn);
?>