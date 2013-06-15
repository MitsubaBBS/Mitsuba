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
$mode = "index";
if (!empty($_GET['mode']))
{
	$mode = $_GET['mode'];
}
switch ($mode)
{
	case "install":
		?>
		<div class="box-outer top-box">
		<div class="box-inner">
		<div class="boxbar"><h2>Mitsuba installer</h2></div>
		<div class="boxcontent">
		<form action="?mode=install2" method="POST">
		Database host: <input type="text" name="db_host" value="localhost" /><br />
		Database username: <input type="text" name="db_username" /><br />
		Database password: <input type="password" name="db_password" /><br />
		Database <b>name</b>: <input type="text" name="db_name" /><br />
		<em>(Will be created if not exists)</em><br />
		<hr />
		Admin username: <input type="text" name="username" value="root" /><br />
		Admin password: <input type="text" name="password" value="" /><br />
		<input type="submit" value="Install!" />
		</form>
		</div>
		</div>
		</div>
		<?php
		break;
	case "install2":
		if ((!empty($_POST['db_host'])) && (!empty($_POST['db_username'])) && (!empty($_POST['db_name'])) && (!empty($_POST['username'])) && (!empty($_POST['password'])))
		{
			$db_host = $_POST['db_host'];
			$db_username = $_POST['db_username'];
			$db_password = $_POST['db_password'];
			$db_name = $_POST['db_name'];
			$username = $_POST['username'];
			$password = $_POST['password'];
			$conn = new mysqli($db_host, $db_username, $db_password);
			if(!$conn->select_db($db_name)) {
				if(!$conn->query("CREATE DATABASE ".$db_name)) {
					$conn->close();
					$msg = "Could not create database!";
				} elseif(!$conn->select_db($db_name)) {
					$conn->close();
				}
			}
			if (!$conn)
			{
			if(!isset($msg)) {
				$msg = "Could not connect to database!";
			}
			?>
			<div class="box-outer top-box">
		<div class="box-inner">
		<div class="boxbar"><h2><?php echo $msg; ?></h2></div>
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
					$result = $conn->multi_query($db);
					while ($conn->more_results())
					{
						$conn->next_result();
						$conn->use_result();
					}
					
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
						$result = $conn->query("INSERT INTO users (username, password, type, boards) VALUES ('".$conn->real_escape_string($username)."', '".hash("sha512", $password)."', 3, '*')");
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
						$file .= 'date_default_timezone_set("UTC")'.";\n";
						$file .= '$db_username = "'.$db_username.'"'.";\n";
						$file .= '$db_password = "'.$db_password.'"'.";\n";
						$file .= '$db_database = "'.$db_name.'"'.";\n";
						$file .= '$db_host = "'.$db_host.'"'.";\n";
						$file .= '?>'."\n";
						fwrite($handle, $file);
						fclose($handle);
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
			
		}
		break;
	default:
		$graphics = "<b style='color: red;'>None</b>";
		if (extension_loaded("imagick"))
		{
			$graphics = "<b style='color: green;'>imagick</b>";
		} elseif (extension_loaded("gd"))
		{
			$graphics = "<b style='color: yellow;'>gd</b>";
		}

		$database = "<b style='color: red;'>None</b>";
		if (extension_loaded("mysqli"))
		{
			$database = "<b style='color: green;'>mysqli</b>";
		}

		$fileinfo = "<b style='color: red;'>None</b>";
		if (extension_loaded("fileinfo"))
		{
			$fileinfo = "<b style='color: green;'>fileinfo extension</b>";
		} elseif (function_exists("mime_content_type")) {
			$fileinfo = "<b style='color: yellow;'>mime_content_type</b>";
		}
		?>
		<div class="box-outer top-box">
		<div class="box-inner">
		<div class="boxbar"><h2>Mitsuba installer</h2></div>
		<div class="boxcontent">
		Image library: <?php echo $graphics; ?><br />
		Database: <?php echo $database; ?><br />
		Fileinfo: <?php echo $fileinfo; ?><br />
		<?php
			if (($fileinfo != "<b style='color: red;'>None</b>") && ($database != "<b style='color: red;'>None</b>") && ($graphics != "<b style='color: red;'>None</b>"))
			{
				echo '[ <a href="?mode=install">Install</a> ] [ <a href="?mode=convert">Convert</a> ]';
			} else {
				echo '<b>Installation can not continue, because of missing dependencies</b>';
			}
		?>
		</div>
		</div>
		</div>
		<?php
		break;
}

?>
</div>
</body>
</html>