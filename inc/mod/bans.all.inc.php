<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
?>
<div class="box-outer top-box">
	<div class="box-inner">
	<div class="boxbar"><h2><?php echo $lang['mod/all_bans']; ?></h2></div>
	<div class="boxcontent">
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
	</tr>
	</thead>
	<tbody>
	<?php
	$result = $conn->query("SELECT * FROM bans ORDER BY created;");
	while ($row = $result->fetch_assoc())
	{
	echo "<tr>";
	echo "<td>".$row['ip']."</td>";
	echo "<td>".$row['reason']."</td>";
	echo "<td>".$row['note']."</td>";
	echo "<td>".date("d/m/Y @ H:i", $row['created'])."</td>";
	if ($row['expires'] != 0)
	{
	echo "<td>".date("d/m/Y @ H:i", $row['expires'])."</td>";
	} else {
	echo "<td><b>never</b></td>";
	}
	echo "<td>".$row['boards']."</td>";
	if ($_SESSION['type']>=1)
	{
	echo "<td><a href='?/bans&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
	} else {
	echo "<td></td>";
	}
	echo "</tr>";
	}
	?>
	</tbody>
	</table>
	</div>
	</div>
	</div>