<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
/*
<?php echo $lang['mod/frontpage_style']; ?>: <select name="frontpage_style">
		<option value="0" <?php if ($config['frontpage_style'] == 0) { echo "selected"; } ?>>Kusaba X</option>
		<option value="1" <?php if ($config['frontpage_style'] == 1) { echo "selected"; } ?>>4chan</option></select><br />
		<?php echo $lang['mod/frontpage_url']; ?>: <input type="text" name="frontpage_url" value="<?php echo $config['frontpage_url']; ?>" /><br />
		<?php echo $lang['mod/frontpage_menu_url']; ?>: <input type="text" name="frontpage_menu_url"  value="<?php echo $config['frontpage_menu_url']; ?>" /><br />
		<?php echo $lang['mod/news_url']; ?>: <input type="text" name="news_url" value="<?php echo $config['news_url']; ?>" /><br />
		<?php echo $lang['mod/sitename']; ?>: <input type="text" name="sitename" value="<?php echo $config['sitename']; ?>"  /><br />
		*/

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

if ((isset($_POST['caching_mode'])) && (is_numeric($_POST['caching_mode'])))
{
	$config['caching_mode'] = $_POST['caching_mode'];
}

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