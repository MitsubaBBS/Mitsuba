<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$ip = "";
		if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
			$ip = $_GET['ip'];
		}
		if ((!empty($_POST['ip'])) && (filter_var($_POST['ip'], FILTER_VALIDATE_IP)))
		{
			$ip = $_POST['ip'];
		}
		
		if (empty($ip))
		{
		?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/no_ip']; ?></h2></div>
<div class="boxcontent"><a href="?/ipnotes"><?php echo $lang['mod/back']; ?></a></div>
</div></div>

			<?php
		} else {
			if ((!empty($ip)) && (!empty($_POST['note'])))
			{
				$note = processEntry($conn, $_POST['note']);
				$conn->query("INSERT INTO ip_notes (ip, text, created, mod_id) VALUES ('".$ip."', '".$note."', ".time().", ".$_SESSION['id'].")");
				?>
				<div class="box-outer top-box">
	<div class="box-inner">
	<div class="boxbar"><h2><?php echo $lang['mod/ip_note_added']; ?></h2></div>
	<div class="boxcontent"><a href="?/ipnotes"><?php echo $lang['mod/back']; ?></a></div>
	</div></div>

				<?php
			}
		}
		if (empty($_POST['note']))
		{
		?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/fill_all_fields']; ?></h2></div>
<div class="boxcontent"><a href="?/ipnotes"><?php echo $lang['mod/back']; ?></a></div>
</div></div>

			<?php
		}
?>