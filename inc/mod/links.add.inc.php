<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		if (isset($_GET['p']))
		{
			$id = $conn->real_escape_string($_GET['p']);
			$cat = $conn->query("SELECT * FROM links WHERE url='' AND id=".$id);
			if ($cat->num_rows == 1)
			{
				if (empty($_POST['title']))
				{
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_link']; ?></h2></div>
<div class="boxcontent">
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<form action="?/links/add&p=<?php echo $id; ?>" method="POST">
<?php echo $lang['mod/short']; ?>: <input type="text" name="short" value="" /><br />
<?php echo $lang['mod/url']; ?>: <input type="text" name="url" value="../" /><br />
<?php echo $lang['mod/url_thread']; ?>: <input type="text" name="url_thread" value="../../" /><br />
<?php echo $lang['mod/url_index']; ?>: <input type="text" name="url_index" value="./" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" value="" /><br />
<br /><input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
				} else {
				//$parent, $url, $url_thread, $title, $short
					addBoardLink($conn, $id, $_POST['url'], $_POST['url_thread'], $_POST['url_index'],  $_POST['title'], $_POST['short']);
					
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