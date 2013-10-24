<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("ads.list");
if (isset($_POST['mode']))
{
	switch($_POST['mode'])
	{
		case "add":
			$mitsuba->admin->reqPermission("ads.add");
			$mitsuba->admin->ui->checkToken($_POST['token']);
			$shown = 0;
			if ((!empty($_POST['shown'])) && (is_numeric($_POST['shown'])) && ($_POST['shown']==1)) { $shown = 1; }
			$text = "";
			$board = "%";
			$position = "head";
			if (!empty($_POST['text'])) { $text = $conn->real_escape_string($_POST['text']); }
			if (!empty($_POST['board'])) { $board = $conn->real_escape_string($_POST['board']); }
			if (!empty($_POST['position'])) { $position = $conn->real_escape_string($_POST['position']); }
			$conn->query("INSERT INTO ads (`text`, `board`, `position`, `show`) VALUES ('".$text."', '".$board."', '".$position."', ".$shown.")");
			echo $conn->error;
			break;
		case "edit":
			$mitsuba->admin->reqPermission("ads.update");
			$mitsuba->admin->ui->checkToken($_POST['token']);
			if (is_numeric($_POST['id']))
			{
				$shown = 0;
				if ((!empty($_POST['shown'])) && (is_numeric($_POST['shown'])) && ($_POST['shown']==1)) { $shown = 1; }
				$text = "";
				$board = "%";
				$position = "head";
				if (!empty($_POST['text'])) { $text = $conn->real_escape_string($_POST['text']); }
				if (!empty($_POST['board'])) { $board = $conn->real_escape_string($_POST['board']); }
				if (!empty($_POST['position'])) { $position = $conn->real_escape_string($_POST['position']); }
				$conn->query("UPDATE ads SET board='".$board."', `text`='".$text."', position='".$position."', `show`=".$shown." WHERE id=".$_POST['id']);
			}
			break;
	}
}
if (isset($_GET['mode']))
{
	switch($_GET['mode'])
	{
		case "delete":
			$mitsuba->admin->reqPermission("ads.delete");
			if (is_numeric($_GET['i']))
			{
				$conn->query("DELETE FROM ads WHERE id=".$_GET['i']);
			}
			break;
	}
}
?>
<?php $mitsuba->admin->ui->startSection($lang['mod/manage_ads']); ?>
<table>
<thead>
<tr>
<td><?php echo $lang['mod/board']; ?></td>
<td><?php echo $lang['mod/position']; ?></td>
<td><?php echo $lang['mod/text']; ?></td>
<td><?php echo $lang['mod/shown']; ?></td>
<td><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM ads");
while ($row = $result->fetch_assoc())
{
	echo "<tr>";
	if ($row['board']=="%")
	{
		echo "<td class='text-center'>All boards</td>";
	} else {
		echo "<td class='text-center'>/".$row['board']."/</td>";
	}

	echo "<td class='text-center'>";
	switch ($row['position'])
	{
		case "head":
			echo $lang['mod/pos_head'];
			break;
		case "aboveform":
			echo $lang['mod/pos_aboveform'];
			break;
		case "underform":
			echo $lang['mod/pos_underform'];
			break;
		case "footer":
			echo $lang['mod/pos_footer'];
			break;
		case "bottom":
			echo $lang['mod/pos_bottom'];
			break;
		case "rules":
			echo $lang['mod/pos_rules'];
			break;
		default:
			echo "<b>WRONG POSITION: ".$row['position']."</b>";
			break;
	}
	echo "</td>";
	echo "<td>".htmlspecialchars($row['text'])."</td>";
	if ($row['show']==1)
	{
		echo "<td class='text-center'>YES</td>";
	} else {
		echo "<td class='text-center'>NO</td>";
	}
	echo "<td class='text-center'><a href='?/ads/edit&i=".$row['id']."'>Edit</a> / <a href='?/ads&mode=delete&i=".$row['id']."'>Delete</a></td>";
	echo "</tr>";
}
?>
</tbody>
</table>
<?php $mitsuba->admin->ui->endSection(); ?>
<?php $mitsuba->admin->ui->startSection($lang['mod/add_ad']); ?>
<form action="?/ads" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<input type="hidden" name="mode" value="add">
<?php echo $lang['mod/board']; ?>: 
<select name="board">
<option value='%'>All boards</option>
<?php
$result = $conn->query("SELECT * FROM boards;");
while ($row = $result->fetch_assoc())
{
	echo "<option value='".$row['short']."'>/".$row['short']."/ - ".$row['name']."</option>";
}
?>
</select><br />
<?php echo $lang['mod/position']; ?>:
<select name="position">
<option value='head'><?php echo $lang['mod/pos_head']; ?></option>
<option value='aboveform'><?php echo $lang['mod/pos_aboveform']; ?></option>
<option value='underform'><?php echo $lang['mod/pos_underform']; ?></option>
<option value='footer'><?php echo $lang['mod/pos_footer']; ?></option>
<option value='bottom'><?php echo $lang['mod/pos_bottom']; ?></option>
</select><br />
<?php echo $lang['mod/text']; ?>:
<textarea name="text" cols="70" rows="10"></textarea><br />
<?php echo $lang['mod/shown']; ?>: <input type="checkbox" name="shown" value="1" checked /><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>