<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		if ((!empty($_POST['bans'])) && ($_POST['bans']==1))
		{
			$conn->query("DELETE FROM bans WHERE expires<".time());
		}
		if ((!empty($_POST['warnings'])) && ($_POST['warnings']==1))
		{
			$conn->query("DELETE FROM warnings WHERE shown=1");
		}
		?>
					<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/cleaning_done']; ?></h2></div>
<div class="boxcontent">
<a href="?/cleaner"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>