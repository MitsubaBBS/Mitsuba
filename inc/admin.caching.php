<?php
function rebuildBoardLinks($conn)
{
	updateConfig($conn, "boardLinks", generateBoardLinks($conn));
	updateConfig($conn, "boardLinks_thread", generateBoardLinks($conn, 1));
	updateConfig($conn, "boardLinks_index", generateBoardLinks($conn, 2));
}

function rebuildStyles($conn)
{
	updateConfig($conn, "styles", generateStyles($conn));
	updateConfig($conn, "styles_thread", generateStyles($conn, 1));
}

function generatePost($conn, $board, $id)
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
			generateView($conn, $board, $post['id']);
		} else {
			generateView($conn, $board, $post['resto']);
		}
		generateView($conn, $board);
	}
}
?>