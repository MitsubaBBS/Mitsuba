<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(2);
if (!empty($_GET['m']))
{
	if ($_GET['m'] == "clear")
	{
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$conn->query("DELETE FROM appeals WHERE id=".$_GET['id']);
		}
	}
	
	if ($_GET['m'] == "clear_all_yes")
	{
		$conn->query("TRUNCATE TABLE appeals;");
	}
}
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/appeals']; ?></h2></div>
<div class="boxcontent">
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
			echo "<td><center>".$ban['ip']."</center></td>";
		} else {
			echo "<td>".$ban['start_ip']." - ".$ban['end_ip']." ( ".$row['ip']." )</td>";
		}
		if ($ban['expires'] != 0)
		{
			$left = floor($ban['expires'] - time()/(60*60*24));
		} else {
			$left = -1;
		}
		echo "<td>".$ban['reason']."</td>";
		echo "<td>".$ban['note']."</td>";
		if ($left = -1)
		{
			echo "<td><center><b>".$lang['mod/permaban']."</b></center></td>";
		} else {
			echo "<td><center>".$left." days</center></td>";
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
</div>
</div>
</div>
<script type="text/javascript">parent.nav.location.reload();</script>