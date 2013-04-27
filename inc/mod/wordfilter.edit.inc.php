<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(2);
		if (!empty($_GET['n']))
		{
		$result = $conn->query("SELECT * FROM wordfilter WHERE id=".$conn->real_escape_string($_GET['n']));
		if ($result->num_rows == 1)
		{
		$info = $result->fetch_assoc();
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/wf_edit']; ?></h2></div>
<div class="boxcontent">
<form action="?/wordfilter" method="POST">
<input type="hidden" name="mode" value="edit">
<input type="hidden" name="id" value="<?php echo $_GET['n']; ?>">
<?php echo $lang['mod/wf_search']; ?>: <input type="text" name="search" value="<?php echo htmlspecialchars($info['search']); ?>"/><br />
<?php echo $lang['mod/wf_replace']; ?>: <input type="text" name="replace" value="<?php echo htmlspecialchars($info['replace']); ?>"/><br />
<br /><br />
<?php
if ($info['boards'] == "*")
{
?>
<?php echo $lang['mod/boards']; ?>: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1 checked/> <?php echo $lang['mod/all']; ?><br/>
<select name="boards[]" id="boardSelect" multiple style="display: none;">
<?php
} else {
?>
<?php echo $lang['mod/boards']; ?>: <input type="checkbox" name="all" id="all" onClick="$('#boardSelect').toggle()" value=1/> <?php echo $lang['mod/all']; ?><br/>
<select name="boards[]" id="boardSelect" multiple>
<?php
}
?>
<?php
$boards = explode(",", $info['boards']);
$result = $conn->query("SELECT * FROM boards;");
while ($row = $result->fetch_assoc())
{
$checked = "";
if ($boards !== "*")
{
	if (in_array($boards, $row['short']))
	{
		$checked = " checked ";
	}
}
echo "<option onClick='document.getElementById(\"all\").checked=false;' value='",$row['short']."'".$checked.">/".$row['short']."/ - ".$row['name']."</option>";
}
?>
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
		}
		}
?>