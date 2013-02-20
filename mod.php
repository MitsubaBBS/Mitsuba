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
			$new .= "<p>".$line."</p>";
		}
	}
	return $new;
}
?>

<?php
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
<?php
if ($_SESSION['type'] >= 1)
{
?>
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
			if (addBoard($conn, $_POST['short'], $_POST['name'], $_POST['des'], $_POST['msg']) > 0)
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
Board message (optional): <br /><textarea cols=70 rows=7 name="message"></textarea><br />
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
<td>Message</td>
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
if (!empty($row['message']))
{
echo "<td>Yes</td>";
} else {
echo "<td>No</td>";
}
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
				if (updateBoard($conn, $_GET['board'], $_POST['name'], $_POST['des'], $_POST['msg']))
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
Board message (optional): <br /><textarea cols=70 rows=7 name="message"><?php echo $data['message']; ?></textarea><br />
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
echo "<td><a href='?/bans/delete&b=".$row['id']."'>Delete</a></td>";
} else {
echo "<td></td>";
}
echo "</tr>";
}
?>
</tbody>
</table>
Showing recent 15 bans. <a href="?/bans/all">Show all</a>
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
	echo "<td><a href='?/bans/delete&b=".$row['id']."'>Delete</a></td>";
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
	case "/bans/add":
	reqPermission(1);
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
<div class="boxbar"><h2>All bans</h2></div>
<div class="boxcontent">
<form action="?/bans/add" method="POST">
IP: <input type="text" name="ip" value="<?php echo $ip; ?>"/><br />
Reason: <input type="text" name="reason" /><br />
Staff note: <input type="text" name="note" /><br />
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
Append text to post: <input type="text" name="append_text" value='<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>' style="width: 400px;"/><input type="checkbox" name="append" value="1" checked=1/>Yes<br/>
<?php
}
?>
<br />
<input type="submit" value="Ban" />
</form>
</div>
</div>
</div>
		<?php
		} else {
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
		addBan($conn, $_POST['ip'], $_POST['reason'], $_POST['note'], $_POST['expires'], $boards);
		if ((!empty($_GET['d'])) && ($_GET['d']==1))
		{
			deletePostMod($conn, $board, $post);
		} else {
			if ((!empty($post)) && (!empty($_POST['append'])) && ($_POST['append'] == 1))
			{
				appendToPost($conn, $board, $post, $_POST['append_text']);
			}
		}
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>User banned!</h2></div>
<div class="boxcontent"><a href="?/bans">[ BACK ]</a></div>
</div>
</div>
				<?php
		}
		break;
	case "/bans/delete":
		reqPermission(1);
		if ((!empty($_GET['b'])) && (is_numeric($_GET['b'])))
		{
			mysqli_query($conn, "DELETE FROM bans WHERE id=".$_GET['b']);
		}
		?>
		<meta http-equiv="refresh" content="0;URL='?/bans'" />
		<?php
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
		if ((!empty($_POST['username'])) && (!empty($_POST['password'])) && (!empty($_POST['type'])) && (is_numeric($_POST['type'])))
		{
			$type = $_POST['type'];
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
<div class="boxbar"><h2>Fill all field!</h2></div>
<div class="boxcontent"><a href="?/users">[ BACK ]</a></div>
</div>
</div>
				<?php
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
echo '<h3><span class="newssub">'.date("d/m/Y @ H:i", $row['created']).'</span><a href="?/notes/delete&id='.$row['id'].'">Delete</a></span></h3>';
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
<div class="boxbar"><h2>Fill all fields!</h2></div>
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
		reqPermission(1);
		if ((!empty($_GET['b'])) && (isBoard($conn, $_GET['b'])))
		{
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
		reqPermission(1);
		$mode = $_POST['mode'];
		switch($mode)
		{
			case "regist":
				$filename = null;
				if (empty($_POST['board']))
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
				?>
	<html>
	<head>
	<title>Updating index</title>
	</head>
	<body>
	<center><h1>Updating Index...</h1></center>
				<?php
				if (!empty($_FILES['upfile']['name']))
				{
					$target_path = "./".$board."/src/";
					$fileid = time() . rand(10000000, 999999999);
					$ext = pathinfo($_FILES['upfile']['name'], PATHINFO_EXTENSION);
					$filename = $fileid . "." . $ext; 
					$target_path .= $filename;
					if (!isImage($_FILES['upfile']['tmp_name']))
					{
						echo "<h1>File is not an image!</h1></body></html>";
						exit;
					}
					$file_size = $_FILES['upfile']['size'];
					if ($file_size > 2097152)
					{
						echo "<h1>File size too big!</h1></body></html>";
						exit;
					}

					if(move_uploaded_file($_FILES['upfile']['tmp_name'], $target_path)) {
						echo "The file ".basename( $_FILES['upfile']['name'])." has been uploaded";
					} else {
						echo "There was an error uploading the file, please try again!";
						$filename = "";
					}
				}

				$name = "Anonymous";
				if ($_POST['name'] != "") { $name = $_POST['name']; }
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
				if (!empty($_FILES['upfile']['name']))
				{
					if ($resto != 0)
					{
						if (thumb($board, $fileid, ".".$ext, 125) < 0)
						{
							echo "<h1>Could not create thumbnail!</h1></body></html>"; exit;
						}
					} else {
						if (thumb($board, $fileid, ".".$ext) < 0)
						{
							echo "<h1>Could not create thumbnail!</h1></body></html>"; exit;
						}
					}
				}
				
				setcookie("password", $password, time() + 86400*256);
				$capcode = 0;
				$raw = 0;
				$sticky = 0;
				$lock = 0;
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
				$is = addPostMod($conn, $_POST['board'], $name, $_POST['email'], $_POST['sub'], $_POST['com'], $password, $filename, basename($_FILES['upfile']['name']), $resto, $capcode, $raw, $sticky, $lock);
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
					echo '<meta http-equiv="refresh" content="2;URL='."'./".$_POST['board']."/index.html'".'">';
				}
				break;
		}
	?>
	</body>
	</html>
	<?php
		break;
	case "/reports":
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
				} else {
					$fileparts = explode('.',$pdata['filename']);
					echo "<td><img src='./".$row['board']."/src/thumb/".$fileparts[0].".jpg' /></td>";
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
			echo "<td>[ <a href='?/reports/clear&id=".$row['id']."'>C</a> ] [ <a href='?/bans/add&b=".$row['board']."&p=".$row['reported_post']."'>B</a> / <a href='?/bans/add&b=".$row['board']."&p=".$row['reported_post']."&d=1'>&</a> / <a href='?/delete_post&b=".$row['board']."&p=".$row['reported_post']."'>D</a> / <a href='?/delete_post&b=".$row['board']."&p=".$row['reported_post']."&f=1'>F</a> ] [ <a href='?/info&ip=".$pdata['ip']."'>N</a> ]</td>";
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
	case "/reports/clear":
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			mysqli_query($conn, "DELETE FROM reports WHERE id=".$_GET['id']);
			?>
			<meta http-equiv="refresh" content="0;URL='?/reports'" />
			<?php
		}
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
<div class="boxbar"><h2>Do you clear all reports?</h2></div>
<div class="boxcontent"><a href="?/reports">[ NO ]</a> <a href="?/reports/clear_all_yes">[ YES ]</a></div>
</div>
</div>
		<?php
		break;
	case "/delete_post":
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$imageonly = 0;
			if ((!empty($_GET['f'])) && ($_GET['f'] == 1))
			{
				$imageonly = 1;
			}
			deletePostMod($conn, $_GET['b'], $_GET['p'], $imageonly);
			?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Post deleted</h2></div>
</div>
</div>
		<?php
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
echo "<td><a href='?/bans/delete&b=".$row['id']."'>Delete</a></td>";
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
<div class="boxbar"><h2>Thread unlocked</h2></div>
</div>
</div>
		<?php
				} else {
					mysqli_query($conn, "UPDATE posts_".$_GET['b']." SET sage=1 WHERE id=".$_GET['t']);
					generatePost($conn, $_GET['b'], $_GET['t']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Thread locked</h2></div>
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
		echo "<td> [ <a href='?/appeals/clear&id=".$row['id']."'>C</a> / <a href='?/bans/delete&b=".$ban['id']."'>U</a> ]</td>";
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
<div class="boxbar"><h2>Do you clear all appeals?</h2></div>
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
To: <b><?php echo $row['username']; ?></b><br />
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
			<table>
			<thead>
			<tr>
			<td>Name</td>
			<td>Email</td>
			<td>Date</td>
			<td>Comment</td>
			<td>Subject</td>
			<td>File</td>
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
						} else {
							$fileparts = explode('.',$row['filename']);
							echo "<td><img src='./".$board['short']."/src/thumb/".$fileparts[0].".jpg' /></td>";
						}
					} else {
						echo "<td></td>";
					}
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