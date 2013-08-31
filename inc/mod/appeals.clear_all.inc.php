<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("appeals.clear.all");
		?>
	
<?php $mitsuba->admin->ui->startSection($lang['mod/want_clear_appeals']); ?>
<a href="?/appeals"><?php echo $lang['mod/no_big']; ?></a> <a href="?/appeals&m=clear_all_yes"><?php echo $lang['mod/yes_big']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
		<?php