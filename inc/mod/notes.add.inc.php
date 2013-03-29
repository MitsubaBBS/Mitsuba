<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if (!empty($_POST['note']))
		{
			$note = $conn->real_escape_string($_POST['note']);
			$conn->query("INSERT INTO notes (mod_id, note, created) VALUES (".$_SESSION['id'].", '".$note."', ".time().")");
		?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/note_added']; ?></h2></div>
<div class="boxcontent">
<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
<?php
		} else {
				?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/fill_all_fields']; ?></h2></div>
<div class="boxcontent">
<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
<?php
		}
?>