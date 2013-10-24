<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("range.view");
$delete = $mitsuba->admin->checkPermission("bans.delete");
$logs = $mitsuba->admin->checkPermission("logs.view");
if ((isset($_GET['del'])) && ($_GET['del']==1))
	{
$mitsuba->admin->reqPermission("range.delete");
		if ((!empty($_GET['b'])) && (is_numeric($_GET['b'])))
		{
			$conn->query("DELETE FROM rangebans WHERE id=".$_GET['b']);
		}
	}
	?>
	<?php $mitsuba->admin->ui->startSection($lang['mod/range_bans']); ?>

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
		$result = $conn->query("SELECT rangebans.*, users.username FROM rangebans LEFT JOIN users ON rangebans.mod_id=users.id ORDER BY created DESC LIMIT 0, 15;");
	} else {
		$result = $conn->query("SELECT * FROM rangebans ORDER BY created LIMIT 0, 15;");
	}
	while ($row = $result->fetch_assoc())
	{
	echo "<tr>";
	echo "<td class='text-center text-nowrap'>".$row['ip']."</td>";
	echo "<td>".$row['reason']."</td>";
	echo "<td>".$row['note']."</td>";
	echo "<td class='text-center text-nowrap'>".date("d/m/Y @ H:i", $row['created'])."</td>";
	if ($row['expires'] != 0)
	{
	echo "<td class='text-center text-nowrap'>".date("d/m/Y @ H:i", $row['expires'])."</td>";
	} else {
	echo "<td><b>never</b></td>";
	}
	if ($row['boards']=="%")
	{
		echo "<td class='text-center'>All boards</td>";
	} else {
		echo "<td class='text-center'>".$row['boards']."</td>";
	}
	if ($delete)
	{
	echo "<td class='text-center'><a href='?/rangebans&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
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
	<?php printf($lang['mod/showing_bans'], 15); ?> <a href="?/bans/all"><?php echo $lang['mod/show_all']; ?></a> <a href="?/rangebans/recent&c=100"><?php printf($lang['mod/show_recent'], 100); ?></a>
	<?php $mitsuba->admin->ui->endSection(); ?>