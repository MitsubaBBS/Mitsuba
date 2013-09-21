<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("warnings.view");
if ((isset($_GET['del'])) && ($_GET['del']==1))
	{
$mitsuba->admin->reqPermission("warnings.delete");
		if ((!empty($_GET['b'])) && (is_numeric($_GET['b'])))
		{
			$conn->query("DELETE FROM warnings WHERE id=".$_GET['b']);
		}
	}
	?>
<?php $mitsuba->admin->ui->startSection($lang['mod/all_warnings']); ?>

<table>
<thead>
<tr>
<td><?php echo $lang['mod/ip']; ?></td>
<td><?php echo $lang['mod/reason']; ?></td>
<td><?php echo $lang['mod/staff_note']; ?></td>
<td><?php echo $lang['mod/created']; ?></td>
<td><?php echo $lang['mod/shown']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$canDelete = $mitsuba->admin->checkPermission("warnings.delete");
$result = $conn->query("SELECT * FROM warnings ORDER BY created;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td class='nowrapIP'><center>".$row['ip']."</center></td>";
echo "<td>".$row['reason']."</td>";
echo "<td>".$row['note']."</td>";
echo "<td><center>".date("d/m/Y @ H:i", $row['created'])."</center></td>";
if ($row['seen']==1)
{
	echo "<td><center>YES</center></td>";
} else {
	echo "<td><center><b>NO</b></center></td>";
}
if ($canDelete)
{
echo "<td><center><a href='?/warnings/all&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
} else {
echo "<td></td>";
}
echo "</tr>";
}
?>
</tbody>
</table>
<?php $mitsuba->admin->ui->endSection(); ?>