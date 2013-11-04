<?php
if (file_exists("./config.php"))
{
die("Y U R TRYIN TO HACK THIS WONDERFUL SCRIPT?");
}
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Mitsuba</title>
<link rel="stylesheet" href="./styles/mitsuba.css" />
<style type="text/css">
tbody td {
	color: #000000;
}
.tfailed {
	background-color: #FF4D69 !important;
}
.tpassed {
	background-color: #7DFF8C !important;
}
.twarning {
	background-color: #FBFF7D !important;
}
</style>
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
function randomSalt($length) {
	$alphabet = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789+_-)(*&^%$#@!~|';
	$pass = array();
	$alphaLength = strlen($alphabet) - 1;
	for ($i = 0; $i < $length; $i++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass);
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
		<hr />
		Secure tripcode salt: <input type="text" name="secure_salt" value="<?php echo randomSalt(24); ?>" /><br />
		ID salt: <input type="text" name="id_salt" value="<?php echo randomSalt(24); ?>" /><br />
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
			$idsalt = addslashes($_POST['id_salt']);
			$stsalt = addslashes($_POST['secure_salt']);
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
						$salt = $conn->real_escape_string(randomSalt(15));
						$result = $conn->query("INSERT INTO users (`username`, `password`, `salt`, `group`, `boards`) VALUES ('".$conn->real_escape_string($username)."', '".hash("sha512", $password.$salt)."', '".$salt."', 3, '%')");
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
						$file .= '$db_type = "mysqli";'."\n";
						$file .= '$db_username = "'.$db_username.'"'.";\n";
						$file .= '$db_password = "'.$db_password.'"'.";\n";
						$file .= '$db_database = "'.$db_name.'"'.";\n";
						$file .= '$db_host = "'.$db_host.'"'.";\n";
						$file .= '$securetrip_salt = \''.$stsalt.'\''.";\n";
						$file .= '$id_salt = \''.$idsalt.'\''.";\n";
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
	case "chmod":
		//TODO: chmod fixer
		break;
	default:
		$tests = array();
		$tests[] = array(
			'category' => 'Environment',
			'name' => 'Is PHP version >= 5.3?',
			'test' => PHP_VERSION_ID >= 50300,
			'on_fail' => 'fatal_error',
			'fail_message' => 'Mitsuba requires at least PHP version 5.3 to run.'
		);
		$tests[] = array(
			'category' => 'Environment',
			'name' => 'Is PHP version >= 5.4?',
			'test' => PHP_VERSION_ID >= 50400,
			'on_fail' => 'warning',
			'fail_message' => 'In future Mitsuba will require PHP version 5.4, so you should get ready for it.'
		);
		$tests[] = array(
			'category' => 'Environment',
			'name' => 'Is MySQLi extension installed?',
			'test' => extension_loaded("mysqli"),
			'on_fail' => 'fatal_error',
			'fail_message' => 'Mitsuba requires MySQLi to store boards, posts and stuff.'
		);
		$tests[] = array(
			'category' => 'Environment',
			'name' => 'Is safe mode disabled?',
			'test' => !ini_get('safe_mode'),
			'on_fail' => 'warning',
			'fail_message' => 'PHP safe mode may cause problems in future.'
		);
		$tests[] = array(
			'category' => 'Features',
			'name' => 'Is mime_content_type supported?',
			'test' => function_exists("mime_content_type"),
			'on_fail' => 'fatal_error',
			'fail_message' => 'Mitsuba needs mime_content_type to detect filetypes of uploaded files'
		);
		$tests[] = array(
			'category' => 'Features',
			'name' => 'Is fileinfo installed?',
			'test' => extension_loaded("fileinfo"),
			'on_fail' => 'warning',
			'fail_message' => 'Fileinfo is a better way to detect mimetypes than mime_content_type'
		);
		$tests[] = array(
			'category' => 'Features',
			'name' => 'Is JSON supported?',
			'test' => function_exists("json_encode"),
			'on_fail' => 'fatal_error',
			'fail_message' => 'JSON is required to view posters\' IPs and edit posts'
		);
		$tests[] = array(
			'category' => 'Features',
			'name' => 'Is ZipArchive installed?',
			'test' => extension_loaded("zip"),
			'on_fail' => 'warning',
			'fail_message' => 'You won\'t be able to upload modules via mod panel because of no ZipArchive extension'
		);
		$tests[] = array(
			'category' => 'File system',
			'name' => 'Is '.getcwd().'/ writable?',
			'test' => is_writable("./"),
			'on_fail' => 'fatal_error',
			'fail_message' => 'You have to set up 755 permissions for '.getcwd().'/ or you won\'t be able to create new boards'
		);
		$tests[] = array(
			'category' => 'File system',
			'name' => 'Is '.getcwd().'/styles/ writable?',
			'test' => is_writable("./styles/"),
			'on_fail' => 'fatal_error',
			'fail_message' => 'You have to set up 755 permissions for '.getcwd().'/styles/ or you won\'t be able to upload new stylesheets'
		);
		$tests[] = array(
			'category' => 'File system',
			'name' => 'Is '.getcwd().'/modules/ writable?',
			'test' => is_writable("./modules/"),
			'on_fail' => 'fatal_error',
			'fail_message' => 'You have to set up 755 permissions for '.getcwd().'/modules/ or you won\'t be able to upload new modules'
		);
		$tests[] = array(
			'category' => 'Imaging',
			'name' => 'Is GD extension available and JPG, GIF and PNG supported?',
			'test' => (extension_loaded("gd")) && (function_exists('imagecreatefromjpeg')) && (function_exists('imagecreatefromgif')) && (function_exists('imagecreatefrompng')),
			'on_fail' => 'fatal_error',
			'fail_message' => 'Mitsuba requires GD to thumbnail images.'
		);
		$tests[] = array(
			'category' => 'Imaging',
			'name' => 'Is imagick PHP extension available?',
			'test' => extension_loaded("imagick"),
			'on_fail' => 'warning',
			'fail_message' => 'Mitsuba uses imagick to make animated thumbnails from GIFs.'
		);
		?>
		<div class="box-outer top-box">
		<div class="box-inner">
		<div class="boxbar"><h2>Mitsuba installer</h2></div>
		<div class="boxcontent">
		<table>
		<thead>
		<tr>
		<td>Category</td>
		<td>Name</td>
		</tr>
		</thead>
		<tbody>
		<?php
		$fatals = array();
		$warnings = array();
		foreach ($tests as $test) {
			if ($test['test'])
			{
				echo '<tr class="tpassed">';
				echo '<td>'.$test['category'].'</td>';
				echo '<td>'.$test['name'].'</td>';
				echo '</tr>';
			} else {
				switch ($test['on_fail'])
				{
					case "fatal_error":
						echo '<tr class="tfailed">';
						echo '<td>'.$test['category'].'</td>';
						echo '<td>'.$test['name'].'</td>';
						echo '</tr>';
						$fatals[] = $test['fail_message'];
						break;
					case "warning":
						echo '<tr class="twarning">';
						echo '<td>'.$test['category'].'</td>';
						echo '<td>'.$test['name'].'</td>';
						echo '</tr>';
						$warnings[] = $test['fail_message'];
						break;
				}
			}
		}
		?>
		</tbody>
		</table>
		<?php
		foreach ($fatals as $msg) {
			echo "<p><b>Fatal: </b>".$msg."</p>";
		}
		foreach ($warnings as $msg) {
			echo "<p><b>Warning: </b>".$msg."</p>";
		}
		if (count($fatals) >= 1)
		{
			echo "<b>Mitsuba installation can not continue because of unsolved errors</b>";
		} else {
			echo '[ <a href="?mode=install">Install</a> ] [ <a href="?mode=convert">Convert</a> ]';
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
