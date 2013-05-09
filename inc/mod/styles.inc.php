<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		$search = "";
		$replace = "";
		if ((!empty($_POST['mode'])) && ($_POST['mode'] == "upload"))
		{
			$shouldnt = 0;
			if (empty($_POST['name'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; $shouldnt = 1; }
			if (empty($_FILES['upfile']['tmp_name'])) { echo "<b style='color: red;'>".$lang['mod/no_file']."</b>"; $shouldnt = 1; }
			if (!$shouldnt)
			{
				$name = $conn->real_escape_string($_POST['name']);
				$filename = strtolower(preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $_FILES['upfile']['name']));
				if(move_uploaded_file($_FILES['upfile']['tmp_name'], "./styles/".$filename)) {
					$conn->query("INSERT INTO styles (`name`, `path`, `path_thread`, `path_index`, `default`) VALUES ('".$name."', '../styles/".$filename."', '../../styles/".$filename."', './styles/".$filename."', 0);");
					echo "<b style='color: green;'>".$lang['mod/style_uploaded']."</b>";
				}
			}
		}
		
		if ((!empty($_GET['def'])) && ($_GET['def'] == 1) && (!empty($_GET['n'])))
		{
			$n = $conn->real_escape_string($_GET['n']);
			if (!is_numeric($n)) { echo "<b style='color: red;'>".$lang['mod/fool']."</b>"; }
			$conn->query("UPDATE styles SET `default`=0");
			$conn->query("UPDATE styles SET `default`=1 WHERE id=".$n);
		}

		if ((!empty($_GET['d'])) && ($_GET['d'] == 1) && (!empty($_GET['n'])))
		{
			$n = $conn->real_escape_string($_GET['n']);
			if (!is_numeric($n)) { echo "<b style='color: red;'>".$lang['mod/fool']."</b>"; }
			$conn->query("DELETE FROM styles WHERE id=".$n);
		}
		
		if ((!empty($_GET['f'])) && ($_GET['f'] == 1) && (!empty($_GET['n'])))
		{
			$n = $conn->real_escape_string($_GET['n']);
			if (!is_numeric($n)) { echo "<b style='color: red;'>".$lang['mod/fool']."</b>"; }
			$result = $conn->query("SELECT * FROM styles WHERE id=".$n);
			$row = $result->fetch_assoc();
			unlink($row['path_index']);
			$conn->query("DELETE FROM styles WHERE id=".$n);
		}
		?>
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/manage_styles']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/name']; ?></td>
<td><?php echo $lang['mod/file']; ?></td>
<td><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM styles ORDER BY name ASC");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".htmlspecialchars($row['name']);
if ($row['default'] == 1) { echo " ( <b>".$lang['mod/default']."</b> )"; }
echo "</center></td>";
echo "<td><center><a href='".htmlspecialchars($row['path_index'])."' target='_blank'>".$lang['mod/show_file']."</a></center></td>";
echo "<td><center><a href='?/styles&f=1&n=".$row['id']."'>".$lang['mod/delete']."</a>(<a href='?/styles&d=1&n=".$row['id']."'>".$lang['mod/delete_no_file']."</a>)";
if ($row['default'] == 0)
{
	echo " <a href='?/styles&def=1&n=".$row['id']."'>".$lang['mod/make_default']."</a>";
}
echo "</center></td>";
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
<div class="boxbar"><h2><?php echo $lang['mod/upload_style']; ?></h2></div>
<div class="boxcontent">
<form action="?/styles" method="POST" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="2097152">
<input type="hidden" name="mode" value="upload">
<?php echo $lang['mod/file']; ?>: <input id="postFile" name="upfile" type="file"><br />
<?php echo $lang['mod/name']; ?>: <input type="text" name="name"/><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>