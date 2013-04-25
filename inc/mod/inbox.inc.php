<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/inbox']; ?></h2></div>
<div class="boxcontent">
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
				echo "<td><center><b><a href='?/inbox/read&id=".$row['id']."'>".$row['title']."</a></b></center></td>";
			} else {
				echo "<td><center><a href='?/inbox/read&id=".$row['id']."'>".$row['title']."</a></center></td>";
			}
			echo "<td><center>".date("d/m/Y @ H:i", $row['created'])."</center></td>";
			echo "<td><center>".$row['username']."</center></td>";
			echo "<td><center><a href='?/inbox/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
			echo "</tr>";
		}
		?>
		</tbody>
		</div></div></div>