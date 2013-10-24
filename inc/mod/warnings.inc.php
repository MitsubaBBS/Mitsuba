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
<?php $mitsuba->admin->ui->startSection($lang['mod/warnings']); ?>

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
$result = $conn->query("SELECT * FROM warnings ORDER BY created LIMIT 0, 15;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td class='text-center text-nowrap'>".$row['ip']."</td>";
echo "<td>".$row['reason']."</td>";
echo "<td>".$row['note']."</td>";
echo "<td class='text-center text-nowrap'>".date("d/m/Y @ H:i", $row['created'])."</td>";
if ($row['seen']==1)
{
	echo "<td class='text-center'>YES</td>";
} else {
	echo "<td class='text-center'><b>NO</b></td>";
}
if ($_SESSION['type']>=2)
{
echo "<td class='text-center'><a href='?/warnings&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
} else {
echo "<td></td>";
}
echo "</tr>";
}
?>
</tbody>
</table>
<?php printf($lang['mod/showing_warnings'], 15); ?> <a href="?/warnings/all"><?php echo $lang['mod/show_all']; ?></a> <a href="?/warnings/recent&c=100"><?php printf($lang['mod/show_recent'], 100); ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>