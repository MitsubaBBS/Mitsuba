<?php

function rebuildBoardCache($conn, $board)
{
generateView($conn, $board);
updateThreads($conn, $board);
regenThumbnails($conn, $board);
regenIDs($conn, $board);
}

function regenIDs($conn, $board)
{
	if (isBoard($conn, $board))
	{
		$bdata = getBoardData($conn, $board);
		if ($bdata['ids'] == 1)
		{
			$result = $conn->query("SELECT * FROM posts_".$board);
			while ($row = $result->fetch_assoc())
			{
				$poster_id = "";
				if ($row['resto'] != 0)
				{
					$poster_id = mkid($row['ip'], $row['resto'], $board);
				} else {
					$poster_id = mkid($row['ip'], $row['id'], $board);
				}
				$conn->query("UPDATE posts_".$board." SET poster_id='".$poster_id."' WHERE id=".$row['id']);
			}
		} else {
			$conn->query("UPDATE posts_".$board." SET poster_id=''");
		}
	}
}

function createDirectories($board)
{
	if (!file_exists("./".$board))
	{
		mkdir("./".$board);
	}
	if (!file_exists("./".$board."/res"))
	{
		mkdir("./".$board."/res");
	}
	if (!file_exists("./".$board."/src"))
	{
		mkdir("./".$board."/src");
	}
	if (!file_exists("./".$board."/src/thumb"))
	{
		mkdir("./".$board."/src/thumb");
	}
}

function addBoard($conn, $short, $name, $des = "", $message = "", $bumplimit = 0, $spoilers = 0, $noname = 0, $ids = 0, $embeds = 0, $bbcode = 1, $time_between_posts = 20, $time_to_delete = 120, $filesize = 2097152, $pages = 15)
{
	$short = $conn->real_escape_string(trim($short, "/ "));
	$name = $conn->real_escape_string($name);
	$des = $conn->real_escape_string($des);
	$message = $conn->real_escape_string($message);
	if (!is_numeric($bumplimit))
	{
		$bumplimit = 0;
	}
	if (!is_numeric($spoilers))
	{
		$spoilers = 0;
	}
	if (!is_numeric($noname))
	{
		$noname = 0;
	}
	if (!is_numeric($ids))
	{
		$ids = 0;
	}
	if (!is_numeric($embeds))
	{
		$embeds = 0;
	}
	if (!is_numeric($bbcode))
	{
		$bbcode = 1;
	}
	if (!is_numeric($time_between_posts))
	{
		$time_between_posts = 20;
	}
	if (!is_numeric($time_to_delete))
	{
		$time_to_delete = 120;
	}
	if (!is_numeric($filesize))
	{
		$filesize = 2097152;
	}
	if (!is_numeric($pages))
	{
		$pages = 15;
	}
	$result = $conn->query("INSERT INTO boards (short, name, des, message, bumplimit, spoilers, noname, ids, embeds, bbcode, time_between_posts, time_to_delete, filesize, pages) VALUES ('".$short."', '".$name."', '".$des."', '".$message."', ".$bumplimit.", ".$spoilers.", ".$noname.", ".$ids.", ".$embeds.", ".$bbcode.", ".$time_between_posts.", ".$time_to_delete.", ".$filesize.", ".$pages.")");
	if ($result)
	{
		$conn->query("CREATE TABLE IF NOT EXISTS `posts_".$short."` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `date` int(30) NOT NULL,
  `name` varchar(60) NOT NULL,
  `trip` varchar(11) NOT NULL,
  `poster_id` varchar(8) NOT NULL,
  `email` varchar(60) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `password` varchar(100) NOT NULL,
  `orig_filename` varchar(80) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `resto` int(20) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `lastbumped` int(20) NOT NULL,
  `filehash` varchar(80) NOT NULL,
  `filesize` varchar(15) NOT NULL,
  `imagesize` varchar(20) NOT NULL,
  `sticky` int(1) NOT NULL,
  `sage` int(1) NOT NULL,
  `locked` int(1) NOT NULL,
  `capcode` int(1) NOT NULL,
  `raw` int(1) NOT NULL,
  PRIMARY KEY (`id`)
);");
		createDirectories($short);
		generateView($conn,$short);
		return 1;
	} else {
		return -1; //error
	}
}

function deleteBoard($conn, $short)
{
	$conn->query("DELETE FROM boards WHERE short='".$conn->real_escape_string($short)."'");
	$conn->query("DROP TABLE posts_".$short.";");
	delTree("./".$short);
}

function updateBoard($conn, $short, $new_name, $new_des, $new_msg, $new_limit = 0, $new_spoilers = 0, $new_noname = 0, $new_ids = 0, $new_embeds = 0, $bbcode = 1, $time_between_posts = 20, $time_to_delete = 120, $filesize = 2097152, $pages = 15)
{
	if (isBoard($conn, $short))
	{
		if (!is_numeric($new_limit))
		{
			$new_limit = 0;
		}
		if (!is_numeric($new_spoilers))
		{
			$new_spoilers = 0;
		}
		if (!is_numeric($new_noname))
		{
			$new_noname = 0;
		}
		if (!is_numeric($new_ids))
		{
			$new_ids = 0;
		}
		if (!is_numeric($new_embeds))
		{
			$new_embeds = 0;
		}
		if (!is_numeric($bbcode))
		{
			$bbcode = 1;
		}
		if (!is_numeric($time_between_posts))
		{
			$time_between_posts = 20;
		}
		if (!is_numeric($time_to_delete))
		{
			$time_to_delete = 120;
		}
		if (!is_numeric($filesize))
		{
			$filesize = 2097152;
		}
		if (!is_numeric($pages))
		{
			$pages = 15;
		}
		$conn->query("UPDATE boards SET name='".$conn->real_escape_string($new_name)."', des='".$conn->real_escape_string($new_des)."', message='".$conn->real_escape_string($new_msg)."', bumplimit=".$new_limit.", spoilers=".$new_spoilers.", noname=".$new_noname.", ids=".$new_ids.", embeds=".$new_embeds.", bbcode=".$bbcode.", time_between_posts=".$time_between_posts.", time_to_delete=".$time_to_delete.", filesize=".$filesize.", pages=".$pages." WHERE short='".$conn->real_escape_string($short)."'");
		rebuildBoardCache($conn, $short);
		return 1;
	} else {
		return 0;
	}
}

function moveBoard($conn, $short, $new)
{
	$short = trim($short, "/ ");
	$new = trim($new, "/ ");
	if (isBoard($conn, $short))
	{
		if (!isBoard($conn, $new))
		{
			$conn->query("UPDATE boards SET short='".$conn->real_escape_string($new)."' WHERE short='".$conn->real_escape_string($short)."'");
			$conn->query("RENAME TABLE posts_".$conn->real_escape_string($short)." TO posts_".$conn->real_escape_string($new));
			rename("./".$short, "./".$new);
			rebuildBoardCache($conn, $new);
			return 1;
		} else {
			return -1; //newname exists
		}
	} else {
		return 0; //board not found
	}
}
?>