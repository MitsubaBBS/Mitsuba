<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission(3);
	?>
<?php $mitsuba->admin->ui->startSection($lang['mod/new_user']); ?>

<form action="?/users/add" method="POST">
<?php echo $lang['mod/username']; ?>: <input type="text" name="username" /><br />
<?php echo $lang['mod/password']; ?>: <input type="password" name="password"/><br />
<?php echo $lang['mod/type']; ?>: <select name="type"><option value="1"><?php echo $lang['mod/janitor']; ?></option><option value="2"><?php echo $lang['mod/moderator']; ?></option><option value="3"><?php echo $lang['mod/administrator']; ?></option></select>

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
$result = $conn->query("SELECT * FROM users;");
$usern = $result->num_rows;
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".$row['username']."</center></td>";
echo "<td><center>";
switch ($row['type'])
{
	case 0:
		echo $lang['mod/disabled'];
		break;
	case 1:
		echo $lang['mod/janitor'];
		break;
	case 2:
		echo $lang['mod/moderator'];
		break;
	case 3:
		echo $lang['mod/administrator'];
		break;
	default:
		echo $lang['mod/faggot'];
		break;
}
echo "<center></td>";
echo "<td><center>".$row['boards']."</center></td>";
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