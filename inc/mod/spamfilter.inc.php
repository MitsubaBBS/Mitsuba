<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		$search = "";
		$reason = "";
		$expires = "";
		if ((!empty($_POST['mode'])) && ($_POST['mode'] == "add"))
		{
			$continue = 0;
			if (empty($_POST['search'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $search = $_POST['search']; $continue = 1; }
			if (empty($_POST['reason'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $reason = $_POST['reason']; $continue = 1; }
			if ($continue == 1)
			{
				$search = $conn->real_escape_string($_POST['search']);
				$reason = $conn->real_escape_string($_POST['reason']);
				$boards = "";
				if ((!empty($_POST['all'])) && ($_POST['all']==1))
				{
					$boards = "*";
				} else {
					if (!empty($_POST['boards']))
					{
						foreach ($_POST['boards'] as $board)
						{
							$boards .= $board.",";
						}
					} else {
						$board = "*";
					}
				}
				if ($boards != "*") { $boards = substr($boards, 0, strlen($boards) - 1); }
				$expires = $_POST['expires'];
				$perma = 1;
				if (($expires == "0") || ($expires == "never") || ($expires == "") || ($expires == "perm") || ($expires == "permaban"))
				{
					$expires = "never";
				} else {
					$expirex = parse_time($expires);
					if (($expirex == false) && ($perma == 0))
					{
						echo "<b style='color: red;'>".$lang['mod/fool']."</b>";
					}
				}
				$conn->query("INSERT INTO spamfilter (`search`, `reason`, `boards`, `expires`, `active`, `regex`) VALUES ('".$search."', '".$reason."', '".$boards."', '".$expires."', 1, 0);");
			}
			$search = "";
			$reason = "";
			$expires = "";
		} elseif ((!empty($_POST['mode'])) && ($_POST['mode'] == "edit") && (!empty($_POST['id']))) {
			$continue = 0;
			if (empty($_POST['search'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $search = $_POST['search']; $continue = 1; }
			if (empty($_POST['reason'])) { echo "<b style='color: red;'>".$lang['mod/fill_all_fields']."</b>"; } else { $reason = $_POST['reason']; $continue = 1; }
			if ($continue == 1)
			{
				$search = $conn->real_escape_string($_POST['search']);
				$id = $_POST['id'];
				if (!is_numeric($id)) { echo "<b style='color: red;'>".$lang['mod/fool']."</b>"; }
				$reason = $conn->real_escape_string($_POST['reason']);
				$boards = "";
				if ((!empty($_POST['all'])) && ($_POST['all']==1))
				{
					$boards = "*";
				} else {
					if (!empty($_POST['boards']))
					{
						foreach ($_POST['boards'] as $board)
						{
							$boards .= $board.",";
						}
					} else {
						$board = "*";
					}
				}
				if ($boards != "*") { $boards = substr($boards, 0, strlen($boards) - 1); }
				$expires = $_POST['expires'];
				$perma = 1;
				if (($expires == "0") || ($expires == "never") || ($expires == "") || ($expires == "perm") || ($expires == "permaban"))
				{
					$expires = "never";
				} else {
					$expirex = parse_time($expires);
					if (($expirex == false) && ($perma == 0))
					{
						echo "<b style='color: red;'>".$lang['mod/fool']."</b>";
					}
				}
				$conn->query("UPDATE spamfilter SET `search`='".$search."', `reason`='".$reason."', `boards`='".$boards."', `expires`='".$expires."' WHERE id=".$id);
			}
			$search = "";
			$reason = "";
			$expires = "";
		}

		if ((!empty($_GET['d'])) && ($_GET['d'] == 1) && (!empty($_GET['n'])))
		{
			$n = $conn->real_escape_string($_GET['n']);
			if (!is_numeric($n)) { echo "<b style='color: red;'>".$lang['mod/fool']."</b>"; }
			$conn->query("DELETE FROM spamfilter WHERE id=".$n);
		}
		?>
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/manage_spamfilter']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/wf_search']; ?></td>
<td><?php echo $lang['mod/reason']; ?></td>
<td><?php echo $lang['mod/boards']; ?></td>
<td><?php echo $lang['mod/expires']; ?></td>
<td><?php echo $lang['mod/actions']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM spamfilter ORDER BY search ASC");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".htmlspecialchars($row['search'])."</center></td>";
echo "<td><center>".htmlspecialchars($row['reason'])."</center></td>";
echo "<td><center>".$row['boards']."</center></td>";
echo "<td><center>".$row['expires']."</center></td>";
echo "<td><center><a href='?/spamfilter&d=1&n=".$row['id']."'>".$lang['mod/delete']."</a> <a href='?/spamfilter/edit&n=".$row['id']."'>".$lang['mod/edit']."</a></center></td>";
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
<div class="boxbar"><h2><?php echo $lang['mod/wf_add']; ?></h2></div>
<div class="boxcontent">
<form action="?/spamfilter" method="POST">
<input type="hidden" name="mode" value="add">
<?php echo $lang['mod/wf_search']; ?>: <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"/><br />
<?php echo $lang['mod/reason']; ?>: <input type="text" name="reason" value="<?php echo htmlspecialchars($reason); ?>"/><br />
<?php echo $lang['mod/expires']; ?>: <input type="text" name="expires" value="<?php echo htmlspecialchars($expires); ?>"/><br />
<br /><br />
<?php echo $lang['mod/boards']; ?>: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1/> <?php echo $lang['mod/all']; ?><br/>
<select name="boards[]" id="boardSelect" multiple>
<?php
$result = $conn->query("SELECT * FROM boards;");
while ($row = $result->fetch_assoc())
{
echo "<option onClick='document.getElementById(\"all\").checked=false;' value='".$row['short']."'>/".$row['short']."/ - ".$row['name']."</option>";
}
?>
</select><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>