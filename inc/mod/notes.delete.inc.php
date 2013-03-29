<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$note = $conn->query("SELECT * FROM notes WHERE id=".$_GET['id']);
			if ($note->num_rows == 1)
			{
				$info = $note->fetch_assoc();
				if ($info['mod_id'] == $_SESSION['id'])
				{
					$conn->query("DELETE FROM notes WHERE id=".$_GET['id']);
					?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/note_deleted']; ?></h2></div>
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
<div class="boxbar"><h2><?php echo $lang['mod/error']; ?></h2></div>
<div class="boxcontent">
<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
<?php
				}
			} else {
			?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/error']; ?></h2></div>
<div class="boxcontent">
<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
<?php
			}
		} else {
				?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/error']; ?></h2></div>
<div class="boxcontent">
<a href="?/notes"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>
<?php
		}
?>