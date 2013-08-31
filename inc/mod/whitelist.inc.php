<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("whitelist.view");
if ((isset($_GET['del'])) && ($_GET['del']==1))
	{
$mitsuba->admin->reqPermission("whitelist.delete");
		if ((!empty($_GET['b'])) && (is_numeric($_GET['b'])))
		{
			$conn->query("DELETE FROM whitelist WHERE id=".$_GET['b']);
		}
	}
if ((!empty($_GET['m'])) && ($_GET['m']=="add"))
{
$mitsuba->admin->reqPermission("whitelist.add");
		$mitsuba->admin->ui->checkToken($_POST['token']);
		if (!filter_var($_POST['ip'], FILTER_VALIDATE_IP))
		{
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/ip_syntax_wrong']); ?>
<a href="?/whitelist"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
</body>
</html>
		<?php
		exit;
		}
		$nolimits = 0;
		if ((!empty($_POST['nolimits'])) && ($_POST['nolimits']==1))
		{
			$nolimits = 1;
		}
		$conn->query("INSERT INTO whitelist (ip, mod_id, note, nolimits) VALUES ('".$_POST['ip']."', ".$_SESSION['id'].", '".$conn->real_escape_string($_POST['note'])."', ".$nolimits.")");
		
}
	?>
<?php $mitsuba->admin->ui->startSection($lang['mod/manage_whitelist']); ?>

<table>
<thead>
<tr>
<td><?php echo $lang['mod/ip']; ?></td>
<td><?php echo $lang['mod/staff_note']; ?></td>
<td><?php echo $lang['mod/nolimits']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM whitelist ORDER BY id DESC;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".$row['ip']."</center></td>";
echo "<td>".$row['note']."</td>";
if ($row['nolimits'] == 1)
{
echo "<td><center><b>YES</b></center></td>";
} else {
echo "<td><center><b>NO</b></center></td>";
}
echo "<td><center><a href='?/whitelist&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></center></td>";
echo "</tr>";
}
?>
</tbody>
</table>
<?php $mitsuba->admin->ui->endSection(); ?>
<?php $mitsuba->admin->ui->startSection($lang['mod/add_whitelist']); ?>

<form action="?/whitelist&m=add" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/ip']; ?>: <input type="text" name="ip" /><br />
<?php echo $lang['mod/staff_note']; ?>: <input type="text" name="note" /><br />
<input type="checkbox" name="nolimits" value="1"/><?php echo $lang['mod/nolimits']; ?><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>