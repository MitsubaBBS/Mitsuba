<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("links.update");
		if (isset($_GET['i']))
		{
			$id = $conn->real_escape_string($_GET['i']);
			$link = $conn->query("SELECT * FROM links WHERE id=".$id);
			if ($link->num_rows == 1)
			{
				$data = $link->fetch_assoc();
				if (empty($_POST['title']))
				{
$mitsuba->admin->ui->startSection($lang['mod/edit_link']);
		?>
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<form action="?/links/edit&i=<?php echo $id; ?>" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/short']; ?>: <input type="text" name="short" value="<?php echo $data['short']; ?>" /><br />
<?php echo $lang['mod/url']; ?>: <input type="text" name="url" value="<?php echo $data['url']; ?>" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" value="<?php echo $data['title']; ?>" /><br />
<?php echo $lang['mod/relativity']; ?>: <input type="radio" name="relative" value="0" <?php if ($data['relative']==0) { echo "checked"; } ?>/>Absolute <input type="radio" name="relative" value="1"  <?php if ($data['relative']==1) { echo "checked"; } ?>/>Relative (to board index/mod.php) <input type="radio" name="relative" value="2"  <?php if ($data['relative']==2) { echo "checked"; } ?>/>Board link 
<br /><input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
				} else {
					if (($_POST['relative'] == 2) && (!$mitsuba->common->isBoard($_POST['url'])))
					{
						$mitsuba->admin->ui->startSection(sprintf($lang['mod/board_not_exists'], $_POST['url']));
						?>
<a href="?/links"><?php echo $lang['mod/back']; ?></a>
						<?php 
						$mitsuba->admin->ui->endSection();

					} else {
						$mitsuba->admin->ui->checkToken($_POST['token']);
						$mitsuba->admin->links->updateBoardLink($id, $_POST['url'], $_POST['relative'], $_POST['title'], $_POST['short']);
						?>
						<meta http-equiv="refresh" content="0;URL='?/links'" />
						<?php
					}
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