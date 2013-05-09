<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php printf($lang['mod/ip_info'], $_GET['ip']); ?></h2></div>
<div class="boxcontent">
<?php
if ($_SESSION['type']>=2)
{
?>
<a href="?/search/ip&ip=<?php echo $_GET['ip']; ?>"><?php echo $lang['mod/search_ip']; ?></a><br />
<?php
}
?>
<b><?php printf($lang['mod/recent_bans_ip'], 15); ?></b>
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
$result = $conn->query("SELECT * FROM bans WHERE ip='".$_GET['ip']."' ORDER BY created LIMIT 0, 15;");
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
echo "<td><center><b>never</b></center></td>";
}
echo "<td><center>".$row['boards']."</center></td>";
if ($_SESSION['type']>=2)
{
echo "<td><center><a href='?/bans&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
} else {
echo "<td></td>";
}
echo "</tr>";
}
?>
</tbody>
</table>
<br />
<b><?php echo $lang['mod/notes_ip']; ?></b>
<br />
<table>
<thead>
<td><?php echo $lang['mod/created']; ?></td>
<td><?php echo $lang['mod/note']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM ip_notes WHERE ip='".$_GET['ip']."';");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".date("d/m/Y(D)H:i:s", $row['created'])."</center></td>";
echo "<td>".$row['text']."</td>";
echo "<td><center><a href='?/ipnotes/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
echo "</td>";
}
?>
</tbody>
</table>
</div>
</div>
</div><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_note']; ?></h2></div>
<div class="boxcontent">
<form action="?/ipnotes/add&ip=<?php echo $_GET['ip']; ?>" method="POST">
<textarea name="note" cols=70 rows=12></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
		}
?>