<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("ads.update");
if ((!empty($_GET['i'])) && (is_numeric($_GET['i'])))
{
	$addata = $conn->query("SELECT * FROM ads WHERE id=".$_GET['i']);
	if ($addata->num_rows == 1) { $addata = $addata->fetch_assoc(); } else {
		$mitsuba->admin->ui->startSection($lang['mod/ad_not_found']);
		$mitsuba->admin->ui->endSection();
	}
?>
<?php $mitsuba->admin->ui->startSection($lang['mod/edit_ad']); ?>
<form action="?/ads" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<input type="hidden" name="mode" value="edit">
<input type="hidden" name="id" value="<?php echo $addata['id']; ?>">
<?php echo $lang['mod/board']; ?>: 
<select name="board">
<option value='%'>All boards</option>
<?php
$result = $conn->query("SELECT * FROM boards;");
while ($row = $result->fetch_assoc())
{
	$selected = "";
	if ($addata['board'] == $row['short'])
	{
		$selected = " selected='selected'";
	}
	echo "<option value='".$row['short']."'".$selected.">/".$row['short']."/ - ".$row['name']."</option>";
}
?>
</select><br />
<?php echo $lang['mod/position']; ?>:
<select name="position">
<option value='head'<?php if ($addata['position']=="head") { echo " selected='selected'"; }?>><?php echo $lang['mod/pos_head']; ?></option>
<option value='aboveform'<?php if ($addata['position']=="aboveform") { echo " selected='selected'"; }?>><?php echo $lang['mod/pos_aboveform']; ?></option>
<option value='underform'<?php if ($addata['position']=="underform") { echo " selected='selected'"; }?>><?php echo $lang['mod/pos_underform']; ?></option>
<option value='footer'<?php if ($addata['position']=="footer") { echo " selected='selected'"; }?>><?php echo $lang['mod/pos_footer']; ?></option>
<option value='bottom'<?php if ($addata['position']=="bottom") { echo " selected='selected'"; }?>><?php echo $lang['mod/pos_bottom']; ?></option>
<option value='rules'<?php if ($addata['position']=="rules") { echo " selected='selected'"; }?>><?php echo $lang['mod/pos_rules']; ?></option>
</select><br />
<?php echo $lang['mod/text']; ?>:
<textarea name="text" cols="70" rows="10"><?php echo $addata['text']; ?></textarea><br />
<?php echo $lang['mod/shown']; ?>: <input type="checkbox" name="shown" value="1" <?php if ($addata['show']==1) { echo " checked"; }?> /><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>
<?php
} else {
	$mitsuba->admin->ui->startSection($lang['mod/ad_not_found']);
	$mitsuba->admin->ui->endSection();
}
?>