<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("appeals.view");
if (!empty($_GET['m']))
{
	if ($_GET['m'] == "clear")
	{
		$mitsuba->admin->reqPermission("appeals.clear.single");
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$conn->query("DELETE FROM appeals WHERE id=".$_GET['id']);
		}
	}
	
	if ($_GET['m'] == "clear_all_yes")
	{
		$mitsuba->admin->reqPermission("appeals.clear.all");
		$conn->query("TRUNCATE TABLE appeals;");
	}
}
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/appeals']); ?>

<a href="?/appeals/clear_all"><?php echo $lang['mod/clear_all']; ?></a>
<table>
<thead>
<tr>
<td><?php echo $lang['mod/ip']; ?></td>
<td><?php echo $lang['mod/ban_reason']; ?></td>
<td><?php echo $lang['mod/staff_note']; ?></td>
<td><?php echo $lang['mod/days_left']; ?></td>
<td><?php echo $lang['mod/e_mail']; ?></td>
<td><?php echo $lang['mod/appeal_text']; ?></td>
<td><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php
$appeals = $conn->query("SELECT * FROM appeals;");
while ($row = $appeals->fetch_assoc())
{
	if ($row['rangeban'] == 0)
	{
		$bandata = $conn->query("SELECT * FROM bans WHERE id=".$row['ban_id']);
	} else {
		$bandata = $conn->query("SELECT * FROM rangebans WHERE id=".$row['ban_id']);
	}
	if ($bandata->num_rows == 1)
	{
		$ban = $bandata->fetch_assoc();
		echo "<tr>";
		if ($row['rangeban'] == 0)
		{
			echo "<td class='nowrapIP'><center>".$ban['ip']."</center></td>";
		} else {
			echo "<td>".$ban['start_ip']." - ".$ban['end_ip']." ( ".$row['ip']." )</td>";
		}
		echo "<td>".$ban['reason']."</td>";
		echo "<td>".$ban['note']."</td>";
		if ($row['expires'] != 0)
		{
		echo "<td><center>".date("d/m/Y @ H:i", $row['expires'])."</center></td>";
		} else {
		echo "<td><b>never</b></td>";
		}
		echo "<td><center>".$row['email']."</center></td>";
		echo "<td>".$row['msg']."</td>";
		echo "<td><center> [ <a href='?/appeals&m=clear&id=".$row['id']."'>C</a> / <a href='?/bans&del=1&b=".$ban['id']."'>U</a> ]</center></td>";
		echo "</tr>";
	} else {
		$conn->query("DELETE FROM appeals WHERE id=".$row['id']);
	}
}
?>
</tbody>
<?php $mitsuba->admin->ui->endSection(); ?>
<script type="text/javascript">parent.nav.location.reload();</script>