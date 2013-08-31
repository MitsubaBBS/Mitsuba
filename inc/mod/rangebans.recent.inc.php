<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("range.view");
$delete = $mitsuba->admin->checkPermission("bans.delete");
$logs = $mitsuba->admin->checkPermission("logs.view");
if ((!empty($_GET['c'])) && (is_numeric($_GET['c'])))
	{
	$mitsuba->admin->ui->startSection(sprintf($lang['mod/recent_range_bans'], $_GET['c']));
	?>
	<table>
	<thead>
	<tr>
	<td><?php echo $lang['mod/ip']; ?></td>
	<td><?php echo $lang['mod/reason']; ?></td>
	<td><?php echo $lang['mod/staff_note']; ?></td>
	<td><?php echo $lang['mod/created']; ?></td>
	<td><?php echo $lang['mod/expires']; ?></td>
	<td><?php echo $lang['mod/boards']; ?></td>
	<td><?php echo $lang['mod/delete']; ?></td>
	<?php
		if ($logs) { echo "<td>".$lang['mod/staff_member']."</td>"; }
	?>
	</tr>
	</thead>
	<tbody>
	<?php
	if ($logs) {
		$result = $conn->query("SELECT rangebans.*, users.username FROM rangebans LEFT JOIN users ON rangebans.mod_id=users.id ORDER BY created DESC LIMIT 0, ".$_GET['c'].";");
	} else {
		$result = $conn->query("SELECT * FROM rangebans ORDER BY created LIMIT 0, ".$_GET['c'].";");
	}
	while ($row = $result->fetch_assoc())
	{
	echo "<tr>";
	echo "<td class='nowrapIP'><center>".$row['ip']."</center></td>";
	echo "<td>".$row['reason']."</td>";
	echo "<td>".$row['note']."</td>";
	echo "<td><center>".date("d/m/Y @ H:i", $row['created'])."</center></td>";
	if ($row['expires'] != 0)
	{
	echo "<td><center>".date("d/m/Y @ H:i", $row['expires'])."</center></td>";
	} else {
	echo "<td><b>never</b></td>";
	}
	if ($row['boards']=="%")
	{
		echo "<td><center>All boards</center></td>";
	} else {
		echo "<td><center>".$row['boards']."</center></td>";
	}
	if ($delete)
	{
	echo "<td><center><a href='?/rangebans&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
	} else {
	echo "<td></td>";
	}
	if ($logs)
	{
		echo "<td>".$row['username']."</td>";
	}
	echo "</tr>";
	}
	?>
	</tbody>
	</table>
	<?php $mitsuba->admin->ui->endSection(); ?>
	<?php
	}
?>