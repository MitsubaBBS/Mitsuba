<?php

function deletePost($conn, $cacher, $board, $postno, $password, $onlyimgdel = 0, $adm_type = -1)
{
	if (!is_numeric($adm_type))
	{
		$adm_type = -1;
	}
	if (is_numeric($postno))
	{
		$board = $conn->real_escape_string($board);
		if (!isBoard($conn, $board))
		{
			return -16;
		}
		$bdata = getBoardData($conn, $board);
		$result = $conn->query("SELECT * FROM posts WHERE id=".$postno." AND board='".$board."'");
		if ($result->num_rows == 1)
		{
			$postdata = $result->fetch_assoc();
			if ($adm_type <= 0)
			{
				if (time() <= ($postdata['date'] + $bdata['time_to_delete']))
				{
					return -4;
				}
				if (md5($password) != $postdata['password'])
				{
					return -1;
				}
			}
			if ($onlyimgdel == 1)
			{
				if ((!empty($postdata['filename'])) && ($postdata['filename'] != "deleted"))
				{
						
					$filename = $postdata['filename'];
					if (substr($filename, 0, 8) == "spoiler:")
					{
						$filename = substr($filename, 8);
					}
					if ((substr($filename, 0, 6) != "embed:") && ($filename != "deleted"))
					{
						unlink("./".$board."/src/".$filename);
						unlink("./".$board."/src/thumb/".$filename);
					}
					$conn->query("UPDATE posts SET filename='deleted', mimetype='', filehash='' WHERE id=".$postno." AND board='".$board."';");
					if ($postdata['resto'] != 0)
					{
						$cacher->generateView($board, $postdata['resto']);

						if ($config['caching_mode']==1)
						{
							$cacher->forceGetThread($board, $postdata['resto']);
						}
						$cacher->generateView($board);

					} else {
						$cacher->generateView($board, $postno);
						if ($config['caching_mode']==1)
						{
							$cacher->forceGetThread($board, $postno);
						}
						$cacher->generateView($board);
					}
					return 1; //done-image
				} else {
					return -3;
				}
			} else {
				if ($postdata['resto'] == 0) //we'll have to delete whole thread
				{
					$files = $conn->query("SELECT * FROM posts WHERE filename != '' AND resto=".$postdata['id']." AND board='".$board."'");
					while ($file = $files->fetch_assoc())
					{
						$filename = $file['filename'];
						if (substr($filename, 0, 8) == "spoiler:")
						{
							$filename = substr($filename, 8);
						}
						if ((substr($filename, 0, 6) != "embed:") && ($filename != "deleted"))
						{
							unlink("./".$board."/src/".$filename);
							unlink("./".$board."/src/thumb/".$filename);
						}
					}
					if ((!empty($postdata['filename'])) && ($postdata['filename'] != "deleted"))
					{
						$filename = $postdata['filename'];
						if (substr($filename, 0, 8) == "spoiler:")
						{
							$filename = substr($filename, 8);
						}
						if ((substr($filename, 0, 6) != "embed:") && ($filename != "deleted"))
						{
							unlink("./".$board."/src/".$filename);
							unlink("./".$board."/src/thumb/".$filename);
						}
					}
					$conn->query("DELETE FROM posts WHERE resto=".$postno." AND board='".$board."';");
					$conn->query("DELETE FROM posts WHERE id=".$postno." AND board='".$board."';");
					if ($bdata['hidden'] == 0)
					{
						if (file_exists("./".$board."/res/".$postno.".json"))
						{
							unlink("./".$board."/res/".$postno.".json");
						}
						if (file_exists("./".$board."/res/".$postno."_index.html"))
						{
							unlink("./".$board."/res/".$postno."_index.html");
						}
						unlink("./".$board."/res/".$postno.".html");
					}
					//$cacher->generateView($board, $postno);
					$cacher->generateView($board);
					return 2; //done post
				} else {
					if ((!empty($postdata['filename'])) && ($postdata['filename'] != "deleted"))
					{
						
						$filename = $postdata['filename'];
						if (substr($filename, 0, 8) == "spoiler:")
						{
							$filename = substr($filename, 8);
						}
						if ((substr($filename, 0, 6) != "embed:") && ($filename != "deleted"))
						{
							unlink("./".$board."/src/".$filename);
							unlink("./".$board."/src/thumb/".$filename);
						}
					}
					$conn->query("DELETE FROM posts WHERE id=".$postno." AND board='".$board."';");
					$cacher->generateView($board, $postdata['resto']);
					if ($config['caching_mode']==1)
					{
						$cacher->forceGetThread($board, $postdata['resto']);
					}
					$cacher->generateView($board);
					return 2;
				}
			}
				
		} else {
			return -2;
		}
	} else {
		return -2;
	}
}

function addPost($conn, $cacher, $board, $name, $email, $subject, $comment, $password, $filename, $orig_filename, $mimetype = "", $resto = null, $md5 = "", $t_w = 0, $t_h = 0, $spoiler = 0, $embed = 0, $adm_type = -1, $capcode = 0, $raw = 0, $sticky = 0, $locked = 0, $nolimit = 0, $nofile = 0, $fake_id = "", $cc_text = "", $cc_color = "")
{
	global $lang;
	$config = getConfig($conn);
	if (!isBoard($conn, $board))
	{
		return -16;
	}
	if (!is_numeric($resto))
	{
		$resto = 0;
	}
	
	if (!is_numeric($adm_type))
	{
		$adm_type = -1;
	}
	
	if (!is_numeric($t_w))
	{
		$t_w = 0;
	}
	if (!is_numeric($t_h))
	{
		$t_h = 0;
	}
	
	if ((!is_numeric($raw)) || ($adm_type <= 0))
	{
		$raw = 0;
	}
	if ((!is_numeric($capcode)) || ($adm_type <= 0))
	{
		$capcode = 0;
	}
	if ((!is_numeric($sticky)) || ($adm_type <= 0))
	{
		$sticky = 0;
	}
	if ((!is_numeric($locked)) || ($adm_type <= 0))
	{
		$locked = 0;
	}
	
	if ($resto != 0)
	{
		$sticky = 0;
		$locked = 0;
	}
	
	if (($resto == 0) && (empty($filename)) && ($nofile == 0))
	{
		echo "<center><h1>".$lang['img/no_file']."</h1><br /><a href='./".$board."'>".$lang['img/return']."</a></center>";
		return;
	}
	
	if ((empty($filename)) && (empty($comment)))
	{
		echo "<center><h1>".$lang['img/no_file']."</h1><br /><a href='./".$board."'>".$lang['img/return']."</a></center>";
		return;
	}
	
	$bdata = getBoardData($conn, $board);
	$fname2 = $filename;
	if ((!empty($filename)) && ($spoiler == 1) && ($bdata['spoilers'] == 1))
	{
		$filename = "spoiler:".$filename;
	}
	$embed_img = 0;
	if ((!empty($filename)) && ($embed == 1) && ($bdata['embeds'] == 1))
	{
		$fname2 = "embed";
		$embed_img = 1;
	}
	$thread = "";
	$tinfo = "";
	$replies = 0;
	if ($resto != 0)
	{
		$thread = $conn->query("SELECT * FROM posts WHERE id=".$resto." AND board='".$board."'");
		
		if ($bdata['bumplimit'] > 0)
		{
			$replies = $conn->query("SELECT * FROM posts WHERE resto=".$resto." AND board='".$board."'");
			$replies = $replies->num_rows;
		}
		
		if ($thread->num_rows == 0)
		{
			echo "<center><h1>".$lang['img/cant_reply']."</h1><br /><a href='./".$board."'>".$lang['img/return']."</a></center>";
			return;
		}
		
		$tinfo = $thread->fetch_assoc();
		if (($tinfo['locked'] == 1) && ($adm_type <= 0))
		{
			echo "<center><h1>".$lang['img/thread_locked']."</h1><br /><a href='./".$board."'>".$lang['img/return']."</a></center>";
			return;
		}
		
	}
	$lastbumped = time();
	$trip = "";
	$strip = "";
	if (($bdata['noname'] == 0) || ($adm_type >= 1))
	{
		$arr = processName($conn, $name);
		$trip = $arr['trip'];
		$name = $arr['name'];
		$strip = $arr['strip'];
	} else {
		$name = "Anonymous";
		/*if (($email != "nonoko") || ($email != "nonokosage") || ($email != "noko") || ($email != "nokosage") || ($email != "sage"))
		{
			$email = "";
		}*/
	}
	$old_email = $email;
	if (($bdata['noname'] == 1) && (!empty($email)) && ($adm_type <= 0))
	{
		if (($email == "noko") || ($email == "nonoko"))
		{
			$email = "";
		} elseif (($email == "nokosage") || ($email == "nonokosage") || ($email == "sage"))
		{
			$email = "sage";
		} else {
			$email = "";
		}
	}
	
	$mimetype = $conn->real_escape_string($mimetype);
	$md5 = $conn->real_escape_string($md5);
	$poster_id = "";
	if (!empty($fake_id))
	{
		$poster_id = $fake_id;
	} else {
		if ($bdata['ids'] == 1)
		{
			if ($resto != 0)
			{
				$poster_id = mkid($_SERVER['REMOTE_ADDR'], $resto, $board);
			}
			
		}
	}
	$isize = "";
	$osize = 0;
	$fsize = "";
	if ((!empty($fname2)) && ($fname2 != "embed"))
	{
		if (substr($filename, 0, 8) == "spoiler:")
		{
			$d = getimagesize("./".$board."/src/".substr($filename, 8));
			$isize = $d[0]."x".$d[1];
			$osize = filesize("./".$board."/src/".substr($filename, 8));
			$fsize = human_filesize($osize);
		} else {
			$d = getimagesize("./".$board."/src/".$filename);
			$isize = $d[0]."x".$d[1];
			$osize = filesize("./".$board."/src/".$filename);
			$fsize = human_filesize($osize);
		}
	}
	if ((empty($cc_text)) || (empty($cc_color)))
	{
		$cc_text = "";
		$cc_color = "";
	} else {
		$cc_text = $conn->real_escape_string(htmlspecialchars($cc_text));
		$cc_color = $conn->real_escape_string(htmlspecialchars($cc_color));
	}
	$conn->query("INSERT INTO posts (board, date, name, trip, strip, poster_id, email, subject, comment, password, orig_filename, filename, resto, ip, lastbumped, filehash, orig_filesize, filesize, imagesize, mimetype, t_w, t_h, sticky, sage, locked, capcode, raw, cc_text, cc_color)".
	"VALUES ('".$board."', ".time().", '".$name."', '".$trip."', '".$strip."', '".$conn->real_escape_string($poster_id)."', '".processString($conn, $email)."', '".processString($conn, $subject)."', '".preprocessComment($conn, $comment)."', '".md5($password)."', '".processString($conn, $orig_filename)."', '".$filename."', ".$resto.", '".$_SERVER['REMOTE_ADDR']."', ".$lastbumped.", '".$md5."', ".$osize.", '".$fsize."', '".$isize."', '".$mimetype."', ".$t_w.", ".$t_h.", ".$sticky.", 0, ".$locked.", ".$capcode.", ".$raw.", '".$cc_text."', '".$cc_color."')");
	$id = mysqli_insert_id($conn);
	if (empty($fake_id))
	{
		$poster_id = "";
		if ($bdata['ids'] == 1)
		{
			if ($resto == 0)
			{
				$poster_id = mkid($_SERVER['REMOTE_ADDR'], $id, $board);
			}
			
		}
		if ($poster_id != "")
		{
			$conn->query("UPDATE posts SET poster_id='".$conn->real_escape_string($poster_id)."' WHERE id=".$id." AND board='".$board."'");
		}
	}
	if ($resto != 0)
	{
		if (($email == "sage") || ($tinfo['sage'] == 1) || ($replies > $bdata['bumplimit']))
		{
		
		} else {
			$conn->query("UPDATE posts SET lastbumped=".time()." WHERE id=".$resto." AND board='".$board."'");
		}
	}
	$email = $old_email;
	
	if ($adm_type > 0)
	{
		if (($email == "nonoko") || ($email == "nonokosage"))
		{
			echo '<meta http-equiv="refresh" content="2;URL='."'./mod.php?/board&b=".$board."'".'">';
			
		} else {
			if ($resto != 0)
			{
				echo '<meta http-equiv="refresh" content="2;URL='."'./mod.php?/board&b=".$board."&t=".$resto."#p".$id."".'">';
			} else {
				echo '<meta http-equiv="refresh" content="2;URL='."'./mod.php?/board&b=".$board."&t=".$id."'".'">';
				
			}
		}
	} else {
		if (($email == "nonoko") || ($email == "nonokosage"))
		{
			echo '<meta http-equiv="refresh" content="2;URL='."'./".$board."/index.html'".'">';
			
		} else {
			if ($resto != 0)
			{
				echo '<meta http-equiv="refresh" content="2;URL='."'./".$board."/res/".$resto.".html#p".$id."".'">';
			} else {
				echo '<meta http-equiv="refresh" content="2;URL='."'./".$board."/res/".$id.".html'".'">';
				
			}
		}
	}
	if ($resto == 0)
	{
		pruneOld($conn, $board);
	}
	
	if ($resto == 0)
	{
		$cacher->generateView($board, $id);
		if ($config['caching_mode']==1)
		{
			$cacher->forceGetThread($board, $id);
		}
		if ($config['enable_api']==1)
		{
			serializeThread($conn, $board, $id);
		}
	} else {
		$cacher->generateView($board, $resto);
		if ($config['caching_mode']==1)
		{
			$cacher->forceGetThread($board, $resto);
		}
		if ($config['enable_api']==1)
		{
			serializeThread($conn, $board, $resto);
		}
	}
	$cacher->generateView($board);
	
	if ($config['frontpage_style'] == 1)
	{
		$cacher->generateFrontpage();
	}
}

function reportPost($conn, $board, $id, $reason)
{
	if (is_numeric($id))
	{
		$board = $conn->real_escape_string($board);
		if (!isBoard($conn, $board))
		{
			return -16;
		}
		$result = $conn->query("SELECT * FROM posts WHERE id=".$id." AND board='".$board."'");
		if ($result->num_rows == 1)
		{
			$postdata = $result->fetch_assoc();
			$result = $conn->query("SELECT * FROM reports WHERE reported_post=".$id." AND board='".$board."'");
			if ($result->num_rows == 0)
			{
				$reason = $conn->real_escape_string(htmlspecialchars($reason));
				$conn->query("INSERT INTO reports (reporter_ip, reported_post, reason, created, board) VALUES ('".$_SERVER['REMOTE_ADDR']."', ".$id.", '".$reason."', ".time().", '".$board."')");
			} else {
				return 1;
			}
		} else {
			return -15;
		}
	} else {
		return -15;
	}
}

?>