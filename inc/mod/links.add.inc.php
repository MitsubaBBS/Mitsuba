<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("links.add");
		if (isset($_GET['p']))
		{
			$id = $conn->real_escape_string($_GET['p']);
			$cat = $conn->query("SELECT * FROM links WHERE url='' AND id=".$id);
			if ($cat->num_rows == 1)
			{
				if (empty($_POST['title']))
				{
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/add_link']); ?>

<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<form action="?/links/add&p=<?php echo $id; ?>" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/short']; ?>: <input type="text" name="short" value="" /><br />
<?php echo $lang['mod/url']; ?>: <input type="text" name="url" value="./" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" value="" /><br />
<?php echo $lang['mod/relativity']; ?>: <input type="radio" name="relative" value="0" />Absolute <input type="radio" name="relative" value="1" checked/>Relative (to board index/mod.php) <input type="radio" name="relative" value="2" />Board link 
<br /><input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
				} else {
				//$parent, $url, $url_thread, $title, $short
					if (($_POST['relative'] == 2) && (!$mitsuba->common->isBoard($_POST['url'])))
					{
						$mitsuba->admin->ui->startSection(sprintf($lang['mod/board_not_exists'], $_POST['url']));
						?>
<a href="?/links"><?php echo $lang['mod/back']; ?></a>
						<?php 
						$mitsuba->admin->ui->endSection();
					} else {
						$mitsuba->admin->ui->checkToken($_POST['token']);
						$mitsuba->admin->links->addBoardLink($id, $_POST['url'], $_POST['relative'],  $_POST['title'], $_POST['short']);
						?>
						<meta http-equiv="refresh" content="0;URL='?/links'" />
						<?php
					}
					
					
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