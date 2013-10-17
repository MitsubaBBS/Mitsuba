<?php
namespace Mitsuba\Admin;
class Groups {
	private $conn;
	private $mitsuba;

	function __construct($connection, &$mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
	}

	function isGroup($id)
	{
		if (!is_numeric($id))
		{
			return 0;
		}
		$result = $this->conn->query("SELECT * FROM groups WHERE id=".$this->conn->real_escape_string($id));
		if ($result->num_rows == 1)
		{
			$row = $result->fetch_assoc();
			return $row['name'];
		} else {
			return 0;
		}
	}

	function addGroup($name, $capcode, $capcode_style, $capcode_icon)
	{
		$name = $this->conn->real_escape_string($name);
		$capcode = $this->conn->real_escape_string($capcode);
		$capcode_style = $this->conn->real_escape_string($capcode_style);
		$capcode_icon = $this->conn->real_escape_string($capcode_icon);
		$result = $this->conn->query("INSERT INTO groups (`name`, `capcode`, `capcode_style`, `capcode_icon`) VALUES ('".$name."', '".$capcode."', '".$capcode_style."', '".$capcode_icon."')");
		if ($result)
		{
			return 1;
		} else {
			return 0;
		}
	}

	function updateGroup($id, $name, $capcode, $capcode_style, $capcode_icon)
	{
		if (!is_numeric($id))
		{
			return -1;
		}
		$group = $this->conn->query("SELECT * FROM groups WHERE id=".$id);
		if ($group->num_rows == 1)
		{
			$group = $group->fetch_assoc();
			$name = $this->conn->real_escape_string($name);
			$capcode = $this->conn->real_escape_string($capcode);
			$capcode_style = $this->conn->real_escape_string($capcode_style);
			$capcode_icon = $this->conn->real_escape_string($capcode_icon);
			$this->conn->query("UPDATE groups SET name='".$name."', capcode='".$capcode."', capcode_style='".$capcode_style."', capcode_icon='".$capcode_icon."' WHERE id=".$id);
		}
	}

	function delGroup($id)
	{
		if (!is_numeric($id))
		{
			return -1;
		}
		$this->conn->query("DELETE FROM groups WHERE id=".$id);
		//MAYBE: delete users
	}
}
?>