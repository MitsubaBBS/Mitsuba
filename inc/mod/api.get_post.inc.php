<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("post.edit");
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && ($mitsuba->common->isBoard($_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$result = $conn->query("SELECT * FROM posts WHERE id=".$_GET['p']." AND board='".$_GET['b']."'");
			if ($result->num_rows == 1)
			{
				$row = $result->fetch_assoc();
				echo json_encode(array('comment' => htmlspecialchars($row['comment']), 'raw' => $row['raw'], 'id' => $row['id']));
			} else {
				echo json_encode(array('error' => 404));
			}
		} else {
			echo json_encode(array('error' => 404));
		}
?>