<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if ((!empty($_POST['old'])) && (!empty($_POST['new'])) && (!empty($_POST['new2'])))
		{
			if ($_POST['new']==$_POST['new2'])
			{
		
			$result = $conn->query("SELECT password FROM users WHERE id=".$_SESSION['id']);
			$row = $result->fetch_assoc();
				if ($row['password'] != hash("sha512", $_POST['old']))
				{
							?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/pwd_no_match']; ?></h2></div>
<div class="boxcontent"><a href="?/password"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
			<?php
				} else {
					$conn->query("UPDATE users SET password='".hash("sha512", $_POST['new'])."' WHERE id=".$_SESSION['id']);
				?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/pwd_updated']; ?></h2></div>
<div class="boxcontent"><a href="?/password"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
				}
			} else {
				?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/pwd_wrong']; ?></h2></div>
<div class="boxcontent"><a href="?/password"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
			<?php
			}
		} else {
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/pwd_change']; ?></h2></div>
<div class="boxcontent">
<form action="?/password" method="POST">
<?php echo $lang['mod/pwd_current']; ?>: <input type="password" name="old"><br />
<?php echo $lang['mod/pwd_new']; ?>: <input type="password" name="new"><br />
<?php echo $lang['mod/pwd_confirm']; ?>: <input type="password" name="new2"><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>"><br />
</form>
</div>
</div>
</div>
		<?php
		}
?>