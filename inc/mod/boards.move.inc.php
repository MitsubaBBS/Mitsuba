<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("boards.move");
		if ($mitsuba->common->isBoard($_GET['board']))
		{
			if (!empty($_POST['new']))
			{
				$result = $mitsuba->admin->boards->moveBoard($_GET['board'], $_POST['new']);
				$mitsuba->admin->logAction(sprintf($lang['log/moved_board'], $conn->real_escape_string($_GET['board']), $conn->real_escape_string($_POST['new'])));
				if($result == 1)
				{
				?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_moved']); ?>
<script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
				} elseif ($result == 0) {
				?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_not_found']); ?>
<a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
				} elseif ($result == -1) {
				?>
<?php $mitsuba->admin->ui->startSection(sprintf($lang['mod/board_exists'], $_POST['new'])); ?>
<a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
				}
			}
		} else {
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_not_found']); ?>
<a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		}
?>