<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("search.ip");

		if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
			$mitsuba->admin->ui->startSection(sprintf($lang['mod/showing_posts'], $_GET['ip']));
			?>
			<a href="?/delete_posts&ip=<?php echo $_GET['ip']; ?>"><?php echo $lang['mod/delete_ip']; ?></a>
			<table>
			<thead>
			<tr>
			<td><?php echo $lang['mod/post']; ?></td>
			<td><?php echo $lang['mod/name']; ?></td>
			<td><?php echo $lang['mod/e_mail']; ?></td>
			<td><?php echo $lang['mod/date']; ?></td>
			<td><?php echo $lang['mod/comment']; ?></td>
			<td><?php echo $lang['mod/subject']; ?></td>
			<td><?php echo $lang['mod/file']; ?></td>
			<td><?php echo $lang['mod/delete']; ?></td>
			</tr>
			</thead>
			<tbody>
			
			<?php
			require_once( "libs/jbbcode/Parser.php" );
			$parser = new \JBBCode\Parser();
			$bbcode = $conn->query("SELECT * FROM bbcodes;");
			
			while ($row = $bbcode->fetch_assoc())
			{
				$parser->addBBCode($row['name'], $row['code']);
			}
			$posts = $conn->query("SELECT * FROM posts WHERE ip='".$_GET['ip']."'");
			while ($row = $posts->fetch_assoc())
			{
				echo "<tr><td class='text-center text-nowrap'>";
				
				if($row['resto'] == 0)
				{
					echo("<a href='./".$row['board']."/res/".$row['id'].".html'>/".$row['board']."/".$row['id'].".html</a>");
					
				} else {
					echo("<a href='./".$row['board']."/res/".$row['resto'].".html#p".$row['id']."'>/".$row['board']."/".$row['resto'].".html#p".$row['id']."</a");
				}
				
				echo "</td><td class='text-center'>"; //roote technology xD
				
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
				echo "<td class='text-center'>".$row['email']."</td>";
				echo "<td class='text-center text-nowrap'>".date("d/m/Y @ H:i", $row['date'])."</td>";
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
						echo "<td class='text-center'><a href='./".$row['board']."/src/".$row['filename']."' target='_blank'><img src='./".$row['board']."/src/thumb/".$row['filename']."' /></a></td>";
					}
				} else {
					echo "<td></td>";
				}
				echo '<td class="text-center">[<a href="?/delete_post&b='.$row['board'].'&p='.$row['id'].'">D</a>] [<a href="?/delete_post&b='.$row['board'].'&p='.$row['id'].'&f=1">F</a>] [<a href="?/bans/add&b='.$row['board'].'&p='.$row['id'].'">B</a>]</td>';
			}
		
		}
			
		if ( (empty($_GET['ip'])) && (!empty($_GET['id'])) && (is_numeric($_GET['id'])) && (!empty($_GET['board'])) )
		{
			$mitsuba->admin->ui->startSection(sprintf("Search by post password Beta xD /%s/%d", $_GET['board'], $_GET['id']));
			?>
			<table>
			<thead>
			<tr>
			<td><?php echo $lang['mod/post']; ?></td>
			<td><?php echo $lang['mod/name']; ?></td>
			<td><?php echo $lang['mod/ip']; 	?></td>
			<td><?php echo $lang['mod/e_mail']; ?></td>
			<td><?php echo $lang['mod/date']; ?></td>
			<td><?php echo $lang['mod/comment']; ?></td>
			<td><?php echo $lang['mod/subject']; ?></td>
			<td><?php echo $lang['mod/file']; ?></td>
			<td><?php echo $lang['mod/delete']; ?></td>
			</tr>
			</thead>
			<tbody>
			<?php
			require_once( "libs/jbbcode/Parser.php" );
			$parser = new \JBBCode\Parser();
			$bbcode = $conn->query("SELECT * FROM bbcodes;");
			
			while ($row = $bbcode->fetch_assoc())
			{
				$parser->addBBCode($row['name'], $row['code']);
			}
			
			
			$qID = $conn->real_escape_string($_GET['id']);
			$qBoard = $conn->real_escape_string($_GET['board']);

			$getPass = $conn->query("SELECT password FROM posts WHERE board='".$qBoard."' AND id='".$qID."' ");
			$rPass = $getPass->fetch_row();
			
			$posts = $conn->query("SELECT * FROM posts WHERE password='".$rPass[0]."'");
			while ($row = $posts->fetch_assoc())
			{
				echo "<tr><td class='text-center text-nowrap'>";
				
				if($row['resto'] == 0)
				{
					echo("<a href='./".$row['board']."/res/".$row['id'].".html'>/".$row['board']."/".$row['id'].".html</a>");
					
				} else {
					echo("<a href='./".$row['board']."/res/".$row['resto'].".html#p".$row['id']."'>/".$row['board']."/".$row['resto'].".html#p".$row['id']."</a>");
				}
				
				echo "</td><td class='text-center'>";
				
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
				echo "<td class='text-center'>".$row['ip']."</td>";
				echo "<td class='text-center'>".$row['email']."</td>";
				echo "<td class='text-center text-nowrap'>".date("d/m/Y @ H:i", $row['date'])."</td>";
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
						echo "<td class='text-center'><a href='./".$row['board']."/src/".$row['filename']."' target='_blank'><img src='./".$row['board']."/src/thumb/".$row['filename']."' /></a></td>";
					}
				} else {
					echo "<td></td>";
				}
				echo '<td class="text-center">[<a href="?/delete_post&b='.$row['board'].'&p='.$row['id'].'">D</a>] [<a href="?/delete_post&b='.$row['board'].'&p='.$row['id'].'&f=1">F</a>] [<a href="?/bans/add&b='.$row['board'].'&p='.$row['id'].'">B</a>]</td>';
			
			}
		}
			
			?>
			</tbody>
			</table>

<?php $mitsuba->admin->ui->endSection(); ?>
