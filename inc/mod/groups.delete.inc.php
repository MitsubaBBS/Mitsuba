<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("groups.delete");
		if (!empty($_GET['id']))
		{
					?>
<?php $mitsuba->admin->ui->startSection($lang['mod/group_want_delete']); ?>
<a href="?/groups"><?php echo $lang['mod/no_big']; ?></a> <a href="?/groups/delete_yes&id=<?php echo $_GET['id']; ?>"><?php echo $lang['mod/yes_big']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		} else {
						?>
<?php $mitsuba->admin->ui->startSection($lang['mod/group_not_exists']); ?>
<a href="?/groups"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		}
?>