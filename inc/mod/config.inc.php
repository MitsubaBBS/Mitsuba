<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
$config = getConfig($conn);
		?>
				<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/configuration']; ?></h2></div>
<div class="boxcontent">
<a href="?/config/reset">Reset config</a>
		<form action="?/config/update" method="POST">
		<?php echo $lang['mod/frontpage_style']; ?>: <select name="frontpage_style">
		<option value="0" <?php if ($config['frontpage_style'] == 0) { echo "checked"; } ?>>Kusaba X</option>
		<option value="1" <?php if ($config['frontpage_style'] == 1) { echo "checked"; } ?>>4chan</option></select><br />
		<?php echo $lang['mod/frontpage_url']; ?>: <input type="text" name="frontpage_url" value="<?php echo $config['frontpage_url']; ?>" /><br />
		<?php echo $lang['mod/frontpage_menu_url']; ?>: <input type="text" name="frontpage_menu_url"  value="<?php echo $config['frontpage_menu_url']; ?>" /><br />
		<?php echo $lang['mod/news_url']; ?>: <input type="text" name="news_url" value="<?php echo $config['news_url']; ?>" /><br />
		<?php echo $lang['mod/sitename']; ?>: <input type="text" name="sitename" value="<?php echo $config['sitename']; ?>"  /><br />
		<?php echo $lang['mod/enable_api']; ?>: <input type="checkbox" name="enable_api" value="1" <?php if ($config['enable_api']==1) { echo "checked"; } ?> /><br />
		<?php echo $lang['mod/caching_mode']; ?>: <input type="radio" name="caching_mode" value="0" checked /> Normal <input type="radio" name="caching_mode" value="1" <?php if ($config['caching_mode']==1) { echo "checked"; } ?> /> <?php echo $lang['mod/super_caching']; ?> <input type="radio" name="caching_mode" value="2" <?php if ($config['caching_mode']==2) { echo "checked"; } ?> /> <?php echo $lang['mod/apc']; ?> <input type="radio" name="caching_mode" value="3" <?php if ($config['caching_mode']==3) { echo "checked"; } ?> /> <?php echo $lang['mod/apc_memcached']; ?><br />
		<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
		</form>
		</div>
		</div>
		</div>