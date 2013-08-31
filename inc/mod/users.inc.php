<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("users.view");
	?>
<?php $mitsuba->admin->ui->startSection($lang['mod/new_user']); ?>

<form action="?/users/add" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/username']; ?>: <input type="text" name="username" /><br />
<?php echo $lang['mod/password']; ?>: <input type="password" name="password"/><br />
<?php echo $lang['mod/type']; ?>: 
<select name="type">
<?php 
$groups = $conn->query("SELECT * FROM groups");
while ($row = $groups->fetch_assoc())
{
	echo "<option value=".$row['id'].">".$row['name']."</option>";
}
?>
</select>

<br /><br />
<?php
$mitsuba->admin->ui->getBoardList();
?>
<br />
<input type="submit" value="<?php echo $lang['mod/add_user']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?><br />
<?php $mitsuba->admin->ui->startSection($lang['mod/all_users']); ?>

<table>
<thead>
<tr>
<td style="width: 30%;"><?php echo $lang['mod/username']; ?></td>
<td style="width: 20%;"><?php echo $lang['mod/type']; ?></td>
<td style="width: 30%;"><?php echo $lang['mod/boards']; ?></td>
<td style="width: 10%;"><?php echo $lang['mod/edit']; ?></td>
<td style="width: 10%;"><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT users.*, groups.name AS gname FROM users LEFT JOIN groups ON users.group=groups.id;");
$usern = $result->num_rows;
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".$row['username']."</center></td>";
echo "<td><center>";
echo $row['gname'];
echo "<center></td>";
if ($row['boards']=="%")
{
	echo "<td><center>All boards</center></td>";
} else {
	echo "<td><center>".$row['boards']."</center></td>";
}
echo "<td><center><a href='?/users/edit&id=".$row['id']."'>".$lang['mod/edit']."</a></center></td>";
if ($usern != 1)
{
echo "<td><center><a href='?/users/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
} else {
echo "<td></td>";
}
echo "</tr>";
}
?>
</tbody>
</table>
<?php $mitsuba->admin->ui->endSection(); ?>