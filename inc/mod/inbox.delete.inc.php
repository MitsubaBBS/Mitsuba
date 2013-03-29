<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}

		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$conn->query("DELETE FROM pm WHERE id=".$_GET['id']." AND to_user=".$_SESSION['id']);
			?>
			<script type="text/javascript">parent.nav.location.reload();</script>
			<meta http-equiv="refresh" content="0;URL='?/inbox'" />
			<?php
		}
?>