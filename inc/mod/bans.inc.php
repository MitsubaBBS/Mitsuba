<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if ((isset($_GET['del'])) && ($_GET['del']==1))
	{
		$mitsuba->admin->reqPermission(2);
		if ((!empty($_GET['b'])) && (is_numeric($_GET['b'])))
		{
			$conn->query("DELETE FROM bans WHERE id=".$_GET['b']);
		}
	}
	?>
	<?php $mitsuba->admin->ui->startSection($lang['mod/bans']); ?>

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
		if ($_SESSION['type'] >= 3) { echo "<td>".$lang['mod/staff_member']."</td>"; }
	?>
	</tr>
	</thead>
	<tbody>
	<?php
	if ($_SESSION['type'] >= 3) {
		$result = $conn->query("SELECT bans.*, users.username FROM bans LEFT JOIN users ON bans.mod_id=users.id ORDER BY created LIMIT 0, 15;");
	} else {
		$result = $conn->query("SELECT * FROM bans ORDER BY created LIMIT 0, 15;");
	}
	while ($row = $result->fetch_assoc())
	{
	echo "<tr>";
	echo "<td><center>".$row['ip']."</center></td>";
	echo "<td>".$row['reason']."</td>";
	echo "<td>".$row['note']."</td>";
	echo "<td><center>".date("d/m/Y @ H:i", $row['created'])."</center></td>";
	if ($row['expires'] != 0)
	{
	echo "<td><center>".date("d/m/Y @ H:i", $row['expires'])."</center></td>";
	} else {
	echo "<td><b>never</b></td>";
	}
	echo "<td><center>".$row['boards']."</center></td>";
	if ($_SESSION['type']>=2)
	{
	echo "<td><center><a href='?/bans&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
	} else {
	echo "<td></td>";
	}
	if ($_SESSION['type'] >= 3)
	{
		echo "<td>".$row['username']."</td>";
	}
	echo "</tr>";
	}
	?>
	</tbody>
	</table>
	<?php printf($lang['mod/showing_bans'], 15); ?> <a href="?/bans/all"><?php echo $lang['mod/show_all']; ?></a> <a href="?/bans/recent&c=100"><?php printf($lang['mod/show_recent'], 100); ?></a>
	<?php $mitsuba->admin->ui->endSection(); ?>