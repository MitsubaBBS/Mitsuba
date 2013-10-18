<?php
namespace Mitsuba\Admin;
class Users {
	private $conn;
	private $mitsuba;

	function __construct($connection, &$mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
	}

	function canDoBoard($short)
	{
		if ($_SESSION['boards'] != "%")
		{
			$boards = explode(",", $_SESSION['boards']);
		} else {
			$boards = "%";
		}

		if (($boards == "%") || (in_array($short, $boards)))
		{
			return 1;
		} else {
			return 0;
		}
	}

	function addUser($username, $password, $group, $boards)
	{
		$username = $this->conn->real_escape_string($username);
		if (!$this->mitsuba->admin->groups->isGroup($group))
		{
			return -1;
		}
		$boards = $this->conn->real_escape_string($boards);
		$salt = $this->conn->real_escape_string($this->mitsuba->common->randomSalt());
		$password = hash("sha512", $password.$salt);
		$result = $this->conn->query("INSERT INTO users (`username`, `password`, `salt`, `group`, `boards`) VALUES ('".$username."', '".$password."', '".$salt."', ".$group.", '".$boards."')");
		if ($result)
		{
			return 1;
		} else {
			return 0;
		}
	}

	function delUser($id)
	{
		if (!is_numeric($id))
		{
			return -1;
		}
		$this->conn->query("DELETE FROM users WHERE id=".$id);
		$this->conn->query("DELETE FROM notes WHERE mod_id=".$id);
	}

	function updateUser($id, $username, $password, $group, $boards)
	{
		if (!is_numeric($id))
		{
			return -1;
		}
		if (!$this->mitsuba->admin->groups->isGroup($group))
		{
			return -1;
		}
		$user = $this->conn->query("SELECT * FROM users WHERE id=".$id);
		if ($user->num_rows == 1)
		{
			$userdata = $user->fetch_assoc();
			$username = $this->conn->real_escape_string($username);
			$password_db = "";
			if (!empty($password))
			{
				$password_db = ", password='".hash("sha512", $password.$userdata['salt'])."'";
			}
			$boards = $this->conn->real_escape_string($boards);
			$this->conn->query("UPDATE users SET username='".$username."'".$password_db.", `group`=".$group.", boards='".$boards."' WHERE id=".$id);
		}
	}

	function isUser($id)
	{
		if (!is_numeric($id))
		{
			return 0;
		}
		$result = $this->conn->query("SELECT * FROM users WHERE id=".$this->conn->real_escape_string($id));
		if ($result->num_rows == 1)
		{
			$row = $result->fetch_assoc();
			return $row['username'];
		} else {
			return 0;
		}
	}
}
?>