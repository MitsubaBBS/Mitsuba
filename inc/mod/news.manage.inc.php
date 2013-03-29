<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(2);
		?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/all_news_entries']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/title']; ?></td>
<td><?php echo $lang['mod/date']; ?></td>
<td><?php echo $lang['mod/edit']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM news ORDER BY date DESC;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".$row['title']."</td>";
echo "<td>".date("d/m/Y @ H:i", $row['date'])."</td>";
echo "<td><a href='?/news/edit&b=".$row['id']."'>".$lang['mod/edit']."</a></td>";
echo "<td><a href='?/news/delete&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>