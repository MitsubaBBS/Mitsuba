<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}

		reqPermission(2);
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$conn->query("DELETE FROM ip_notes WHERE id=".$_GET['id']);
		}
		?>
		<meta http-equiv="refresh" content="0;URL='?/ipnotes'" />