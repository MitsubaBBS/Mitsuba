<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		if (isset($_POST['message']))
		{
			updateConfigValue($conn, "global_message", $_POST['message']);
		?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/global_message_updated']; ?></h2></div>
<div class="boxcontent">
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<a href="?/message"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
		<?php
		} else {
		$config = getConfig($conn);
		$msg = $config['global_message'];
		
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/edit_global_message']; ?></h2></div>
<div class="boxcontent">
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
		<form action="?/message" method="POST">
		<textarea cols=70 rows=14 name="message"><?php echo $msg; ?></textarea><br />
		<input type="submit" value="<?php echo $lang['mod/submit']; ?>">
		</form>
		</div>
		</div>
		</div>
		</div>
		<?php
		}
?>