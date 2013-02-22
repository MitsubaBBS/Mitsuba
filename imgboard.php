<?php
if (!file_exists("./config.php"))
{
header("Location: ./install.php");
}

include("config.php");
include("inc/common.php");
include("inc/common.caching.php");
include("inc/common.posting.php");

if (isset($_POST['mode']))
{
$conn = mysqli_connect($db_host, $db_username, $db_password, $db_database);
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
			banMessage($conn, $board);
			?>
<html>
<head>
<title>Updating index</title>
</head>
<body>
<center><h1>Updating Index...</h1></center>
			<?php
			
			$md5 = "";
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
			$bdata = getBoardData($conn, $_POST['board']);

			$name = "Anonymous";
			if ((!empty($_POST['name'])) && ($bdata['noname'] == 0)) { $name = $_POST['name']; }
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
			
			$spoiler = 0;
			if ((!empty($_POST['spoiler'])) && ($_POST['spoiler'] == 1) && ($bdata['spoilers'] == 1))
			{
				$spoiler = 1;
			}
			setcookie("password", $password, time() + 86400*256);
			$fname = $_FILES['upfile']['name'];
			if (empty($_FILES['upfile']['tmp_name']))
			{
				$fname = "";
			}
			$is = addPost($conn, $_POST['board'], $name, $_POST['email'], $_POST['sub'], $_POST['com'], $password, $fname, basename($fname), $resto, $md5, $spoiler);
			if ($is == -16)
			{
						echo "<h1>This board does not exist!</h1></body></html>"; exit;
			}
			break;
		case "usrform":
			if (!empty($_POST['delete']))
			{
				$onlyimgdel = 0;
				$password = "";
				if (empty($_POST['board']))
				{
					echo "<h1>No board selected!</h1></body></html>";
					exit;
				}
				$board = $_POST['board'];
				banMessage($conn, $board);
				if (isset($_COOKIE['password'])) { $password = $_COOKIE['password']; }
				if ((isset($_POST['onlyimgdel']) && ($_POST['onlyimgdel'] == "on"))) { $onlyimgdel = 1; }
				if (!empty($_POST['pwd'])) { $password = $_POST['pwd']; }
				foreach ($_POST as $key => $value)
				{
					if ($value == "delete")
					{
						$done = deletePost($conn, $_POST['board'], $key, $password, $onlyimgdel);
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
			} elseif (!empty($_POST['report'])) {
				if (empty($_POST['board']))
				{
					echo "<h1>No board selected!</h1></body></html>";
					exit;
				}
				$board = $_POST['board'];
				banMessage($conn, $board);
				foreach ($_POST as $key => $value)
				{
					if ($value == "delete")
					{
						$done = reportPost($conn, $_POST['board'], $key, $_POST['reason']);
						if ($done == 1)
						{
							echo "Post ".$key." reported.<br />";
						}
					}
				}
				echo '<meta http-equiv="refresh" content="2;URL='."'./".$_POST['board']."/index.html'".'">';
			}
			break;
		case "usrapp":
			//$_POST['email']; $_POST['msg'];
			if (!empty($_POST['msg']))
			{
				$msg = preprocessComment($conn, $_POST['msg']);
				$email = mysqli_real_escape_string($conn, $_POST['email']);
				$ip = $_SERVER['REMOTE_ADDR'];
				$ban = isBanned($conn, $ip, $_POST['board']);
				$ban_id = $ban['id'];
				$range = 0;
				if (!empty($bandata['start_ip'])) { $range = 1; }
				mysqli_query($conn, "INSERT INTO appeals (created, ban_id, ip, msg, email, rangeban) VALUES (".time().", ".$ban_id.", '".$ip."', '".$msg."', '".$email."', ".$range.")");
				echo "Your appeal has been sent. Keep calm and wait for reply";
			}
			break;
	}
	mysqli_close($conn);
} else {

}
?>
</body>
</html>