<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("warnings.add");
	if (empty($_POST['ip']))
	{
		$ip = "";
		$post = "";
		$board = "";
		$postinfo = "";
		if ((!empty($_GET['p'])) && (!empty($_GET['b'])) && (is_numeric($_GET['p'])) && ($mitsuba->common->isBoard($_GET['b'])))
		{
			$board = $conn->real_escape_string($_GET['b']);
			$post = $_GET['p'];
			//<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>
			$postdata = $conn->query("SELECT * FROM posts WHERE id=".$post." AND board='".$board."'");
			if ($postdata->num_rows == 1)
			{
				$postinfo = $postdata->fetch_assoc();
				$ip = $postinfo['ip'];
			} else {
				$post = "";
				$board = "";
			}
		}
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/add_warning']); ?>

<form action="?/warnings/add" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/ip']; ?>: <input type="text" name="ip" value="<?php echo $ip; ?>"/><br />
<?php echo $lang['mod/reason']; ?>: <input type="text" name="reason" /><br />
<?php echo $lang['mod/staff_note']; ?>: <input type="text" name="note" /><br />
<?php
if (!empty($postinfo))
{
?>
<input type="hidden" name="post" value="<?php echo $post; ?>" />
<input type="hidden" name="board" value="<?php echo $board; ?>" />
<?php
if ((!empty($_GET['d'])) && ($_GET['d'] == 1))
{
?>
<input type="hidden" name="delete" value="1" /><b><?php echo $lang['mod/will_delete']; ?></b>
<?php
} else {
?>
<?php echo $lang['mod/append_text']; ?>: <input type="text" name="append_text" value='<b style="color:red;">(USER WAS WARNED FOR THIS POST)</b>' style="width: 400px;"/><input type="checkbox" name="append" value="1" checked=1/><?php echo $lang['mod/yes']; ?><br/>
<?php
}
}
?>
<br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		} else {
		$mitsuba->admin->ui->checkToken($_POST['token']);
		if (!filter_var($_POST['ip'], FILTER_VALIDATE_IP))
		{
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/ip_syntax_wrong']); ?>
<a href="?/warnings/add"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
</body>
</html>
		<?php
		exit;
		}
		$post = "";
		$board = "";
		$postinfo = "";
		if ((!empty($_POST['post'])) && (!empty($_POST['board'])) && (is_numeric($_POST['post'])) && ($mitsuba->common->isBoard($_POST['board'])))
		{
			$board = $conn->real_escape_string($_POST['board']);
			$post = $_POST['post'];
			//<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>
			$postdata = $conn->query("SELECT * FROM posts WHERE id=".$post." AND board='".$board."'");
			if ($postdata->num_rows == 0)
			{
				$post = "";
				$board = "";
			}
		}
		
		$result = 0;
		
		$result = $mitsuba->admin->bans->addWarning($_POST['ip'], $_POST['reason'], $_POST['note']);
		if ($result != -2)
		{
			if ((!empty($_POST['delete'])) && ($_POST['delete']=="1"))
			{
				$mitsuba->posting->deletePost($board, $post, "", 0, $mitsuba->admin->checkPermission("post.delete"));
			} else {
				if ((!empty($post)) && (!empty($_POST['append'])) && ($_POST['append'] == 1))
				{
					$mitsuba->admin->appendToPost($board, $post, $_POST['append_text']);
				}
			}
		}
		
		if ($result == 1)
		{
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/user_warned']); ?>
<a href="?/warnings"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		} else {
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/filled_wrong']); ?>
<a href="javascript:history.back(-1);"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		}
		}
?>