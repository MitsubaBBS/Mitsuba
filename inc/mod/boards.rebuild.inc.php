<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("boards.rebuild");
		if ((!empty($_GET['board'])) && ($mitsuba->common->isBoard($_GET['board'])))
		{
			$mitsuba->caching->rebuildBoardCache($_GET['board']);
			$mitsuba->admin->logAction(sprintf($lang['log/rebuilt_board'], $conn->real_escape_string($_GET['board'])));
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_cache_rebuilded']); ?>
<script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		} else {
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_not_found']); ?>
<a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		}
?>