<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("reports.clear.all");
		?>
	
<?php $mitsuba->admin->ui->startSection($lang['mod/want_clear_reports']); ?>
<a href="?/reports"><?php echo $lang['mod/no_big']; ?></a> <a href="?/reports/clear_all_yes"><?php echo $lang['mod/yes_big']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>