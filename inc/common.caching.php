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
	$result = $conn->query("SELECT * FROM links WHERE parent=".$id." ORDER BY short ASC, title ASC, id DESC;");
	if ($result->num_rows == 0)
	{
		return "";
	}
	$links = "";
	$no = 0;
	while ($row = $result->fetch_assoc())
	{	
		if (!empty($row['url']))
		{
			if ($no > 0) { $links .= ' / '; }
			if ((!empty($row['url_index'])) && ($in_thread == 2))
			{
				$links .= '<a href="'.$row['url_index'].'" title="'.$row['title'].'">'.$row['short'].'</a>';
			} elseif ((!empty($row['url_thread'])) && ($in_thread == 1))
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
	} elseif ($in_thread == 0) {
		return $config['boardLinks'];
	} else {
		return $config['boardLinks_index'];
	}
}

function processComment($board, $conn, $string, $parser, $thread = 0, $specialchars = 1, $bbcode = 1)
{
	$new = $string;
	if ($specialchars == 1)
	{
		$new = htmlspecialchars($new);
	}
	
	$fresult = $conn->query("SELECT * FROM wordfilter WHERE active=1");
	$replace_array = array();
	while ($row = $fresult->fetch_assoc()) { $replace_array[$row['search']] = $row['replace']; }
	$new = strtr($new, $replace_array);
	$lines = explode("\n", $new);
	$new = "";
	
	foreach ($lines as $line)
	{
		if (substr($line, 0, 8) == "&gt;&gt;")
		{
			$space = explode(" ", $line, 2);
			if (is_numeric(substr($space[0], 8)))
			{
				$result = $conn->query("SELECT * FROM posts_".$board." WHERE id='".substr($space[0], 8)."';");
				if (empty($space[1])) { $space[1] = ""; }
				if ($result->num_rows == 1)
				{
					$row = $result->fetch_assoc();
					if ($row['resto'] != 0)
					{
						if ($thread == 1)
						{
							$new .= '<a href="../res/'.$row['resto'].'.html#p'.$row['id'].'" class="quotelink">'.$space[0].'</a> '.$space[1].' <br />';
						} elseif ($thread == 0) {
							$new .= '<a href="./res/'.$row['resto'].'.html#p'.$row['id'].'" class="quotelink">'.$space[0].'</a> '.$space[1].' <br />';
						} else {
							$new .= '<a href="?/board&b='.$board.'&t='.$row['resto'].'#p'.$row['id'].'" class="quotelink">'.$space[0].'</a> '.$space[1].'<br />';
						}
					} else {
						if ($thread == 1)
						{
							$new .= '<a href="../res/'.$row['id'].'.html#p'.$row['id'].'" class="quotelink">'.$space[0].'</a> '.$space[1].' <br />';
						} elseif ($thread == 0) {
							$new .= '<a href="./res/'.$row['id'].'.html#p'.$row['id'].'" class="quotelink">'.$space[0].'</a> '.$space[1].' <br />';
						} else {
							$new .= '<a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row['id'].'" class="quotelink">'.$space[0].'</a> '.$space[1].' <br />';
						}
					}
				} else {
					$new .= "<span class='quote'>".$space[0]." ".$space[1]."</span><br />";
				}
			} elseif ((substr($space[0], 0, 9) == "&gt;&gt;/") || (substr($space[0], 0, 13) == "&gt;&gt;&gt;/"))
			{
				$parts = explode("/", $space[0]);
				if ((isBoard($conn, $parts[1])) && (is_numeric($parts[2])))
				{
					$result = $conn->query("SELECT * FROM posts_".$parts[1]." WHERE id='".$parts[2]."';");
					if (empty($space[1])) { $space[1] = ""; }
					if ($result->num_rows == 1)
					{
						$row = $result->fetch_assoc();
						if ($row['resto'] != 0)
						{
							if ($thread == 1)
							{
								$new .= '<a href="../../'.$parts[1].'/res/'.$row['resto'].'.html#p'.$row['id'].'" class="quotelink cross">'.$space[0].'</a> '.$space[1].' <br />';
							} elseif ($thread == 0) {
								$new .= '<a href="../'.$parts[1].'/res/'.$row['resto'].'.html#p'.$row['id'].'" class="quotelink cross">'.$space[0].'</a> '.$space[1].' <br />';
							} else {
								$new .= '<a href="?/board&b='.$parts[1].'&t='.$row['resto'].'#p'.$row['id'].'" class="quotelink cross">'.$space[0].'</a> '.$space[1].' <br />';
							}
						} else {
							if ($thread == 1)
							{
								$new .= '<a href="../../'.$parts[1].'/res/'.$row['id'].'.html#p'.$row['id'].'" class="quotelink cross">'.$space[0].'</a> '.$space[1].' <br />';
							} elseif ($thread == 0) {
								$new .= '<a href="../'.$parts[1].'/res/'.$row['id'].'.html#p'.$row['id'].'" class="quotelink cross">'.$space[0].'</a> '.$space[1].' <br />';
							} else {
								$new .= '<a href="?/board&b='.$parts[1].'&t='.$row['id'].'#p'.$row['id'].'" class="quotelink cross">'.$space[0].'</a> '.$space[1].' <br />';
							}
						}
					} else {
						$new .= "<span class='quote'>".$space[0]."</span> ".$space[1]." <br />";
					}
				} else {
					$new .= "<span class='quote'>".$line."</span><br />";
				}
			} else {
				$new .= "<span class='quote'>".$line."</span> <br />";
			}
		} elseif (substr($line, 0, 4) == "&gt;")
		{
			$new .= "<span class='quote'>".$line."</span><br />";
		} else {
			$new .= $line." <br />";
			
		}
	}
	$rexProtocol = '(https?://)?';
	$rexDomain   = '((?:[-a-zA-Z0-9]{1,63}\.)+[-a-zA-Z0-9]{2,63}|(?:[0-9]{1,3}\.){3}[0-9]{1,3})';
	$rexPort     = '(:[0-9]{1,5})?';
	$rexPath     = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
	$rexQuery    = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
	$rexFragment = '(#[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
	if ($bbcode == 1)
	{
		$parser->parse($new);
		$new = $parser->getAsHtml();
	}
	$rurl = "&\\b$rexProtocol$rexDomain$rexPort$rexPath$rexQuery$rexFragment(?=[?.!,;:\"]?(\s|$))&";
	$new = preg_replace_callback($rurl, "urlCallback", $new);
	return $new;
}

function urlCallback($match)
{
	if ((substr($match[0], 0, 5) == "http:") || (substr($match[0], 0, 6) == "https:"))
	{
		return "<a href='".$match[0]."'>".$match[0]."</a>";
	} else {
		return $match[0];
	}
}

function generateView($conn, $board, $threadno = 0, $return = 0, $mode = 0, $adm_type = 0)
{
	
	$config = getConfig($conn);
	$board = $conn->real_escape_string($board);
	if (!isBoard($conn, $board))
	{
		return -16;
	}
	$boarddata = getBoardData($conn, $board);
	$max_pages = $boarddata['pages'];
	$all_pages = $max_pages;
	$pages = $max_pages;
	$page = 0;
	if (!is_numeric($threadno))
	{
		return -15; //error
	}
	if ($return == 1)
	{
		if (!is_numeric($mode))
		{
			return -15; //error
		}
		if (($mode == 0) && ($threadno != 0))
		{
			$page = $threadno;
			$threadno = 0;
			$pages = 0;
		}
	}
	if ($threadno != 0)
	{
		$pages = 0;
	}
	
	require_once( "./jbbcode/Parser.php" );
	$parser = new JBBCode\Parser();
	$bbcode = $conn->query("SELECT * FROM bbcodes;");
	
	while ($row = $bbcode->fetch_assoc())
	{
		$parser->addBBCode($row['name'], $row['code']);
	}
	
	$embed_table = array();
	$result = $conn->query("SELECT * FROM embeds;");
	while ($row = $result->fetch_assoc())
	{
		$embed_table[] = $row;
	}
	if ($return == 1)
	{
		$pages = $page;
	}
	
	if ($threadno == 0)
	{
		$cnt = $conn->query("SELECT id FROM posts_".$board." WHERE resto=0");
		$all_pages = ceil(($cnt->num_rows)/10);
		if ($all_pages == 0) { $all_pages = 1; }
		if (($max_pages+1) < $all_pages)
		{
			$all_pages = $max_pages;
		}
		if ($return == 0)
		{
			$pages = $all_pages - 1;
		}
	}
	
	for ($pg = $page; $pg <= $pages; $pg++)
	{
		$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">';
		
		if ($return == 1)
		{
			$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">';
			$file .= "<head><title>/".$boarddata['short']."/ - ".$boarddata['name']."</title>";
			$style = $conn->query("SELECT * FROM styles WHERE `default`=1");
			$first_default = 0;
			if ($style->num_rows > 0)
			{
				$sdata = $style->fetch_assoc();
				$file .= '<link rel="stylesheet" id="switch" href="'.$sdata['path_index'].'">';
			} else {
				$first_default = 1;
			}
			$styles = $conn->query("SELECT * FROM styles");
			while ($row = $styles->fetch_assoc())
			{
				if ($first_default == 1)
				{
					$file .= '<link rel="stylesheet" id="switch" href="'.$row['path_index'].'">';
					$first_default = 0;
				}
				$file .= '<link rel="alternate stylesheet" style="text/css" href="'.$row['path_index'].'" title="'.$row['name'].'">';
			}
			$file .= "<script type='text/javascript' src='./js/jquery.js'></script>";
			$file .= "<script type='text/javascript' src='./js/jquery.cookie.js'></script>";
			$file .= "<script type='text/javascript' src='./js/common.js'></script>";
			$file .= "<script type='text/javascript' src='./js/admin.js'></script>";
			$file .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			$file .= "</head><body>";
			$file .= getBoardLinks($conn, 2);
		} elseif ($threadno != 0)
		{
			$file .= "<head><title>/".$boarddata['short']."/ - ".$boarddata['name']."</title>";
			$style = $conn->query("SELECT * FROM styles WHERE `default`=1;");
			$first_default = 0;
			if (mysqli_num_rows($style) > 0)
			{
				$sdata = $style->fetch_assoc();
				$file .= '<link rel="stylesheet" id="switch" href="'.$sdata['path_thread'].'">';
			} else {
				$first_default = 1;
			}
			$styles = $conn->query("SELECT * FROM styles");
			while ($row = $styles->fetch_assoc())
			{
				if ($first_default == 1)
				{
					$file .= '<link rel="stylesheet" id="switch" href="'.$row['path_thread'].'">';
					$first_default = 0;
				}
				$file .= '<link rel="alternate stylesheet" style="text/css" href="'.$row['path_thread'].'" title="'.$row['name'].'">';
			}
			$file .= "<script type='text/javascript' src='../../js/jquery.js'></script>";
			$file .= "<script type='text/javascript' src='../../js/common.js'></script>";
			$file .= "<script type='text/javascript' src='../../js/jquery.cookie.js'></script>";
			$file .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			$file .= "</head><body>";
			$file .= getBoardLinks($conn, 1);
		} else {
			$file .= "<head><title>/".$boarddata['short']."/ - ".$boarddata['name']."</title>";
			$style = $conn->query("SELECT * FROM styles WHERE `default`=1;");
			$first_default = 0;
			if (mysqli_num_rows($style) > 0)
			{
				$sdata = $style->fetch_assoc();
				$file .= '<link rel="stylesheet" id="switch" href="'.$sdata['path'].'">';
			} else {
				$first_default = 1;
			}
			$styles = $conn->query("SELECT * FROM styles");
			while ($row = $styles->fetch_assoc())
			{
				if ($first_default == 1)
				{
					$file .= '<link rel="stylesheet" id="switch" href="'.$row['path'].'">';
					$first_default = 0;
				}
				$file .= '<link rel="alternate stylesheet" style="text/css" href="'.$row['path'].'" title="'.$row['name'].'">';
			}
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
		if ($return == 1)
		{
			$file .= '<img class="title" src="'.$randomImage.'" alt="Mitsuba" />';
		} elseif ($threadno != 0)
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
		
		
		
		if (($return == 1) && ($adm_type >= 1))
		{
			
		} elseif ($threadno != 0)
		{
			$result = $conn->query("SELECT * FROM posts_".$board." WHERE id=".$threadno.";");
			if ($result->num_rows == 1)
			{
				$tdata = $result->fetch_assoc();
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
				if ($return == 1)
				{
					$file .= '<div class="navLinks">[<a href="?/board&b='.$board.'" accesskey="a">Return</a>]</div>';
					$file .= '<form action="?/board/action" method="post" enctype="multipart/form-data">';
				} else {
					$file .= '<div class="navLinks">[<a href=".././" accesskey="a">Return</a>]</div>';
					$file .= '<form action="../../imgboard.php" method="post" enctype="multipart/form-data">';
				}
			} else {
				if ($return == 1)
				{
					$file .= '<form action="?/board/action" method="post" enctype="multipart/form-data">';
				} else {
					$file .= '<form action="../imgboard.php" method="post" enctype="multipart/form-data">';
				}
			}
			if ($adm_type <= 0)
			{
				$file .= '<input type="hidden" name="MAX_FILE_SIZE" value="'.$boarddata['filesize'].'" />';
			}
			$file .= '<input type="hidden" name="mode" value="regist" />
				<table class="postForm" id="postForm">
				<tbody>';
			if (($boarddata['noname'] == 0) || ($adm_type >= 1))
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
				</tr>';
			if ($adm_type >= 1)
			{
				$file .='<tr>
					<td>Mod</td>
					<td><input type="checkbox" name="capcode" value=1" />Capcode<input type="checkbox" name="raw" value=1" />Raw HTML<input type="checkbox" name="sticky" value=1" />Sticky<input type="checkbox" name="lock" value=1" />Lock<input type="checkbox" name="nolimit" value=1" />Ignore bumplimit<input type="checkbox" name="ignoresizelimit" value=1" />Ignore filesize limit</td>';
			}
			$file .= '<tr class="rules">
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
		if ($return == 1)
		{
			$file .= '<form id="delform" action="?/board/action" method="post"><div class="board">';
		} elseif ($threadno != 0)
		{
			$file .= '<form id="delform" action="../../imgboard.php" method="post"><div class="board">';
		} else {
			$file .= '<form id="delform" action="../imgboard.php" method="post"><div class="board">';
		}

		if ($threadno != 0)
		{
			$result = $conn->query("SELECT * FROM posts_".$board." WHERE id=".$threadno.";");
		} else {
			
			$result = $conn->query("SELECT * FROM posts_".$board." WHERE resto=0 ORDER BY sticky DESC, lastbumped DESC LIMIT ".($pg*10).",10");
		}

		while ($row = $result->fetch_assoc())
		{
			$file .= '<div class="thread" id="t'.$row['id'].'">';
			$file .= '<div class="postContainer opContainer" id="pc'.$row['id'].'">';
			$file .= '<div id="p'.$row['id'].'" class="post op">';
			if ($row['filename'] == "deleted")
			{
				$file .= '<div class="file" id="f'.$row['id'].'">';
				$file .= '<div class="fileInfo">';$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <b>deleted</b></span>';
				$file .= '</div>';
				if ($return == 1)
				{
					$file .= '<a class="fileThumb" target="_blank"><img src="./img/deleted.gif" alt="Deleted"/></a>';
				} elseif ($threadno != 0)
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
				if ($return == 1)
				{
					$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <a href="./'.$board.'/src/'.substr($row['filename'],8).'" target="_blank"><b>Spoiler image</b></a></span>';
				
				} elseif ($threadno != 0)
				{
					$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <a href="../src/'.substr($row['filename'],8).'" target="_blank"><b>Spoiler image</b></a></span>';
				} else {
					$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <a href="./src/'.substr($row['filename'],8).'" target="_blank"><b>Spoiler image</b></a></span>';
				}
				$file .= '</div>';
				if ($return == 1)
				{
					$file .= '<a class="fileThumb" href="./'.$board.'/src/'.substr($row['filename'],8).'" target="_blank"><img src="./img/spoiler.png" alt="Spoiler image"/></a>';
				} elseif ($threadno != 0)
				{
					$file .= '<a class="fileThumb" href="../src/'.substr($row['filename'],8).'" target="_blank"><img src="../../img/spoiler.png" alt="Spoiler image"/></a>';
				} else {
					$file .= '<a class="fileThumb" href="./src/'.substr($row['filename'],8).'" target="_blank"><img src="../img/spoiler.png" alt="Spoiler image"/></a>';
				}
				$file .= '</div>';
			} elseif (substr($row['filename'], 0, 6) == "embed:")
			{
				$file .= '<div class="file" id="f'.$row['id'].'">';
				$file .= '<div class="fileInfo">';
				$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <b>Embed</b></span>';
				
				$file .= '</div>';
				$file .= '<a class="fileThumb">'.getEmbed(substr($row['filename'], 6), $embed_table).'</a>';
				
				$file .= '</div>';
			} else {
				$file .= '<div class="file" id="f'.$row['id'].'">';
				$file .= '<div class="fileInfo">';
				if ($return == 1)
				{
					$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <a href="./'.$board.'/src/'.$row['filename'].'" target="_blank">'.$row['filename'].'</a> -('.$row['filesize'].', '.$row['imagesize'].', <span title="'.$row['orig_filename'].'">'.$row['orig_filename'].'</span>)</span>';
				} elseif ($threadno != 0)
				{
					$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <a href="../src/'.$row['filename'].'" target="_blank">'.$row['filename'].'</a> -('.$row['filesize'].', '.$row['imagesize'].', <span title="'.$row['orig_filename'].'">'.$row['orig_filename'].'</span>)</span>';
				} else {
					$file .= '<span class="fileText" id="fT'.$row['id'].'">File: <a href="./src/'.$row['filename'].'" target="_blank">'.$row['filename'].'</a> -('.$row['filesize'].', '.$row['imagesize'].', <span title="'.$row['orig_filename'].'">'.$row['orig_filename'].'</span>)</span>';
				}
				$file .= '</div>';
				if ($return == 1)
				{
					$file .= '<a class="fileThumb" href="./'.$board.'/src/'.$row['filename'].'" target="_blank"><img src="./'.$board.'/src/thumb/'.$row['filename'].'" alt="Thumbnail"/></a>';
				} elseif ($threadno != 0)
				{
					$file .= '<a class="fileThumb" href="../src/'.$row['filename'].'" target="_blank"><img src="../src/thumb/'.$row['filename'].'" alt="Thumbnail"/></a>';
				} else {
					$file .= '<a class="fileThumb" href="./src/'.$row['filename'].'" target="_blank"><img src="./src/thumb/'.$row['filename'].'" alt="Thumbnail"/></a>';
				}
				
				$file .= '</div>';
			}
			$file .= '<div class="postInfo" id="pi'.$row['id'].'">';
			$file .= '<input type="checkbox" name="'.$row['id'].'" value="delete" />';
			$file .= '<span class="subject">'.$row['subject'].'</span> ';
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
			$c_image = "";
			if ($row['capcode'] == 1)
			{
				if ($return == 1)
				{
					$c_image = ' <img src="./img/mod.png" alt="Moderator" style="margin-bottom: -3px;" />';
				} elseif ($threadno != 0)
				{
					$c_image = ' <img src="../../img/mod.png" alt="Moderator" style="margin-bottom: -3px;" />';
				} else {
					$c_image = ' <img src="../img/mod.png" alt="Moderator" style="margin-bottom: -3px;" />';
				}
			} elseif ($row['capcode'] == 2)
			{
				
				if ($return == 1)
				{
					$c_image = ' <img src="./img/admin.png" alt="Administrator" style="margin-bottom: -3px;" />';
				} elseif ($threadno != 0)
				{
					$c_image = ' <img src="../../img/admin.png" alt="Administrator" style="margin-bottom: -3px;" />';
				} else {
					$c_image = ' <img src="../img/admin.png" alt="Administrator" style="margin-bottom: -3px;" />';
				}
			}
			if (!empty($row['email'])) {
				$file .= '<span class="nameBlock"><a href="mailto:'.$row['email'].'" class="useremail"><span class="name">'.$row['name'].'</span>'.$trip.'</a> '.$poster_id.'</span>';
			} else {
				if ($row['capcode'] == 1)
				{
					$file .= '<span class="nameBlock"><span class="name"><span style="color:#800080">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#800080">## Mod</span>'.$c_image.'</span> '.$poster_id.'</span>';
				} elseif ($row['capcode'] == 2)
				{
					$file .= '<span class="nameBlock"><span class="name"><span style="color:#FF0000">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF0000">## Admin</span>'.$c_image.'</span> '.$poster_id.'</span>';
				} elseif ($row['capcode'] == 3)
				{
					$file .= '<span class="nameBlock"><span class="name"><span style="color:#FF00FF">'.$row['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF00FF">## Faggot</span>'.$c_image.'</span> '.$poster_id.'</span>';
				} else {
					$file .= '<span class="nameBlock"><span class="name">'.$row['name'].'</span>'.$trip.' '.$poster_id.'</span>';
				}
			}
			$file .= ' <span class="dateTime">'.date("d/m/Y(D)H:i:s", $row['date']).'</span> ';
		
			if ($return == 1)
			{
				$file .= '<span class="postNum"><a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row['id'].'" title="Highlight this post">No.</a><a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row['id'].'#q'.$row['id'].'" title="Quote this post">'.$row['id'].'</a></span>';
				if ($row['locked']==1)
				{
					$file .= '<img src="./img/closed.gif" alt="Closed" title="Closed" class="stickyIcon" />';
				}
				if ($row['sticky']==1)
				{
					$file .= '<img src="./img/sticky.gif" alt="Sticky" title="Sticky" class="stickyIcon" />';
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
				if ($_SESSION['type'] >= 2)
				{
					$file .= ' [<a href="?/edit_post&b='.$board.'&p='.$row['id'].'" class="edit">E</a>]';
				}
				
				if ($_SESSION['type'] >= 1)
				{
					$file .= ' [<a href="?/sticky/toggle&b='.$board.'&t='.$row['id'].'">S</a> / <a href="?/locked/toggle&b='.$board.'&t='.$row['id'].'">L</a> / <a href="?/antibump/toggle&b='.$board.'&t='.$row['id'].'">A</a>]';
				}
				if ($threadno == 0)
				{
					$file .= '&nbsp; <span>[<a href="?/board&b='.$board.'&t='.$row['id'].'" class="replylink">Reply</a>]</span>';
				}
				$file .= '</span>';
			} elseif ($threadno != 0)
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
					if ($return == 1)
					{
						$file .= processComment($board, $conn, $row['comment'], $parser, 2, 0, $boarddata['bbcode']);
					} else {
						$file .= processComment($board, $conn, $row['comment'], $parser, $threadno != 0, 0, $boarddata['bbcode']);
					}
				} else {
					if ($return == 1)
					{
						$file .= processComment($board, $conn, $row['comment'], $parser, 2, $boarddata['bbcode']);
					} else {
						$file .= processComment($board, $conn, $row['comment'], $parser, $threadno != 0, $boarddata['bbcode']);
					}
				}
			} else {
				$file .= $row['comment'];
			}
			$file .= '</blockquote>';
			
			
			
			$file .= '</div>';
			$file .= '</div>';
			if ($threadno != 0)
			{
				$posts = $conn->query("SELECT * FROM posts_".$board." WHERE resto=".$row['id']." ORDER BY id ASC");
			} else {
			$posts = $conn->query("SELECT COUNT(*) FROM posts_".$board." WHERE resto=".$row['id']." ORDER BY id ASC");
			$row1 = $posts->fetch_row();
			if ($row1[0] == 0)
			{
				$file .= '</div><hr />';
				continue;
			}
			if ($row1[0] > 3)
			{
				if ($return == 1)
				{
					$file .= '<span class="summary">'.($row1[0]-3).' posts omitted. Click <a href="?/board&b='.$board.'&t='.$row['id'].'" class="replylink">here</a> to view.</span>';
				} else {
					$file .= '<span class="summary">'.($row1[0]-3).' posts omitted. Click <a href="./res/'.$row['id'].'.html" class="replylink">here</a> to view.</span>';
				}
			}
			$offset = 0;
			if ($row1[0] > 3)
			{
				$offset = $row1[0] - 3;
				
			}
			$posts = $conn->query("SELECT * FROM posts_".$board." WHERE resto=".$row['id']." ORDER BY id ASC LIMIT ".$offset.",3");
				
			}
			while ($row2 = $posts->fetch_assoc())
			{
				$file .= '<div class="postContainer replyContainer" id="pc'.$row2['id'].'">';
				$file .= '<div class="sideArrows" id="sa'.$row2['id'].'">&gt;&gt;</div>';
				$file .= '<div id="p'.$row2['id'].'" class="post reply">';
				$file .= '<div class="postInfo" id="pi'.$row2['id'].'">';
				$file .= '<input type="checkbox" name="'.$row2['id'].'" value="delete" />';
				$file .= '<span class="subject">'.$row2['subject'].'</span> ';
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
				$c_image = "";
				if ($row2['capcode'] == 1)
				{
					if ($return == 1)
					{
						$c_image = ' <img src="./img/mod.png" alt="Moderator" style="margin-bottom: -3px;" />';
					} elseif ($threadno != 0)
					{
						$c_image = ' <img src="../../img/mod.png" alt="Moderator" style="margin-bottom: -3px;" />';
					} else {
						$c_image = ' <img src="../img/mod.png" alt="Moderator" style="margin-bottom: -3px;" />';
					}
				} elseif ($row2['capcode'] == 2)
				{
					if ($return == 1)
					{
						$c_image = ' <img src="./img/admin.png" alt="Administrator" style="margin-bottom: -3px;" />';
					} elseif ($threadno != 0)
					{
						$c_image = ' <img src="../../img/admin.png" alt="Administrator" style="margin-bottom: -3px;" />';
					} else {
						$c_image = ' <img src="../img/admin.png" alt="Administrator" style="margin-bottom: -3px;" />';
					}
				}
				if (!empty($row2['email'])) {
					$file .= '<span class="nameBlock"><a href="mailto:'.$row2['email'].'" class="useremail"><span class="name">'.$row2['name'].'</span>'.$trip.'</a> '.$poster_id.'</span>';
				} else {
					if ($row2['capcode'] == 1)
					{
						$file .= '<span class="nameBlock"><span class="name"><span style="color:#800080">'.$row2['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#800080">## Mod</span>'.$c_image.'</span> '.$poster_id.'</span>';
					} elseif ($row2['capcode'] == 2)
					{
						$file .= '<span class="nameBlock"><span class="name"><span style="color:#FF0000">'.$row2['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF0000">## Admin</span>'.$c_image.'</span> '.$poster_id.'</span>';
					} elseif ($row2['capcode'] == 3)
					{
						$file .= '<span class="nameBlock"><span class="name"><span style="color:#FF00FF">'.$row2['name'].'</span></span>'.$trip.' <span class="commentpostername"><span style="color:#FF00FF">## Faggot</span>'.$c_image.'</span> '.$poster_id.'</span>';
					} else {
						$file .= '<span class="nameBlock"><span class="name">'.$row2['name'].'</span>'.$trip.' '.$poster_id.'</span>';
					}
				}
				$file .= ' <span class="dateTime">'.date("d/m/Y(D)H:i:s", $row2['date']).'</span> ' ;
				if ($return == 1)
				{
					$file .= '<span class="postNum"><a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row2['id'].'" title="Highlight this post">No.</a><a href="?/board&b='.$board.'&t='.$row['id'].'#q'.$row2['id'].'" title="Quote this post">'.$row2['id'].'</a></span>';
					$file .= ' <span class="adminControls">[<a href="?/bans/add&b='.$board.'&p='.$row2['id'].'">B</a> / <a href="?/bans/add&b='.$board.'&p='.$row2['id'].'&d=1">&</a> / <a href="?/delete_post&b='.$board.'&p='.$row2['id'].'">D</a>';
					
					
					if (!empty($row2['filename']))
					{
						$file .= ' / <a href="?/delete_post&b='.$board.'&p='.$row2['id'].'&f=1">F</a>] ';
					} else {
						$file .= ']';
					}
					if ($_SESSION['type'] >= 2)
					{
						$file .= ' [<a href="?/edit_post&b='.$board.'&p='.$row2['id'].'" class="edit">E</a>]';
					}
					$file .= "</span>";
				} elseif ($threadno != 0)
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
						$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <b>deleted</b></span>';
						$file .= '</div>';
						if ($return == 1)
						{
							$file .= '<a class="fileThumb" target="_blank"><img src="./img/deleted.gif" alt="Deleted" /></a>';
						} elseif ($threadno != 0)
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
						if ($return == 1)
						{
							$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <a href="./'.$board.'/src/'.substr($row2['filename'], 8).'" target="_blank"><b>Spoiler image</b></a></span>';
						} elseif ($threadno != 0)
						{
							$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <a href="../src/'.substr($row2['filename'], 8).'" target="_blank"><b>Spoiler image</b></a></span>';
						} else {
							$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <a href="./src/'.substr($row2['filename'], 8).'" target="_blank"><b>Spoiler image</b></a></span>';
						}
						$file .= '</div>';
						if ($return == 1)
						{
							$file .= '<a class="fileThumb" href="./'.$board.'/src/'.substr($row2['filename'], 8).'" target="_blank"><img src="./img/spoiler.png" alt="Spoiler image"/></a>';
						} elseif ($threadno != 0)
						{
							$file .= '<a class="fileThumb" href="../src/'.substr($row2['filename'], 8).'" target="_blank"><img src="../../img/spoiler.png" alt="Spoiler image"/></a>';
						} else {
							$file .= '<a class="fileThumb" href="./src/'.substr($row2['filename'], 8).'" target="_blank"><img src="../img/spoiler.png" alt="Spoiler image"/></a>';
						}
						$file .= '</div>';
					} elseif (substr($row2['filename'], 0, 6) == "embed:")
					{
						$file .= '<div class="file" id="f'.$row2['id'].'">';
						$file .= '<div class="fileInfo">';
						$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <b>Embed</b></span>';
						
						$file .= '</div>';
						$file .= '<a class="fileThumb">'.getEmbed(substr($row2['filename'], 6), $embed_table).'</a>';
						
						$file .= '</div>';
					} else {
						$file .= '<div class="file" id="f'.$row2['id'].'">';
						$file .= '<div class="fileInfo">';
						if ($return == 1)
						{
							$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <a href="./'.$board.'/src/'.$row2['filename'].'" target="_blank">'.$row2['filename'].'</a> -('.$row2['filesize'].', '.$row2['imagesize'].', <span title="'.$row2['orig_filename'].'">'.$row2['orig_filename'].'</span>)</span>';
						} elseif ($threadno != 0)
						{
							$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <a href="../src/'.$row2['filename'].'" target="_blank">'.$row2['filename'].'</a> -('.$row2['filesize'].', '.$row2['imagesize'].', <span title="'.$row2['orig_filename'].'">'.$row2['orig_filename'].'</span>)</span>';
						} else {
							$file .= '<span class="fileText" id="fT'.$row2['id'].'">File: <a href="./src/'.$row2['filename'].'" target="_blank">'.$row2['filename'].'</a> -('.$row2['filesize'].', '.$row2['imagesize'].', <span title="'.$row2['orig_filename'].'">'.$row2['orig_filename'].'</span>)</span>';
						}
						$file .= '</div>';
						
						if ($return == 1)
						{
							$file .= '<a class="fileThumb" href="./'.$board.'/src/'.$row2['filename'].'" target="_blank"><img src="./'.$board.'/src/thumb/'.$row2['filename'].'" alt="Thumbnail"/></a>';
						} elseif ($threadno != 0)
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
						if ($return == 1)
						{
							$file .= processComment($board, $conn, $row2['comment'], $parser, 2, 0);
						} else {
							$file .= processComment($board, $conn, $row2['comment'], $parser, $threadno != 0, 0);
						}
					} else {
						if ($return == 1)
						{
							$file .= processComment($board, $conn, $row2['comment'], $parser, 2);
						} else {
							$file .= processComment($board, $conn, $row2['comment'], $parser, $threadno != 0);
						}
					}
				} else {
					$file .= $row2['comment'];
				}
				$file .= '</blockquote>';
				
				$file .= "</div>";
				
				
				$file .= '</div>';
			}
			
			$file .= '</div>';
			$file .= '<hr />';
		}
		$file .= "</div>";
		$file .= '<div class="deleteform">
			<input type="hidden" name="board" value="'.$board.'" />
			<input type="hidden" name="mode" value="usrform" />Delete Post [<input type="checkbox" name="onlyimgdel" value="on" />File Only] ';
		if ($adm_type <= 0)
		{
		$file .= 'Password <input type="password" id="delPassword" name="pwd" maxlength="8" /> ';
		}
		$file .= '<input type="submit" name="delete" value="Delete" /><br />';
		if ($adm_type <= 0)
		{
		$file .= 'Reason <input type="text" name="reason" /><input type="submit" name="report" value="Report" />';
		}
		$file .= '<div class="stylechanger" id="stylechangerDiv" style="display:none;">Style: <select id="stylechanger"></select></div>
			</div>';
		$file .= "</form>";
		if (($return == 1) && ($threadno == 0))
		{
			$file .= '<div class="pagelist desktop">';
			$file .= '<div class="prev">';
			if ($page != 0)
			{
				
				$file .= '<form action="?/board&b='.$board.'&p='.($page-1).'" onsubmit="location=this.action; return false;"><input type="submit" value="Previous" /></form>';
				
			} else {
				$file .= '<span>Previous</span>';
			}
			$file .= ' </div>';
			$file .= '<div class="pages">';
			for ($i = 0; $i <= $max_pages; $i++)
			{
				if (($i+1) > $all_pages)
				{
					$file .= "[".$i."] ";
				} else {
					if ($i == $page)
					{
						$file .= "[<strong>".$i."</strong>] ";
					} else {
						$file .= "[<a href='?/board&b=".$board."&p=".$i."'>".$i."</a>] ";
					}
				}
			}
			$file .= '</div>';
			$file .= ' <div class="next">';
			if ($page != ($all_pages-1))
			{
				$file .= '<form action="?/board&b='.$board.'&p='.($page+1).'" onsubmit="location=this.action; return false;"><input type="submit" value="Next" /></form>';
			} else {
				$file .= '<span>Next</span>';
			}
			$file .= '</div>';
			$file .= '</div>';
		} elseif ($threadno == 0)
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
				$file .= '<span>Previous</span>';
			}
			$file .= ' </div>';
			$file .= '<div class="pages">';
			for ($i = 0; $i <= $max_pages; $i++)
			{
				if ($i == $pg)
				{
					$file .= "[<strong>".$i."</strong>] ";
				} else {
					if (($i+1) > $all_pages)
					{
						$file .= "[".$i."] ";
					} else {
						if ($i != 0)
						{
							$file .= "[<a href='./".$i.".html'>".$i."</a>] ";
						} else {
							$file .= "[<a href='./index.html'>".$i."</a>] ";
						}
					}
				}
			}
			$file .= '</div>';
			$file .= ' <div class="next">';
			if ($pg != ($all_pages-1))
			{
				$file .= '<form action="./'.($pg+1).'.html" onsubmit="location=this.action; return false;"><input type="submit" value="Next" /></form>';
			} else {
				$file .= '<span>Next</span>';
			}
			$file .= '</div>';
			$file .= '</div>';
		}
		$file .= "</body></html>";
		if ($return != 1)
		{
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
		} else {
			return $file;
		}
	}
}

function updateThreads($conn, $board)
{
	$board = $conn->real_escape_string($board);
	if (!isBoard($conn, $board))
	{
		return -16;
	}
	$result = $conn->query("SELECT id FROM posts_".$board." WHERE resto=0");
	while ($row = $result->fetch_assoc())
	{
		generateView($conn, $board, $row['id']);
	}
}


function regenThumbnails($conn, $board)
{
	$board = $conn->real_escape_string($board);
	if (!isBoard($conn, $board))
	{
		return -16;
	}
	$result = $conn->query("SELECT filename, resto FROM posts_".$board);
	while ($row = $result->fetch_assoc())
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
		$cats = $conn->query("SELECT * FROM links WHERE parent=-1;");
		while ($row = $cats->fetch_assoc())
		{
			$menu .= '<h2><span class="coll" onclick="toggle(this,' ."'".$row['short']."');". '" title="Toggle Category">&minus;</span>'.$row['title'].'</h2>';
			$menu .= '<div id="'.$row['short'].'" style="">
				<ul>';
			$children = $conn->query("SELECT * FROM links WHERE parent=".$row['id']);
			while ($child = $children->fetch_assoc())
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
		$result = $conn->query("SELECT * FROM news ORDER BY date DESC;");
		while ($row = $result->fetch_assoc())
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
	$result = $conn->query("SELECT * FROM news ORDER BY date DESC;");
	while ($row = $result->fetch_assoc())
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