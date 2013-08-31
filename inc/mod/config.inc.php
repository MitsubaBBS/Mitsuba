<?php
$mitsuba->admin->reqPermission("config.view");
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$config = $mitsuba->config;
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/configuration']); ?>

<form action="?/config/reset" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<input type="submit" value="Reset config" />
</form>
<form action="?/config/update" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/frontpage_style']; ?>: <select name="frontpage_style">
<?php
$styles = glob('./inc/frontpage/*.php', GLOB_BRACE);
foreach ($styles as $style)
{
	echo "<option value='".basename($style)."'>".basename($style)."</option>";
}
?></select><br />
<?php echo $lang['mod/frontpage_url']; ?>: <input type="text" name="frontpage_url" value="<?php echo $config['frontpage_url']; ?>" /><br />
<?php echo $lang['mod/frontpage_menu_url']; ?>: <input type="text" name="frontpage_menu_url"  value="<?php echo $config['frontpage_menu_url']; ?>" /><br />
<?php echo $lang['mod/news_url']; ?>: <input type="text" name="news_url" value="<?php echo $config['news_url']; ?>" /><br />
<?php echo $lang['mod/sitename']; ?>: <input type="text" name="sitename" value="<?php echo $config['sitename']; ?>"  /><br />
<?php echo $lang['mod/enable_api']; ?>: <input type="checkbox" name="enable_api" value="1" <?php if ($config['enable_api']==1) { echo "checked"; } ?> /><br />
<?php echo $lang['mod/caching_mode']; ?>: <input type="radio" name="caching_mode" value="0" checked /> Normal <input type="radio" name="caching_mode" value="1" <?php if ($config['caching_mode']==1) { echo "checked"; } ?> /> <?php echo $lang['mod/super_caching']; ?> <input type="radio" name="caching_mode" value="2" <?php if ($config['caching_mode']==2) { echo "checked"; } ?> /> <?php echo $lang['mod/apc']; ?> <input type="radio" name="caching_mode" value="3" <?php if ($config['caching_mode']==3) { echo "checked"; } ?> /> <?php echo $lang['mod/apc_memcached']; ?><br />
<?php echo $lang['mod/enable_meny']; ?>: <input type="checkbox" name="enable_meny" value="1" <?php if ($config['enable_meny']==1) { echo "checked"; } ?> /><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>

<?php $mitsuba->admin->ui->startSection($lang['mod/extras']); ?>
<form action="?/config/extras&m=ecaptcha" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<input type="submit" value="<?php echo $lang['mod/captcha_enable_all']; ?>" />
</form>
<form action="?/config/extras&m=dcaptcha" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<input type="submit" value="<?php echo $lang['mod/captcha_disable_all']; ?>" />
</form>
<br />
<form action="?/config/extras&m=ecatalog" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<input type="submit" value="<?php echo $lang['mod/catalog_enable_all']; ?>" />
</form>
<form action="?/config/extras&m=dcatalog" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<input type="submit" value="<?php echo $lang['mod/catalog_disable_all']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>