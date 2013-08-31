<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("boards.delete");
$mitsuba->admin->ui->checkToken();
		if (!empty($_GET['board']))
		{
			$board = $conn->real_escape_string($_GET['board']);
			if ($mitsuba->common->isBoard($board))
			{
				$mitsuba->admin->boards->deleteBoard($board);
				$mitsuba->admin->logAction(sprintf($lang['log/deleted_board'], $board));
					?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_deleted']); ?>
<script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			} else {
			
					?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_not_found']); ?>
<a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			}
			
		}
?>