<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("logs.view");
?>
<?php $mitsuba->admin->ui->startSection($lang['mod/action_log']); ?>

	<table>
	<thead>
	<tr>
	<td><?php echo $lang['mod/user']; ?></td>
	<td><?php echo $lang['mod/action']; ?></td>
	<td><?php echo $lang['mod/date']; ?></td>
	</tr>
	</thead>
	<tbody>
		<?php
		$log = $conn->query("SELECT log.*, users.username FROM log LEFT JOIN users ON log.mod_id=users.id ORDER BY date DESC");
		while ($row = $log->fetch_assoc())
		{
			echo "<tr>";
			echo "<td><center>".$row['username']."</center></td>";
			echo "<td>".$row['event']."</td>";
			echo "<td><center>".date("d/m/Y(D)H:i:s", $row['date'])."</center></td>";
			echo "</tr>";
		}
		?>
	</tbody>
</table>
<?php $mitsuba->admin->ui->endSection(); ?>