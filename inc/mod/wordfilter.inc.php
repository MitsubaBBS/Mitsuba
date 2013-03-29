<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(2);
		$search = "";
		$replace = "";
		if ((!empty($_POST['mode'])) && ($_POST['mode'] == "add"))
		{
			if (empty($_POST['search'])) { echo "<b style='color: red;'>Please fill search field!</b>"; } else { $search = $_POST['search']; }
			if (empty($_POST['replace'])) { echo "<b style='color: red;'>Please fill replace field!</b>"; } else { $replace = $_POST['replace']; }
			$search = $conn->real_escape_string($_POST['search']);
			$replace = $conn->real_escape_string($_POST['replace']);
			$conn->query("INSERT INTO wordfilter (`search`, `replace`, `active`) VALUES ('".$search."', '".$replace."', 1);");
			$search = "";
			$replace = "";
		} elseif ((!empty($_POST['mode'])) && ($_POST['mode'] == "edit") && (!empty($_POST['id']))) {
			
			if (empty($_POST['search'])) { echo "<b style='color: red;'>Please fill search field!</b>"; } else { $search = $_POST['search']; }
			if (empty($_POST['replace'])) { echo "<b style='color: red;'>Please fill replace field!</b>"; } else { $replace = $_POST['replace']; }
			$search = $conn->real_escape_string($_POST['search']);
			$id = $_POST['id'];
			if (!is_numeric($id)) { echo "<b style='color: red;'>Don't try to fool me!</b>"; }
			$replace = $conn->real_escape_string($_POST['replace']);
			$conn->query("UPDATE wordfilter SET `search`='".$search."', `replace`='".$replace."' WHERE id=".$id);
			$search = "";
			$replace = "";
		}

		if ((!empty($_GET['d'])) && ($_GET['d'] == 1) && (!empty($_GET['n'])))
		{
			$n = $conn->real_escape_string($_GET['n']);
			if (!is_numeric($n)) { echo "<b style='color: red;'>Don't try to fool me!</b>"; }
			$conn->query("DELETE FROM wordfilter WHERE id=".$n);
		}
		?>
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Wordfilter</h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td>Search</td>
<td>Replace</td>
<td>Actions</td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM wordfilter ORDER BY search ASC");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td>".htmlspecialchars($row['search'])."</td>";
echo "<td>".htmlspecialchars($row['replace'])."</td>";
echo "<td><a href='?/wordfilter&d=1&n=".$row['id']."'>Delete</a> <a href='?/wordfilter/edit&n=".$row['id']."'>Edit</a></td>";
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
<div class="boxbar"><h2>Add wordfilter</h2></div>
<div class="boxcontent">
<form action="?/wordfilter" method="POST">
<input type="hidden" name="mode" value="add">
Search: <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"/><br />
Replace: <input type="text" name="replace" value="<?php echo htmlspecialchars($replace); ?>"/><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>