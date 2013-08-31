<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("config.global_message");
		if (isset($_POST['message']))
		{
			$mitsuba->admin->ui->checkToken($_POST['token']);
			$mitsuba->updateConfigValue("global_message", $_POST['message']);
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/global_message_updated']); ?>

<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<a href="?/message"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		} else {
		$config = $mitsuba->config;
		$msg = $config['global_message'];
		
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/edit_global_message']); ?>

<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
		<form action="?/message" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
		<textarea cols=70 rows=14 name="message"><?php echo $msg; ?></textarea><br />
		<input type="submit" value="<?php echo $lang['mod/submit']; ?>">
		</form>
		<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		}
?>