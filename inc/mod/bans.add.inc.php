<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if (empty($_GET['r']))
	{
	if (empty($_POST['ip']))
	{
		$ip = "";
		$post = "";
		$board = "";
		$postinfo = "";
		if ((!empty($_GET['p'])) && (!empty($_GET['b'])) && (is_numeric($_GET['p'])) && (isBoard($conn, $_GET['b'])))
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
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php if ($_SESSION['type']>=2) { echo $lang['mod/add_ban']; } else { echo $lang['mod/add_ban_request']; } ?></h2></div>
<div class="boxcontent">
<form action="?/bans/add" method="POST">
<?php echo $lang['mod/ip']; ?>: <input type="text" name="ip" value="<?php echo $ip; ?>"/><br />
<?php echo $lang['mod/reason']; ?>: <input type="text" name="reason" /><br />
<?php echo $lang['mod/staff_note']; ?>: <input type="text" name="note" /><br />
<?php
if ($_SESSION['type']>=1) {
?>
<?php echo $lang['mod/expires_eg']; ?>: <input type="text" name="expires" /><br />
<?php getBoardList($conn); ?><br />
<br />
<?php
}
if (!empty($postinfo))
{
?>
<input type="hidden" name="post" value="<?php echo $post; ?>" />
<input type="hidden" name="board" value="<?php echo $board; ?>" />
<?php
if ($_SESSION['type']>=1) {
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
</div>
</div>
</div>
		<?php
		} else {
		if (!filter_var($_POST['ip'], FILTER_VALIDATE_IP))
		{
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/ip_syntax_wrong']; ?></h2></div>
<div class="boxcontent"><a href="?/bans/add"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
</div>
</body>
</html>
		<?php
		exit;
		}
		$post = "";
		$board = "";
		$postinfo = "";
		if ((!empty($_POST['post'])) && (!empty($_POST['board'])) && (is_numeric($_POST['post'])) && (isBoard($conn, $_POST['board'])))
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
		$what = 1;
		if ($_SESSION['type'] <= 1)
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
			$result = addBanRequest($conn, $_POST['ip'], $_POST['reason'], $_POST['note'], $board, $post, $append);
			$what = 2;
		} else {
			$result = addBan($conn, $_POST['ip'], $_POST['reason'], $_POST['note'], $_POST['expires'], $boards);
			if ($result != -2)
			{
				if ((!empty($_POST['delete'])) && ($_POST['delete']=="1"))
				{
					deletePost($conn, $cacher, $board, $post, "", 0, $_SESSION['type']);
				} else {
					if ((!empty($post)) && (!empty($_POST['append'])) && ($_POST['append'] == 1))
					{
						appendToPost($conn, $cacher, $board, $post, $_POST['append_text']);
					}
				}
			}
		}
		if (($what == 1) && ($result == 1))
		{
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_banned']; ?></h2></div>
<div class="boxcontent"><a href="?/bans"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		} elseif (($what == 2) && ($result == 1))
		{
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/request_sent']; ?></h2></div>
<div class="boxcontent"><a href="javascript:history.go(-2);"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/filled_wrong']; ?></h2></div>
<div class="boxcontent"><a href="javascript:history.back(-1);"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
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
					?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php if ($_SESSION['type']>=2) { echo $lang['mod/add_ban']; } else { echo $lang['mod/add_ban_request']; } ?></h2></div>
<div class="boxcontent">
<form action="?/bans/add" method="POST">
<?php echo $lang['mod/ip']; ?>: <input type="text" name="ip" value="<?php echo $ip; ?>"/><br />
<?php echo $lang['mod/reason']; ?>: <input type="text" name="reason" value="<?php echo $request['reason']; ?>"/><br />
<?php echo $lang['mod/staff_note']; ?>: <input type="text" name="note" value="<?php echo $request['note']; ?>"/><br />
<?php echo $lang['mod/expires_eg']; ?>: <input type="text" name="expires" /><br />
<br /><br />
<?php getBoardList($conn); ?><br />
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
</div>
</div>
</div>
		<?php
				}
			}
		}
?>