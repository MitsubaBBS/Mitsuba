<?php

function deletePost($conn, $board, $postno, $password, $onlyimgdel = 0, $adm_type = -1)
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
		$result = $conn->query("SELECT * FROM posts_".$board." WHERE id=".$postno);
		if ($result->num_rows == 1)
		{
			$postdata = $result->fetch_assoc();
			if ((time() > ($postdata['date'] + $bdata['time_to_delete'])) || ($adm_type >= 1))
			{
				if ((md5($password) == $postdata['password']) || ($adm_type >= 1))
				{
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
							$conn->query("UPDATE posts_".$board." SET filename='deleted' WHERE id=".$postno.";");
							if ($postdata['resto'] != 0)
							{
								generateView($conn, $board, $postdata['resto']);
								generateView($conn, $board);
							} else {
								generateView($conn, $board, $postno);
								generateView($conn, $board);
							}
							return 1; //done-image
						} else {
							return -3;
						}
					} else {
						if ($postdata['resto'] == 0) //we'll have to delete whole thread
						{
							$files = $conn->query("SELECT * FROM posts_".$board." WHERE filename != '' AND resto=".$postdata['id']);
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
							$conn->query("DELETE FROM posts_".$board." WHERE resto=".$postno.";");
							$conn->query("DELETE FROM posts_".$board." WHERE id=".$postno.";");
							unlink("./".$board."/res/".$postno.".html");
							//generateView($conn, $board, $postno);
							generateView($conn, $board);
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
							$conn->query("DELETE FROM posts_".$board." WHERE id=".$postno.";");
							generateView($conn, $board, $postdata['resto']);
							generateView($conn, $board);
							return 2;
						}
					}
				} else {
					return -1; //wrong password
				}
			} else {
				return -4;
			}
		} else {
			return -2;
		}
	} else {
		return -2;
	}
}

function addPost($conn, $board, $name, $email, $subject, $comment, $password, $filename, $orig_filename, $resto = null, $md5 = "", $spoiler = 0, $embed = 0, $adm_type = -1, $capcode = 0, $raw = 0, $sticky = 0, $locked = 0, $nolimit = 0)
{
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
	
	if (($resto == 0) && (empty($filename)))
	{
		echo "<center><h1>Error: No file selected.</h1><br /><a href='./".$board."'>RETURN</a></center>";
		return;
	}
	
	if ((empty($filename)) && (empty($comment)))
	{
		echo "<center><h1>Error: No file selected.</h1><br /><a href='./".$board."'>RETURN</a></center>";
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
		$thread = $conn->query("SELECT * FROM posts_".$board." WHERE id=".$resto);
		
		if ($bdata['bumplimit'] > 0)
		{
			$replies = $conn->query("SELECT * FROM posts_".$board." WHERE resto=".$resto);
			$replies = $replies->num_rows;
		}
		
		if ($thread->num_rows == 0)
		{
			echo "<center><h1>Error: Cannot reply to thread because thread does not exist.</h1><br /><a href='./".$board."'>RETURN</a></center>";
			return;
		}
		
		$tinfo = $thread->fetch_assoc();
		if (($tinfo['locked'] == 1) && ($adm_type <= 0))
		{
			echo "<center><h1>Error: This thread is locked.</h1><br /><a href='./".$board."'>RETURN</a></center>";
			return;
		}
		
	}
	$lastbumped = time();
	$trip = "";
	if (($bdata['noname'] == 0) || ($adm_type >= 1))
	{
		$name = processString($conn, $name, 1);
		if (isset($name['trip']))
		{
			$trip = $name['trip'];
			$name = $name['name'];
		}
	} else {
		$name = "Anonymous";
		if (($email != "nonoko") || ($email != "nonokosage") || ($email != "noko") || ($email != "nokosage") || ($email != "sage"))
		{
			$email = "";
		}
	}
	$old_email = $email;
	if (($bdata['noname'] == 1) && (!empty($email)))
	{
		if (($email == "noko") || ($email == "nonoko"))
		{
			$email = "";
		}
		if (($email == "nokosage") || ($email == "nonokosage"))
		{
			$email = "sage";
			$old_email = "sage";
		}
	}
	
	if (($email == "nokosage") || ($email == "nonokosage"))
	{
		$old_email = "sage";
	}
	$md5 = $conn->real_escape_string($md5);
	$poster_id = "";
	if ($bdata['ids'] == 1)
	{
		if ($resto != 0)
		{
			$poster_id = mkid($_SERVER['REMOTE_ADDR'], $resto, $board);
		}
		
	}
	$isize = "";
	$fsize = "";
	if ((!empty($fname2)) && ($fname2 != "embed"))
	{
		if (substr($filename, 0, 8) == "spoiler:")
		{
			$d = getimagesize("./".$board."/src/".substr($filename, 8));
			$isize = $d[0]."x".$d[1];
			$fsize = human_filesize(filesize("./".$board."/src/".substr($filename, 8)));
		} else {
			$d = getimagesize("./".$board."/src/".$filename);
			$isize = $d[0]."x".$d[1];
			$fsize = human_filesize(filesize("./".$board."/src/".$filename));
		}
	}
	$conn->query("INSERT INTO posts_".$board." (date, name, trip, poster_id, email, subject, comment, password, orig_filename, filename, resto, ip, lastbumped, filehash, filesize, imagesize, sticky, sage, locked, capcode, raw)".
	"VALUES (".time().", '".$name."', '".$trip."', '".$conn->real_escape_string($poster_id)."', '".processString($conn, $email)."', '".processString($conn, $subject)."', '".preprocessComment($conn, $comment)."', '".md5($password)."', '".processString($conn, $orig_filename)."', '".$filename."', ".$resto.", '".$_SERVER['REMOTE_ADDR']."', ".$lastbumped.", '".$md5."', '".$fsize."', '".$isize."', ".$sticky.", 0, ".$locked.", ".$capcode.", ".$raw.")");
	$id = mysqli_insert_id($conn);
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
		$conn->query("UPDATE posts_".$board." SET poster_id='".$conn->real_escape_string($poster_id)."' WHERE id=".$id);
	}
	$email = $old_email;
	if ($resto != 0)
	{
		if (($email == "sage") || ($tinfo['sage'] == 1) || ($replies > $bdata['bumplimit']))
		{
		
		} else {
			$conn->query("UPDATE posts_".$board." SET lastbumped=".time()." WHERE id=".$resto);
		}
	
	
	}
	if ($adm_type >= 0)
	{
		if (($email == "nonoko") || ($email == "nonokosage"))
		{
			echo '<meta http-equiv="refresh" content="2;URL='."'?/board&b=".$board."'".'">';
			
		} else {
			if ($resto != 0)
			{
				echo '<meta http-equiv="refresh" content="2;URL='."'?/board&b=".$board."&t=".$resto."#p".$id."".'">';
			} else {
				echo '<meta http-equiv="refresh" content="2;URL='."'?/board&b=".$board."&t=".$id."'".'">';
				
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
	pruneOld($conn, $board);
	if ($resto == 0)
	{
		generateView($conn, $board, $id);
	} else {
		generateView($conn, $board, $resto);
	}
	generateView($conn, $board);
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
		$result = $conn->query("SELECT * FROM posts_".$board." WHERE id=".$id);
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