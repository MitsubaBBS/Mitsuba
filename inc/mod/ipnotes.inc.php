<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/recent_ip_notes']; ?></h2></div>
<div class="boxcontent">
	<table>
<thead>
<td><?php echo $lang['mod/created']; ?></td>
<td><?php echo $lang['mod/note']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM ip_notes LIMIT 0, 15;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".date("d/m/Y(D)H:i:s", $row['created'])."</center></td>";
echo "<td>".$row['text']."</td>";
echo "<td><center><a href='?/ipnotes/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
echo "</tr>";
}
?>
</tbody>
</table>
<?php printf($lang['mod/showing_notes'], 15); ?> <a href="?/ipnotes/all"><?php echo $lang['mod/show_all']; ?></a>
</div>
</div>
</div><br />
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_ip_note']; ?></h2></div>
<div class="boxcontent">
<form action="?/ipnotes/add" method="POST">
<?php echo $lang['mod/ip']; ?>: <input type="text" name="ip" /><br />
<textarea name="note" cols=70 rows=12></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>