<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}

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
<div class="boxbar"><h2><?php echo $lang['mod/add_warning']; ?></h2></div>
<div class="boxcontent">
<form action="?/warnings/add" method="POST">
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
<div class="boxcontent"><a href="?/warnings/add"><?php echo $lang['mod/back']; ?></a></div>
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
		
		$result = 0;
		
		$result = addWarning($conn, $_POST['ip'], $_POST['reason'], $_POST['note']);
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
		
		if ($result == 1)
		{
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_warned']; ?></h2></div>
<div class="boxcontent"><a href="?/warnings"><?php echo $lang['mod/back']; ?></a></div>
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
?>