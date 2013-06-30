<?php
class Admin
{
	private $conn;
	private $mitsuba;
	public $bans;
	public $boards;
	public $links;
	public $users;

	function __construct($connection, $mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
		include("admin.bans.php");
		$this->bans = new \Mitsuba\Admin\Bans($this->conn, $this->mitsuba);
		include("admin.boards.php");
		$this->boards = new \Mitsuba\Admin\Boards($this->conn, $this->mitsuba);
		include("admin.links.php");
		$this->links = new \Mitsuba\Admin\Links($this->conn, $this->mitsuba);
		include("admin.users.php");
		$this->users = new \Mitsuba\Admin\Users($this->conn, $this->mitsuba);
	}

	function reqPermission($level)
	{
		if ($_SESSION['type']<$level) { die(); }
	}

	function appendToPost($board, $postid, $text)
	{
		if (is_numeric($postid))
		{
			$config = $this->mitsuba->config;
			$post = $this->conn->query("SELECT * FROM posts WHERE id=".$postid." AND board='".$board."'");
			if ($post->num_rows == 1)
			{
				$pdata = $post->fetch_assoc();
				$text = $this->conn->real_escape_string($text);
				$new_text = $this->conn->real_escape_string($pdata['comment'])."\n\n".$text;
				$this->conn->query("UPDATE posts SET comment='".$new_text."', raw=2 WHERE id=".$postid." AND board='".$board."'");
				if ($pdata['resto'] == 0)
				{
					$mitsuba->caching->generateView($board, $pdata['id']);
					if ($config['caching_mode']==1)
					{
						$mitsuba->caching->forceGetThread($board, $pdata['id']);
					}
				} else {
					$mitsuba->caching->generateView($board, $pdata['resto']);
					if ($config['caching_mode']==1)
					{
						$mitsuba->caching->forceGetThread($board, $pdata['resto']);
					}
				}
				$mitsuba->caching->generateView($board);
			}
		}
	}

	function canBoard($board)
	{
		if (empty($_SESSION['logged']))
		{
			die("NOT LOGGED IN");
		}
		if (($_SESSION['boards'] != "*") && ($_SESSION['type'] != 2))
		{
			$boards = explode(",", $_SESSION['boards']);
			if (in_array($board, $boards))
			{
				return 1;
			} else {
				die("CAN'T BOARD");
			}
		} else {
			return 1;
		}
	}

	function checkForUpdates()
	{
		if ((defined(MITSUBA_VERSION)) && (MITSUBA_VERSION != "disabled"))
		{
			//magic here
		}
	}

	function updateConfig($config)
	{
		foreach ($config as $key => $value)
		{
			$this->conn->query("UPDATE config SET value='".$this->conn->real_escape_string($value)."' WHERE name='".$key."';");
		}
	}
}

class Mitsuba
{
	private $conn;
	public $config;
	public $caching;
	public $common;
	public $posting;
	public $admin;

	function __construct($connection) {
		$this->conn = $connection;
		$this->config = $this->getConfig();
		include("caching.php");
		$this->caching = new \Mitsuba\Caching($this->conn, $this);
		include("common.php");
		$this->common = new \Mitsuba\Common($this->conn, $this);
		include("posting.php");
		$this->posting = new \Mitsuba\Posting($this->conn, $this);
		$this->admin = new Admin($this->conn, $this);
	}

	function getConfig()
	{
		$result = $this->conn->query("SELECT * FROM config;");
		$array = array();
		while ($row = $result->fetch_assoc())
		{
			$array[$row['name']] = $row['value'];
		}
		return $array;
	}

	function getConfigValue($name)
	{
		$name = $this->conn->real_escape_string($name);
		$result = $this->conn->query("SELECT * FROM config WHERE name='".$name."';");
		if ($result->num_rows == 1)
		{
			return $result->fetch_assoc();
		} else {
			return 0;
		}
	}

	function updateConfigValue($name, $value)
	{
		$name = $this->conn->real_escape_string($name);
		$value = $this->conn->real_escape_string($value);
		$this->conn->query("UPDATE config SET value='".$value."' WHERE name='".$name."';");
	}

	function getPath($path, $location, $relative)
	{
		if ($relative == 1)
		{
			return $path;
		}
		switch ($location)
		{
			case "index":
				return $path;
				break;
			case "board":
				return ".".$path;
				break;
			case "thread":
				return "../.".$path;
				break;
			default:
				return $path;
				break;
		}
	}
}
?>