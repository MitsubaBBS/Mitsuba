<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("links.view");
	if (!empty($_GET['m']))
	{
		if ($_GET['m'] == "del")
		{
$mitsuba->admin->reqPermission("links.delete");
			if (!empty($_GET['i']))
			{
				$id = $conn->real_escape_string($_GET['i']);
				$mitsuba->admin->links->deleteBoardLink($id);
			}
		}
		if ($_GET['m'] == "addc")
		{
$mitsuba->admin->reqPermission("links.add");
			$mitsuba->admin->ui->checkToken($_POST['token']);
			if (!empty($_POST['title']))
			{
				$mitsuba->admin->links->addLinkCategory($_POST['title']);
			}
		}
		
		if ($_GET['m'] == "up")
		{
$mitsuba->admin->reqPermission("links.move");
			if (!empty($_GET['l']))
			{
				$id = $conn->real_escape_string($_GET['l']);
				$mitsuba->admin->links->moveUpCategory($id);
			}
		}
		
		if ($_GET['m'] == "down")
		{
$mitsuba->admin->reqPermission("links.move");
			if (!empty($_GET['l']))
			{
				$id = $conn->real_escape_string($_GET['l']);
				$mitsuba->admin->links->moveDownCategory($id);
			}
		}
	}
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/manage_board_links']); ?>

<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<?php
echo $mitsuba->admin->links->getLinkTable(-1);
?>
<?php $mitsuba->admin->ui->endSection(); ?>
<?php $mitsuba->admin->ui->startSection($lang['mod/add_link_category']); ?>

<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<form action="?/links&m=addc" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/name']; ?>: <input type="text" name="title" value="<?php echo $lang['mod/category']; ?>" /><input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>