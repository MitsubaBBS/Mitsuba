<?php
function generatePost($conn, $cacher, $board, $id)
{
	if ((empty($id)) || (!is_numeric($id)))
	{
		return -15;
	}
	if ((empty($id)) || (!isBoard($conn, $board)))
	{
		return -16;
	}
	$result = $conn->query("SELECT * FROM posts WHERE id=".$id." AND board='".$board."'");
	if ($result->num_rows == 1)
	{
		$post = $result->fetch_assoc();
		if ($post['resto'] == 0)
		{
			$cacher->generateView($board, $post['id']);
		} else {
			$cacher->generateView($board, $post['resto']);
		}
		$cacher->generateView($board);
	}
}
?>