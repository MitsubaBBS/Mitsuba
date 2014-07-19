<?php
namespace Mitsuba\Admin;
class Boards {
	private $conn;
	private $mitsuba;

	function __construct($connection, &$mitsuba) {
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

	function addBoard($short, $type, $name, $des = "", $message = "", $bumplimit = 0, $spoilers = 0, $noname = 0, $ids = 0, $embeds = 0, $bbcode = 1, $time_between_posts = 20, $time_between_threads = 60, $time_to_delete = 120, $filesize = 2097152, $pages = 15, $hidden = 0, $unlisted = 0, $nodup = 0, $nofile = 0, $maxchars = 2000, $anonymous = "Anonymous", $extensions = "png,jpg,gif", $catalog = 0, $captcha = 0, $overboard_boards = "", $allow_replies = 1, $file_replies = 1, $links = "", $files = 0)
	{
		$short = $this->conn->real_escape_string(trim(str_replace("%", "_", $short), "/ ")); 
		$type = $this->conn->real_escape_string($type);
		$name = $this->conn->real_escape_string($name);
		$des = $this->conn->real_escape_string($des);
		$message = $this->conn->real_escape_string($message);
		$anonymous = $this->conn->real_escape_string($anonymous);
		$overboard_boards = $this->conn->real_escape_string($overboard_boards);
		$extensions = $this->conn->real_escape_string($extensions);
		$links = $this->conn->real_escape_string($links);
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
		if (!is_numeric($captcha))
		{
			$captcha = 0;
		}
		if (!is_numeric($hidden))
		{
			$hidden = 0;
		}
		if (!is_numeric($unlisted))
		{
			$unlisted = 0;
		}
		if (!is_numeric($nodup))
		{
			$nodup = 0;
		}
		if (!is_numeric($nofile))
		{
			$nofile = 0;
		}
		if (!is_numeric($catalog))
		{
			$catalog = 0;
		}
		if (!is_numeric($allow_replies))
		{
			$allow_replies = 1;
		}
		if (!is_numeric($file_replies))
		{
			$file_replies = 1;
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
		if (!is_numeric($files))
		{
			$files = 15;
		}
		$result = $this->conn->query("INSERT INTO boards (short, type, name, des, message, bumplimit, spoilers, noname, ids, embeds, bbcode, time_between_posts, time_between_threads, time_to_delete, filesize, pages, hidden, unlisted, nodup, nofile, maxchars, anonymous, extensions, catalog, captcha, overboard_boards, allow_replies, file_replies, links, files) VALUES ('".$short."', '".$type."', '".$name."', '".$des."', '".$message."', ".$bumplimit.", ".$spoilers.", ".$noname.", ".$ids.", ".$embeds.", ".$bbcode.", ".$time_between_posts.", ".$time_between_threads.", ".$time_to_delete.", ".$filesize.", ".$pages.", ".$hidden.", ".$unlisted.", ".$nodup.", ".$nofile.", ".$maxchars.", '".$anonymous."', '".$extensions."', ".$catalog.", ".$captcha.", '".$overboard_boards."', ".$allow_replies.", ".$file_replies.", '".$links."', ".$files.")");
		if ($result)
		{
			$this->createDirectories($short);
			$this->mitsuba->caching->generateView($short);
			if ($catalog == 1)
			{
				$this->mitsuba->caching->generateCatalog($short);
			}
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

	function updateBoard($short, $name, $des, $msg, $limit = 0, $spoilers = 0, $noname = 0, $ids = 0, $embeds = 0, $bbcode = 1, $time_between_posts = 20, $time_between_threads = 60, $time_to_delete = 120, $filesize = 2097152, $pages = 15, $hidden = 0, $unlisted = 0, $nodup = 0, $nofile = 0, $maxchars = 2000, $anonymous = "Anonymous", $extensions = "png,jpg,gif", $catalog = 0, $captcha = 0, $overboard_boards = "", $allow_replies = 1, $file_replies = 1, $links = "", $files = 0)
	{
		if ($this->mitsuba->common->isBoard($short))
		{
			$name = $this->conn->real_escape_string($name);
			$des = $this->conn->real_escape_string($des);
			$msg = $this->conn->real_escape_string($msg);
			$anonymous = $this->conn->real_escape_string($anonymous);
			$overboard_boards = $this->conn->real_escape_string($overboard_boards);
			$extensions = $this->conn->real_escape_string($extensions);
			$links = $this->conn->real_escape_string($links);
			if (!is_numeric($limit))
			{
				$limit = 0;
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
			if (!is_numeric($unlisted))
			{
				$unlisted = 0;
			}
			if (!is_numeric($nodup))
			{
				$nodup = 0;
			}
			if (!is_numeric($nofile))
			{
				$nofile = 0;
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
			if (!is_numeric($files))
			{
				$files = 15;
			}
			if (!is_numeric($maxchars))
			{
				$maxchars = 2000;
			}
			if (!is_numeric($catalog))
			{
				$catalog = 0;
			}
			if (!is_numeric($captcha))
			{
				$captcha = 0;
			}
			if (!is_numeric($allow_replies))
			{
				$allow_replies = 1;
			}
			if (!is_numeric($file_replies))
			{
				$file_replies = 1;
			}
			$this->conn->query("UPDATE boards SET name='".$name."', des='".$des."', message='".$msg."', bumplimit=".$limit.", spoilers=".$spoilers.", noname=".$noname.", ids=".$ids.", embeds=".$embeds.", bbcode=".$bbcode.", time_between_posts=".$time_between_posts.", time_between_threads=".$time_between_threads.", time_to_delete=".$time_to_delete.", filesize=".$filesize.", pages=".$pages.", hidden=".$hidden.", unlisted=".$unlisted.", nodup=".$nodup.", nofile=".$nofile.", maxchars=".$maxchars.", anonymous='".$anonymous."', extensions='".$extensions."', catalog=".$catalog.", captcha=".$captcha.", overboard_boards='".$overboard_boards."', allow_replies=".$allow_replies.", file_replies=".$file_replies.", links='".$links."', files=".$files." WHERE short='".$this->conn->real_escape_string($short)."'");
			$this->mitsuba->caching->rebuildBoardCache($short);
			if (($catalog == 0) && (file_exists("./".$short."/catalog.html")))
			{
				unlink("./".$short."/catalog.html");
			}
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
				$new_e = $this->conn->real_escape_string($new);
				$short_e = $this->conn->real_escape_string($short);
				$this->conn->query("UPDATE boards SET short='".$new_e."' WHERE short='".$short_e."'");
				$this->conn->query("UPDATE posts SET board='".$new_e."' WHERE board='".$short_e."'");
				$this->conn->query("UPDATE links SET url='".$new_e."' WHERE url='".$short_e."' AND relativity=2");
				$this->mitsuba->caching->rebuildBoardLinks();
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