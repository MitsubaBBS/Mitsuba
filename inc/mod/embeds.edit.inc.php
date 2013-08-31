<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("embeds.edit");
		if (!empty($_GET['n']))
		{
		$result = $conn->query("SELECT * FROM embeds WHERE name='".$conn->real_escape_string($_GET['n'])."'");
		if ($result->num_rows == 1)
		{
		$binfo = $result->fetch_assoc();
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/edit_embed']); ?>

<form action="?/embeds" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<input type="hidden" name="mode" value="edit">
<input type="hidden" name="name2" value="<?php echo $conn->real_escape_string($_GET['n']); ?>">
<?php echo $lang['mod/name']; ?>: <input type="text" name="name" value="<?php echo $binfo['name']; ?>"/><br />
<?php echo $lang['mod/regex']; ?>: <input type="text" name="regex" value="<?php echo $binfo['regex']; ?>"/><br />
<?php echo $lang['mod/html_code']; ?>: <textarea cols=40 rows=9 name="code"><?php echo $binfo['code']; ?></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		}
		}
?>