<?php
class Admin
{
	private $conn;
	private $mitsuba;
	private $user_permissions;
	public $bans;
	public $boards;
	public $groups;
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
		include("admin.groups.php");
		$this->groups = new \Mitsuba\Admin\Groups($this->conn, $this->mitsuba);
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
		$e = array("permission" => $permission);
		$this->mitsuba->emitEvent("permission", $e);
		if (!$this->checkPermission($permission, $groupid))
		{
			die("Insufficient permissions");
		}
	}

	function listPermissions($groupid = false)
	{
		if ($groupid == false)
		{
			if (empty($_SESSION['group']))
			{
				return false;
			}
			$groupid = $_SESSION['group'];
		}
		$permissions = $this->conn->query("SELECT * FROM group_permissions INNER JOIN permissions ON group_permissions.pid=permissions.id WHERE gid=".$groupid);
		$list = array();
		while ($row = $permissions->fetch_assoc())
		{
			$list[$row['name']] = 1;
		}
		return $list;
	}

	function checkPermission($permission, $groupid = false)
	{
		if ($groupid == false)
		{
			if (!empty($this->user_permissions))
			{
				$groupid = $this->user_permissions;
			} else {
				$groupid = $this->listPermissions();
				if ($groupid == false) {
					return false;
				}
			}
		}
		if (is_array($groupid))
		{
			$p = explode(".", $permission);
			if (!empty($groupid[$permission]))
			{
				return true;
			} elseif (count($p) > 1) {
				array_pop($p);
				return $this->checkPermission(implode(".", $p), $groupid);
			} else {
				return false;
			}
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
	public $module_config;
	public $posting;
	public $admin;

	function __construct($connection) {
		$this->conn = $connection;
		$this->config = $this->getConfig();
		$this->module_config = $this->getModuleConfig();
		include("board.php");
		$this->board = new \Mitsuba\Board($this->conn, $this);
		include("caching.php");
		$this->caching = new \Mitsuba\Caching($this->conn, $this);
		include("common.php");
		$this->common = new \Mitsuba\Common($this->conn, $this);
		include("posting.php");
		$this->posting = new \Mitsuba\Posting($this->conn, $this);
		$this->admin = new Admin($this->conn, $this);
		$modules = $this->conn->query("SELECT * FROM module_classes");
		while ($module = $modules->fetch_assoc())
		{
			include("./".$module['namespace']."/".$module['file']);
			$this->$module['name'] = new $module['class']($this->conn, $this->mitsuba);
		}
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

	function getModuleConfig()
	{
		$result = $this->conn->query("SELECT * FROM module_config;");
		$array = array();
		while ($row = $result->fetch_assoc())
		{
			$array[$row['namespace'].".".$row['name']] = $row['value'];
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

	function updateModuleConfigValue($namespace, $name, $value)
	{
		$namespace = $this->conn->real_escape_string($namespace);
		$name = $this->conn->real_escape_string($name);
		$value = $this->conn->real_escape_string($value);
		$this->conn->query("UPDATE module_config SET value='".$value."' WHERE name='".$name."' AND namespace='".$namespace."';");
	}

	function emitEvent($name, &$data)
	{
		$modules = $this->conn->query("SELECT * FROM module_events WHERE event='".$this->conn->real_escape_string($name)."'");
		while ($module = $modules->fetch_assoc())
		{
			include("./modules/".$module['namespace']."/".$module['file']);
			$eventclass = new $module['class']($this->conn, $this);
			$eventclass->$module['method']($name, $data);
		}
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