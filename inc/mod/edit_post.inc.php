<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("post.edit");
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && ($mitsuba->common->isBoard($_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$result = $conn->query("SELECT * FROM posts WHERE id=".$_GET['p']." AND board='".$_GET['b']."'");
			if ($result->num_rows == 1)
			{
			$row = $result->fetch_assoc();
			?>
<?php $mitsuba->admin->ui->startSection($lang['mod/edit_post']); ?>

			<form action="?/save_post" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
			<input type="hidden" name="b" value="<?php echo $_GET['b']; ?>" />
			<input type="hidden" name="p" value="<?php echo $_GET['p']; ?>" />
			<?php echo $lang['mod/text']; ?>: <textarea cols="50" rows="7" name="text"><?php echo $row['comment']; ?></textarea><br />
			<?php echo $lang['mod/options']; ?>: <input type="checkbox" name="raw" value="1" <?php if ($row['raw'] == 1) { echo "checked='checked'"; }?> /><?php echo $lang['mod/raw_html']; ?><br />
			<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
			</form>
<?php $mitsuba->admin->ui->endSection(); ?>
			<?php
			} else {
			
			}
		} else {
		
		}
?>