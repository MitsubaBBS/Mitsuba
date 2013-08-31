<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("users.delete");
		if (!empty($_GET['id']))
		{
					?>
<?php $mitsuba->admin->ui->startSection($lang['mod/user_want_delete']); ?>
<a href="?/users"><?php echo $lang['mod/no_big']; ?></a> <a href="?/users/delete_yes&id=<?php echo $_GET['id']; ?>"><?php echo $lang['mod/yes_big']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		} else {
						?>
<?php $mitsuba->admin->ui->startSection($lang['mod/user_not_exists']); ?>
<a href="?/users"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		}
?>