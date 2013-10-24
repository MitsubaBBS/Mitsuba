<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
		$mitsuba->admin->reqPermission("user.inbox");
?>
<?php $mitsuba->admin->ui->startSection($lang['mod/inbox']); ?>

<table>
<thead>
<td><?php echo $lang['mod/title']; ?></td>
<td><?php echo $lang['mod/date']; ?></td>
<td><?php echo $lang['mod/from']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</thead>
<tbody>
		<?php
		$pms = $conn->query("SELECT users.username, pm.* FROM pm LEFT JOIN users ON pm.from_user=users.id WHERE pm.to_user=".$_SESSION['id']." ORDER BY pm.created DESC");
		while ($row = $pms->fetch_assoc())
		{
			echo "<tr>";
			if ($row['read_msg']==0)
			{
				echo "<td class='text-center'><b><a href='?/inbox/read&id=".$row['id']."'>".$row['title']."</a></b></td>";
			} else {
				echo "<td class='text-center'><a href='?/inbox/read&id=".$row['id']."'>".$row['title']."</a></td>";
			}
			echo "<td class='text-center text-nowrap'>".date("d/m/Y @ H:i", $row['created'])."</td>";
			echo "<td class='text-center'>".$row['username']."</td>";
			echo "<td class='text-center'><a href='?/inbox/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></td>";
			echo "</tr>";
		}
		?>
		</tbody>
<?php $mitsuba->admin->ui->endSection(); ?>