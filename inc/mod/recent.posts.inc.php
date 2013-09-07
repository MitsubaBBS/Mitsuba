<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("recent.posts");
if ((!empty($_GET['max'])) && (is_numeric($_GET['max'])))
		{
			$max = $_GET['max'];
		} else {
			$max = 50;
		}
		$mitsuba->admin->ui->startSection(sprintf($lang['mod/recent_n_posts'], $max)); 
		?>

			<?php echo $lang['mod/show_recent_none']; ?>: <a href="?/recent/posts">50</a> <a href="?/recent/posts&max=100">100</a> <a href="?/recent/posts&max=250">250</a> <a href="?/recent/posts&max=500">500</a>
<table>
			<thead>
			<tr>
			<td><?php echo $lang['mod/post']; ?></td>
			<td><?php echo $lang['mod/name']; ?></td>
			<td><?php echo $lang['mod/e_mail']; ?></td>
			<td><?php echo $lang['mod/date']; ?></td>
			<td class="comments"><?php echo $lang['mod/comment']; ?></td>
			<td><?php echo $lang['mod/subject']; ?></td>
			<td><?php echo $lang['mod/file']; ?></td>
			<td><?php echo $lang['mod/delete']; ?></td>
			</tr>
			</thead>
			<tbody>
			<?php
			$post_array = array();
			$num = 0;
			require_once( "libs/jbbcode/Parser.php" );
			$parser = new \JBBCode\Parser();
			$bbcode = $conn->query("SELECT * FROM bbcodes;");
			
			while ($row = $bbcode->fetch_assoc())
			{
				$parser->addBBCode($row['name'], $row['code']);
			}
			$posts = $conn->query("SELECT * FROM posts ORDER BY date DESC LIMIT 0, ".$max);
			while ($row = $posts->fetch_assoc())
			{
				echo "<tr><td>";
				$resto = $row['resto'];
				$op = 0;
				if ($row['resto'] == 0) { $resto = $row['id']; $op = 1; }
				echo "<center><a href='?/board&b=".$row['board']."&t=".$resto."'>/".$row['board']."/".$row['id']."</a></center> ";
				if ($op == 1) { echo "<center><b>OP</b></center>"; }
				echo "</td><td>";
				$trip = "";
				if (!empty($row['trip']))
				{
					$trip = "<span class='postertrip'>!".$row['trip']."</span>";
				}
				
				if (!empty($row['capcode_text']))
				{
					echo '<span class="name"><span style="'.$row['capcode_style'].'">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="'.$row['capcode_style'].'">## '.$row['capcode_text'].'</span></span>';
				} else {
					echo '<span class="name">'.$row['name'].'</span>'.$trip;
				}
			
			
				echo "</td>";
				echo "<td><center>".$row['email']."</center></td>";
				echo "<td><center>".date("d/m/Y @ H:i", $row['date'])."</center></td>";
				if ($row['raw'] != 1)
				{
					if ($row['raw'] == 2)
					{
						$comment = $mitsuba->caching->processComment($row['board'], $row['comment'], $parser, 2, 0);
					} else {
						$comment = $mitsuba->caching->processComment($row['board'], $row['comment'], $parser, 2);
					}
				} else {
					$comment = $row['comment'];
				}
				echo "<td>".$comment."</td>";
				echo "<td>".$row['subject']."</td>";
				if (!empty($row['filename']))
				{
					if ($row['filename'] == "deleted")
					{
						echo "<td><img src='./img/deleted.gif' /></td>";
					} elseif (substr($row['filename'], 0, 8) == "spoiler:") {
						echo "<td><a href='./".$row['board']."/src/".substr($row['filename'], 8)."' target='_blank'><img src='./".$row['board']."/src/thumb/".substr($row['filename'], 8)."' /></a><br /><b>Spoiler image</b></td>";
					} elseif (substr($row['filename'], 0, 6) == "embed:") {
						echo "<td><a href='".substr($row['filename'], 6)."'>Embed</a></td>";
					} else {
						echo "<td><center><a href='./".$row['board']."/src/".$row['filename']."' target='_blank'><img src='./".$row['board']."/src/thumb/".$row['filename']."' /></a></center></td>";
					}
				} else {
					echo "<td></td>";
				}
				echo '<td><center>[<a href="?/delete_post&b='.$row['board'].'&p='.$row['id'].'">D</a>] [<a href="?/delete_post&b='.$row['board'].'&p='.$row['id'].'&f=1">F</a>] [<a href="?/bans/add&b='.$row['board'].'&p='.$row['id'].'">B</a>]</center></td>';
			}
			
			
			?>
			</tbody>
			</table>
<?php $mitsuba->admin->ui->endSection(); ?>
