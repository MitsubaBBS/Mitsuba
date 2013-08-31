<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("reports.clear.all");
		$conn->query("TRUNCATE TABLE reports;");
		?>
		<meta http-equiv="refresh" content="0;URL='?/reports'" />