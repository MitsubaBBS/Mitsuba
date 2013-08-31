<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("config.update");
$mitsuba->admin->ui->checkToken($_POST['token']);
$config = array();
if (!empty($_POST['frontpage_style']))
{
	$config['frontpage_style'] = $_POST['frontpage_style'];
}

if (!empty($_POST['frontpage_url']))
{
	$config['frontpage_url'] = $_POST['frontpage_url'];
}

if (!empty($_POST['frontpage_menu_url']))
{
	$config['frontpage_menu_url'] = $_POST['frontpage_menu_url'];
}

if (!empty($_POST['news_url']))
{
	$config['news_url'] = $_POST['news_url'];
}

if (!empty($_POST['sitename']))
{
	$config['sitename'] = $_POST['sitename'];
}

if (!empty($_POST['enable_api']))
{
	$config['enable_api'] = 1;
} else {
	$config['enable_api'] = 0;
}

if (!empty($_POST['enable_meny']))
{
	$config['enable_meny'] = 1;
} else {
	$config['enable_meny'] = 0;
}

if (isset($_POST['caching_mode']))
{
	$config['caching_mode'] = $_POST['caching_mode'];
}

$mitsuba->admin->updateConfig($config);

?>
<?php $mitsuba->admin->ui->startSection($lang['mod/config_updated']); ?>

<a href="?/config"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>