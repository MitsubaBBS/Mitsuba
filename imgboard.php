<?php
session_start();
if (empty($_POST['mode']))
{
	die("Sorry.");
}

if ((empty($_POST['board'])) && (empty($_POST['delete'])) && (empty($_POST['report'])) && (empty($_POST['msg'])))
{
	die("Sorry.");
}
if (!file_exists("./config.php"))
{
header("Location: ./install.php");
die();
}

include("config.php");
include("inc/mitsuba.php");
include("inc/strings/imgboard.strings.php");

if (!empty($_POST['mode']))
{
	$return_url = "./";
	if (!empty($_POST['board']))
	{
		$return_url = "./".$_POST['board']."/";
	}
	$conn = new mysqli($db_host, $db_username, $db_password, $db_database);
	$mitsuba = new Mitsuba($conn);
	$e = array("requestdata" => &$_POST);
	$mitsuba->emitEvent("imgboard.begin", $e);
	$mod = 0;
	if ((!empty($_GET['mod'])) && ($_GET['mod']>=1))
	{
		if ((!empty($_POST['board'])) && ($mitsuba->common->isBoard($_POST['board'])))
		{
			$mitsuba->admin->canBoard($_POST['board']);
			$mod = 1;
			if ($_GET['mod']==1)
			{
				$return_url = "mod.php?/board&b=".$_POST['board'];
			} else {
				$mod = 2;
			}
		} else {
			$mod = 1;
			if ($_GET['mod']==1)
			{
				$return_url = "mod.php";
			} else {
				$mod = 2;
			}
		}
	}
	$mode = $_POST['mode'];
	switch($mode)
	{
		case "regist":
			$filename = null;
			if (empty($_POST['board']))
			{
				$mitsuba->common->showMsg($lang['img/error'], $lang['img/no_board']);
				exit;
			}
			$board = $_POST['board'];
			if (($mod == 0) && ($mitsuba->common->isWhitelisted($_SERVER['REMOTE_ADDR']) < 1))
			{
				$mitsuba->common->banMessage($board);
				$mitsuba->common->warningMessage();
			}
			$ignoresizelimit = 0;
			if ($mod >= 1)
			{
				if ((!empty($_POST['ignoresizelimit'])) && ($_POST['ignoresizelimit']==1) && ($mitsuba->admin->checkPermission("post.ignoresizelimit")))
				{
					$ignoresizelimit = 1;
				}
			}
			if (!$mitsuba->common->isBoard($_POST['board']))
			{
				$mitsuba->common->showMsg($lang['img/error'], $lang['img/board_no_exists']);
				exit;
			}
			
			
			$md5 = "";
			$bdata = $mitsuba->common->getBoardData($_POST['board']);
			if ($bdata['type']=="overboard")
			{
				$mitsuba->common->showMsg($lang['img/error'], $lang['img/board_no_exists']);
				exit;
			}
			if (($bdata['allow_replies']==0) && ($_POST['resto']!=0))
			{
				$mitsuba->common->showMsg($lang['img/error'], $lang['img/replies_not_allowed']);
				exit;
			}
			if ($bdata['hidden'] == 1)
			{
				if ($mod >= 1)
				{
					$mitsuba->admin->canBoard($bdata['short']);
				} else {
					$mitsuba->common->showMsg($lang['img/error'], $lang['img/board_no_exists']);
					exit;
				}
			}
			
			if ((!$mitsuba->admin->checkPermission("post.ignorecaptcha")) && ($bdata['captcha'] == 1) && (empty($_SESSION['captcha']) || empty($_POST['captcha']) || strtolower(trim($_POST['captcha'])) != $_SESSION['captcha']))
			{
				$_SESSION['captcha'] = "";
				$mitsuba->common->showMsg($lang['img/error'], $lang['img/wrong_captcha']);
				exit;
			}
			$_SESSION['captcha'] = "";

			$wfresult = $conn->query("SELECT * FROM wordfilter WHERE active=1");
			$replace_array = array();
			while ($row = $wfresult->fetch_assoc())
			{
				if ($row['boards'] != "%")
				{
					$boards = explode(",", $row['boards']);
					if (in_array($bdata['short'], $boards))
					{
						$replace_array[$row['search']] = $row['replace'];
					}
				} else {
					$replace_array[$row['search']] = $row['replace'];
				}
			}
			if (strlen(strtr($_POST['com'], $replace_array)) > $bdata['maxchars'])
			{
				$mitsuba->common->showMsg($lang['img/error'], sprintf($lang['img/comment_too_long'],strlen($_POST['com']),$bdata['maxchars']));
				exit;
			}
			if (!$mitsuba->admin->checkPermission("post.ignorespamfilter"))
			{
				$mitsuba->board->checkSpam($_POST['com'], $_POST['board']);
			}
			if ((!empty($_POST['embed'])) && (!empty($_FILES['upfile']['tmp_name'])))
			{
				$mitsuba->common->showMsg($lang['img/error'], $lang['img/choose_one']);
				exit;
			}
			$raw = 0;
			$sticky = 0;
			$lock = 0;
			$nolimit = 0;
			$nofile = 0;
			$fake_id = "";
			$cc_text = "";
			$cc_style = "";
			$cc_icon = "";
			if ((!empty($_POST['nofile'])) && ($_POST['nofile']==1) && ($bdata['nofile']==1))
			{
				$nofile = 1;
			}
			if ($mod >= 1)
			{
				if ((!empty($_POST['nolimit'])) && ($_POST['nolimit']==1) && ($mitsuba->admin->checkPermission("post.ignorebumplimit")))
				{
					$nolimit = 1;
				}
				if ((!empty($_POST['capcode'])) && ($_POST['capcode']==1) && ($mitsuba->admin->checkPermission("post.capcode")))
				{
					$cc_text = $_SESSION['capcode_text'];
					$cc_style = $_SESSION['capcode_style'];
					$cc_icon = $_SESSION['capcode_icon'];
				} elseif ((!empty($_POST['capcode'])) && ($_POST['capcode']==2) && (!empty($_POST['cc_text'])) && (!empty($_POST['cc_color'])) && ($mitsuba->admin->checkPermission("post.customcapcode")))
				{
					$cc_text = $_POST['cc_text'];
					$cc_style = $_POST['cc_style'];
				}
				if ((!empty($_POST['raw'])) && ($_POST['raw']==1) && ($mitsuba->admin->checkPermission("post.raw")))
				{
					$raw = 1;
				}
				if ((!empty($_POST['nofile'])) && ($_POST['nofile']==1) && ($mitsuba->admin->checkPermission("post.nofile")))
				{
					$nofile = 1;
				}
				if ((!empty($_POST['sticky'])) && ($_POST['sticky']==1) && ($mitsuba->admin->checkPermission("post.sticky")))
				{
					$sticky = 1;
				}
				if ((!empty($_POST['lock'])) && ($_POST['lock']==1) && ($mitsuba->admin->checkPermission("post.closed")))
				{
					$lock = 1;
				}
				if (!empty($_POST['fake_id']) && ($mitsuba->admin->checkPermission("post.fakeid")))
				{
					$fake_id = $_POST['fake_id'];
				}
			}
			if (($mitsuba->common->isWhitelisted($_SERVER['REMOTE_ADDR']) != 2) && (($mod == 0) || (!$mitsuba->admin->checkPermission("post.ignorespamlimits"))))
			{
				if ((empty($_POST['resto'])) || ($_POST['resto']==0))
				{
					$mitsuba->board->checkThreadDate($bdata, $return_url);
				}
				$mitsuba->board->checkPostDate($bdata, $return_url);
			}
			$mime = "";
			$url = "";
			$url_title = "";
			$gen_thumb = 0;
			if (($bdata['type']=="linkboard"))
			{
				if (!empty($_POST['url']))
				{
					//TODO: Links
				} else {
					$mitsuba->common->showMsg($lang['img/error'], $lang['img/no_link']);
					exit;
				}
			} elseif ((!empty($_POST['embed'])) && ($nofile == 0) && ($bdata['embeds']==1))
			{
				if (($bdata['file_replies']==0) && ($_POST['resto']!=0))
				{
					$mitsuba->common->showMsg($lang['img/error'], $lang['img/file_replies_not_allowed']);
					exit;
				}
				$filename = $mitsuba->board->checkEmbed($bdata, $_POST['embed'], $return_url);
			} elseif (($nofile == 0) && ($bdata['type']!="textboard")) {
				if (($bdata['file_replies']==0) && ($_POST['resto']!=0))
				{
					$mitsuba->common->showMsg($lang['img/error'], $lang['img/file_replies_not_allowed']);
					exit;
				}
				if ((empty($_FILES['upfile']['tmp_name'])) && (!empty($_FILES['upfile']['name'])))
				{
					$mitsuba->common->showMsg($lang['img/error'], $lang['img/file_too_big']);
					exit;
				}
				if (!empty($_FILES['upfile']['tmp_name']))
				{
					$target_path = "./".$board."/src/";
					$file_size = $_FILES['upfile']['size'];
					if (($file_size > $bdata['filesize']) && ($ignoresizelimit != 1))
					{
						$mitsuba->common->showMsg($lang['img/error'], $lang['img/file_too_big']);
						exit;
					}
					if (!($nfo = $mitsuba->common->isFile($_FILES['upfile']['tmp_name'], $bdata['extensions'])))
					{
						$mitsuba->common->showMsg($lang['img/error'], $lang['img/file_too_big']);
						exit;
					}
					$mime = $nfo['mimetype'];
					$ext = ".".$nfo['extension'];
					$fileid = time() . rand(10000000, 999999999);
					$filename = $fileid . $ext; 
					$target_path .= $filename;
					$md5 = md5_file($_FILES['upfile']['tmp_name']);
					if (($bdata['nodup'] == 1) && (($mod == 0) || (!$mitsuba->admin->checkPermission("post.ignorenodup"))))
					{
						$isit = $conn->query("SELECT * FROM posts WHERE filehash='".$md5."' AND board='".$_POST['board']."'");
						if ($isit->num_rows >= 1)
						{		
							$row6 = $isit->fetch_assoc();	
							$postlink = "";
							if ($row6['resto']==0)
							{
								$postlink = "./".$_POST['board']."/res/".$row6['id'].".html#p".$row6['id'];
							} else {
								$postlink = "./".$_POST['board']."/res/".$row6['resto'].".html#p".$row6['id'];
							}
							$mitsuba->common->showMsg($lang['img/error'], sprintf($lang['img/file_duplicate'], $postlink));
							exit;
						}
					}
					if(move_uploaded_file($_FILES['upfile']['tmp_name'], $target_path)) {
						if ($nfo['image']==1) { $gen_thumb = 1; }
						printf($lang['img/file_uploaded'], basename( $_FILES['upfile']['name']));
					} else {
						echo $lang['img/upload_error'];
						$filename = "";
					}
				}
			}
			$name = $lang['img/anonymous'];
			if (!empty($bdata['anonymous']))
			{
				$name = $bdata['anonymous'];
			}
			if ((!empty($_POST['name'])) && (($bdata['noname'] == 0) || (($mod >= 1) && ($mitsuba->admin->checkPermission("post.ignorenoname"))))) { $name = $_POST['name']; }
			$resto = 0;
			if (isset($_POST['resto'])) { $resto = $_POST['resto']; }
			$password = "";
			if (empty($_POST['pwd']))
			{
				if (isset($_COOKIE['password']))
				{
					$password = $_COOKIE['password'];
				} else {
					$password = $mitsuba->common->randomPassword();
				}
			} else {
				$password = $_POST['pwd'];
			}
			$thumb_w = 0;
			$thumb_h = 0;
			if ((substr($filename, 0, 6) != "embed:") && ($gen_thumb == 1))
			{
				if (!empty($_FILES['upfile']['tmp_name']))
				{
					if ($resto != 0)
					{
						$returned = $mitsuba->common->thumb($board, $fileid.$ext, 125);
						if ((empty($returned['width'])) || (empty($returned['height'])))
						{
							unlink($target_path);
							$mitsuba->common->showMsg($lang['img/error'], $lang['img/no_thumb']);
							exit;
						}
						$thumb_w = $returned['width'];
						$thumb_h = $returned['height'];
					} else {
						$returned = $mitsuba->common->thumb($board, $fileid.$ext);
						if ((empty($returned['width'])) || (empty($returned['height'])))
						{
							unlink($target_path);
							$mitsuba->common->showMsg($lang['img/error'], $lang['img/no_thumb']);
							exit;
						}
						$thumb_w = $returned['width'];
						$thumb_h = $returned['height'];
					}
				}
			}
			if (!empty($_POST['name'])) { setcookie("mitsuba_name", $_POST['name'], time() + 86400*256); } else { setcookie("mitsuba_name","", time() + 86400*256); }
			if ((!empty($_POST['email'])) && (strtolower($_POST['email']) != "sage")) { setcookie("mitsuba_email", $_POST['email'], time() + 86400*256); } else { setcookie("mitsuba_email","", time() + 86400*256); }
			if (!empty($_POST['fake_id'])) { setcookie("mitsuba_fakeid", $_POST['fake_id'], time() + 86400*256); } else { setcookie("mitsuba_fakeid","", time() + 86400*256); }
			
			$spoiler = 0;
			if ((!empty($_POST['spoiler'])) && ($_POST['spoiler'] == 1) && ($bdata['spoilers'] == 1) && (substr($filename, 0, 6) != "embed:"))
			{
				$spoiler = 1;
			}
			setcookie("password", $password, time() + 86400*256);
			$embed = 0;
			$fname = "";
			if (!empty($filename))
			{
				if (substr($filename, 0, 6) != "embed:")
				{
					$fname = $_FILES['upfile']['name'];
					$filename = "";
					if (empty($_FILES['upfile']['tmp_name']))
					{
						$fname = "";
					} else {
						$filename = $fileid.$ext;
					}
				} else {
					$embed = 1;
					$fname = "embed";
				}
			}
			$redirect = 0;
			if ($mod == 1)
			{
				$redirect = 1;
			}
			if (!empty($url))
			{
				$filename = "url:".$conn->real_escape_string($url);
				$fname = $conn->real_escape_string($url_title);
			}
			$mitsuba->common->showMsg($lang['img/updating_index'], $lang['img/updating_index']);
			//We'll remove here all "non-printable" characters
			$com = $_POST['com'];
			$is = $mitsuba->posting->addPost($_POST['board'], $name, $_POST['email'], $_POST['sub'], $com, $password, $filename, $fname, $mime, $resto, $md5, $thumb_w, $thumb_h, $spoiler, $embed, $raw, $sticky, $lock, $nolimit, $nofile, $fake_id, $cc_text, $cc_style, $cc_icon, $redirect, $_POST);
			if ($is == -16)
			{
				$mitsuba->common->showMsg($lang['img/error'], $lang['img/board_no_exists']);
				exit;
			}
			break;
		case "usrform":
			if (!empty($_POST['delete']))
			{
				$onlyimgdel = 0;
				$password = "";
				if ($mod == 0)
				{
					if (isset($_COOKIE['password'])) { $password = $_COOKIE['password']; }
					if (!empty($_POST['pwd'])) { $password = $_POST['pwd']; }
				}
				$canDelete = false;
				if ($mod >= 1)
				{
					$canDelete = $mitsuba->admin->checkPermission("post.delete.single");
				}
				if ((isset($_POST['onlyimgdel']) && ($_POST['onlyimgdel'] == "on"))) { $onlyimgdel = 1; }
				foreach ($_POST as $key => $value)
				{
					if ($value == "delete")
					{
						$keys = explode("%", $key);
						$done = $mitsuba->posting->deletePost($keys[1], $keys[2], $password, $onlyimgdel, $canDelete);
						if ($done == -1) {
							echo sprintf($lang["img/post_bad_password"],$keys[1]."/".$keys[2]).".<br />";
						} elseif ($done == -2) {
							echo sprintf($lang["img/post_not_found"],$keys[1]."/".$keys[2])."<br />";
						} elseif ($done == -3) {
							echo sprintf($lang["img/post_no_image"],$keys[1]."/".$keys[2])."<br />";
						} elseif ($done == -4) {
							echo sprintf($lang["img/post_wait_more"],$keys[1]."/".$keys[2]).".<br />";
						} elseif ($done == 1) {
							echo sprintf($lang["img/post_deleted_image"],$keys[1]."/".$keys[2]).".<br />";
						} elseif ($done == 2) {
							echo sprintf($lang["img/post_deleted"],$keys[1]."/".$keys[2]).".<br />";
						}
						if ($done == -16)
						{
							echo "<h1>".$lang['img/board_no_exists']."</h1></body></html>"; exit;
						}
					}
				}
				echo '<meta http-equiv="refresh" content="2;URL='."'".$return_url."index.html'".'">';
			} elseif (!empty($_POST['report'])) {
				$board = $conn->real_escape_string($_POST['board']);
				$mitsuba->common->banMessage($board);
				foreach ($_POST as $key => $value)
				{
					if ($value == "delete")
					{
						$keys = explode("%", $key);
						$done = $mitsuba->posting->reportPost($keys[1], $keys[2], $_POST['reason']);
						if ($done == 1)
						{
							echo sprintf($lang['img/post_reported'], $keys[1]."/".$keys[2])."<br />";
						}
					}
				}
				echo '<meta http-equiv="refresh" content="2;URL='."'./".$board."/index.html'".'">';
			}
			break;
		case "usrapp":
				$board = $conn->real_escape_string($_POST['board']);
			if (!empty($_POST['msg']))
			{
				$msg = $conn->real_escape_string(htmlspecialchars($_POST['msg']));
				$email = $conn->real_escape_string(htmlspecialchars($_POST['email']));
				$ip = $_SERVER['REMOTE_ADDR'];
				if ($mitsuba->common->verifyBan($ip, $_POST['banid'], $_POST['banrange']))
				{
					$ban_id = $_POST['banid'];
					$range = $_POST['banrange'];
					$conn->query("INSERT INTO appeals (created, ban_id, ip, msg, email, rangeban) VALUES (".time().", ".$ban_id.", '".$ip."', '".$msg."', '".$email."', ".$range.") ON DUPLICATE KEY UPDATE msg='".$msg."', email='".$email."'");
					echo $lang['img/appeal_sent'];
				}
			}
			break;
		default:
			$e = array("mode" => $mode, "requestdata" => &$_POST);
			$mitsuba->emitEvent("imgboard.mode", $e);
	}
	$conn->close();
} else {

}
?>
