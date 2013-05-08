<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
		$result = $conn->query("SELECT users.username, pm.* FROM pm LEFT JOIN users ON pm.from_user=users.id WHERE (pm.to_user=".$_SESSION['id']." OR pm.from_user=".$_SESSION['id'].") AND pm.id=".$_GET['id']);
		if ($result->num_rows == 1)
			{
				$row = $result->fetch_assoc();
				if (($row['read_msg'] != 1) && ($row['to_user']==$_SESSION['id']))
				{
					$conn->query("UPDATE pm SET read_msg=1 WHERE id=".$_GET['id']);
				}
				?>
				<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/read_msg']; ?></h2></div>
<div class="boxcontent">
<?php echo $lang['mod/from']; ?>: <b><?php echo $row['username']; ?></b><br />
<?php echo $lang['mod/title']; ?>: <b><?php echo $row['title']; ?></b><br />
<?php echo $lang['mod/text']; ?>:<br />
<?php echo $row['text']; ?><br /><br />
<a href="?/inbox/new&id=<?php $_GET['id']; ?>">[ <?php echo $lang['mod/reply']; ?> ]</a>
</div>
</div>
</div>
<script type="text/javascript">parent.nav.location.reload();</script>
				<?php
			}
		}
?>