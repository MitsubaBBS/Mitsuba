<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if ((isset($_GET['del'])) && ($_GET['del']==1))
	{
		reqPermission(2);
		if ((!empty($_GET['b'])) && (is_numeric($_GET['b'])))
		{
			$conn->query("DELETE FROM warnings WHERE id=".$_GET['b']);
		}
	}
	?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php printf($lang['mod/recent_warnings'], $_GET['c']); ?></h2></div>
<div class="boxcontent">
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
$result = $conn->query("SELECT * FROM warnings ORDER BY created LIMIT 0, ".$_GET['c'].";");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".$row['ip']."</center></td>";
echo "<td>".$row['reason']."</td>";
echo "<td>".$row['note']."</td>";
echo "<td><center>".date("d/m/Y @ H:i", $row['created'])."</center></td>";
if ($row['shown']==1)
{
	echo "<td><center>YES</center></td>";
} else {
	echo "<td><center><b>NO</b></center></td>";
}
if ($_SESSION['type']>=2)
{
echo "<td><center><a href='?/warnings&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
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