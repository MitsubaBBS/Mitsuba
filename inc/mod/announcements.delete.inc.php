<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
		if (isset($_GET['b']))
		{
			deleteEntry($conn, 0, $_GET['b']);
	?>
<?php $mitsuba->admin->ui->startSection($lang['mod/post_deleted_short']); ?>
<a href="?/announcements"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		}
?>