<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("bans.view");
$delete = $mitsuba->admin->checkPermission("bans.delete");
$logs = $mitsuba->admin->checkPermission("logs.view");
?>
<?php $mitsuba->admin->ui->startSection($lang['mod/all_bans']); ?>

	<table>
	<thead>
	<tr>
	<td><?php echo $lang['mod/ip']; ?></td>
	<td><?php echo $lang['mod/reason']; ?></td>
	<td><?php echo $lang['mod/staff_note']; ?></td>
	<td><?php echo $lang['mod/created']; ?></td>
	<td><?php echo $lang['mod/expires']; ?></td>
	<td><?php echo $lang['mod/boards']; ?></td>
	<td><?php echo $lang['mod/seen']; ?></td>
	<td><?php echo $lang['mod/delete']; ?></td>
	<?php
		if ($logs) { echo "<td>".$lang['mod/staff_member']."</td>"; }
	?>
	</tr>
	</thead>
	<tbody>
	<?php

	$_data = $conn->query("SELECT short FROM boards");
	$_boards = array();
	while ($row = $_data->fetch_assoc()) $_boards[] = $row['short'];

	if ($logs) {
		$result = $conn->query("SELECT bans.*, users.username FROM bans LEFT JOIN users ON bans.mod_id=users.id ORDER BY created DESC;");
	} else {
		$result = $conn->query("SELECT * FROM bans ORDER BY created;");
	}
	while ($row = $result->fetch_assoc())
	{
	echo "<tr>";
	echo "<td class='text-center text-nowrap'>".$row['ip']."</td>";
	echo "<td>".$row['reason']."</td>";
	echo "<td>".$row['note']."</td>";
	echo "<td class='text-center text-nowrap'>".date("d/m/Y <br />@ H:i", $row['created'])."</td>";
	if ($row['expires'] != 0)
	{
	echo "<td class='text-center text-nowrap'>".date("d/m/Y <br />@ H:i", $row['expires'])."</td>";
	} else {
	echo "<td><b>never</b></td>";
	}
	if ($row['boards']=="%")
	{
		echo "<td class='text-center'>All boards</td>";
	} else {
		$banBoards = explode(',', $row['boards']);
		if (0.6 * sizeof($_boards) < sizeof($banBoards))
			echo "<td class='text-center'>All boards <b>excluding</b>: ".implode(', ', array_diff($_boards, $banBoards))."</td>";
		else
			echo "<td class='text-center'>".implode(', ', $banBoards)."</td>";
	}
	if ($row['seen']==1)
	{
		echo "<td class='text-center'>YES</td>";
	} else {
		echo "<td class='text-center'><b>NO</b></td>";
	}
	if ($delete)
	{
	echo "<td class='text-center'><a href='?/bans&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
	} else {
	echo "<td></td>";
	}
	if ($logs)
	{
		echo "<td class='text-center text-nowrap'>".$row['username']."</td>";
	}
	echo "</tr>";
	}
	?>
	</tbody>
	</table>
	<?php $mitsuba->admin->ui->endSection(); ?>