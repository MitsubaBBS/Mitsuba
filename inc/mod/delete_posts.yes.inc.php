<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
		if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
			
			$threads = $conn->query("SELECT * FROM posts WHERE ip='".$_GET['ip']."' AND resto=0");
			while ($row = $threads->fetch_assoc())
			{
				$conn->query("DELETE FROM posts WHERE resto=".$row['id']." AND board='".$row['board']."'");
				if ($row['resto'] == 0)
				{
					unlink("./".$row['board']."/res/".$row['id'].".html");
				}
			}
			$conn->query("DELETE FROM posts WHERE ip='".$_GET['ip']."'");
			rebuildBoardCache($conn, $row['short']);
				?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/posts_deleted']; ?></h2></div>
<div class="boxcontent"><a href="?/info&ip=<?php echo $_GET['ip']; ?>"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
		}
?>