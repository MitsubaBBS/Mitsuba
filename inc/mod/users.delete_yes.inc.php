<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission(3);
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$id = $_GET['id'];
			if ($username = $mitsuba->admin->users->isUser($id))
			{
				$mitsuba->admin->users->delUser($id);
				logAction($conn, sprintf($lang['log/deleted_user'], $username));
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_deleted']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			} else {
			
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_not_exists']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			}
			
		}
?>