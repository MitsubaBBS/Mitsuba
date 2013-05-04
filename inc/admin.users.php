<?php

function canDoBoard($short)
{
if (($_SESSION['boards'] != "*") && ($_SESSION['type'] != 2))
{
$boards = explode(",", $_SESSION['boards']);
} else {
$boards = "*";
}

if (($boards == "*") || (in_array($short, $boards)))
{
return 1;
} else {
return 0;
}
}

function addUser($conn, $username, $password, $type, $boards)
{
	$username = $conn->real_escape_string($username);
	$password = hash("sha512", $password);
	if (!is_numeric($type))
	{
		return -1;
	}
	$boards = $conn->real_escape_string($boards);
	$result = $conn->query("INSERT INTO users (username, password, type, boards) VALUES ('".$username."', '".$password."', ".$type.", '".$boards."')");
	if ($result)
	{
		return 1;
	} else {
		return 0;
	}
}

function delUser($conn, $id)
{
	if (!is_numeric($id))
	{
		return -1;
	}
	$conn->query("DELETE FROM users WHERE id=".$id);
	$conn->query("DELETE FROM notes WHERE mod_id=".$id);
}

function updateUser($conn, $id, $username, $password, $type, $boards)
{
	if (!is_numeric($id))
	{
		return -1;
	}
	if (!is_numeric($type))
	{
		return -1;
	}
	$username = $conn->real_escape_string($username);
	$password_db = "";
	if (!empty($password))
	{
		$password_db = ", password='".hash("sha512", $password)."'";
	}
	
	$type = $conn->real_escape_string($type);
	$boards = $conn->real_escape_string($boards);
	$conn->query("UPDATE users SET username='".$username."'".$password_db.", type=".$type.", boards='".$boards."' WHERE id=".$id);

}

function isUser($conn, $id)
{
	if (!is_numeric($id))
	{
		return 0;
	}
	$result = $conn->query("SELECT * FROM users WHERE id=".$conn->real_escape_string($id));
	if ($result->num_rows == 1)
	{
		$row = $result->fetch_assoc();
		return $row['username'];
	} else {
		return 0;
	}
}

?>