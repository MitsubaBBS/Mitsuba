<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("info.view");
if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
		?>
<?php $mitsuba->admin->ui->startSection(sprintf($lang['mod/ip_info'], $_GET['ip'])); ?>
<?php
if ($mitsuba->admin->checkPermission("search.ip"))
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
echo "<td class='text-center text-nowrap'>".$row['ip']."</td>";
echo "<td>".$row['reason']."</td>";
echo "<td>".$row['note']."</td>";
echo "<td class='text-center text-nowrap'>".date("d/m/Y @ H:i", $row['created'])."</td>";
if ($row['expires'] != 0)
{
echo "<td class='text-center text-nowrap'>".date("d/m/Y @ H:i", $row['expires'])."</td>";
} else {
echo "<td class='text-center'><b>never</b>td>";
}
if ($row['boards']=="%")
{
	echo "<td class='text-center'>All boards</td>";
} else {
	echo "<td class='text-center'>".$row['boards']."</td>";
}
if ($mitsuba->admin->checkPermission("bans.delete"))
{
echo "<td class='text-center'><a href='?/bans&del=1&b=".$row['id']."'>".$lang['mod/delete']."</a></td>";
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
echo "<td class='text-center text-nowrap'>".date("d/m/Y(D)H:i:s", $row['created'])."</td>";
echo "<td>".$row['text']."</td>";
echo "<td class='text-center'><a href='?/ipnotes/delete&id=".$row['id']."'>".$lang['mod/delete']."</a></td>";
echo "</td>";
}
?>
</tbody>
</table>
<?php $mitsuba->admin->ui->endSection(); ?><br />
<?php $mitsuba->admin->ui->startSection($lang['mod/add_note']); ?>

<form action="?/ipnotes/add&ip=<?php echo $_GET['ip']; ?>" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<textarea name="note" cols=70 rows=12></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		}
?>