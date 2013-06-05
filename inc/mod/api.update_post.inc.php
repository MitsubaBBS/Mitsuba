<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$result = $conn->query("SELECT * FROM posts WHERE id=".$_GET['p']." AND board='".$_GET['b']."'");
			if ($result->num_rows == 1)
			{
				$row = $result->fetch_assoc();
				$raw = 0;
				if ((isset($_POST['raw'])) && ($_POST['raw'] == 1))
				{
					$raw = 1;
				}
				$conn->query("UPDATE posts SET comment='".preprocessComment($conn, $_POST['comment'])."', raw=".$raw." WHERE id=".$_GET['p']." AND board='".$_GET['b']."'");
				$resto = $row['resto'];
				if ($row['resto'] == 0)
				{
					$cacher->generateView($_GET['b'], $row['id']);
					if ($config['caching_mode']==1)
					{
						$cacher->forceGetThread($_GET['b'], $row['id']);
					}
					if ($config['enable_api']==1)
					{
						serializeThread($conn, $_GET['b'], $row['id']);
					}
					$resto = $row['id'];
				} else {
					$cacher->generateView($_GET['b'], $row['resto']);
					if ($config['caching_mode']==1)
					{
						$cacher->forceGetThread($_GET['b'], $row['resto']);
					}
					if ($config['enable_api']==1)
					{
						serializeThread($conn, $_GET['b'], $row['resto']);
					}
				}
				$cacher->generateView($_GET['b']);
			}
		} else {
			echo json_encode(array('error' => 404));
		}
?>