<?php
function showView($conn, $board, $mode = 0, $threadno = 0)
{
	$pages = 15;
	$page = 0;
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
	if (!is_numeric($mode))
	{
		return -15; //error
	}
	if (($mode == 0) && ($threadno != 0))
	{
		$page = $threadno;
		$threadno = 0;
	}
	
	if ($threadno != 0)
	{
		$pages = 0;
	}
	$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">';
	$file .= "<head><title>/".$boarddata['short']."/ - ".$boarddata['name']."</title><link rel='stylesheet' href='./styles/stylesheet.css' />";
	$file .= "<script type='text/javascript' src='./js/jquery.js'></script>";
	$file .= "<script type='text/javascript' src='./js/jquery.cookie.js'></script>";
	$file .= "<script type='text/javascript' src='./js/common.js'></script>";
	$file .= "<script type='text/javascript' src='./js/admin.js'></script>";
	$file .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	$file .= "</head><body>";
	$file .= getBoardLinks($conn, 2);
	$file .= '<div class="boardBanner">';
	$imagesDir = './rnd/';
	$images = glob($imagesDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
	$randomImage = $images[array_rand($images)]; 
	$file .= '<img class="title" src="'.$randomImage.'" alt="Mitsuba">';
	$file .= '<div class="boardTitle">/'.$boarddata['short'].'/ - '.$boarddata['name'].'</div>';
	$file .= '<div class="boardSubtitle">'.$boarddata['des'].'</div>';
	$file .= '</div>';
	$file .= '<br />';
	$file .= '<hr />';
		
	$locked = 0;
	if ($locked == 0)
	{
		if ($threadno != 0)
	{
			$file .= '<div class="postingMode">Posting mode: Reply</div>';
			$file .= '<div class="navLinks">[<a href="?/board&b='.$board.'" accesskey="a">Return</a>]</div>';
			$file .= '<form action="?/board/action" method="post" enctype="multipart/form-data">';
		} else {
			$file .= '<form action="?/board/action" method="post" enctype="multipart/form-data">';
		}
		$file .= '<input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
			<input type="hidden" name="mode" value="regist" />
			<table class="postForm" id="postForm">
			<tbody>
			<tr>
			<td>Name</td>
			<td><input name="name" type="text" /></td>
			</tr>
			<tr>
			<td>E-mail</td>
			<td><input name="email" type="text"></td>
			</tr>
			<tr>
			<td>Subject</td>
			<td><input name="sub" type="text">';
		$file .= '<input type="hidden" name="board" value="'.$board.'" />';
		if ($threadno != 0)
		{
			$file .= '<input type="hidden" name="resto" value="'.$threadno.'" />';
		}
		$file .= '<input type="submit" value="Submit" /></td>
			</tr>
			<tr>
			<td>Comment</td>
			<td><textarea name="com" cols="48" rows="4" wrap="soft"></textarea></td>
			</tr>
			<tr>
			<td>File</td>
			<td><input id="postFile" name="upfile" type="file" /><div id="fileError"></div></td>
			</tr>
			<tr>
			<td>Password</td>
			<td><input id="postPassword" name="pwd" type="password" maxlength="8" /> <span class="password">(Password used for deletion)</span></td>
			</tr>
			<tr>
			<td>Mod</td>
			<td><input type="checkbox" name="capcode" value=1" />Capcode<input type="checkbox" name="raw" value=1" />Raw HTML<input type="checkbox" name="sticky" value=1" />Sticky<input type="checkbox" name="lock" value=1" />Lock<input type="checkbox" name="nolimit" value=1" />Ignore bumplimit</td>
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
		$file .= '<form id="delform" action="?/board/action" method="post"><div class="board">';
	} else {
		$file .= '<form id="delform" action="?/board/action" method="post"><div class="board">';
	}

	if ($threadno != 0)
	{
		$result = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE id=".$threadno.";");
	} else {
		$result = mysqli_query($conn, "SELECT * FROM posts_".$board." WHERE resto=0 ORDER BY sticky DESC, lastbumped DESC LIMIT ".($page*10).",10");
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
				$file .= '<a class="fileThumb" target="_blank"><img src="./img/deleted.gif" alt="Deleted" /></a>';
			} else {
				$file .= '<a class="fileThumb" target="_blank"><img src="./img/deleted.gif" alt="Deleted" /></a>';
			}
			$file .= '</div>';
		} else {
			$file .= '<div class="file" id="f'.$row['id'].'">';
			$file .= '<div class="fileInfo">';
			
			$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <a href="./'.$board.'/src/'.$row['filename'].'" target="_blank">'.$row['filename'].'</a>-(<span title="'.$row['orig_filename'].'">'.$row['orig_filename'].'</span>)</span>';
			
			$file .= '</div>';
			$fileparts = explode('.',$row['filename']);
			
			$file .= '<a class="fileThumb" href="./'.$board.'/src/'.$row['filename'].'" target="_blank"><img src="./'.$board.'/src/thumb/'.$fileparts[0].'.jpg" alt="Thumbnail" /></a>';
			
			
			$file .= '</div>';
		}
		$file .= '<div class="postInfo" id="pi'.$row['id'].'">';
		$file .= '<input type="checkbox" name="'.$row['id'].'" value="delete">';
		$file .= '<span class="subject">'.$row['subject'].'</span>';
		$trip = "";
		if (!empty($row['trip']))
		{
			$trip = "<span class='postertrip'>!".$row['trip']."</span>";
		}
		if (!empty($row['email'])) {
			$file .= '<span class="nameBlock"><a href="mailto:'.$row['email'].'" class="useremail"><span class="name">'.$row['name'].'</span>'.$trip.'</a></span>';
		} else {
			if ($row['capcode'] == 1)
			{
				$file .= '<span class="nameBlock"><span class="name"><span style="color:#800080">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#800080">## Mod</span></span></span>';
			} elseif ($row['capcode'] == 2)
			{
				$file .= '<span class="nameBlock"><span class="name"><span style="color:#FF0000">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF0000">## Admin</span></span></span>';
			} elseif ($row['capcode'] == 3)
			{
				$file .= '<span class="nameBlock"><span class="name"><span style="color:#FF00FF">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF00FF">## Faggot</span></span></span>';
			} else {
				$file .= '<span class="nameBlock"><span class="name">'.$row['name'].'</span>'.$trip.'</span>';
			}
		}
		$file .= ' <span class="posterIp">(<a href="http://whatismyipaddress.com/ip/'.$row['ip'].'" target="_blank">'.$row['ip'].'</a>)</span>';
		$file .= ' [<a href="?/info&ip='.$row['ip'].'">N</a>]';
		$file .= ' <span class="dateTime">'.date("d/m/Y(D)H:i:s", $row['date']).'</span> ';
	
		if ($threadno != 0)
		{
			$file .= '<span class="postNum"><a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row['id'].'" title="Highlight this post">No.</a><a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row['id'].'#q'.$row['id'].'" title="Quote this post">'.$row['id'].'</a>';
			if ($row['locked']==1)
			{
				$file .= '<img src="./img/closed.gif" alt="Closed" title="Closed" class="stickyIcon" />';
			}
			if ($row['sticky']==1)
			{
				$file .= '<img src="./img/sticky.gif" alt="Sticky" title="Sticky" class="stickyIcon" />';
			}
			$file .= '</span>';
		} else {
			$file .= '<span class="postNum"><a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row['id'].'" title="Highlight this post">No.</a><a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row['id'].'#q'.$row['id'].'" title="Quote this post">'.$row['id'].'</a> ';
			if ($row['locked']==1)
			{
				$file .= '<img src="./img/closed.gif" alt="Closed" title="Closed" class="stickyIcon" />';
			}
			if ($row['sticky']==1)
			{
				$file .= '<img src="./img/sticky.gif" alt="Sticky" title="Sticky" class="stickyIcon" />';
			}
			$file .= ' <span>[<a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row['id'].'" class="replylink">Reply</a>]</span></span>';
		}
		if ($row['sage']==1)
		{
			$file .= ' <span style="color: red;">[A]</a> ';
		}
		$file .= ' <span class="adminControls">[<a href="?/bans/add&b='.$board.'&p='.$row['id'].'">B</a> / <a href="?/bans/add&b='.$board.'&p='.$row['id'].'&d=1">&</a> / <a href="?/delete_post&b='.$board.'&p='.$row['id'].'">D</a>';
		if (!empty($row['filename']))
		{
			$file .= ' / <a href="?/delete_post&b='.$board.'&p='.$row['id'].'&f=1">F</a>]';
		} else {
			$file .= ']';
		}
		
		$file .= ' [<a href="?/sticky/toggle&b='.$board.'&t='.$row['id'].'">S</a> / <a href="?/locked/toggle&b='.$board.'&t='.$row['id'].'">L</a> / <a href="?/antibump/toggle&b='.$board.'&t='.$row['id'].'">A</a>]';
	
		$file .= '</span>';
		$file .= '</div>';
		
		
		
		$file .= '<blockquote class="postMessage" id="m'.$row['id'].'">';
		if ($row['raw'] != 1)
		{
			if ($row['raw'] == 2)
			{
				$file .= processComment($board, $conn, $row['comment'], 2, 0);
			} else {
				$file .= processComment($board, $conn, $row['comment'], 2);
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
			$file .= '<span class="summary">'.($row1[0]-3).' posts omitted. Click <a href="?/board&b='.$board.'&t='.$row['id'].'" class="replylink">here</a> to view.</span>';
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
			$file .= '<input type="checkbox" name="'.$row2['id'].'" value="delete">';
			$file .= '<span class="subject">'.$row2['subject'].'</span>';
			$trip = "";
			if (!empty($row2['trip']))
			{
				$trip = "<span class='postertrip'>!".$row2['trip']."</span>";
			}
			if (!empty($row2['email'])) {
				$file .= '<span class="nameBlock"><a href="mailto:'.$row2['email'].'" class="useremail"><span class="name">'.$row2['name'].'</span>'.$trip.'</a></span>';
			} else {
				if ($row2['capcode'] == 1)
				{
					$file .= '<span class="nameBlock"><span class="name"><span style="color:#800080">'.$row2['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#800080">## Mod</span></span></span>';
				} elseif ($row2['capcode'] == 2)
				{
					$file .= '<span class="nameBlock"><span class="name"><span style="color:#FF0000">'.$row2['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF0000">## Admin</span></span></span>';
				} elseif ($row2['capcode'] == 3)
				{
					$file .= '<span class="nameBlock"><span class="name"><span style="color:#FF00FF">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF00FF">## Faggot</span></span></span>';
				} else {
					$file .= '<span class="nameBlock"><span class="name">'.$row2['name'].'</span>'.$trip.'</span>';
				}
			}
			$file .= ' <span class="posterIp">(<a href="http://whatismyipaddress.com/ip/'.$row2['ip'].'" target="_blank">'.$row2['ip'].'</a>) [<a href="?/info&ip='.$row2['ip'].'">N</a>]</span>';
			$file .= ' <span class="dateTime">'.date("d/m/Y(D)H:i:s", $row2['date']).'</span> ' ;
			if ($threadno != 0)
			{
				$file .= '<span class="postNum"><a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row2['id'].'" title="Highlight this post">No.</a><a href="?/board&b='.$board.'&t='.$row['id'].'#q'.$row2['id'].'" title="Quote this post">'.$row2['id'].'</a></span></span>';
			} else {
				$file .= '<span class="postNum"><a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row2['id'].'" title="Highlight this post">No.</a><a href="?/board&b='.$board.'&t='.$row['id'].'#q'.$row2['id'].'" title="Quote this post">'.$row2['id'].'</a></span>';
			}
			$file .= ' <span class="adminControls">[<a href="?/bans/add&b='.$board.'&p='.$row2['id'].'">B</a> / <a href="?/bans/add&b='.$board.'&p='.$row2['id'].'&d=1">&</a> / <a href="?/delete_post&b='.$board.'&p='.$row2['id'].'">D</a>';
			if (!empty($row2['filename']))
			{
				$file .= ' / <a href="?/delete_post&b='.$board.'&p='.$row['id'].'&f=1">F</a>] ';
			} else {
				$file .= ']';
			}
			$file .= '</span> ';
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
						$file .= '<a class="fileThumb" target="_blank"><img src="./img/deleted.gif" alt="Deleted" /></a>';
					} else {
						$file .= '<a class="fileThumb" target="_blank"><img src="./imgdeleted.gif" alt="Deleted" /></a>';
					}
				
					$file .= '</div>';
				} else {
					$file .= '<div class="file" id="f'.$row2['id'].'">';
					$file .= '<div class="fileInfo">';
				
					$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <a href="./'.$board.'/src/'.$row2['filename'].'" target="_blank">'.$row2['filename'].'</a> (<span title="'.$row2['orig_filename'].'">'.$row2['orig_filename'].'</span>)</span>';
					
					$file .= '</div>';
					$fileparts = explode('.',$row2['filename']);
					
					$file .= '<a class="fileThumb" href="./'.$board.'/src/'.$row2['filename'].'" target="_blank"><img src="./'.$board.'/src/thumb/'.$fileparts[0].'.jpg" alt="Thumbnail" /></a>';
					
				
					$file .= '</div>';
				}
			}
			
			$file .= '<blockquote class="postMessage" id="m'.$row2['id'].'">';
			if ($row2['raw'] != 1)
			{
				if ($row2['raw'] == 2)
				{
					$file .= processComment($board, $conn, $row2['comment'], 2, 0);
				} else {
					$file .= processComment($board, $conn, $row2['comment'], 2);
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
		<input type="hidden" name="mode" value="usrform" />Delete Post [<input type="checkbox" name="onlyimgdel" value="on" />File Only]
		<input type="submit" name="delete" value="Delete" /><br />
	</div>';
	$file .= "</form>";
	if ($threadno == 0)
	{
		$file .= '<div class="pagelist desktop">';
		$file .= '<div class="prev">';
		if ($page != 0)
		{
			
			$file .= '<form action="?/board&b='.$board.'&p='.($page-1).'" onsubmit="location=this.action; return false;"><input type="submit" value="Previous" /></form>';
			
		} else {
			$file .= '<span>Next</span>';
		}
		$file .= ' </div>';
		$file .= '<div class="pages">';
		for ($i = 0; $i <= $pages; $i++)
		{
			if ($i == $page)
			{
				$file .= "[<strong>".$i."</strong>] ";
			} else {
				$file .= "[<a href='?/board&b=".$board."&p=".$i."'>".$i."</a>] ";
			}
		}
		$file .= '</div>';
		$file .= ' <div class="next">';
		if ($page != $pages)
		{
			$file .= '<form action="?/board&b='.$board.'&p='.($page+1).'" onsubmit="location=this.action; return false;"><input type="submit" value="Next" /></form>';
		} else {
			$file .= '<span>Previous</span>';
		}
		$file .= '</div>';
		$file .= '</div>';
	}
	$file .= "</body></html>";
	echo $file;
}
?>