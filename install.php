<?php
if (file_exists("./config.php"))
{
die("Y U R TRYIN TO HACK THIS WONDERFUL SCRIPT?");
}
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
if ((empty($_POST['db_host'])) || (empty($_POST['db_username'])) || (empty($_POST['db_name'])) || (empty($_POST['username'])) || (empty($_POST['password'])))
{
?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Mitsuba installer</h2></div>
<div class="boxcontent">
<form action="?mode=install2" method="POST">
Database host: <input type="text" name="db_host" value="localhost" /><br />
Database username: <input type="text" name="db_username" /><br />
Database password: <input type="password" name="db_password" /><br />
Database <b>name</b>: <input type="text" name="db_name" /><br /><br />
<hr />
Admin username: <input type="text" name="username" value="root" /><br />
Admin password: <input type="text" name="password" value="" /><br />
<input type="submit" value="Install!" />
</form>
</div>
</div>
</div>
<?php
} else {
	$db_host = $_POST['db_host'];
	$db_username = $_POST['db_username'];
	$db_password = $_POST['db_password'];
	$db_name = $_POST['db_name'];
	$username = $_POST['username'];
	$password = $_POST['password'];
	$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
	if (!$conn)
	{
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Could not connect to database!</h2></div>
<div class="boxcontent">
<a href="./install.php">[ BACK ]</a>
</div>
</div>
</div>
	<?php
	} else {
		if (file_exists("./database.sql"))
		{
			$db = file_get_contents("./database.sql");
			$result = mysqli_query($conn, $db);
			if (!$result)
			{
			?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>There was an error when importing database!</h2></div>
<div class="boxcontent">
<a href="./install.php">[ BACK ]</a>
</div>
</div>
</div>
	<?php
			} else {
				$result = mysqli_query($conn, "INSERT INTO users (username, password, type, board) VALUES ('".mysqli_real_escape_string($conn, $username)."', '".hash("sha512", $password)."', 2, '*')");
				if (!$result)
				{
				?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>There was an error when creating your account!</h2></div>
<div class="boxcontent">
<a href="./install.php">[ BACK ]</a>
</div>
</div>
</div>
	<?php
				} else {
				$handle = fopen("./config.php", "w");
				$file = '<?php'."\n";
				$file .= 'date_default_timezone_set("UTC")'."\n";
				$file .= '$db_username = "'.$db_username.'"'."\n";
				$file .= '$db_password = "'.$db_password.'"'."\n";
				$file .= '$db_database = "'.$db_database.'"'."\n";
				$file .= '$db_host = "'.$db_host.'"'."\n";
				$file .= '?>'."\n";
				?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Mitsuba installed succesfully!</h2></div>
<div class="boxcontent">
<a href="./mod.php">[ GO TO ADMIN PANEL ]</a>
</div>
</div>
</div>
	<?php
				}
			}
		} else {
		?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>database.sql not found!</h2></div>
<div class="boxcontent">
<a href="./install.php">[ BACK ]</a>
</div>
</div>
</div>
	<?php
		}
		
		
	}
	//INSERT INTO `users` (`id`, `username`, `password`, `type`, `boards`) VALUES
	//(1, 'root', '99adc231b045331e514a516b4b7680f588e3823213abe901738bc3ad67b2f6fcb3c64efb93d18002588d3ccc1a49efbae1ce20cb43df36b38651f11fa75678e8', 2, '*');

}
?>
</div>
</body>
</html>