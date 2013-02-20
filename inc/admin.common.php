<?php
function reqPermission($level)
{
	if ($_SESSION['type']<$level) { die(); }
}

function appendToPost($conn, $board, $postid, $text)
{
	if (is_numeric($postid))
	{
		$post = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE id=".$postid);
		if (mysqli_num_rows($post) == 1)
		{
			$pdata = mysqli_fetch_assoc($post);
			$text = mysqli_real_escape_string($conn, $text);
			$new_text = mysqli_real_escape_string($conn, $pdata['comment'])."\n\n".$text;
			mysqli_query($conn, "UPDATE posts_".$board." SET comment='".$new_text."', raw=2 WHERE id=".$postid);
			if ($pdata['resto'] == 0)
			{
				generateView($conn, $board, $pdata['id']);
			} else {
				generateView($conn, $board, $pdata['resto']);
			}
			generateView($conn, $board);
		}
	}
}

function logEvent($conn, $event)
{
	mysqli_query($conn, "INSERT INTO log (date, event, mod_id) VALUES (".time().", '".mysqli_real_escape_string($conn, $event)."', ".$_SESSION['id'].")");
}

function canBoard($board)
{
	if (($_SESSION['boards'] != "*") && ($_SESSION['type'] != 2))
	{
		$boards = explode(",", $_SESSION['boards']);
		if (in_array($board, $boards))
		{
			return 1;
		} else {
			die();
		}
	} else {
		return 1;
	}
}
?>