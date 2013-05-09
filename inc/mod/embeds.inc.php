<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		$name = "";
		$code = "";
		$regex = "";
		if ((!empty($_POST['mode'])) && ($_POST['mode'] == "add"))
		{
			$continue = 0;
			if (empty($_POST['name'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $name = $_POST['name']; $continue = 1; }
			if (empty($_POST['code'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $code = $_POST['code']; $continue = 1; }
			if (empty($_POST['regex'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $regex = $_POST['regex']; $continue = 1; }
			if ($continue == 1)
			{
				if (!preg_match("/^[a-zA-Z0-9]*$/", $_POST['name']))
				{ echo "<b style='color: red;'>".$lang['mod/name_error']."</b>"; }
				else {
					$name = $conn->real_escape_string($_POST['name']);
					$regex = $conn->real_escape_string($_POST['regex']);
					$code = $conn->real_escape_string($_POST['code']);
					$conn->query("INSERT INTO embeds (name, regex, code) VALUES ('".$name."', '".$regex."', '".$code."');");
					$name = "";
					$regex = "";
					$code = "";
				}
			}
		} elseif ((!empty($_POST['mode'])) && ($_POST['mode'] == "edit") && (!empty($_POST['name2']))) {
			$continue = 0;
			if (empty($_POST['name'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $name = $_POST['name']; $continue = 1; }
			if (empty($_POST['regex'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $regex = $_POST['regex']; $continue = 1; }
			if (empty($_POST['code'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $code = $_POST['code']; $continue = 1; }
			if ($continue == 1)
			{
				if (!preg_match("/^[a-zA-Z0-9]*$/", $_POST['name']))
				{ echo "<b style='color: red;'>".$lang['mod/name_error']."</b>"; }
				else {
					$name = $conn->real_escape_string($_POST['name']);
					$name2 = $conn->real_escape_string($_POST['name2']);
					$regex = $conn->real_escape_string($_POST['regex']);
					$code = $conn->real_escape_string($_POST['code']);
					$conn->query("UPDATE embeds SET name='".$name."', code='".$code."', regex='".$regex."' WHERE name='".$name2."';");
				}
			}
			$name = "";
			$code = "";
			$regex = "";
		}

		if ((!empty($_GET['d'])) && ($_GET['d'] == 1) && (!empty($_GET['n'])))
		{
			$n = $conn->real_escape_string($_GET['n']);
			$conn->query("DELETE FROM embeds WHERE name='".$n."'");
		}
		?>
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/manage_embeds']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/name']; ?></td>
<td><?php echo $lang['mod/regex']; ?></td>
<td><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM embeds ORDER BY name ASC");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".$row['name']."</center></td>";
echo "<td>".htmlspecialchars($row['regex'])."</td>";
echo "<td><center><a href='?/embeds&d=1&n=".$row['name']."'>".$lang['mod/delete']."</a> <a href='?/embeds/edit&n=".$row['name']."'>".$lang['mod/edit']."</a></center></td>";
echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
<br /><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_embed']; ?></h2></div>
<div class="boxcontent">
<form action="?/embeds" method="POST">
<input type="hidden" name="mode" value="add">
<?php echo $lang['mod/name']; ?>: <input type="text" name="name" value="<?php echo $name; ?>"/><br />
<?php echo $lang['mod/regex']; ?>: <input type="text" name="regex" value="<?php echo $regex; ?>"/><br />
<?php echo $lang['mod/html_code']; ?>: <textarea cols=40 rows=9 name="code"><?php echo $code; ?></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>