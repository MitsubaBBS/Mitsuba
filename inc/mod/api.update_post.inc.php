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
				$raw = 0;
				if ((isset($_POST['raw'])) && ($_POST['raw'] == 1))
				{
					$raw = 1;
				}
				$conn->query("UPDATE posts SET comment='".$mitsuba->common->preprocessComment($_POST['comment'])."', raw=".$raw." WHERE id=".$_GET['p']." AND board='".$_GET['b']."'");
				$resto = $row['resto'];
				if ($row['resto'] == 0)
				{
					$mitsuba->caching->generateView($_GET['b'], $row['id']);
					$mitsuba->caching->generateCatalog($_GET['b']);
					if ($config['caching_mode']==1)
					{
						$mitsuba->caching->forceGetThread($_GET['b'], $row['id']);
					}
					if ($config['enable_api']==1)
					{
						$mitsuba->caching->serializeThread($_GET['b'], $row['id']);
					}
					$resto = $row['id'];
				} else {
					$mitsuba->caching->generateView($_GET['b'], $row['resto']);
					if ($config['caching_mode']==1)
					{
						$mitsuba->caching->forceGetThread($_GET['b'], $row['resto']);
					}
					if ($config['enable_api']==1)
					{
						$mitsuba->caching->serializeThread($_GET['b'], $row['resto']);
					}
				}
				if ($config['enable_api']==1)
				{
					$mitsuba->caching->serializeBoard($_GET['b']);
				}
				$mitsuba->caching->generateView($_GET['b']);
			}
		} else {
			echo json_encode(array('error' => 404));
		}
?>