<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);

$config = array();

$config['frontpage_style'] = 0;
$config['frontpage_url'] = "index.html";
$config['frontpage_menu_url'] = "menu.html";
$config['news_url'] = "news.html";
$config['sitename'] = "Mitsuba";
$config['enable_api'] = 0;
$config['caching_mode'] = 0;

updateConfig($conn, $config);

?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/config_updated']; ?></h2></div>
<div class="boxcontent">
<a href="?/config"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>