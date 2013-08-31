<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("boards.delete");
		if (!empty($_GET['board']))
		{
					?>
<?php $mitsuba->admin->ui->startSection(sprintf($lang['mod/want_delete_board'], $_GET['board'])); ?>
<a href="?/boards"><?php echo $lang['mod/no_big']; ?></a> <form action="?/boards/delete_yes&board=<?php echo $_GET['board']; ?>" method="POST"><?php $mitsuba->admin->ui->getToken($path); ?><input type='submit' value='<?php echo $lang['mod/yes_big']; ?>' /></form><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		} else {
						?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_not_found']); ?>
<a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		}
?>