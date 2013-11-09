<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("groups.add");
$mitsuba->admin->ui->checkToken($_POST['token']);
		if ((!empty($_POST['username'])) && (!empty($_POST['password'])) && (is_numeric($_POST['type'])))
		{
			//STUFF
			$result = $mitsuba->admin->groups->addGroup(STUFF);
			if ($result == 1)
			{
				$mitsuba->admin->logAction(sprintf($lang['log/group_added'], $conn->real_escape_string($_POST['username'])));
			?>
<?php $mitsuba->admin->ui->startSection($lang['mod/group_added']); ?>
<a href="?/users"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			} else {
			?>
<?php $mitsuba->admin->ui->startSection($lang['mod/group_exists']); ?>
<a href="?/users"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			}
		} else {
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/fill_all_fields']); ?>
<a href="?/users"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		}
?>