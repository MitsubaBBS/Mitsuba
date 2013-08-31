<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("notes.delete");
if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$note = $conn->query("SELECT * FROM notes WHERE id=".$_GET['id']);
			if ($note->num_rows == 1)
			{
				$info = $note->fetch_assoc();
				if ($info['mod_id'] == $_SESSION['id'])
				{
					$conn->query("DELETE FROM notes WHERE id=".$_GET['id']);
					?>
<?php $mitsuba->admin->ui->startSection($lang['mod/note_deleted']); ?>

<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
<?php
				} else {
				?>
<?php $mitsuba->admin->ui->startSection($lang['mod/error']); ?>

<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
<?php
				}
			} else {
			?>
<?php $mitsuba->admin->ui->startSection($lang['mod/error']); ?>

<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
<?php
			}
		} else {
				?>
<?php $mitsuba->admin->ui->startSection($lang['mod/error']); ?>

<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
<?php
		}
?>