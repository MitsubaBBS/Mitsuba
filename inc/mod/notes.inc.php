<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("notes.view");
?>
<?php $mitsuba->admin->ui->startSection($lang['mod/your_notes']); ?>

<?php
$result = $conn->query("SELECT * FROM notes WHERE mod_id=".$_SESSION['id']." ORDER BY created DESC;");
while ($row = $result->fetch_assoc())
{
echo '<div class="content">';
echo '<h3><span class="newssub">'.date("d/m/Y @ H:i", $row['created']).'</span> <a href="?/notes/delete&id='.$row['id'].'">Delete</a></span></h3>';
echo $row['note'];
echo '</div>';
}
?>
<?php $mitsuba->admin->ui->endSection(); ?><br />
<?php $mitsuba->admin->ui->startSection($lang['mod/add_note']); ?>

<form action="?/notes/add" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<textarea name="note" cols=70 rows=12></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>