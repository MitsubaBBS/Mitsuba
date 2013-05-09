<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		if (isset($_GET['i']))
		{
			$id = $conn->real_escape_string($_GET['i']);
			$link = $conn->query("SELECT * FROM links WHERE id=".$id);
			if ($link->num_rows == 1)
			{
				$data = $link->fetch_assoc();
				if (empty($_POST['title']))
				{
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2></h2></div>
<div class="boxcontent">
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<form action="?/links/edit&i=<?php echo $id; ?>" method="POST">
<?php echo $lang['mod/short']; ?>: <input type="text" name="short" value="<?php echo $data['short']; ?>" /><br />
<?php echo $lang['mod/url']; ?>: <input type="text" name="url" value="<?php echo $data['url']; ?>" /><br />
<?php echo $lang['mod/url_thread']; ?>: <input type="text" name="url_thread" value="<?php echo $data['url_thread']; ?>" /><br />
<?php echo $lang['mod/url_index']; ?>: <input type="text" name="url_index" value="<?php echo $data['url_index']; ?>" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" value="<?php echo $data['title']; ?>" /><br />
<br /><input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
				} else {
					updateBoardLink($conn, $id, $_POST['url'], $_POST['url_thread'], $_POST['url_index'], $_POST['title'], $_POST['short']);
					
			?>
			<meta http-equiv="refresh" content="0;URL='?/links'" />
			<?php
				}
			} else {
			?>
			<meta http-equiv="refresh" content="0;URL='?/links'" />
			<?php
			}
		} else {
		?>
		<meta http-equiv="refresh" content="0;URL='?/links'" />
		<?php
		}
?>