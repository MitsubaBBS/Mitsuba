<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("post.delete.ip");
if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
	?>
	
<?php $mitsuba->admin->ui->startSection(sprintf($lang['mod/want_delete_ip'], $_GET['ip'])); ?>
<a href="?/info&ip=<?php echo $_GET['ip']; ?>"><?php echo $lang['mod/no_big']; ?></a> <a href="?/delete_posts/yes&ip=<?php echo $_GET['ip']; ?>"><?php echo $lang['mod/yes_big']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		}
?>