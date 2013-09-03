<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("bans.add.request");
$canBan = $mitsuba->admin->checkPermission("bans.add");
if (empty($_GET['r']))
	{
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
		$title = "";
		if ($canBan)
		{ 
			$title = $lang['mod/add_ban'];
		} else {
			$title = $lang['mod/add_ban_request'];
		}
		$mitsuba->admin->ui->startSection($title);
		?>
<form action="?/bans/add" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/ip']; ?>: <input type="text" name="ip" value="<?php echo $ip; ?>"/><br />
<?php echo $lang['mod/reason']; ?>: <input type="text" name="reason" /><br />
<?php echo $lang['mod/staff_note']; ?>: <input type="text" name="note" /><br />
<?php
if ($canBan) {
?>
<?php echo $lang['mod/expires_eg']; ?>: <input type="text" name="expires" /><br />
<?php echo $lang['mod/appeal_in']; ?>: <input type="text" name="appeal" value="1s" /><br />
<?php $mitsuba->admin->ui->getBoardList(); ?><br />
<br />
<?php
}
if (!empty($postinfo))
{
?>
<input type="hidden" name="post" value="<?php echo $post; ?>" />
<input type="hidden" name="board" value="<?php echo $board; ?>" />
<?php
if ($canBan) {
if ((!empty($_GET['d'])) && ($_GET['d'] == 1))
{
?>
<input type="hidden" name="delete" value="1" /><b><?php echo $lang['mod/will_delete']; ?></b>
<?php
} else {
?>
<?php echo $lang['mod/append_text']; ?>: <input type="text" name="append_text" value='<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>' style="width: 400px;"/><input type="checkbox" name="append" value="1" checked=1/><?php echo $lang['mod/yes']; ?><br/>
<?php
}
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
<a href="?/bans/add"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
</div>
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
		$boards = "";
		if ((!empty($_POST['all'])) && ($_POST['all']==1))
		{
			$boards = "%";
		} else {
			if (!empty($_POST['boards']))
			{
				foreach ($_POST['boards'] as $board)
				{
					$boards .= $board.",";
				}
			} else {
				$boards = "%";
			}
		}
		if ($boards != "%") { $boards = substr($boards, 0, strlen($boards) - 1); }
		$result = 0;
		$what = 1;
		if (!$canBan)
		{
			$append = 0;
			if ((!empty($_POST['delete'])) && ($_POST['delete']=="1"))
			{
				$append = 2;
			} else {
				if ((!empty($post)) && (!empty($_POST['append'])) && ($_POST['append'] == 1))
				{
					$append = 1;
				}
			}
			$result = $mitsuba->admin->bans->addBanRequest($_POST['ip'], $_POST['reason'], $_POST['note'], $board, $post, $append);
			$what = 2;
		} else {
			$result = $mitsuba->admin->bans->addBan($_POST['ip'], $_POST['reason'], $_POST['note'], $_POST['expires'], $boards, $_POST['appeal']);
			if ($result != -2)
			{
				if ((!empty($_POST['delete'])) && ($_POST['delete']=="1"))
				{
					$mitsuba->posting->deletePost($board, $post, "", 0, $mitsuba->admin->checkPermission("post.delete.single"));
				} else {
					if ((!empty($post)) && (!empty($_POST['append'])) && ($_POST['append'] == 1))
					{
						$mitsuba->admin->appendToPost($board, $post, $_POST['append_text']);
					}
				}
			}
		}
		if (($what == 1) && ($result == 1))
		{
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/user_banned']); ?>
<a href="?/bans"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		} elseif (($what == 2) && ($result == 1))
		{
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/request_sent']); ?>
<a href="javascript:history.go(-2);"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		} else {
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/filled_wrong']); ?>
<a href="javascript:history.back(-1);"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		}
		}
		} else {
			if (is_numeric($_GET['r']))
			{
				$req = $conn->query("SELECT * FROM ban_requests WHERE id=".$_GET['r']);
				if ($req->num_rows == 1)
				{
				$request = $req->fetch_assoc();
				$board = $request['board'];
				$post = $request['post'];
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
				$title = "";
				if ($canBan)
				{ 
					$title = $lang['mod/add_ban'];
				} else {
					$title = $lang['mod/add_ban_request'];
				}
				$mitsuba->admin->ui->startSection($title);
			?>
<form action="?/bans/add" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/ip']; ?>: <input type="text" name="ip" value="<?php echo $ip; ?>"/><br />
<?php echo $lang['mod/reason']; ?>: <input type="text" name="reason" value="<?php echo $request['reason']; ?>"/><br />
<?php echo $lang['mod/staff_note']; ?>: <input type="text" name="note" value="<?php echo $request['note']; ?>"/><br />
<?php echo $lang['mod/expires_eg']; ?>: <input type="text" name="expires" /><br />
<br /><br />
<?php $mitsuba->admin->ui->getBoardList(); ?><br />
<br />
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
<?php echo $lang['mod/append_text']; ?>: <input type="text" name="append_text" value='<b style="color:red;">(USER WAS BANNED FOR THIS POST)</b>' style="width: 400px;"/><input type="checkbox" name="append" value="1" checked=1/><?php echo $lang['mod/yes']; ?><br/>
<?php
}
}
?>
<br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
				}
			}
		}
?>