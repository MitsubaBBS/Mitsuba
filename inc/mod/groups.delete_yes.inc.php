<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("groups.delete");
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$id = $_GET['id'];
			if ($name = $mitsuba->admin->groups->isGroup($id))
			{
				$mitsuba->admin->groups->delGroup($id);
				$mitsuba->admin->logAction(sprintf($lang['log/deleted_group'], $name));
					?>
<?php $mitsuba->admin->ui->startSection($lang['mod/group_deleted']); ?>
<a href="?/groups"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			} else {
			
					?>
<?php $mitsuba->admin->ui->startSection($lang['mod/group_not_exists']); ?>
<a href="?/groups"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			}
			
		}
?>