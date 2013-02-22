<?php
function generateStyles($conn, $in_thread = 0)
{

}

function generateBoardLinks($conn, $in_thread = 0)
{
	$links = '<div id="boardLinks">';
	$links .= generateLinks($conn, -1, $in_thread);
	$links .= '</div>';
	return $links;
}

function generateLinks($conn, $id, $in_thread = 0)
{
	// MAGIC IS HAPPENING HERE, DO NOT EDIT
	// MAGIC IS HAPPENING HERE, DO NOT EDIT
	// MAGIC IS HAPPENING HERE, DO NOT EDIT
	// MAGIC IS HAPPENING HERE, DO NOT EDIT
	// MAGIC IS HAPPENING HERE, DO NOT EDIT
	// MAGIC IS HAPPENING HERE, DO NOT EDIT
	$result = mysqli_query($conn, "SELECT * FROM links WHERE parent=".$id." ORDER BY short ASC, title ASC, id DESC;");
	if (mysqli_num_rows($result) == 0)
	{
		return "";
	}
	$links = "";
	$no = 0;
	while ($row = mysqli_fetch_assoc($result))
	{	
		if (!empty($row['url']))
		{
			if ($no > 0) { $links .= ' / '; }
			if ((!empty($row['url_thread'])) && ($in_thread == 1))
			{
				$links .= '<a href="'.$row['url_thread'].'" title="'.$row['title'].'">'.$row['short'].'</a>';
			} else {
				$links .= '<a href="'.$row['url'].'" title="'.$row['title'].'">'.$row['short'].'</a>';
			}
		}
		$l2 = generateLinks($conn, $row['id'], $in_thread);
		if (!empty($l2))
		{
			$links .= "[".$l2."] ";
		}
		$no = 1;
	}
	return $links;
}

function getBoardLinks($conn, $in_thread = 0)
{
	$config = getConfig($conn);
	if ($in_thread == 1)
	{
		return $config['boardLinks_thread'];
	} else {
		return $config['boardLinks'];
	}
}

function processComment($board, $conn, $string, $thread = 0, $specialchars = 1)
{
	$new = $string;
	if ($specialchars == 1)
	{
		$new = htmlspecialchars($new);
	}
	$lines = explode("\n", $new);
	$new = "";
	foreach ($lines as $line)
	{
		if (substr($line, 0, 8) == "&gt;&gt;")
		{
			$space = explode(" ", $line, 2);
			if (is_numeric(substr($space[0], 8)))
			{
				$result = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE id='".substr($space[0], 8)."';");
				if (empty($space[1])) { $space[1] = ""; }
				if (mysqli_num_rows($result) == 1)
				{
					$row = mysqli_fetch_assoc($result);
					if ($row['resto'] != 0)
					{
						if ($thread == 1)
						{
							$new .= '<a href="../res/'.$row['resto'].'.html#p'.$row['id'].'" class="quotelink">'.$space[0].'</a> '.$space[1].'<br />';
						} elseif ($thread == 0) {
							$new .= '<a href="./res/'.$row['resto'].'.html#p'.$row['id'].'" class="quotelink">'.$space[0].'</a> '.$space[1].'<br />';
						} else {
							$new .= '<a href="?/board&b='.$board.'&t='.$row['resto'].'#p'.$row['id'].'" class="quotelink">'.$space[0].'</a> '.$space[1].'<br />';
						}
					} else {
						if ($thread == 1)
						{
							$new .= '<a href="../res/'.$row['id'].'.html#p'.$row['id'].'" class="quotelink">'.$space[0].'</a> '.$space[1].'<br />';
						} elseif ($thread == 0) {
							$new .= '<a href="./res/'.$row['id'].'.html#p'.$row['id'].'" class="quotelink">'.$space[0].'</a> '.$space[1].'<br />';
						} else {
							$new .= '<a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row['id'].'" class="quotelink">'.$space[0].'</a> '.$space[1].'<br />';
						}
					}
				} else {
					$new .= "<span class='quote'>".$space[0]."</span> ".$space[1]."<br />";
				}
			}
		} elseif (substr($line, 0, 4) == "&gt;")
		{
			$new .= "<span class='quote'>".$line."</span><br />";
		} else {
			$rurl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
			if(preg_match($rurl, $line, $url)) {
				$new .= preg_replace($rurl, '<a href="'.$url[0].'">'.$url[0].'</a> ', $line)."<br />";
			} else {
				$new .= $line."<br />";
			}
		}
	}
	return $new;
}

function generateView($conn, $board, $threadno = 0)
{
	$pages = 15;
	$config = getConfig($conn);
	$board = mysqli_real_escape_string($conn, $board);
	if (!isBoard($conn, $board))
	{
		return -16;
	}
	$boarddata = getBoardData($conn, $board);
	if (!is_numeric($threadno))
	{
		return -15; //error
	}
	
	if ($threadno != 0)
	{
		$pages = 0;
	}
	
	for ($pg = 0; $pg <= $pages; $pg++)
	{
		$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">';
		if ($threadno != 0)
		{
			$file .= "<head><title>/".$boarddata['short']."/ - ".$boarddata['name']."</title><link rel='stylesheet' href='../../styles/stylesheet.css' />";
			$file .= "<script type='text/javascript' src='../../js/jquery.js'></script>";
			$file .= "<script type='text/javascript' src='../../js/common.js'></script>";
			$file .= "<script type='text/javascript' src='../../js/jquery.cookie.js'></script>";
			$file .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			$file .= "</head><body>";
			$file .= getBoardLinks($conn, 1);
		} else {
			$file .= "<head><title>/".$boarddata['short']."/ - ".$boarddata['name']."</title><link rel='stylesheet' href='../styles/stylesheet.css' />";
			$file .= "<script type='text/javascript' src='../js/jquery.js'></script>";
			$file .= "<script type='text/javascript' src='../js/common.js'></script>";
			$file .= "<script type='text/javascript' src='../js/jquery.cookie.js'></script>";
			$file .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			$file .= "</head><body>";
			$file .= getBoardLinks($conn, 0);
		}
		$file .= '<div class="boardBanner">';
		$imagesDir = './rnd/';
		$images = glob($imagesDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
		$randomImage = $images[array_rand($images)]; 
		if ($threadno != 0)
		{
			$file .= '<img class="title" src="../.'.$randomImage.'" alt="Mitsuba" />';
		} else {
			$file .= '<img class="title" src=".'.$randomImage.'" alt="Mitsuba" />';
		}
		$file .= '<div class="boardTitle">/'.$boarddata['short'].'/ - '.$boarddata['name'].'</div>';
		$file .= '<div class="boardSubtitle">'.$boarddata['des'].'</div>';
		$file .= '</div>';
		$file .= '<br />';
		$file .= '<hr />';
			
		$locked = 0;
		
		if ($threadno != 0)
		{
			$result = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE id=".$threadno.";");
			if (mysqli_num_rows($result) == 1)
			{
				$tdata = mysqli_fetch_assoc($result);
				$locked = $tdata['locked'];
			} else {
				return;
			}
		}
		
		

		
		if ($locked == 0)
		{
			if ($threadno != 0)
		{
				$file .= '<div class="postingMode">Posting mode: Reply</div>';
				$file .= '<div class="navLinks">[<a href=".././" accesskey="a">Return</a>]</div>';
				$file .= '<form action="../../imgboard.php" method="post" enctype="multipart/form-data">';
			} else {
				$file .= '<form action="../imgboard.php" method="post" enctype="multipart/form-data">';
			}
			$file .= '<input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
				<input type="hidden" name="mode" value="regist" />
				<table class="postForm" id="postForm">
				<tbody>';
			if ($boarddata['noname'] == 0)
			{
				$file .= '<tr>
					<td>Name</td>
					<td><input name="name" type="text" /></td>
					</tr>';
			}
			$file .= '<tr>
				<td>E-mail</td>
				<td><input name="email" type="text" /></td>
				</tr>
				<tr>
				<td>Subject</td>
				<td><input name="sub" type="text" />';
			$file .= '<input type="hidden" name="board" value="'.$board.'" />';
			if ($threadno != 0)
			{
				$file .= '<input type="hidden" name="resto" value="'.$threadno.'" />';
			}
			$file .= '<input type="submit" value="Submit" /></td>
				</tr>
				<tr>
				<td>Comment</td>
				<td><textarea name="com" cols="35" rows="4"></textarea></td>
				</tr>
				<tr>
				<td>File</td>
				<td><input id="postFile" name="upfile" type="file" />';
			if ($boarddata['spoilers'] == 1)
			{
				$file .= '<label><input type="checkbox" name="spoiler" value="1">Spoiler Image?</label>';
			}
			if ($boarddata['embeds'] == 1)
			{
				$file .= '<br />Embed: <input type="text" name="embed"/>';
			}
			$file .= '</td>
				</tr>
				<tr>
				<td>Password</td>
				<td><input id="postPassword" name="pwd" type="password" maxlength="8" /> <span class="password">(Password used for deletion)</span></td>
				</tr>
				<tr class="rules">
				<td colspan="2">
				<ul class="rules">
				<li>Supported file types are: GIF, JPG, PNG</li>
				<li>Maximum file size allowed is 2048 KB.</li>
				<li>Images greater than 250x250 pixels will be thumbnailed.</li>
				</ul>
				</td>
				</tr>
				</tbody>
				</table>
				</form>';
		} else {
			$file .= "<div class='closed'><h1>This thread is locked.</h1></div>";
		}
		$file .= "<hr />";
		if (!empty($config['global_message']))
		{
			$file .= '<div class="globalMessage" id="globalMessage">';
			$file .= $config['global_message'];
			$file .= '</div>';
		}
		
		if (!empty($boarddata['message']))
		{
			$file .= '<hr />';
			$file .= '<div class="globalMessage" id="boardMessage">';
			$file .= $boarddata['message'];
			$file .= '</div>';
		}
		$file .= '<hr />';
		if ($threadno != 0)
		{
			$file .= '<form id="delform" action="../../imgboard.php" method="post"><div class="board">';
		} else {
			$file .= '<form id="delform" action="../imgboard.php" method="post"><div class="board">';
		}

		if ($threadno != 0)
		{
			$result = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE id=".$threadno.";");
		} else {
			
			$result = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE resto=0 ORDER BY sticky DESC, lastbumped DESC LIMIT ".($pg*10).",10");
		}

		while ($row = mysqli_fetch_assoc($result))
		{
			$file .= '<div class="thread" id="t'.$row['id'].'">';
			$file .= '<div class="postContainer opContainer" id="pc'.$row['id'].'">';
			$file .= '<div id="p'.$row['id'].'" class="post op">';
			if ($row['filename'] == "deleted")
			{
				$file .= '<div class="file" id="f'.$row['id'].'">';
				$file .= '<div class="fileInfo">';
				if ($threadno != 0)
				{
					$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <b>deleted</b></span>';
				} else {
					$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <b>deleted</b></span>';
				}
				$file .= '</div>';
				if ($threadno != 0)
				{
					$file .= '<a class="fileThumb" target="_blank"><img src="../../img/deleted.gif" alt="Deleted"/></a>';
				} else {
					$file .= '<a class="fileThumb" target="_blank"><img src="../img/deleted.gif" alt="Deleted"/></a>';
				}
				$file .= '</div>';
			} elseif (substr($row['filename'], 0, 8) == "spoiler:")
			{
				$file .= '<div class="file" id="f'.$row['id'].'">';
				$file .= '<div class="fileInfo">';
				if ($threadno != 0)
				{
					$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <a href="../src/'.substr($row['filename'],8).'" target="_blank"><b>Spoiler image</b></a></span>';
				} else {
					$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <a href="./src/'.substr($row['filename'],8).'" target="_blank"><b>Spoiler image</b></a></span>';
				}
				$file .= '</div>';
				if ($threadno != 0)
				{
					$file .= '<a class="fileThumb" href="../src/'.$row['filename'].'" target="_blank"><img src="../../img/spoiler.png" alt="Deleted"/></a>';
				} else {
					$file .= '<a class="fileThumb" href="./src/'.$row['filename'].'" target="_blank"><img src="../img/spoiler.png" alt="Deleted"/></a>';
				}
				$file .= '</div>';
			} elseif (substr($row['filename'], 0, 6) == "embed:")
			{
				$file .= '<div class="file" id="f'.$row['id'].'">';
				$file .= '<div class="fileInfo">';
				$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <b>Embed</b></span>';
				
				$file .= '</div>';
				$file .= '<a class="fileThumb">'.getEmbed(substr($row['filename'], 6)).'</a>';
				
				$file .= '</div>';
			} else {
				$file .= '<div class="file" id="f'.$row['id'].'">';
				$file .= '<div class="fileInfo">';
				if ($threadno != 0)
				{
					$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <a href="../src/'.$row['filename'].'" target="_blank">'.$row['filename'].'</a> -('.$row['filesize'].', '.$row['imagesize'].', <span title="'.$row['orig_filename'].'">'.$row['orig_filename'].'</span>)</span>';
				} else {
					$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <a href="./src/'.$row['filename'].'" target="_blank">'.$row['filename'].'</a> -('.$row['filesize'].', '.$row['imagesize'].', <span title="'.$row['orig_filename'].'">'.$row['orig_filename'].'</span>)</span>';
				}
				$file .= '</div>';
				if ($threadno != 0)
				{
					$file .= '<a class="fileThumb" href="../src/'.$row['filename'].'" target="_blank"><img src="../src/thumb/'.$row['filename'].'" alt="Thumbnail"/></a>';
				} else {
					$file .= '<a class="fileThumb" href="./src/'.$row['filename'].'" target="_blank"><img src="./src/thumb/'.$row['filename'].'" alt="Thumbnail"/></a>';
				}
				
				$file .= '</div>';
			}
			$file .= '<div class="postInfo" id="pi'.$row['id'].'">';
			$file .= '<input type="checkbox" name="'.$row['id'].'" value="delete" />';
			$file .= '<span class="subject">'.$row['subject'].'</span>';
			$trip = "";
			if (!empty($row['trip']))
			{
				$trip = "<span class='postertrip'>!".$row['trip']."</span>";
			}
			$poster_id = "";
			if ((!empty($row['poster_id'])) && ($boarddata['ids']==1))
			{
				$poster_id = '<span class="posteruid">(ID: '.$row['poster_id'].')</span>';
			}
			if (!empty($row['email'])) {
				$file .= '<span class="nameBlock"><a href="mailto:'.$row['email'].'" class="useremail"><span class="name">'.$row['name'].'</span>'.$trip.'</a> '.$poster_id.'</span>';
			} else {
				if ($row['capcode'] == 1)
				{
					$file .= '<span class="nameBlock"><span class="name"><span style="color:#800080">'.$row['name'].'</span></span>'.$trip.' '.$poster_id.' <span class="commentpostername"><span style="color:#800080">## Mod</span></span></span>';
				} elseif ($row['capcode'] == 2)
				{
					$file .= '<span class="nameBlock"><span class="name"><span style="color:#FF0000">'.$row['name'].'</span></span>'.$trip.' '.$poster_id.' <span class="commentpostername"><span style="color:#FF0000">## Admin</span></span></span>';
				} elseif ($row['capcode'] == 3)
				{
					$file .= '<span class="nameBlock"><span class="name"><span style="color:#FF00FF">'.$row['name'].'</span></span>'.$trip.' '.$poster_id.' <span class="commentpostername"><span style="color:#FF00FF">## Faggot</span></span></span>';
				} else {
					$file .= '<span class="nameBlock"><span class="name">'.$row['name'].'</span>'.$trip.' '.$poster_id.'</span>';
				}
			}
			$file .= ' <span class="dateTime">'.date("d/m/Y(D)H:i:s", $row['date']).'</span> ';
		
			if ($threadno != 0)
			{
				$file .= '<span class="postNum"><a href="./res/'.$row['id'].'.html#p'.$row['id'].'" title="Highlight this post">No.</a><a href="./res/'.$row['id'].'.html#q'.$row['id'].'" title="Quote this post">'.$row['id'].'</a>';
				if ($row['locked']==1)
				{
					$file .= '<img src="../../img/closed.gif" alt="Closed" title="Closed" class="stickyIcon" />';
				}
				if ($row['sticky']==1)
				{
					$file .= '<img src="../../img/sticky.gif" alt="Sticky" title="Sticky" class="stickyIcon" />';
				}
				$file .= '</span>';
			} else {
				$file .= '<span class="postNum"><a href="./res/'.$row['id'].'.html#p'.$row['id'].'" title="Highlight this post">No.</a><a href="./res/'.$row['id'].'.html#q'.$row['id'].'" title="Quote this post">'.$row['id'].'</a> ';
				if ($row['locked']==1)
				{
					$file .= '<img src="../img/closed.gif" alt="Closed" title="Closed" class="stickyIcon" />';
				}
				if ($row['sticky']==1)
				{
					$file .= '<img src="../img/sticky.gif" alt="Sticky" title="Sticky" class="stickyIcon" />';
				}
				$file .= '&nbsp; <span>[<a href="./res/'.$row['id'].'.html" class="replylink">Reply</a>]</span></span>';
			}
			$file .= '</div>';
			
			
			
			$file .= '<blockquote class="postMessage" id="m'.$row['id'].'">';
			if ($row['raw'] != 1)
			{
				if ($row['raw'] == 2)
				{
					$file .= processComment($board, $conn, $row['comment'], $threadno != 0, 0);
				} else {
					$file .= processComment($board, $conn, $row['comment'], $threadno != 0);
				}
			} else {
				$file .= $row['comment'];
			}
			$file .= '</blockquote>';
			
			
			
			$file .= '</div>';
			$file .= '</div>';
			if ($threadno != 0)
			{
				$posts = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE resto=".$row['id']." ORDER BY id ASC");
			} else {
			$posts = mysqli_query($conn, "SELECT COUNT(*) FROM posts_".$board." WHERE resto=".$row['id']." ORDER BY id ASC");
			$row1 = mysqli_fetch_row($posts);
			if ($row1[0] == 0)
			{
				$file .= '</div><hr />';
				continue;
			}
			if ($row1[0] > 3)
			{
				$file .= '<span class="summary">'.($row1[0]-3).' posts omitted. Click <a href="./res/'.$row['id'].'.html" class="replylink">here</a> to view.</span>';
			}
			$offset = 0;
			if ($row1[0] > 3)
			{
				$offset = $row1[0] - 3;
				
			}
			$posts = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE resto=".$row['id']." ORDER BY id ASC LIMIT ".$offset.",3");
				
			}
			while ($row2 = mysqli_fetch_assoc($posts))
			{
				$file .= '<div class="postContainer replyContainer" id="pc'.$row2['id'].'">';
				$file .= '<div class="sideArrows" id="sa'.$row2['id'].'">&gt;&gt;</div>';
				$file .= '<div id="p'.$row2['id'].'" class="post reply">';
				$file .= '<div class="postInfo" id="pi'.$row2['id'].'">';
				$file .= '<input type="checkbox" name="'.$row2['id'].'" value="delete" />';
				$file .= '<span class="subject">'.$row2['subject'].'</span>';
				$trip = "";
				if (!empty($row2['trip']))
				{
					$trip = "<span class='postertrip'>!".$row2['trip']."</span>";
				}
				$poster_id = "";
				if ((!empty($row['poster_id'])) && ($boarddata['ids']==1))
				{
					$poster_id = '<span class="posteruid">(ID: '.$row2['poster_id'].')</span>';
				}
				if (!empty($row2['email'])) {
					$file .= '<span class="nameBlock"><a href="mailto:'.$row2['email'].'" class="useremail"><span class="name">'.$row2['name'].'</span>'.$trip.'</a> '.$poster_id.'</span>';
				} else {
					if ($row2['capcode'] == 1)
					{
						$file .= '<span class="nameBlock"><span class="name"><span style="color:#800080">'.$row2['name'].'</span></span>'.$trip.' '.$poster_id.' <span class="commentpostername"><span style="color:#800080">## Mod</span></span></span>';
					} elseif ($row2['capcode'] == 2)
					{
						$file .= '<span class="nameBlock"><span class="name"><span style="color:#FF0000">'.$row2['name'].'</span></span>'.$trip.' '.$poster_id.' <span class="commentpostername"><span style="color:#FF0000">## Admin</span></span></span>';
					} elseif ($row2['capcode'] == 3)
					{
						$file .= '<span class="nameBlock"><span class="name"><span style="color:#FF00FF">'.$row2['name'].'</span></span>'.$trip.' '.$poster_id.' <span class="commentpostername"><span style="color:#FF00FF">## Faggot</span></span></span>';
					} else {
						$file .= '<span class="nameBlock"><span class="name">'.$row2['name'].'</span>'.$trip.' '.$poster_id.'</span>';
					}
				}
				$file .= ' <span class="dateTime">'.date("d/m/Y(D)H:i:s", $row2['date']).'</span> ' ;
				if ($threadno != 0)
				{
					$file .= '<span class="postNum"><a href="../res/'.$row2['resto'].'.html#p'.$row2['id'].'" title="Highlight this post">No.</a><a href="../res/'.$row2['resto'].'.html#q'.$row2['id'].'" title="Quote this post">'.$row2['id'].'</a> &nbsp;</span>';
				} else {
					$file .= '<span class="postNum"><a href="./res/'.$row2['resto'].'.html#p'.$row2['id'].'" title="Highlight this post">No.</a><a href="./res/'.$row2['resto'].'.html#q'.$row2['id'].'" title="Quote this post">'.$row2['id'].'</a> &nbsp;</span>';
				}
				$file .= '</div>';
				if (!empty($row2['filename']))
				{
					if ($row2['filename'] == "deleted")
					{
						$file .= '<div class="file" id="f'.$row2['id'].'">';
						$file .= '<div class="fileInfo">';
						if ($threadno != 0)
						{
							$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <b>deleted</b></span>';
						} else {
							$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <b>deleted</b></span>';
						}
						$file .= '</div>';
						if ($threadno != 0)
						{
							$file .= '<a class="fileThumb" target="_blank"><img src="../../img/deleted.gif" alt="Deleted" /></a>';
						} else {
							$file .= '<a class="fileThumb" target="_blank"><img src="../img/deleted.gif" alt="Deleted" /></a>';
						}
					
						$file .= '</div>';
					} elseif (substr($row2['filename'], 0, 8) == "spoiler:")
					{
						$file .= '<div class="file" id="f'.$row2['id'].'">';
						$file .= '<div class="fileInfo">';
						if ($threadno != 0)
						{
							$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <a href="../src/'.substr($row2['filename'], 8).'" target="_blank"><b>Spoiler image</b></a></span>';
						} else {
							$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <a href="./src/'.substr($row2['filename'], 8).'" target="_blank"><b>Spoiler image</b></a></span>';
						}
						$file .= '</div>';
						if ($threadno != 0)
						{
							$file .= '<a class="fileThumb" href="../src/'.substr($row2['filename'], 8).'" target="_blank"><img src="../../img/spoiler.png" alt="Deleted"/></a>';
						} else {
							$file .= '<a class="fileThumb" href="./src/'.substr($row2['filename'], 8).'" target="_blank"><img src="../img/spoiler.png" alt="Deleted"/></a>';
						}
						$file .= '</div>';
					} elseif (substr($row2['filename'], 0, 6) == "embed:")
					{
						$file .= '<div class="file" id="f'.$row2['id'].'">';
						$file .= '<div class="fileInfo">';
						$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <b>Embed</b></span>';
						
						$file .= '</div>';
						$file .= '<a class="fileThumb">'.getEmbed(substr($row2['filename'], 6)).'</a>';
						
						$file .= '</div>';
					} else {
						$file .= '<div class="file" id="f'.$row2['id'].'">';
						$file .= '<div class="fileInfo">';
						if ($threadno != 0)
						{
							$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <a href="../src/'.$row2['filename'].'" target="_blank">'.$row2['filename'].'</a> -('.$row2['filesize'].', '.$row2['imagesize'].', <span title="'.$row2['orig_filename'].'">'.$row2['orig_filename'].'</span>)</span>';
						} else {
							$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <a href="./src/'.$row2['filename'].'" target="_blank">'.$row2['filename'].'</a> -('.$row2['filesize'].', '.$row2['imagesize'].', <span title="'.$row2['orig_filename'].'">'.$row2['orig_filename'].'</span>)</span>';
						}
						$file .= '</div>';
						
						if ($threadno != 0)
						{
							$file .= '<a class="fileThumb" href="../src/'.$row2['filename'].'" target="_blank"><img src="../src/thumb/'.$row2['filename'].'" alt="Thumbnail"/></a>';
						} else {
							$file .= '<a class="fileThumb" href="./src/'.$row2['filename'].'" target="_blank"><img src="./src/thumb/'.$row2['filename'].'" alt="Thumbnail"/></a>';
						}
					
						$file .= '</div>';
					}
				}
				
				$file .= '<blockquote class="postMessage" id="m'.$row2['id'].'">';
				if ($row2['raw'] != 1)
				{
					if ($row2['raw'] == 2)
					{
						$file .= processComment($board, $conn, $row2['comment'], $threadno != 0, 0);
					} else {
						$file .= processComment($board, $conn, $row2['comment'], $threadno != 0);
					}
				} else {
					$file .= $row2['comment'];
				}
				$file .= '</blockquote>';
				
				$file .= '</div>';
				
				
				
				$file .= '</div>';
			}
			
			$file .= '</div>';
			$file .= '<hr />';
		}
		$file .= "</div>";
		$file .= '<div class="deleteform">
			<input type="hidden" name="board" value="'.$board.'" />
			<input type="hidden" name="mode" value="usrform" />Delete Post [<input type="checkbox" name="onlyimgdel" value="on" />File Only] Password <input type="password" id="delPassword" name="pwd" maxlength="8" /> 
			<input type="submit" name="delete" value="Delete" /><br />
			Reason <input type="text" name="reason" /><input type="submit" name="report" value="Report" /></div>';
		$file .= "</form>";
		if ($threadno == 0)
		{
			$file .= '<div class="pagelist desktop">';
			$file .= '<div class="prev">';
			if ($pg != 0)
			{
				if ($pg != 1)
				{
					$file .= '<form action="./'.($pg-1).'.html" onsubmit="location=this.action; return false;"><input type="submit" value="Previous" /></form>';
				} else {
					$file .= '<form action="./index.html" onsubmit="location=this.action; return false;"><input type="submit" value="Previous" /></form>';
				}
			} else {
				$file .= '<span>Next</span>';
			}
			$file .= ' </div>';
			$file .= '<div class="pages">';
			for ($i = 0; $i <= $pages; $i++)
			{
				if ($i == $pg)
				{
					$file .= "[<strong>".$i."</strong>] ";
				} else {
					if ($i != 0)
					{
						$file .= "[<a href='./".$i.".html'>".$i."</a>] ";
					} else {
						$file .= "[<a href='./index.html'>".$i."</a>] ";
					}
				}
			}
			$file .= '</div>';
			$file .= ' <div class="next">';
			if ($pg != $pages)
			{
				$file .= '<form action="./'.($pg+1).'.html" onsubmit="location=this.action; return false;"><input type="submit" value="Next" /></form>';
			} else {
				$file .= '<span>Previous</span>';
			}
			$file .= '</div>';
			$file .= '</div>';
		}
		$file .= "</body></html>";
		if ($threadno != 0)
		{
			$handle = fopen("./".$board."/res/".$threadno.".html", "w");
		} else {
			if ($pg != 0)
			{
				$handle = fopen("./".$board."/".$pg.".html", "w");
			} else {
				$handle = fopen("./".$board."/index.html", "w");
			}
		}
		fwrite($handle, $file);
		fclose($handle);
	}
}

function updateThreads($conn, $board)
{
	$board = mysqli_real_escape_string($conn, $board);
	if (!isBoard($conn, $board))
	{
		return -16;
	}
	$result = mysqli_query($conn, "SELECT id FROM posts_".$board." WHERE resto=0");
	while ($row = mysqli_fetch_assoc($result))
	{
		generateView($conn, $board, $row['id']);
	}
}


function regenThumbnails($conn, $board)
{
	$board = mysqli_real_escape_string($conn, $board);
	if (!isBoard($conn, $board))
	{
		return -16;
	}
	$result = mysqli_query($conn, "SELECT filename, resto FROM posts_".$board);
	while ($row = mysqli_fetch_assoc($result))
	{
		if ((!empty($row['filename'])) && ($row['filename'] != "deleted"))
		{
			if (substr($row['filename'], 0, 8) == "spoiler:")
			{
				if ($row['resto'] != 0)
				{
					thumb($board, substr($row['filename'], 8), 125);
				} else {
					thumb($board, substr($row['filename'], 8));
				}
			} elseif (substr($row['filename'], 0, 6) != "embed:") {
				if ($row['resto'] != 0)
				{
					thumb($board, $row['filename'], 125);
				} else {
					thumb($board, $row['filename']);
				}
			}
		}
	}
}

function generateFrontpage($conn)
{
	$config = getConfig($conn);
	if ($config['frontpage_style'] == 0) //Kusaba X style
	{
	
		$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		$file .= '<html>
			<head>
			<title>'.$config['sitename'].'</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			</head>
			<frameset cols="200px,*" frameborder="1" border="1" bordercolor="#800">
			<frame src="'.$config['frontpage_menu_url'].'" id="nav">
			<frame src="'.$config['news_url'].'" name="main" id="main">
			<noframes>
			<h1>'.$config['sitename'].'</h1>
			<p>This page uses frames!</p>
			</noframes>
			</frameset>
			</html>';
		$handle = fopen("./".$config['frontpage_url'], "w");
		fwrite($handle, $file);
		fclose($handle);
		
		
		$menu = '<title>Mitsuba Navigation</title>
			<link rel="stylesheet" href="./styles/menu.css" />
			<script type="text/javascript">
			function toggle(button,area) {
			var tog=document.getElementById(area);
			if(tog.style.display)	{
				tog.style.display="";
			}	else {
				tog.style.display="none";
			}';
		$menu .= "button.innerHTML=(tog.style.display)?'+':'&minus;';
			createCookie('nav_show_'+area, tog.style.display?'0':'1', 365);
			}";
		$menu .= '</script>
			</head>
			<body>';
		$menu .= "<h1>".$config['sitename']."</h1>";
		$cats = mysqli_query($conn, "SELECT * FROM links WHERE parent=-1;");
		while ($row = mysqli_fetch_assoc($cats))
		{
			$menu .= '<h2><span class="coll" onclick="toggle(this,' ."'".$row['short']."');". '" title="Toggle Category">&minus;</span>'.$row['title'].'</h2>';
			$menu .= '<div id="'.$row['short'].'" style="">
				<ul>';
			$children = mysqli_query($conn, "SELECT * FROM links WHERE parent=".$row['id']);
			while ($child = mysqli_fetch_assoc($children))
			{
				if (!empty($child['url_index']))
				{
					$menu .= '<li><a href="'.$child['url_index'].'" target="main">/'.$child['short'].'/ - '.$child['title'].'</a></li>';
				} else {
					$menu .= '<li><a href="'.$child['url'].'" target="main">/'.$child['short'].'/ - '.$child['title'].'</a></li>';
				}
			}
			$menu .= '</ul></div>';
		}
		$handle = fopen("./".$config['frontpage_menu_url'], "w");
		fwrite($handle, $menu);
		fclose($handle);
	} elseif ($config['frontpage_style'] == 1) //2chan.tk style
	{
		
		$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		$file .= '<html>
			<head>
			<title>'.$config['sitename'].'</title>
			<link rel="stylesheet" href="./index.css" />
			<link rel="stylesheet" href="./global.css" />
			<link rel="stylesheet" href="./table.css" />
			</head>
			<body>';
		$file .= '<div id="doc">
			<br /><br />';
		$file .= '<div class="box-outer top-box">
			<div class="box-inner">
			<div class="boxbar"><h2>News</h2></div>
			<div class="boxcontent">';
		$result = mysqli_query($conn, "SELECT * FROM news ORDER BY date DESC;");
		while ($row = mysqli_fetch_assoc($result))
		{
			$file .= '<div class="content">';
			$file .= '<h3><span class="newssub">'.$row['title'].' by '.$row['who'].' - '.date("d/m/Y @ H:i", $row['date']).'</span></span></h3>';
			$file .= $row['text'];
			$file .= '</div>';
		}
		$file .= '</div>
			</div>
			</div>
			</div>
			</body>
			</html>';
		$handle = fopen("./".$config['frontpage_url'], "w");
		fwrite($handle, $file);
		fclose($handle);
	}
}

function generateNews($conn)
{
	$config = getConfig($conn);
	
	$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	$file .= '<html>
		<head>
		<title>'.$config['sitename'].'</title>
		<link rel="stylesheet" href="./styles/index.css" />
		<link rel="stylesheet" href="./styles/global.css" />
		<link rel="stylesheet" href="./styles/table.css" />
		</head>
		<body>';
	$file .= '<div id="doc">
		<br /><br />';
	$file .= '<div class="box-outer top-box">
		<div class="box-inner">
		<div class="boxbar"><h2>News</h2></div>
		<div class="boxcontent">';
	$result = mysqli_query($conn, "SELECT * FROM news ORDER BY date DESC;");
	while ($row = mysqli_fetch_assoc($result))
	{
		$file .= '<div class="content">';
		$file .= '<h3><span class="newssub">'.$row['title'].' by '.$row['who'].' - '.date("d/m/Y @ H:i", $row['date']).'</span></span></h3>';
		$file .= $row['text'];
		$file .= '</div>';
	}
	$file .= '</div>
		</div>
		</div>
		</div>
		</body>
		</html>';
	$handle = fopen("./".$config['news_url'], "w");
	fwrite($handle, $file);
	fclose($handle);
}
?>