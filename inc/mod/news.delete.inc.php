<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission(2);
	if (isset($_GET['b']))
	{
		if ($_SESSION['type']==3)
		{
			deleteEntry($conn, 1, $_GET['b']);
		} else {
			deleteEntry($conn, 1, $_GET['b'], 1);
		}
	?>
<?php $mitsuba->admin->ui->startSection($lang['mod/post_deleted']); ?>
<a href="?/news"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
		<?php
	}
?>