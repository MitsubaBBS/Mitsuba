<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
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
				$ignoresizelimit = 0
				if ((!empty($_POST['ignoresizelimit'])) && ($_POST['ignoresizelimit']==1) && ($_SESSION['type'] >= 1))
				{
					$ignoresizelimit = 1;
				}
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
						if (($file_size > $bdata['filesize']) || ($ignoresizelimit))
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
				$is = addPost($conn, $_POST['board'], $name, $_POST['email'], $_POST['sub'], $_POST['com'], $password, $filename, $fname, $resto, $md5, $spoiler, $embed, $_SESSION['type'], $capcode, $raw, $sticky, $lock, $nolimit);
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
					$adm_type = $_SESSION['type'];
					if ((isset($_POST['onlyimgdel']) && ($_POST['onlyimgdel'] == "on"))) { $onlyimgdel = 1; }
					foreach ($_POST as $key => $value)
					{
						if ($value == "delete")
						{
							$done = deletePost($conn, $_POST['board'], $key, "", $onlyimgdel, 2);
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
?>