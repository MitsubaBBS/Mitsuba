<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("notes.add");
if (!empty($_POST['note']))
		{
			$mitsuba->admin->ui->checkToken($_POST['token']);
			$note = $conn->real_escape_string($_POST['note']);
			$conn->query("INSERT INTO notes (mod_id, note, created) VALUES (".$_SESSION['id'].", '".$note."', ".time().")");
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/note_added']); ?>

<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
<?php
		} else {
				?>
<?php $mitsuba->admin->ui->startSection($lang['mod/fill_all_fields']); ?>

<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
<?php
		}
?>