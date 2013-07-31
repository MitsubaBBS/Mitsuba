<?php
$mitsuba->admin->reqPermission(2);
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if (empty($_GET['r']))
	{
	if (empty($_POST['ip']))
	{
		$ip = "";
		$title = "";
		$title = $lang['mod/add_range_ban'];
		$mitsuba->admin->ui->startSection($title);
		?>
<form action="?/rangebans/add" method="POST">
<?php echo $lang['mod/ip']; ?>: <input type="text" name="ip" value="<?php echo $ip; ?>"/><br />
<?php echo $lang['mod/reason']; ?>: <input type="text" name="reason" /><br />
<?php echo $lang['mod/staff_note']; ?>: <input type="text" name="note" /><br />
<?php
if ($_SESSION['type']>=1) {
?>
<?php echo $lang['mod/expires_eg']; ?>: <input type="text" name="expires" /><br />
<?php $mitsuba->admin->ui->getBoardList(); ?><br />
<br />
<?php
}
?>
<br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		} else {
		if (!filter_var($_POST['ip'], FILTER_VALIDATE_IP))
		{
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/ip_syntax_wrong']); ?>
<a href="?/rangebans/add"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
</div>
</body>
</html>
		<?php
		exit;
		}
		$boards = "";
		if ((!empty($_POST['all'])) && ($_POST['all']==1))
		{
			$boards = "*";
		} else {
			if (!empty($_POST['boards']))
			{
				foreach ($_POST['boards'] as $board)
				{
					$boards .= $board.",";
				}
			} else {
				$boards = "*";
			}
		}
		if ($boards != "*") { $boards = substr($boards, 0, strlen($boards) - 1); }
		$result = 0;
		if ($result == 1)
		{
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/user_banned']); ?>
<a href="?/rangebans"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		} else {
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/filled_wrong']); ?>
<a href="javascript:history.back(-1);"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		}
		}
		}
?>