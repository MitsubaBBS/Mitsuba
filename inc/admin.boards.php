<?php
namespace Mitsuba\Admin;
class Boards {
	private $conn;
	private $mitsuba;

	function __construct($connection, $mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
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

	function addBoard($short, $name, $des = "", $message = "", $bumplimit = 0, $spoilers = 0, $noname = 0, $ids = 0, $embeds = 0, $bbcode = 1, $time_between_posts = 20, $time_between_threads = 60, $time_to_delete = 120, $filesize = 2097152, $pages = 15, $hidden = 0, $nodup = 0, $maxchars = 2000, $anonymous = "Anonymous", $extensions = "png,jpg,gif")
	{
		$short = $this->conn->real_escape_string(trim($short, "/ "));
		$name = $this->conn->real_escape_string($name);
		$des = $this->conn->real_escape_string($des);
		$message = $this->conn->real_escape_string($message);
		$anonymous = $this->conn->real_escape_string($anonymous);
		$extensions = $this->conn->real_escape_string($extensions);
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
		if (!is_numeric($hidden))
		{
			$hidden = 0;
		}
		if (!is_numeric($nodup))
		{
			$nodup = 0;
		}
		if (!is_numeric($maxchars))
		{
			$maxchars = 2000;
		}
		if (!is_numeric($time_between_posts))
		{
			$time_between_posts = 20;
		}
		if (!is_numeric($time_between_threads))
		{
			$time_between_threads = 60;
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
		$result = $this->conn->query("INSERT INTO boards (short, name, des, message, bumplimit, spoilers, noname, ids, embeds, bbcode, time_between_posts, time_between_threads, time_to_delete, filesize, pages, hidden, nodup, maxchars, anonymous, extensions) VALUES ('".$short."', '".$name."', '".$des."', '".$message."', ".$bumplimit.", ".$spoilers.", ".$noname.", ".$ids.", ".$embeds.", ".$bbcode.", ".$time_between_posts.", ".$time_between_threads.", ".$time_to_delete.", ".$filesize.", ".$pages.", ".$hidden.", ".$nodup.", ".$maxchars.", '".$anonymous."', '".$extensions."')");
		if ($result)
		{
			$this->createDirectories($short);
			$this->mitsuba->caching->generateView($short);
			return 1;
		} else {
			return -1; //error
		}
	}

	function deleteBoard($short)
	{
		$this->conn->query("DELETE FROM boards WHERE short='".$this->conn->real_escape_string($short)."'");
		$this->conn->query("DELETE FROM posts WHERE board='".$this->conn->real_escape_string($short)."';");
		$this->mitsuba->common->delTree("./".$short);
	}

	function updateBoard($short, $new_name, $new_des, $new_msg, $new_limit = 0, $new_spoilers = 0, $new_noname = 0, $new_ids = 0, $new_embeds = 0, $bbcode = 1, $time_between_posts = 20, $time_between_threads = 60, $time_to_delete = 120, $filesize = 2097152, $pages = 15, $hidden = 0, $nodup = 0, $maxchars = 2000, $anonymous = "Anonymous")
	{
		if ($this->mitsuba->common->isBoard($short))
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
			if (!is_numeric($hidden))
			{
				$hidden = 0;
			}
			if (!is_numeric($nodup))
			{
				$nodup = 0;
			}
			if (!is_numeric($time_between_posts))
			{
				$time_between_posts = 20;
			}
			if (!is_numeric($time_between_threads))
			{
				$time_between_threads = 60;
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
			if (!is_numeric($maxchars))
			{
				$maxchars = 2000;
			}
			$this->conn->query("UPDATE boards SET name='".$this->conn->real_escape_string($new_name)."', des='".$this->conn->real_escape_string($new_des)."', message='".$this->conn->real_escape_string($new_msg)."', bumplimit=".$new_limit.", spoilers=".$new_spoilers.", noname=".$new_noname.", ids=".$new_ids.", embeds=".$new_embeds.", bbcode=".$bbcode.", time_between_posts=".$time_between_posts.", time_between_threads=".$time_between_threads.", time_to_delete=".$time_to_delete.", filesize=".$filesize.", pages=".$pages.", hidden=".$hidden.", nodup=".$nodup.", maxchars=".$maxchars.", anonymous='".$this->conn->real_escape_string($anonymous)."' WHERE short='".$this->conn->real_escape_string($short)."'");
			$this->mitsuba->caching->rebuildBoardCache($short);
			return 1;
		} else {
			return 0;
		}
	}

	function moveBoard($short, $new)
	{
		$short = trim($short, "/ ");
		$new = trim($new, "/ ");
		if ($this->mitsuba->common->isBoard($short))
		{
			if (!$this->mitsuba->common->isBoard($new))
			{
				$this->conn->query("UPDATE boards SET short='".$this->conn->real_escape_string($new)."' WHERE short='".$this->conn->real_escape_string($short)."'");
				$this->conn->query("UPDATE posts SET board='".$this->conn->real_escape_string($new)."' WHERE board='".$this->conn->real_escape_string($short))."'";
				rename("./".$short, "./".$new);
				$this->mitsuba->caching->rebuildBoardCache($new);
				return 1;
			} else {
				return -1; //newname exists
			}
		} else {
			return 0; //board not found
		}
	}
}
?>