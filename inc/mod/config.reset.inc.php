<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("config.reset");
$mitsuba->admin->ui->checkToken($_POST['token']);

$config = array();

$config['frontpage_style'] = 0;
$config['frontpage_url'] = "index.html";
$config['frontpage_menu_url'] = "menu.html";
$config['news_url'] = "news.html";
$config['sitename'] = "Mitsuba";
$config['enable_api'] = 0;
$config['enable_meny'] = 0;
$config['caching_mode'] = "kusabalike.php";

$mitsuba->admin->updateConfig($conn, $config);

?>
<?php $mitsuba->admin->ui->startSection($lang['mod/config_updated']); ?>

<a href="?/config"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>