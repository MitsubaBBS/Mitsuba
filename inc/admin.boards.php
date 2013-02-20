<?php

function rebuildBoardCache($conn, $board)
{
generateView($conn, $board);
updateThreads($conn, $board);
regenThumbnails($conn, $board);
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

function addBoard($conn, $short, $name, $des = "", $message = "", $bumplimit = 0)
{
	$short = mysqli_real_escape_string($conn, trim($short, "/ "));
	$name = mysqli_real_escape_string($conn, $name);
	$des = mysqli_real_escape_string($conn, $des);
	$message = mysqli_real_escape_string($conn, $message);
	if (!is_numeric($bumplimit))
	{
		$bumplimit = 0;
	}
	$result = mysqli_query($conn, "INSERT INTO boards (short, name, des, message, bumplimit) VALUES ('".$short."', '".$name."', '".$des."', '".$message."', ".$bumplimit.")");
	if ($result)
	{
		mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `posts_".$short."` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `date` int(30) NOT NULL,
  `name` varchar(60) NOT NULL,
  `trip` varchar(11) NOT NULL,
  `email` varchar(60) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `password` varchar(80) NOT NULL,
  `orig_filename` varchar(80) NOT NULL,
  `filename` varchar(40) NOT NULL,
  `resto` int(20) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `lastbumped` int(20) NOT NULL,
  `filehash` varchar(80) NOT NULL,
  `sticky` varchar(1) NOT NULL,
  `sage` varchar(1) NOT NULL,
  `locked` varchar(1) NOT NULL,
  `capcode` varchar(1) NOT NULL,
  `raw` varchar(1) NOT NULL,
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
	mysqli_query($conn, "DELETE FROM boards WHERE short='".mysqli_real_escape_string($conn, $short)."'");
	mysqli_query($conn, "DROP TABLE posts_".$short.";");
	delTree("./".$short);
}

function updateBoard($conn, $short, $new_name, $new_des, $new_msg, $new_limit = 0)
{
	if (isBoard($conn, $short))
	{
		if (!is_numeric($new_limit))
		{
			$new_limit = 0;
		}
		mysqli_query($conn, "UPDATE boards SET name='".mysqli_real_escape_string($conn, $new_name)."', des='".mysqli_real_escape_string($conn, $new_des)."', message='".mysqli_real_escape_string($conn, $new_msg)."', bumplimit=".$new_limit." WHERE short='".mysqli_real_escape_string($conn, $short)."'");
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
			mysqli_query($conn, "UPDATE boards SET short='".mysqli_real_escape_string($conn, $new)."' WHERE short='".mysqli_real_escape_string($conn, $short)."'");
			mysqli_query($conn, "RENAME TABLE posts_".mysqli_real_escape_string($conn, $short)." TO posts_".mysqli_real_escape_string($conn, $new));
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