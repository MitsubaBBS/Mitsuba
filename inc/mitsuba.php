<?php
interface IPlugin {
	public function __construct($conn, &$mitsuba);
	public function getName();
	public function getUpdateURL();
}

class Admin
{
	private $conn;
	private $mitsuba;
	public $bans;
	public $boards;
	public $links;
	public $ui;
	public $users;

	function __construct($connection, &$mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
		include("admin.bans.php");
		$this->bans = new \Mitsuba\Admin\Bans($this->conn, $this->mitsuba);
		include("admin.boards.php");
		$this->boards = new \Mitsuba\Admin\Boards($this->conn, $this->mitsuba);
		include("admin.links.php");
		$this->links = new \Mitsuba\Admin\Links($this->conn, $this->mitsuba);
		include("admin.ui.php");
		$this->ui = new \Mitsuba\Admin\UI($this->conn, $this->mitsuba);
		include("admin.users.php");
		$this->users = new \Mitsuba\Admin\Users($this->conn, $this->mitsuba);
	}

	function reqPermission($permission, $uid = -1)
	{
		$groupid = 0;
		if ($uid != -1)
		{
			if (!is_numeric($uid))
			{
				die("Insufficient permissions");
			}
			$user = $this->conn->query("SELECT * FROM users WHERE id=".$uid);
			if ($user->num_rows != 1)
			{
				die("Insufficient permissions");
			}
			$userrow = $user->fetch_assoc();
			$groupid = $userrow['group'];
		} else {
			$groupid = $_SESSION['group'];
		}
		if (!$this->checkPermission($permission, $groupid))
		{
			die("Insufficient permissions");
		}
	}

	function checkPermission($permission, $groupid = false)
	{
		if ($groupid == false)
		{
			if (empty($_SESSION['group']))
			{
				return false;
			}
			$groupid = $_SESSION['group'];
		}
		$p = explode(".", $permission);
		$permission = $this->conn->query("SELECT * FROM group_permissions INNER JOIN permissions ON group_permissions.pid=permissions.id AND permissions.name='".$this->conn->real_escape_string($permission)."' WHERE gid=".$groupid);
		if ($permission->num_rows == 1)
		{
			return true;
		} elseif (count($p) > 1)
		{
			array_pop($p);
			return $this->checkPermission(implode(".", $p), $groupid);
		} else {
			return false;
		}
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
				$new_text = $this->conn->real_escape_string($pdata['comment'])."\n\n<+".$text."+>";
				$this->conn->query("UPDATE posts SET comment='".$new_text."', raw=2 WHERE id=".$postid." AND board='".$board."'");
				if ($pdata['resto'] == 0)
				{
					$this->mitsuba->caching->generateView($board, $pdata['id']);
					if ($config['caching_mode']==1)
					{
						$this->mitsuba->caching->forceGetThread($board, $pdata['id']);
					}
				} else {
					$this->mitsuba->caching->generateView($board, $pdata['resto']);
					if ($config['caching_mode']==1)
					{
						$this->mitsuba->caching->forceGetThread($board, $pdata['resto']);
					}
				}
				$this->mitsuba->caching->generateView($board);
			}
		}
	}

	function canBoard($board)
	{
		if (empty($_SESSION['logged']))
		{
			die("NOT LOGGED IN");
		}
		if ($_SESSION['boards'] != "%")
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

	function logAction($text)
	{
		$this->conn->query("DELETE FROM log WHERE date<".(time()-(60*60*24*7)));
		$text = $this->conn->real_escape_string($text);
		$this->conn->query("INSERT INTO log (date, event, mod_id) VALUES (".time().", '".$text."', ".$_SESSION['id'].")");
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
	private $plugins_array = array();
	private $conn;
	public $board;
	public $config;
	public $caching;
	public $common;
	public $posting;
	public $admin;

	function __construct($connection) {
		$this->conn = $connection;
		$this->config = $this->getConfig();
		$plugins = array();
		if ($array = glob("./plugins/*.php")) { $plugins = $array; }
		foreach ($plugins as $pluginname)
		{
			include($pluginname);
		}
		foreach (get_declared_classes() as $classname)
		{
			if (substr($classname, 0, 7) == "plugin_")
			{
				try {
					$plugin = new $classname($this->conn, $this);
					if ($plugin instanceof IPlugin)
					{
						$this->plugins_array[] = $plugin;
					} 
				} catch (Exception $e)
				{
					//we do nothing because we can't
				}
			}
		}
		include("board.php");
		$this->board = new \Mitsuba\Board($this->conn, $this);
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

	function triggerEvent($event, &$eventData)
	{
		foreach ($this->plugins_array as $class)
		{
			if ($class instanceof IPlugin)
			{
				if (method_exists($class, $event))
				{
					$class->$event($eventData);
				}
			}
		}
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
		if ($relative == 0)
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