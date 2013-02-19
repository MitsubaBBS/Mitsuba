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
	$username = mysqli_real_escape_string($conn, $username);
	$password = hash("sha512", $password);
	if (!is_numeric($type))
	{
		return -1;
	}
	$boards = mysqli_real_escape_string($conn, $boards);
	$result = mysqli_query($conn, "INSERT INTO users (username, password, type, boards) VALUES ('".$username."', '".$password."', ".$type.", '".$boards."')");
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
	mysqli_query($conn, "DELETE FROM users WHERE id=".$id);
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
	$username = mysqli_real_escape_string($conn, $username);
	$password = hash("sha512", $password);
	$type = mysqli_real_escape_string($conn, $type);
	$boards = mysqli_real_escape_string($conn, $boards);
	mysqli_query($conn, "UPDATE users SET username='".$username."', password='".$password."', type=".$type.", boards='".$boards."' WHERE id=".$id);

}

function isUser($conn, $id)
{
	if (!is_numeric($id))
	{
		return 0;
	}
	$result = mysqli_query($conn, "SELECT * FROM users WHERE id=".mysqli_real_escape_string($conn, $id));
	if (mysqli_num_rows($result) == 1)
	{
		return 1;
	} else {
		return 0;
	}
}

?>