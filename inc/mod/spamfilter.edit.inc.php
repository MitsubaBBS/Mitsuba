<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("spamfilter.update");
		if (!empty($_GET['n']))
		{
		$result = $conn->query("SELECT * FROM spamfilter WHERE id=".$conn->real_escape_string($_GET['n']));
		if ($result->num_rows == 1)
		{
		$info = $result->fetch_assoc();
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/sf_edit']); ?>

<form action="?/spamfilter" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<input type="hidden" name="mode" value="edit">
<input type="hidden" name="id" value="<?php echo $_GET['n']; ?>">
<?php echo $lang['mod/wf_search']; ?>: <input type="text" name="search" value="<?php echo htmlspecialchars($info['search']); ?>"/><br />
<?php echo $lang['mod/reason']; ?>: <input type="text" name="reason" value="<?php echo htmlspecialchars($info['replace']); ?>"/><br />
<?php echo $lang['mod/expires']; ?>: <input type="text" name="expires" value="<?php echo htmlspecialchars($info['expires']); ?>"/><br />
<br /><br />
<?php $mitsuba->admin->ui->getBoardList($info['boards']); ?>
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		}
		}
?>