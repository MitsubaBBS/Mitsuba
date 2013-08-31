<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
	if (isset($_GET['b']))
	{
		deleteEntry($conn, 1, $_GET['b']);
	?>
<?php $mitsuba->admin->ui->startSection($lang['mod/post_deleted']); ?>
<a href="?/news"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
		<?php
	}
?>