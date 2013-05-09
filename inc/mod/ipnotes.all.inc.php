<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/all_ip_notes']; ?></h2></div>
<div class="boxcontent">
	<table>
<thead>
<td><?php echo $lang['mod/created']; ?></td>
<td><?php echo $lang['mod/note']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM ip_notes;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".date("d/m/Y(D)H:i:s", $row['created'])."</center></td>";
echo "<td>".$row['text']."</td>";
if ($_SESSION['type']>=2)
{
echo "<td><center><a href='?/ipnotes/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
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