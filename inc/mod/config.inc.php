<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(2);
$config = getConfig($conn);
		?>
				<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/configuration']; ?></h2></div>
<div class="boxcontent">
<a href="?/config/reset">Reset config</a>
		<form action="?/config/update" method="POST">
		<?php echo $lang['mod/frontpage_style']; ?>: <select name="frontpage_style">
		<option value="0" <?php if ($config['frontpage_style'] == 0) { echo "selected"; } ?>>Kusaba X</option>
		<option value="1" <?php if ($config['frontpage_style'] == 1) { echo "selected"; } ?>>4chan</option></select><br />
		<?php echo $lang['mod/frontpage_url']; ?>: <input type="text" name="frontpage_url" value="<?php echo $config['frontpage_url']; ?>" /><br />
		<?php echo $lang['mod/frontpage_menu_url']; ?>: <input type="text" name="frontpage_menu_url"  value="<?php echo $config['frontpage_menu_url']; ?>" /><br />
		<?php echo $lang['mod/news_url']; ?>: <input type="text" name="news_url" value="<?php echo $config['news_url']; ?>" /><br />
		<?php echo $lang['mod/sitename']; ?>: <input type="text" name="sitename" value="<?php echo $config['sitename']; ?>"  /><br />
		<?php echo $lang['mod/enable_api']; ?>: <input type="checkbox" name="enable_api" value="1" <?php if ($config['enable_api']==1) { echo "checked"; } ?> /><br />
		<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
		</form>
		</div>
		</div>
		</div>