<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("groups.view");
	?>
<?php $mitsuba->admin->ui->startSection($lang['mod/all_groups']); ?>

<table>
<thead>
<tr>
<td style="width: 30%;"><?php echo $lang['mod/name']; ?></td>
<td style="width: 20%;"><?php echo $lang['mod/capcode']; ?></td>
<td style="width: 10%;"><?php echo $lang['mod/edit']; ?></td>
<td style="width: 10%;"><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM groups;");
$groupn = $result->num_rows;
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".$row['name']."</center></td>";
echo "<td><center>## ".$row['capcode']."<center></td>";
echo "<td><center><a href='?/groups/edit&id=".$row['id']."'>".$lang['mod/edit']."</a></center></td>";
if ($groupn != 1)
{
echo "<td><center><a href='?/groups/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
} else {
echo "<td></td>";
}
echo "</tr>";
}
?>
</tbody>
</table>
<?php $mitsuba->admin->ui->endSection(); ?>
<?php $mitsuba->admin->ui->startSection($lang['mod/new_group']); ?>

<form action="?/groups/add" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/username']; ?>: <input type="text" name="username" /><br />

<br />
<input type="submit" value="<?php echo $lang['mod/add_group']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?><br />