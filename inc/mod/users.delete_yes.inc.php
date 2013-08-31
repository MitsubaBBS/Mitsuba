<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("users.delete");
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$id = $_GET['id'];
			if ($username = $mitsuba->admin->users->isUser($id))
			{
				$mitsuba->admin->users->delUser($id);
				$mitsuba->admin->logAction(sprintf($lang['log/deleted_user'], $username));
					?>
<?php $mitsuba->admin->ui->startSection($lang['mod/user_deleted']); ?>
<a href="?/users"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			} else {
			
					?>
<?php $mitsuba->admin->ui->startSection($lang['mod/user_not_exists']); ?>
<a href="?/users"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			}
			
		}
?>