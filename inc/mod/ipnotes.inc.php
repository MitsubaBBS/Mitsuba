<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("ipnotes.view");
?>
<?php $mitsuba->admin->ui->startSection($lang['mod/recent_ip_notes']); ?>

	<table>
<thead>
<td><?php echo $lang['mod/created']; ?></td>
<td><?php echo $lang['mod/note']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM ip_notes LIMIT 0, 15;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".date("d/m/Y(D)H:i:s", $row['created'])."</center></td>";
echo "<td>".$row['text']."</td>";
echo "<td><center><a href='?/ipnotes/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
echo "</tr>";
}
?>
</tbody>
</table>
<?php printf($lang['mod/showing_notes'], 15); ?> <a href="?/ipnotes/all"><?php echo $lang['mod/show_all']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?><br />
<?php $mitsuba->admin->ui->startSection($lang['mod/add_ip_note']); ?>

<form action="?/ipnotes/add" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/ip']; ?>: <input type="text" name="ip" /><br />
<textarea name="note" cols=70 rows=12></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>