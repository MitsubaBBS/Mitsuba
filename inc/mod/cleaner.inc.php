<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/cleaner']; ?></h2></div>
<div class="boxcontent">
<form action="?/cleaner/do" method="POST">
<input type="checkbox" name="bans" value=1><?php echo $lang['mod/delete_expired_bans']; ?></input><br />
<input type="checkbox" name="warnings" value=1><?php echo $lang['mod/delete_shown_warnings']; ?></input><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>"><br />
</form>
</div>
</div>
</div>