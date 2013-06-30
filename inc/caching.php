<?php
class Caching
{
	private $conn;
	private $config;
	private $mitsuba;

	function __construct($connection, $mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
		$this->config = $this->mitsuba->config;
	}

	function generateBoardLinks($in_thread = 0)
	{
		$links = '<div id="boardLinks">';
		$links .= $this->generateLinks(-1, $in_thread);
		$links .= '</div>';
		return $links;
	}

	function rebuildBoardLinks()
	{
		$this->mitsuba->updateConfigValue("boardLinks", $this->generateBoardLinks());
		$this->mitsuba->updateConfigValue("boardLinks_thread", $this->generateBoardLinks(1));
		$this->mitsuba->updateConfigValue("boardLinks_index", $this->generateBoardLinks(2));
	}

	function generateLinks($id, $in_thread = 0)
	{
		// MAGIC IS HAPPENING HERE, DO NOT EDIT
		// MAGIC IS HAPPENING HERE, DO NOT EDIT
		// MAGIC IS HAPPENING HERE, DO NOT EDIT
		// MAGIC IS HAPPENING HERE, DO NOT EDIT
		// MAGIC IS HAPPENING HERE, DO NOT EDIT
		// MAGIC IS HAPPENING HERE, DO NOT EDIT
		$result = $this->conn->query("SELECT * FROM links WHERE parent=".$id." ORDER BY short ASC, title ASC, id DESC;");
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
			$l2 = $this->generateLinks($row['id'], $in_thread);
			if (!empty($l2))
			{
				$links .= "[".$l2."] ";
			}
			$no = 1;
		}
		return $links;
	}

	function getBoardLinks($in_thread = 0)
	{
		if ($in_thread == 1)
		{
			return $this->config['boardLinks_thread'];
		} elseif ($in_thread == 0) {
			return $this->config['boardLinks'];
		} else {
			return $this->config['boardLinks_index'];
		}
	}

	function processComment($board, $string, $parser, $thread = 0, $specialchars = 1, $bbcode = 1, $id = 0, $resto = 0, $wordfilter = 1, $wf_table = array())
	{
		global $lang;
		$new = $string;
		
		
		$lines = explode("\n", $new);
		$new = "";
		$c_lines = 0;
		foreach ($lines as $line)
		{
			if ($line == "")
			{
				continue;
			}
			if (substr($line, 0, 2) == ">>")
			{
				$newline = "";
				$space = explode(" ", $line);
				foreach ($space as $word)
				{
					$newline .= $this->getQuotelink($board, $word, $specialchars, $thread)." ";
				}
				$new .= $newline."<br />";
			} elseif (substr($line, 0, 1) == ">")
			{
				if ($specialchars == 1) { $line = htmlspecialchars($line); }
				$new .= "<span class='quote'>".$line."</span><br />";
			} else {
				$newline = "";
				$space = explode(" ", $line);
				foreach ($space as $word)
				{
					$newline .= $this->getQuotelink($board, $word, $specialchars, $thread)." ";
				}
				$new .= $newline."<br />";
				
			}
			$c_lines++;
			if (($c_lines > 15) && ($thread == 0) && (is_numeric($id)) && ($id > 0))
			{
				break;
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
		$new = preg_replace_callback($rurl,
			function ($match)
			{
				if ((substr($match[0], 0, 5) == "http:") || (substr($match[0], 0, 6) == "https:"))
				{
					return "<a href='".$match[0]."'>".$match[0]."</a>";
				} else {
					return $match[0];
				}
			},
			$new);
		if (($c_lines > 15) && ($thread == 0) && (is_numeric($id)) && ($id > 0))
		{
			if ($resto == 0)
			{
				$new .= '<br/><span class="abbr">'.sprintf($lang['img/comment_long'], '<a href="./res/'.$id.'.html#p'.$id.'">', '</a>').'</span>';
			} else {
				$new .= '<br/><span class="abbr">'.sprintf($lang['img/comment_long'], '<a href="./res/'.$resto.'.html#p'.$id.'">', '</a>').'</span>';
			}
		}
		if ($wordfilter == 1)
		{
			$new = strtr($new, $wf_table);
		}
		return $new;
	}

	function getQuotelink($board, $link, $specialchars, $thread)
	{
		$return = $link;
		if ((substr($link, 0, 2) == ">>") && (is_numeric(substr($link, 2))))
		{
			$result = $this->conn->query("SELECT * FROM posts WHERE id='".substr($link, 2)."' AND board='".$board."';");
			if ($result->num_rows == 1)
			{
				
				$row = $result->fetch_assoc();
				if ($row['resto'] != 0)
				{
					if ($thread == 1)
					{
						$return = '<a href="../res/'.$row['resto'].'.html#p'.$row['id'].'" class="quotelink">'.$link.'</a>';
					} elseif ($thread == 0) {
						$return = '<a href="./res/'.$row['resto'].'.html#p'.$row['id'].'" class="quotelink">'.$link.'</a>';
					} else {
						$return = '<a href="?/board&b='.$board.'&t='.$row['resto'].'#p'.$row['id'].'" class="quotelink">'.$link.'</a>';
					}
				} else {
					if ($thread == 1)
					{
						$return = '<a href="../res/'.$row['id'].'.html#p'.$row['id'].'" class="quotelink">'.$link.'</a>';
					} elseif ($thread == 0) {
						$return = '<a href="./res/'.$row['id'].'.html#p'.$row['id'].'" class="quotelink">'.$link.'</a>';
					} else {
						$return = '<a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row['id'].'" class="quotelink">'.$link.'</a>';
					}
				}
			} else {
				$return = "<span class='quote'>".$link."</span>";
			}
		} elseif ((substr($link, 0, 3) == ">>/") || (substr($link, 0, 4) == ">>>/"))
		{
			$parts = explode("/", $link);
			if ($this->mitsuba->common->isBoard($parts[1]))
			{
				if (is_numeric($parts[2]))
				{
					$result = $this->conn->query("SELECT * FROM posts WHERE id='".$parts[2]."' AND board='".$parts[1]."';");
					if ($result->num_rows == 1)
					{
						$row = $result->fetch_assoc();
						if ($row['resto'] != 0)
						{
							if ($thread == 1)
							{
								$return = '<a href="../../'.$parts[1].'/res/'.$row['resto'].'.html#p'.$row['id'].'" class="quotelink cross">'.$link.'</a>';
							} elseif ($thread == 0) {
								$return = '<a href="../'.$parts[1].'/res/'.$row['resto'].'.html#p'.$row['id'].'" class="quotelink cross">'.$link.'</a>';
							} else {
								$return = '<a href="?/board&b='.$parts[1].'&t='.$row['resto'].'#p'.$row['id'].'" class="quotelink cross">'.$link.'</a>';
							}
						} else {
							if ($thread == 1)
							{
								$return = '<a href="../../'.$parts[1].'/res/'.$row['id'].'.html#p'.$row['id'].'" class="quotelink cross">'.$link.'</a>';
							} elseif ($thread == 0) {
								$return = '<a href="../'.$parts[1].'/res/'.$row['id'].'.html#p'.$row['id'].'" class="quotelink cross">'.$link.'</a>';
							} else {
								$return = '<a href="?/board&b='.$parts[1].'&t='.$row['id'].'#p'.$row['id'].'" class="quotelink cross">'.$link.'</a>';
							}
						}
					} else {
						if ($specialchars == 1) {  $link = htmlspecialchars($link); }
						$return = "<span class='quote'>".$link."</span>";
					}
				} else {
					if ($thread == 1)
					{
						$return = '<a href="../../'.$parts[1].'/" class="quotelink cross">'.$link.'</a>';
					} elseif ($thread == 0) {
						$return = '<a href="../'.$parts[1].'/" class="quotelink cross">'.$link.'</a>';
					} else {
						$return = '<a href="?/board&b='.$parts[1].'" class="quotelink cross">'.$link.'</a>';
					}
				}
				
			} else {
				if ($specialchars == 1) {  $link = htmlspecialchars($link); }
				$return = "<span class='quote'>".$link."</span>";
			}
		} elseif (substr($link, 0, 1) == ">") {
			if ($specialchars == 1) { $link = htmlspecialchars($link); }
			$return = "<span class='quote'>".$link."</span>";
		} else {
			if ($specialchars == 1) { $link = htmlspecialchars($link); }
			$return = $link;
		}
		return $return;
	}

	function generateView($board, $threadno = 0, $return = 0, $mode = 0, $adm_type = 0, $overboard = 0)
	{
		global $lang;
		$board = $this->conn->real_escape_string($board);
		if (!$this->mitsuba->common->isBoard($board))
		{
			return -16;
		}
		$boarddata = $this->mitsuba->common->getBoardData($board);
		if (($boarddata['hidden'] == 1) && ($return == 0))
		{
			return -666;
		}
		$wfresult = $this->conn->query("SELECT * FROM wordfilter WHERE active=1");
		$replace_array = array();
		while ($row = $wfresult->fetch_assoc())
		{
			if ($row['boards'] != "*")
			{
				$boards = explode(",", $row['boards']);
				if (in_array($board, $boards))
				{
					$replace_array[$row['search']] = $row['replace'];
				}
			} else {
				$replace_array[$row['search']] = $row['replace'];
			}
		}
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
		if ($boarddata['bbcode']==1)
		{
			$bbcode = $this->conn->query("SELECT * FROM bbcodes;");
			
			while ($row = $bbcode->fetch_assoc())
			{
				$parser->addBBCode($row['name'], $row['code']);
			}
		}
		$embed_table = array();
		$result = $this->conn->query("SELECT * FROM embeds;");
		while ($row = $result->fetch_assoc())
		{
			$embed_table[] = $row;
		}
		$extensions = array();
		$result = $this->conn->query("SELECT * FROM extensions;");
		while ($row = $result->fetch_assoc())
		{
			$extensions[$row['mimetype']]['image'] = $row['image'];
		}
		if ($return == 1)
		{
			$pages = $page;
		}
		
		if ($threadno == 0)
		{
			$cnt = $this->conn->query("SELECT id FROM posts WHERE resto=0 AND board='".$board."'");
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
				$style = $this->conn->query("SELECT * FROM styles WHERE `default`=1");
				$first_default = 0;
				if ($style->num_rows > 0)
				{
					$sdata = $style->fetch_assoc();
					$file .= '<link rel="stylesheet" id="switch" href="'.$this->mitsuba->getPath($sdata['path'], "index", $sdata['relative']).'">';
				} else {
					$first_default = 1;
				}
				$styles = $this->conn->query("SELECT * FROM styles");
				while ($row = $styles->fetch_assoc())
				{
					if ($first_default == 1)
					{
						$file .= '<link rel="stylesheet" id="switch" href="'.$this->mitsuba->getPath($row['path'], "index", $row['relative']).'">';
						$first_default = 0;
					}
					$file .= '<link rel="alternate stylesheet" style="text/css" href="'.$this->mitsuba->getPath($row['path'], "index", $row['relative']).'" title="'.$row['name'].'">';
				}
				$file .= "<script type='text/javascript' src='./js/jquery.js'></script>";
				$file .= "<script type='text/javascript' src='./js/jquery.cookie.js'></script>";
				$file .= "<script type='text/javascript' src='./js/common.js'></script>";
				$file .= "<script type='text/javascript' src='./js/admin.js'></script>";
				$file .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
				$file .= '<meta property="og:boardname" content="'.$boarddata['short'].'" />';
				$file .= "</head><body class='modPanel'>";
				$file .= $this->getBoardLinks(2);
			} elseif ($threadno != 0)
			{
				$file .= "<head><title>/".$boarddata['short']."/ - ".$boarddata['name']."</title>";
				$style = $this->conn->query("SELECT * FROM styles WHERE `default`=1;");
				$first_default = 0;
				if (mysqli_num_rows($style) > 0)
				{
					$sdata = $style->fetch_assoc();
					$file .= '<link rel="stylesheet" id="switch" href="'.$this->mitsuba->getPath($sdata['path'], "thread", $sdata['relative']).'">';
				} else {
					$first_default = 1;
				}
				$styles = $this->conn->query("SELECT * FROM styles");
				while ($row = $styles->fetch_assoc())
				{
					if ($first_default == 1)
					{
						$file .= '<link rel="stylesheet" id="switch" href="'.$this->mitsuba->getPath($row['path'], "thread", $row['relative']).'">';
						$first_default = 0;
					}
					$file .= '<link rel="alternate stylesheet" style="text/css" href="'.$this->mitsuba->getPath($row['path'], "thread", $row['relative']).'" title="'.$row['name'].'">';
				}
				$file .= "<script type='text/javascript' src='../../js/jquery.js'></script>";
				$file .= "<script type='text/javascript' src='../../js/jquery.cookie.js'></script>";
				$file .= "<script type='text/javascript' src='../../js/common.js'></script>";
				$file .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
				$file .= '<meta property="og:boardname" content="'.$boarddata['short'].'" />';
				$file .= "</head><body>";
				$file .= $this->getBoardLinks(1);
			} else {
				$file .= "<head><title>/".$boarddata['short']."/ - ".$boarddata['name']."</title>";
				$style = $this->conn->query("SELECT * FROM styles WHERE `default`=1;");
				$first_default = 0;
				if (mysqli_num_rows($style) > 0)
				{
					$sdata = $style->fetch_assoc();
					$file .= '<link rel="stylesheet" id="switch" href="'.$this->mitsuba->getPath($sdata['path'], "board", $sdata['relative']).'">';
				} else {
					$first_default = 1;
				}
				$styles = $this->conn->query("SELECT * FROM styles");
				while ($row = $styles->fetch_assoc())
				{
					if ($first_default == 1)
					{
						$file .= '<link rel="stylesheet" id="switch" href="'.$this->mitsuba->getPath($row['path'], "board", $row['relative']).'">';
						$first_default = 0;
					}
					$file .= '<link rel="alternate stylesheet" style="text/css" href="'.$this->mitsuba->getPath($row['path'], "board", $row['relative']).'" title="'.$row['name'].'">';
				}
				$file .= "<script type='text/javascript' src='../js/jquery.js'></script>";
				$file .= "<script type='text/javascript' src='../js/jquery.cookie.js'></script>";
				$file .= "<script type='text/javascript' src='../js/common.js'></script>";
				$file .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
				$file .= '<meta property="og:boardname" content="'.$boarddata['short'].'" />';
				$file .= "</head><body>";
				$file .= $this->getBoardLinks(0);
			}
			$file .= '<div class="boardBanner">';
			$imagesDir = './rnd/';
			$images = glob($imagesDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
			$imagesDirBoard = './rnd/'.$board.'/';
			if (is_dir($imagesDirBoard))
			{
				$images = array_merge($images, glob($imagesDirBoard . '*.{jpg,jpeg,png,gif}', GLOB_BRACE));
			}
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
				$result = $this->conn->query("SELECT * FROM posts WHERE id=".$threadno." AND board='".$board."';");
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
					$file .= '<div class="postingMode">'.$lang['img/posting_mode'].'</div>';
					if ($return == 1)
					{
						$file .= '<div class="navLinks">[<a href="?/board&b='.$board.'" accesskey="a">'.$lang['img/return_c'].'</a>] [<a href="#bottom">'.$lang['img/bottom'].'</a>]</div>';
						$file .= '<form id="postform" action="./imgboard.php?mod=1" method="post" enctype="multipart/form-data">';
					} else {
						$file .= '<div class="navLinks">[<a href=".././" accesskey="a">'.$lang['img/return_c'].'</a>] [<a href="#bottom">'.$lang['img/bottom'].'</a>]</div>';
						$file .= '<form id="postform" action="../../imgboard.php" method="post" enctype="multipart/form-data">';
					}
				} else {
					if ($return == 1)
					{
						$file .= '<form id="postform" action="./imgboard.php?mod=1" method="post" enctype="multipart/form-data">';
					} else {
						$file .= '<form id="postform" action="../imgboard.php" method="post" enctype="multipart/form-data">';
					}
				}
				if ($adm_type <= 0)
				{
					$file .= '<input type="hidden" name="MAX_FILE_SIZE" value="'.$boarddata['filesize'].'" />';
				}
				$file .= '<input type="hidden" name="mode" value="regist" />
					<table class="postForm" id="postForm">
					<tbody>';
				if (($boarddata['noname'] == 0) || ($adm_type >= 2))
				{
					$file .= '<tr>
						<td>'.$lang['img/name'].'</td>
						<td><input name="name" type="text" /></td>
						</tr>';
				}
				if (($boarddata['ids'] == 1) && ($adm_type >= 2))
				{
					$file .= '<tr>
						<td>'.$lang['img/fake_id'].'</td>
						<td><input name="fake_id" type="text" /></td>
						</tr>';
				}
				$file .= '<tr>
					<td>'.$lang['img/email'].'</td>
					<td><input name="email" type="text" /></td>
					</tr>
					<tr>
					<td>'.$lang['img/subject'].'</td>
					<td><input name="sub" type="text" />';
				$file .= '<input type="hidden" name="board" value="'.$board.'" />';
				if ($threadno != 0)
				{
					$file .= '<input type="hidden" name="resto" value="'.$threadno.'" />';
				}
				$file .= '<input type="submit" value="'.$lang['img/submit'].'" /></td>
					</tr>
					<tr>
					<td>'.$lang['img/comment'].'</td>
					<td><textarea name="com" cols="35" rows="4"></textarea></td>
					</tr>
					<tr>
					<td>'.$lang['img/file'].'</td>
					<td><input id="postFile" name="upfile" type="file" />';
				if ($boarddata['spoilers'] == 1)
				{
					$file .= '<label><input type="checkbox" name="spoiler" value="1">'.$lang['img/spoiler'].'</label>';
				}
				if ($boarddata['embeds'] == 1)
				{
					$file .= '<br />'.$lang['img/embed'].': <input type="text" name="embed"/>';
				}
				$file .= '</td>
					</tr>
					<tr>
					<td>'.$lang['img/password'].'</td>
					<td><input id="postPassword" name="pwd" type="password" maxlength="8" /> <span class="password">'.$lang['img/password_used'].'</span></td>
					</tr>';
				if ($adm_type >= 2)
				{
					$file .='<tr>
						<td>'.$lang['img/mod'].'</td>
						<td><input type="checkbox" name="raw" value=1 />'.$lang['img/mod_raw'].'<input type="checkbox" name="sticky" value=1 />'.$lang['img/mod_sticky'].'<input type="checkbox" name="lock" value=1 />'.$lang['img/mod_lock'].'<br />';
					$file .= '<input type="checkbox" name="nolimit" value=1 selected/>'.$lang['img/mod_nolimit'].'<input type="checkbox" name="ignoresizelimit" value=1 />'.$lang['img/mod_nosizelimit'].'<input type="checkbox" name="nofile" value=1 />'.$lang['img/mod_nofile'].'</td>';
					$file .='<tr>
						<td>'.$lang['img/mod_capcode'].'</td>
						<td id="capcode_td"><input type="radio" name="capcode" value=0 checked />'.$lang['img/mod_nocapcode'].'<input type="radio" name="capcode" value=1 />'.$lang['img/mod_capcode'];
					if ($adm_type == 3)
					{	
						$file .= '<input type="radio" name="capcode" value=2 id="custom_cc" />'.$lang['img/mod_customcapcode'];
						$file .= '<div style="display: none;" id="cc_fields" value="#FF0000">'.$lang['img/text'].': <input type="text" name="cc_text" /><br />
						'.$lang['img/color'].': <input type="text" name="cc_color" /></div>';
						$file .= "<script type=\"text/javascript\">
	$(\"input[name='capcode']\").change(function() {
	if ($(\"#custom_cc\").prop(\"checked\"))
	{
		$(\"#cc_fields\").css(\"display\", \"\");
	} else {
		$(\"#cc_fields\").css(\"display\", \"none\");
		$(\"#cc_fields input\").val(\"\");
	}
	});
	</script>";
					}
					$file .= "</td></tr>";
				}
				$file .= '<tr class="rules">
					<td colspan="2">
					<ul class="rules">
					<li>'.$lang['img/supported_types'].'</li>
					<li>'.sprintf($lang['img/max_filesize'], $boarddata['filesize']).'</li>
					<li>'.$lang['img/thumbnail'].'</li>
					</ul>
					</td>
					</tr>
					</tbody>
					</table>
					</form>';
			} else {
				$file .= "<div class='closed'><h1>".$lang['img/locked']."</h1></div>";
			}
			$file .= "<hr />";
			if (!empty($this->config['global_message']))
			{
				$file .= '<div class="globalMessage" id="globalMessage">';
				$file .= $this->config['global_message'];
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
				$result = $this->conn->query("SELECT * FROM posts WHERE id=".$threadno." AND board='".$board."';");
			} else {
				
				$result = $this->conn->query("SELECT * FROM posts WHERE resto=0 AND board='".$board."' ORDER BY sticky DESC, lastbumped DESC LIMIT ".($pg*10).",10");
			}
			while ($row = $result->fetch_assoc())
			{
				$file .= $this->getThread($board, $threadno, $return, $adm_type, $parser, $boarddata, $replace_array, $embed_table, $row, $extensions);
			}
			$file .= "</div>";
			if ($threadno != 0)
			{
				if ($return == 1)
				{
					$file .= '<div class="navLinks">[<a href="?/board&b='.$board.'" accesskey="a">'.$lang['img/return_c'].'</a>] [<a href="#top">'.$lang['img/top'].'</a>]</div>';
				} else {
					$file .= '<div class="navLinks">[<a href=".././" accesskey="a">'.$lang['img/return_c'].'</a>] [<a href="#top">'.$lang['img/top'].'</a>]</div>';
				}
			}
			$file .= '<div class="deleteform">
				<input type="hidden" name="board" value="'.$board.'" />
				<input type="hidden" name="mode" value="usrform" />'.$lang['img/delete_post'].' [<input type="checkbox" name="onlyimgdel" value="on" />'.$lang['img/file_only'].'] ';
			if ($adm_type <= 1)
			{
			$file .= $lang['img/password'].' <input type="password" id="delPassword" name="pwd" maxlength="8" /> ';
			}
			$file .= '<input type="submit" name="delete" value="'.$lang['img/delete'].'" /><br />';
			if ($adm_type <= 1)
			{
			$file .= $lang['img/reason'].' <input type="text" name="reason" /><input type="submit" name="report" value="'.$lang['img/report'].'" />';
			}
			$file .= '<div class="stylechanger" id="stylechangerDiv" style="display:none;">'.$lang['img/style'].' <select id="stylechanger"></select></div>
				</div>';
			$file .= "</form>";
			if (($return == 1) && ($threadno == 0))
			{
				$file .= '<div class="pagelist">';
				$file .= '<div class="prev">';
				if ($page != 0)
				{
					
					$file .= '<form action="?/board&b='.$board.'&p='.($page-1).'" onsubmit="location=this.action; return false;"><input type="submit" value="'.$lang['img/previous'].'" /></form>';
					
				} else {
					$file .= '<span>'.$lang['img/previous'].'</span>';
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
					$file .= '<form action="?/board&b='.$board.'&p='.($page+1).'" onsubmit="location=this.action; return false;"><input type="submit" value="'.$lang['img/next'].'" /></form>';
				} else {
					$file .= '<span>'.$lang['img/next'].'</span>';
				}
				$file .= '</div>';
				$file .= '</div>';
			} elseif ($threadno == 0)
			{
				$file .= '<div class="pagelist">';
				$file .= '<div class="prev">';
				if ($pg != 0)
				{
					if ($pg != 1)
					{
						$file .= '<form action="./'.($pg-1).'.html" onsubmit="location=this.action; return false;"><input type="submit" value="'.$lang['img/previous'].'" /></form>';
					} else {
						$file .= '<form action="./index.html" onsubmit="location=this.action; return false;"><input type="submit" value="'.$lang['img/previous'].'" /></form>';
					}
				} else {
					$file .= '<span>'.$lang['img/previous'].'</span>';
				}
				$file .= ' </div>';
				$file .= '<div class="pages">';
				for ($i = 0; $i <= $max_pages; $i++)
				{
					if ($i == $pg)
					{
						$file .= "[<a href='./".$i.".html'><strong>".$i."</strong></a>] ";
					} else {
						if ($i >= $all_pages)
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
					$file .= '<form action="./'.($pg+1).'.html" onsubmit="location=this.action; return false;"><input type="submit" value="'.$lang['img/next'].'" /></form>';
				} else {
					$file .= '<span>'.$lang['img/next'].'</span>';
				}
				$file .= '</div>';
				$file .= '</div>';
			}
			$file .= '<div style="text-align: center; font-size: x-small!important; padding-bottom: 4px; padding-top: 10px; color: #333;"><span class="absBotDisclaimer">- <a href="http://github.com/MitsubaBBS/Mitsuba" target="_top" rel="nofollow">mitsuba</a> -</span></div>';
			$file .= '<div id="bottom"></div>';
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

	function generatePage($name)
	{
		global $lang;
		//markdown parser
		
		$result = $this->conn->query("SELECT * FROM pages WHERE name='".$this->conn->real_escape_string($name)."'");

		if (($result->num_rows == 0) && (file_exists("./".$name.".html")))
		{
			unlink("./".$name.".html");
			return;
		}

		$row = $result->fetch_assoc();
		$title = $row['title'];
		$text = $row['text'];

		if ($row['raw'] == 1)
		{
			$file = $text;
		} else {
			$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			$file .= '<html>
				<head>
				<title>'.$title.'</title>
				<link rel="stylesheet" href="./styles/index.css" />
				<link rel="stylesheet" href="./styles/global.css" />
				<link rel="stylesheet" href="./styles/table.css" />
				</head>
				<body>';
			$file .= '<div id="doc">
				<br /><br />';
			$file .= '<div class="box-outer top-box">
				<div class="box-inner">
				<div class="boxbar"><h2>'.$title.'</h2></div>
				<div class="boxcontent">';
			require_once("inc/markdown.php");
			$file .= Markdown($text);
			$file .= '</div>
				</div>
				</div>
				</div>
				</body>
				</html>';
		}
		$handle = fopen("./".$name.".html", "w");
		fwrite($handle, $file);
		fclose($handle);
	}

	function forceGetThread($board, $threadno)
	{
		global $lang;
		if ($this->mitsuba->common->isBoard($board))
		{
			$result = $this->conn->query("SELECT * FROM posts WHERE id=".$threadno." AND board='".$board."'");
			if ($result->num_rows == 1)
			{
				$trow = $result->fetch_assoc();
				$boarddata = $this->mitsuba->common->getBoardData($board);
				$wfresult = $this->conn->query("SELECT * FROM wordfilter WHERE active=1");
				$replace_array = array();
				while ($row = $wfresult->fetch_assoc())
				{
					if ($row['boards'] != "*")
					{
						$boards = explode(",", $row['boards']);
						if (in_array($board, $boards))
						{
							$replace_array[$row['search']] = $row['replace'];
						}
					} else {
						$replace_array[$row['search']] = $row['replace'];
					}
				}
				require_once( "./jbbcode/Parser.php" );
				$parser = new JBBCode\Parser();
				if ($boarddata['bbcode']==1)
				{
					$bbcode = $this->conn->query("SELECT * FROM bbcodes;");
					
					while ($row = $bbcode->fetch_assoc())
					{
						$parser->addBBCode($row['name'], $row['code']);
					}
				}
				$embed_table = array();
				$result = $this->conn->query("SELECT * FROM embeds;");
				while ($row = $result->fetch_assoc())
				{
					$embed_table[] = $row;
				}
				
				$extensions = array();
				$result = $this->conn->query("SELECT * FROM extensions;");
				while ($row = $result->fetch_assoc())
				{
					$extensions[$row['mimetype']]['image'] = $row['image'];
				}

				$file = "";
				$file = $this->getThread($trow['board'], 0, 0, 0, $parser, $boarddata, $replace_array, $embed_table, $trow, $extensions, 1);
			
			}
		}
	}

	function getThread($board, $threadno, $return, $adm_type, $parser, $boarddata, $replace_array, $embed_table, $row, $extensions, $force = 0)
	{
		global $lang;
		if (($this->config['caching_mode']==1) && ($threadno == 0) && ($return == 0) && ($force == 0) && (file_exists("./".$board."/res/".$row['id']."_index.html")))
		{
			return file_get_contents("./".$board."/res/".$row['id']."_index.html");
		}
		$file = "";
		$file .= '<div class="thread" id="t'.$row['id'].'">';
		$file .= '<div class="postContainer opContainer" id="pc'.$row['id'].'">';
		$file .= '<div id="p'.$row['id'].'" class="post op">';
		$file .= '<div class="postInfo" id="pi'.$row['id'].'">';
		$file .= '<input type="checkbox" name="'.$row['id'].'" value="delete" />';
		$file .= '<span class="subject">'.$row['subject'].'</span> ';
		$trip = "";
		if (!empty($row['trip']))
		{
			$trip = " !".$row['trip']."";
		}
		if (!empty($row['strip']))
		{
			$trip .= " !!".$row['strip']."";
		}
		if ((!empty($row['trip'])) || (!empty($row['strip'])))
		{
			$trip = "<span class='postertrip'>".$trip."</span>";
		}
		$poster_id = "";
		if ((!empty($row['poster_id'])) && ($boarddata['ids']==1) && ($row['capcode']<2))
		{
			$poster_id = '<span class="posteruid">(ID: '.$row['poster_id'].')</span>';
		}
		$c_image = "";
		if ($row['capcode'] == 2)
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
		} elseif ($row['capcode'] == 3)
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
		$email_a = "";
		$email_b = "";
		if (!empty($row['email'])) {
			$email_a = '<a href="mailto:'.$row['email'].'" class="useremail">';
			$email_b = '</a>';
		}
		if ($row['capcode'] == 2)
		{
			$file .= '<span class="nameBlock">'.$email_a.'<span class="name"><span style="color:#800080">'.$row['name'].'</span></span>'.$email_b.$trip.' <span class="commentpostername"><span style="color:#800080">## Mod</span>'.$c_image.'</span> '.$poster_id.'</span>';
		} elseif ($row['capcode'] == 3)
		{
			$file .= '<span class="nameBlock">'.$email_a.'<span class="name"><span style="color:#FF0000">'.$row['name'].'</span></span>'.$email_b.$trip.' <span class="commentpostername"><span style="color:#FF0000">## Admin</span>'.$c_image.'</span> '.$poster_id.'</span>';
		} elseif ($row['capcode'] == 4)
		{
			$file .= '<span class="nameBlock">'.$email_a.'<span class="name"><span style="color:#FF00FF">'.$row['name'].'</span></span>'.$email_b.$trip.' <span class="commentpostername"><span style="color:#FF00FF">## Faggot</span>'.$c_image.'</span> '.$poster_id.'</span>';
		} elseif ($row['capcode'] == 5)
		{
			$file .= '<span class="nameBlock">'.$email_a.'<span class="name"><span style="color:'.$row['cc_color'].'">'.$row['name'].'</span></span>'.$email_b.$trip.' <span class="commentpostername"><span style="color:'.$row['cc_color'].'">## '.$row['cc_text'].'</span>'.$c_image.'</span> '.$poster_id.'</span>';
		} else {
			$file .= '<span class="nameBlock">'.$email_a.'<span class="name">'.$row['name'].'</span>'.$email_b.$trip.' '.$poster_id.'</span>';
		}
		
		$opip = $row['ip'];
		if (($adm_type >= 2) && ($return == 1))
		{
			$file .= ' <span class="posterIp">(<a href="http://whatismyipaddress.com/ip/'.$row['ip'].'" target="_blank">'.$row['ip'].'</a>)</span>';
			$file .= ' [<a href="?/info&ip='.$row['ip'].'">N</a>] <b style="color: red;">[ OP ]</b>';
		}
		$file .= ' <span class="dateTime">'.date("d/m/Y(D)H:i:s", $row['date']).'</span> ';

		if ($return == 1)
		{
			$file .= '<span class="postNum"><a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row['id'].'" title="Highlight this post">No.</a><a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row['id'].'#q'.$row['id'].'" class="quotePost" id="q'.$row['id'].'" title="Quote this post">'.$row['id'].'</a></span>';
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
				$file .= ' <span style="color: red;">[A]</span> ';
			}
			if ($adm_type >= 2)
			{
				$file .= ' <span class="adminControls">[<a href="?/bans/add&b='.$board.'&p='.$row['id'].'">B</a> / <a href="?/bans/add&b='.$board.'&p='.$row['id'].'&d=1">&</a> / <a href="?/delete_post&b='.$board.'&p='.$row['id'].'">D</a>';
				if (!empty($row['filename']))
				{
					$file .= ' / <a href="?/delete_post&b='.$board.'&p='.$row['id'].'&f=1">F</a>]';
				} else {
					$file .= ']';
				}
				if ($adm_type >= 3)
				{
					$file .= ' [<a href="?/edit_post&b='.$board.'&p='.$row['id'].'" class="edit">E</a>]';
				}
			} else {
				$file .= ' <span class="adminControls">[<a href="?/bans/add&b='.$board.'&p='.$row['id'].'">B</a>]';
			}
			if ($adm_type >= 2)
			{
				$file .= ' [<a href="?/sticky/toggle&b='.$board.'&t='.$row['id'].'">S</a> / <a href="?/locked/toggle&b='.$board.'&t='.$row['id'].'">L</a> / <a href="?/antibump/toggle&b='.$board.'&t='.$row['id'].'">A</a>]';
			}
			if ($threadno == 0)
			{
				$file .= '&nbsp; <span>[<a href="?/board&b='.$board.'&t='.$row['id'].'" class="replylink">'.$lang['img/reply'].'</a>]</span>';
			}
			$file .= '</span>';
		} elseif ($threadno != 0)
		{
			$file .= '<span class="postNum"><a href="../res/'.$row['id'].'.html#p'.$row['id'].'" title="Highlight this post">No.</a><a href="../res/'.$row['id'].'.html#q'.$row['id'].'" class="quotePost" id="q'.$row['id'].'" title="Quote this post">'.$row['id'].'</a>';
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
			$file .= '<span class="postNum"><a href="./res/'.$row['id'].'.html#p'.$row['id'].'" title="Highlight this post">No.</a><a href="./res/'.$row['id'].'.html#q'.$row['id'].'" class="quotePost" id="q'.$row['id'].'" title="Quote this post">'.$row['id'].'</a> ';
			if ($row['locked']==1)
			{
				$file .= '<img src="../img/closed.gif" alt="Closed" title="Closed" class="stickyIcon" />';
			}
			if ($row['sticky']==1)
			{
				$file .= '<img src="../img/sticky.gif" alt="Sticky" title="Sticky" class="stickyIcon" />';
			}
			$file .= '&nbsp; <span>[<a href="./res/'.$row['id'].'.html" class="replylink">'.$lang['img/reply'].'</a>]</span></span>';
		}
		$file .= '</div>';
		$file .= $this->getFiles($row, $board, $return, $threadno, $embed_table, $extensions);
		$file .= '<blockquote class="postMessage" id="m'.$row['id'].'">';
		$wf = 1;
		
		if ($row['capcode'] >= 2)
		{
			$wf = 0;
		}
		if ($row['raw'] != 1)
		{
			if ($row['raw'] == 2)
			{
				if ($return == 1)
				{
					$file .= $this->processComment($board, $row['comment'], $parser, 2, 0, $boarddata['bbcode'], $row['id'], $row['resto'], $wf, $replace_array);
				} else {
					$file .= $this->processComment($board, $row['comment'], $parser, $threadno != 0, 0, $boarddata['bbcode'], $row['id'], $row['resto'], $wf, $replace_array);
				}
			} else {
				if ($return == 1)
				{
					$file .= $this->processComment($board, $row['comment'], $parser, 2, 1, $boarddata['bbcode'], $row['id'], $row['resto'], $wf, $replace_array);
				} else {
					$file .= $this->processComment($board, $row['comment'], $parser, $threadno != 0, 1, $boarddata['bbcode'], $row['id'], $row['resto'], $wf, $replace_array);
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
			$posts = $this->conn->query("SELECT * FROM posts WHERE resto=".$row['id']." AND board='".$board."' ORDER BY id ASC");
		} else {
		$postnos = $this->conn->query("SELECT COUNT(*) FROM posts WHERE resto=".$row['id']." AND board='".$row['board']."'");
		$row1 = $postnos->fetch_row();
		if ($row1[0] == 0)
		{
			$file .= '</div><hr />';
			return $file;
		}
		if ($row1[0] > 3)
		{
			if ($return == 1)
			{
				$file .= '<span class="summary">'.sprintf($lang['img/posts_omitted'], ($row1[0]-3), '<a href="?/board&b='.$board.'&t='.$row['id'].'" class="replylink">', '</a>').'</span>';
			} else {
				$file .= '<span class="summary">'.sprintf($lang['img/posts_omitted'], ($row1[0]-3), '<a href="./res/'.$row['id'].'.html" class="replylink">', '</a>').'</span>';
			}
		}
		$offset = 0;
		if ($row1[0] > 3)
		{
			$offset = $row1[0] - 3;
			
		}
		$posts = $this->conn->query("SELECT * FROM posts WHERE resto=".$row['id']." AND board='".$board."' ORDER BY id ASC LIMIT ".$offset.",3");
			
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
				$trip = " !".$row2['trip']."";
			}
			if (!empty($row2['strip']))
			{
				$trip .= " !!".$row2['strip']."";
			}
			if ((!empty($row2['trip'])) || (!empty($row2['strip'])))
			{
				$trip = "<span class='postertrip'>".$trip."</span>";
			}
			$c_image = "";
			if ($row2['capcode'] == 2)
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
			} elseif ($row2['capcode'] == 3)
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
			$email_a = "";
			$email_b = "";
			if (!empty($row2['email'])) {
				$email_a = '<a href="mailto:'.$row2['email'].'" class="useremail">';
				$email_b = '</a>';
			}
			$poster_id = "";
			if ((!empty($row2['poster_id'])) && ($boarddata['ids']==1) && ($row2['capcode']<1))
			{
				$poster_id = '<span class="posteruid">(ID: '.$row2['poster_id'].')</span>';
			}
			if ($row2['capcode'] == 2)
			{
				$file .= '<span class="nameBlock">'.$email_a.'<span class="name"><span style="color:#800080">'.$row2['name'].'</span></span>'.$email_b.$trip.' <span class="commentpostername"><span style="color:#800080">## Mod</span>'.$c_image.'</span> '.$poster_id.'</span>';
			} elseif ($row2['capcode'] == 3)
			{
				$file .= '<span class="nameBlock">'.$email_a.'<span class="name"><span style="color:#FF0000">'.$row2['name'].'</span></span>'.$email_b.$trip.' <span class="commentpostername"><span style="color:#FF0000">## Admin</span>'.$c_image.'</span> '.$poster_id.'</span>';
			} elseif ($row2['capcode'] == 4)
			{
				$file .= '<span class="nameBlock">'.$email_a.'<span class="name"><span style="color:#FF00FF">'.$row2['name'].'</span></span>'.$email_b.$trip.' <span class="commentpostername"><span style="color:#FF00FF">## Faggot</span>'.$c_image.'</span> '.$poster_id.'</span>';
			} elseif ($row2['capcode'] == 5)
			{
				$file .= '<span class="nameBlock">'.$email_a.'<span class="name"><span style="color:'.$row2['cc_color'].'">'.$row2['name'].'</span></span>'.$email_b.$trip.' <span class="commentpostername"><span style="color:'.$row2['cc_color'].'">## '.$row2['cc_text'].'</span>'.$c_image.'</span> '.$poster_id.'</span>';
			} else {
				$file .= '<span class="nameBlock">'.$email_a.'<span class="name">'.$row2['name'].'</span>'.$email_b.$trip.' '.$poster_id.'</span>';
			}
			if (($adm_type >= 2) && ($return == 1))
			{
				$file .= ' <span class="posterIp">(<a href="http://whatismyipaddress.com/ip/'.$row2['ip'].'" target="_blank">'.$row2['ip'].'</a>) [<a href="?/info&ip='.$row2['ip'].'">N</a>] '; 
				if ($row2['ip'] == $opip)
				{
					$file .= '<b style="color: red;">[ OP ]</b>';
				}
			}
			$file .= ' <span class="dateTime">'.date("d/m/Y(D)H:i:s", $row2['date']).'</span> ' ;
			if ($return == 1)
			{
				$file .= '<span class="postNum"><a href="?/board&b='.$board.'&t='.$row['id'].'#p'.$row2['id'].'" title="Highlight this post">No.</a><a href="?/board&b='.$board.'&t='.$row['id'].'#q'.$row2['id'].'" class="quotePost" id="q'.$row2['id'].'" title="Quote this post">'.$row2['id'].'</a></span>';
				$file .= ' <span class="adminControls">[<a href="?/bans/add&b='.$board.'&p='.$row2['id'].'">B</a> / <a href="?/bans/add&b='.$board.'&p='.$row2['id'].'&d=1">&</a> / <a href="?/delete_post&b='.$board.'&p='.$row2['id'].'">D</a>';
				
				
				if (!empty($row2['filename']))
				{
					$file .= ' / <a href="?/delete_post&b='.$board.'&p='.$row2['id'].'&f=1">F</a>] ';
				} else {
					$file .= ']';
				}
				if ($adm_type >= 3)
				{
					$file .= ' [<a href="?/edit_post&b='.$board.'&p='.$row2['id'].'" class="edit">E</a>]';
				}
				$file .= "</span>";
			} elseif ($threadno != 0)
			{
				$file .= '<span class="postNum"><a href="../res/'.$row2['resto'].'.html#p'.$row2['id'].'" title="Highlight this post">No.</a><a href="../res/'.$row2['resto'].'.html#q'.$row2['id'].'" class="quotePost" id="q'.$row2['id'].'" title="Quote this post">'.$row2['id'].'</a> &nbsp;</span>';
			} else {
				$file .= '<span class="postNum"><a href="./res/'.$row2['resto'].'.html#p'.$row2['id'].'" title="Highlight this post">No.</a><a href="./res/'.$row2['resto'].'.html#q'.$row2['id'].'" class="quotePost" id="q'.$row2['id'].'" title="Quote this post">'.$row2['id'].'</a> &nbsp;</span>';
			}
			$file .= '</div>';
			$file .= $this->getFiles($row2, $board, $return, $threadno, $embed_table, $extensions);
			$file .= '<blockquote class="postMessage" id="m'.$row2['id'].'">';
			$wf = 1;
			if ($row2['capcode'] >= 2)
			{
				$wf = 0;
			}
			if ($row2['raw'] != 1)
			{
				if ($row2['raw'] == 2)
				{
					if ($return == 1)
					{
						$file .= $this->processComment($board, $row2['comment'], $parser, 2, 0, $boarddata['bbcode'], $row2['id'], $row2['resto'], $wf, $replace_array);
					} else {
						$file .= $this->processComment($board, $row2['comment'], $parser, $threadno != 0, 0, $boarddata['bbcode'], $row2['id'], $row2['resto'], $wf, $replace_array);
					}
				} else {
					if ($return == 1)
					{
						$file .= $this->processComment($board, $row2['comment'], $parser, 2, 1, $boarddata['bbcode'], $row2['id'], $row2['resto'], $wf, $replace_array);
					} else {
						$file .= $this->processComment($board, $row2['comment'], $parser, $threadno != 0, 1, $boarddata['bbcode'], $row2['id'], $row2['resto'], $wf, $replace_array);
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
		if (($this->config['caching_mode']==1) && ($threadno == 0) && ($return == 0))
		{
				$handle = fopen("./".$board."/res/".$row['id']."_index.html", "w");
				fwrite($handle, $file);
				fclose($handle);
		}
		return $file;
		
	}

	function generateCatalog($board, $return = 0)
	{
			if ($return != 1)
			{
				$handle = fopen("./".$board."/catalog.html", "w");
				fwrite($handle, $file);
				fclose($handle);
			} else {
				return $file;
			}
	}

	function updateThreads($board)
	{
		$board = $this->conn->real_escape_string($board);
		if (!$this->mitsuba->common->isBoard($board))
		{
			return -16;
		}
		$result = $this->conn->query("SELECT id FROM posts WHERE resto=0 AND board='".$board."'");
		while ($row = $result->fetch_assoc())
		{
			$this->generateView($board, $row['id']);
			if ($this->config['enable_api']==1)
			{
				$this->serializeThread($board, $row['id']);
			}
			if ($this->config['caching_mode']==1)
			{
				$this->forceGetThread($board, $row['id']);
			}
		}
	}

	function serializeThread($board, $thread_id)
	{
		if ($this->mitsuba->common->isBoard($board))
		{
			$thread = $this->conn->query("SELECT * FROM posts WHERE board='".$board."' AND id=".$thread_id);
			if ($thread->num_rows == 1)
			{
				$row = $thread->fetch_assoc();
				require_once( "./jbbcode/Parser.php" );
				$parser = new JBBCode\Parser();
				$boarddata = $this->mitsuba->common->getBoardData($board);
				if ($boarddata['bbcode']==1)
				{
					$bbcode = $this->conn->query("SELECT * FROM bbcodes;");
					
					while ($row = $bbcode->fetch_assoc())
					{
						$parser->addBBCode($row['name'], $row['code']);
					}
				}
				$api_posts = array();
				$api_posts[] = $this->serializePost($row, $boarddata, $parser);
				
				$posts = $this->conn->query("SELECT * FROM posts WHERE board='".$board."' AND resto=".$thread_id);
				
				while ($row2 = $posts->fetch_assoc())
				{
					$api_posts[] = $this->serializePost($row2, $boarddata, $parser);
				}
				
				$api_handle = fopen("./".$board."/res/".$thread_id.".json", "w");
				$api['posts'] = $api_posts;
				fwrite($api_handle, json_encode($api));
				fclose($api_handle);
			}
		}
		
	}

	function regenThumbnails($board)
	{
		$board = $this->conn->real_escape_string($board);
		if (!$this->mitsuba->common->isBoard($board))
		{
			return -16;
		}
		$result = $this->conn->query("SELECT filename, resto, id FROM posts WHERE board='".$board."'");
		while ($row = $result->fetch_assoc())
		{
			if ((!empty($row['filename'])) && ($row['filename'] != "deleted"))
			{
				if (substr($row['filename'], 0, 8) == "spoiler:")
				{
					if ($row['resto'] != 0)
					{
						$info = $this->mitsuba->common->thumb($board, substr($row['filename'], 8), 125);
						if (!empty($info['width']))
						{
							$this->conn->query("UPDATE posts SET t_w=".$info['width'].", t_h=".$info['height']." WHERE id=".$row['id']." AND board='".$board."'");
						}
					} else {
						$info = $this->mitsuba->common->thumb($board, substr($row['filename'], 8));
						if (!empty($info['width']))
						{
							$this->conn->query("UPDATE posts SET t_w=".$info['width'].", t_h=".$info['height']." WHERE id=".$row['id']." AND board='".$board."'");
						}
					}
				} elseif (substr($row['filename'], 0, 6) != "embed:") {
					if ($row['resto'] != 0)
					{
						$info = $this->mitsuba->common->thumb($board, $row['filename'], 125);
						if (!empty($info['width']))
						{
							$this->conn->query("UPDATE posts SET t_w=".$info['width'].", t_h=".$info['height']." WHERE id=".$row['id']." AND board='".$board."'");
						}
					} else {
						$info = $this->mitsuba->common->thumb($board, $row['filename']);
						if (!empty($info['width']))
						{
							$this->conn->query("UPDATE posts SET t_w=".$info['width'].", t_h=".$info['height']." WHERE id=".$row['id']." AND board='".$board."'");
						}
					}
				}
			}
		}
	}

	function generateFrontpage()
	{
		if ($this->config['frontpage_style'] == 0) //Kusaba X style
		{
		
			$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			$file .= '<html>
				<head>
				<title>'.$this->config['sitename'].'</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
				</head>
				<frameset cols="200px,*" frameborder="1" border="1" bordercolor="#800">
				<frame src="'.$this->config['frontpage_menu_url'].'" id="nav">
				<frame src="'.$this->config['news_url'].'" name="main" id="main">
				<noframes>
				<h1>'.$this->config['sitename'].'</h1>
				<p>This page uses frames!</p>
				</noframes>
				</frameset>
				</html>';
			$handle = fopen("./".$this->config['frontpage_url'], "w");
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
			$menu .= "<h1>".$this->config['sitename']."</h1>";
			$cats = $this->conn->query("SELECT * FROM links WHERE parent=-1;");
			while ($row = $cats->fetch_assoc())
			{
				$menu .= '<h2><span class="coll" onclick="toggle(this,' ."'".$row['short']."');". '" title="Toggle Category">&minus;</span>'.$row['title'].'</h2>';
				$menu .= '<div id="'.$row['short'].'" style="">
					<ul>';
				$children = $this->conn->query("SELECT * FROM links WHERE parent=".$row['id']);
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
			$handle = fopen("./".$this->config['frontpage_menu_url'], "w");
			fwrite($handle, $menu);
			fclose($handle);
		} elseif ($this->config['frontpage_style'] == 1) //4chan style
		{
			
			$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';

			$file .= '<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
		<title>'.$this->config['sitename'].'</title>
		<link href="./styles/global.css" rel="stylesheet" type="text/css" />
		<link href="./styles/index.css" rel="stylesheet" type="text/css" />
	</head>';

			$file .= '<body>
		<div id="doc">
			<div id="hd">
				<div id="logo">
					<h1>'.$this->config['sitename'].'</h1>
				</div>
			</div>

			<div id="bd">';
			$file .= '<div class="box-outer top-box" id="boards">
					<div class="box-inner">
						<div class="boxbar">
							<h2>Boards</h2>
						</div>

						<div class="boxcontent">';
			$cats = $this->conn->query("SELECT * FROM links WHERE parent=-1;");
			while ($row = $cats->fetch_assoc())
			{
				$file .= '<div class="column">';
				$file .= '<h3 style="text-decoration: underline; display: inline;">'.$row['title'].'</h3>';
				$file .= '<ul>';
				$children = $this->conn->query("SELECT * FROM links WHERE parent=".$row['id']);
				while ($child = $children->fetch_assoc())
				{
					if (!empty($child['url_index']))
					{
						$file .= '<li>
							<a class="boardlink" href="'.$child['url_index'].'" title="'.$child['title'].'">'.$child['title'].'</a>
						</li>';
					} else {
						$file .= '<li>
							<a class="boardlink" href="'.$child['url'].'" title="'.$child['title'].'">'.$child['title'].'</a>
						</li>';
					}
				}
				$file .= '</div>';
			}

			$file .= '<br class="clear-bug" />
						</div>
					</div>
				</div>';

			$file .= '<div class="wrapper">
					<div class="left-boxes">
						<div class="box-outer left-box" id="recent-images">
							<div class="box-inner">
								<div class="boxbar">
									<h2>Recent Images</h2>
								</div>

								<div class="boxcontent">';
			$recent_images = $this->conn->query("SELECT posts.*, boards.hidden FROM posts LEFT JOIN boards ON posts.board=boards.short WHERE boards.hidden=0 AND filename<>'' AND filename<>'deleted' AND filename NOT LIKE 'embed%' AND filename NOT LIKE 'spoiler%' ORDER BY date DESC LIMIT 0, 3;");
			while ($row = $recent_images->fetch_assoc())
			{
				$postfile = $row['id'].".html#p".$row['id'];
				if (!empty($row['resto'])) { $postfile = $row['resto'].".html#p".$row['id']; }
				$file .= '<ul>
					<li>
						<a class="tooltiplink-ws boardlink" href="./'.$row['board'].'/res/'.$postfile.'" /><img alt="'.$row['orig_filename'].'" height="'.$row['t_h'].'" src="./'.$row['board'].'/src/thumb/'.$row['filename'].'" width="'.$row['t_w'].'" /></a>
					</li>
				</ul>';
			}
			$file .= '</div>
							</div>
						</div>
					</div>';

			$file .= '<div class="right-boxes">
						<div class="box-outer right-box" id="recent-threads">
							<div class="box-inner">
								<div class="boxbar">
									<h2>Latest Posts</h2>

									<div class="yui-skin-sam menubutton" id="options-container"></div>
								</div>
								<div class="boxcontent">';
			$recent_posts = $this->conn->query("SELECT posts.*, boards.hidden, boards.name AS bname FROM posts LEFT JOIN boards ON posts.board=boards.short WHERE boards.hidden=0 ORDER BY date DESC LIMIT 0, 15;");
			while ($row = $recent_posts->fetch_assoc())
			{
				$postfile = $row['id'].".html#p".$row['id'];
				if (!empty($row['resto'])) { $postfile = $row['resto'].".html#p".$row['id']; }
				$file .= '<ul>
										<li>'.$row['bname'].': <a class="tooltiplink-ws boardlink" href="./'.$row['board'].'/res/'.$postfile.'" >'.htmlspecialchars(substr($row['comment'], 0, 30)).'...</a>
										</li>
									</ul>';
			}
			$file .= '</div>
							</div>
						</div>';

			$file .= '<div class="box-outer right-box">
							<div class="box-inner">
								<div class="boxbar">
									<h2>Stats</h2>
								</div>

								<div class="boxcontent">
									<ul>
										<li>Total Posts: 1,235,062,437</li>

										<li>Current Users: 127,915</li>

										<li>Active Content: 107 GB</li>
									</ul>
								</div>
							</div>';
			$file .= '</div>
					</div>
				</div>
			</div>
			<div id="ft" class=" ">
				<br class="clear-bug">
				<div id="copyright" class=" ">- <a href="http://github.com/MitsubaBBS/Mitsuba">mitsuba</a> -</div>
			</div>
		</div>
	</body>
	</html>';
			
			$handle = fopen("./".$this->config['frontpage_url'], "w");
			fwrite($handle, $file);
			fclose($handle);
		}
	}

	function generateNews()
	{
		
		$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		$file .= '<html>
			<head>
			<title>'.$this->config['sitename'].'</title>
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
		$result = $this->conn->query("SELECT * FROM news ORDER BY date DESC;");
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
		$handle = fopen("./".$this->config['news_url'], "w");
		fwrite($handle, $file);
		fclose($handle);
	}

	function getFiles($row, $board, $return, $threadno, $embed_table, $extensions)
	{
		$file = "";
		if (!empty($row['filename']))
		{
			$files = array();
			if (substr($row['filename'], 0, 6) == "multi;")
			{
				$filenames = explode(";", $row['filename']);
				$orig_filenames = explode(";", $row['orig_filename']);
				$filesizes = explode(";", $row['filesize']);
				$imagesizes = explode(";", $row['imagesize']);
				$mimetypes = explode(";", $row['mimetype']);
				$t_ws = explode(";", $row['t_w']);
				$t_hs = explode(";", $row['t_h']);
				$num = 0;
				foreach($filenames as $filename)
				{
					$files[$num]['filename'] = $filenames[$num+1];
					$files[$num]['orig_filename'] = $orig_filenames[$num];
					$files[$num]['filesize'] = $filesizes[$num];
					$files[$num]['imagesize'] = $imagesizes[$num];
					$files[$num]['mimetype'] = $mimetypes[$num];
					$files[$num]['t_w'] = $t_ws[$num];
					$files[$num]['t_h'] = $t_hs[$num];
					$num++;
				}
			} else {
				$files[0]['filename'] = $row['filename'];
				$files[0]['orig_filename'] = $row['orig_filename'];
				$files[0]['filesize'] = $row['filesize'];
				$files[0]['imagesize'] = $row['imagesize'];
				$files[0]['mimetype'] = $row['mimetype'];
				$files[0]['t_w'] = $row['t_w'];
				$files[0]['t_h'] = $row['t_h'];
			}
			$filenum = 0;
			foreach($files as $fileinfo)
			{
				if ($fileinfo['filename'] == "deleted")
				{
					$file .= '<div class="file" id="f'.$row['id']."_".$filenum.'">';
					$file .= '<div class="fileInfo">';
					$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'">File: <b>deleted</b></span>';
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
				} elseif (substr($fileinfo['filename'], 0, 8) == "spoiler:")
				{
					$file .= '<div class="file" id="f'.$row['id']."_".$filenum.'">';
					$file .= '<div class="fileInfo">';
					if ($return == 1)
					{
						$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'">File: <a href="./'.$board.'/src/'.substr($fileinfo['filename'],8).'" target="_blank"><b>Spoiler</b></a></span>';
					
					} elseif ($threadno != 0)
					{
						$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'">File: <a href="../src/'.substr($fileinfo['filename'],8).'" target="_blank"><b>Spoiler</b></a></span>';
					} else {
						$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'">File: <a href="./src/'.substr($fileinfo['filename'],8).'" target="_blank"><b>Spoiler</b></a></span>';
					}
					$file .= '</div>';
					$filepath = "";
					$thumbpath = "";
					if ($return == 1)
					{
						$filepath = './'.$board.'/src/'.substr($fileinfo['filename'],8);
						$thumbpath = './'.$board.'/src/thumb/'.substr($fileinfo['filename'],8);
					} elseif ($threadno != 0)
					{
						$filepath = '../src/'.substr($fileinfo['filename'],8);
						$thumbpath = '../src/thumb/'.substr($fileinfo['filename'],8);
					} else {
						$filepath = './src/'.substr($fileinfo['filename'],8);
						$thumbpath = './src/thumb/'.substr($fileinfo['filename'],8);
					}

					$file .= '<a class="fileThumb" href="'.$filepath.'" target="_blank"><img src="./img/spoiler.png" alt="Spoiler image" style="width: 100px; height: 100px"/></a>';
					$file .= '</div>';
				} elseif (substr($fileinfo['filename'], 0, 6) == "embed:")
				{
					$file .= '<div class="file" id="f'.$row['id']."_".$filenum.'">';
					$file .= '<div class="fileInfo">';
					$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'">File: <b>Embed</b></span>';
					
					$file .= '</div>';
					$file .= '<a class="fileThumb">'.$mitsuba->common->getEmbed(substr($fileinfo['filename'], 6), $embed_table).'</a>';
					
					$file .= '</div>';
				} else {
					$file .= '<div class="file" id="f'.$row['id']."_".$filenum.'">';
					$file .= '<div class="fileInfo">';
					$imgsize = "";
					if ((isset($extensions[$fileinfo['mimetype']]['image'])) && ($extensions[$fileinfo['mimetype']]['image']==1))
					{
						$imgsize = ', '.$fileinfo['imagesize'];
					}
					if ($return == 1)
					{
						$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'"><a href="./'.$board.'/src/'.$fileinfo['filename'].'" target="_blank">File</a>: ('.$fileinfo['filesize'].$imgsize.', <span title="'.$fileinfo['orig_filename'].'">'.$fileinfo['orig_filename'].'</span>)</span>';
					} elseif ($threadno != 0)
					{
						$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'"><a href="../src/'.$fileinfo['filename'].'" target="_blank">File</a>: ('.$fileinfo['filesize'].$imgsize.', <span title="'.$fileinfo['orig_filename'].'">'.$fileinfo['orig_filename'].'</span>)</span>';
					} else {
						$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'"><a href="./src/'.$fileinfo['filename'].'" target="_blank">File</a>: ('.$fileinfo['filesize'].$imgsize.', <span title="'.$fileinfo['orig_filename'].'">'.$fileinfo['orig_filename'].'</span>)</span>';
					}
					$file .= '</div>';
					$filepath = "";
					$thumbpath = "";
					if ($return == 1)
					{
						$filepath = './'.$board.'/src/'.$fileinfo['filename'];
						$thumbpath = './'.$board.'/src/thumb/'.$fileinfo['filename'];
					} elseif ($threadno != 0)
					{
						$filepath = '../src/'.$fileinfo['filename'];
						$thumbpath = '../src/thumb/'.$fileinfo['filename'];
					} else {
						$filepath = './src/'.$fileinfo['filename'];
						$thumbpath = './src/thumb/'.$fileinfo['filename'];
					}

					if (isset($extensions[$fileinfo['mimetype']]['image']))
					{
						if ($extensions[$fileinfo['mimetype']]['image']==1)
						{
							$file .= '<a class="fileThumb" href="'.$filepath.'" target="_blank"><img src="'.$thumbpath.'" alt="Thumbnail" style="width: '.$fileinfo['t_w'].'px; height: '.$fileinfo['t_h'].'px"/></a>';
						} elseif ($extensions[$fileinfo['mimetype']]['image']!=0)
						{
							$file .= sprintf($extensions[$fileinfo['mimetype']]['image'], $filepath);
						}
					}
					$file .= '</div>';
				}
				$filenum++;
			}
		}
		return $file;
	}

	function serializePost($row, $boarddata, $parser)
	{
		$post = array();
		$post['no'] = $row['id'];
		$post['resto'] = $row['resto'];
		if ($row['sticky'] == 1)
		{
			$post['sticky'] = 1;
		}
		if ($row['locked'] == 1)
		{
			$post['closed'] = 1;
		}
		$post['now'] = date("d/m/Y(D)H:i:s", $row['date']);
		$post['time'] = $row['date'];
		$post['name'] = $row['name'];
		if (!empty($row['trip']))
		{
			$post['trip'] = "!".$row['trip'];
		}
		if ($row['capcode'] == 4)
		{
			$post['id'] = $row['cc_text'];
		} elseif ($row['capcode'] == 3)
		{
			$post['id'] = "Faggot";
		} elseif ($row['capcode'] == 2)
		{
			$post['id'] = "Admin";
		} elseif ($row['capcode'] == 1) {
			$post['id'] = "Mod";
		} else {
			if ((!empty($row['poster_id'])) && ($boarddata['ids']==1))
			{
				$post['id'] = $row['poster_id'];
			}
		}
		if ($row['capcode'] == 4)
		{
			$post['capcode'] = $row['cc_text'];
		} elseif ($row['capcode'] == 3)
		{
			$post['capcode'] = "faggot";
		} elseif ($row['capcode'] == 2)
		{
			$post['capcode'] = "admin";
		} elseif ($row['capcode'] == 1) {
			$post['capcode'] = "mod";
		}
		if (!empty($row['email']))
		{
			$post['email'] = $row['email'];
		}
		if (!empty($row['subject']))
		{
			$post['sub'] = $row['subject'];
		}
		if ($row['raw'] != 1)
		{
			if ($row['raw'] == 2)
			{
				$post['com'] = $this->processComment($boarddata['short'], $row['comment'], $parser, 2, 0, $boarddata['bbcode'], $row['id'], $row['resto']);
			} else {
				$post['com'] = $this->processComment($boarddata['short'], $row['comment'], $parser, 2, 1, $boarddata['bbcode'], $row['id'], $row['resto']);
			}
		} else {
			$post['com'] = $row['comment'];
		}
		if (!empty($row['filename']))
		{
			if (substr($row['filename'], 0, 6) == "multi;")
			{
				$files = array();
				$filenames = explode(";", $row['filename']);
				$orig_filenames = explode(";", $row['orig_filename']);
				$filesizes = explode(";", $row['orig_filesize']);
				$imagesizes = explode(";", $row['imagesize']);
				$mimetypes = explode(";", $row['mimetype']);
				$t_ws = explode(";", $row['t_w']);
				$t_hs = explode(";", $row['t_h']);
				$num = 0;
				foreach($filenames as $filename)
				{
					$files[$num]['filename'] = $filenames[$num+1];
					$files[$num]['orig_filename'] = $orig_filenames[$num];
					$files[$num]['filesize'] = $filesizes[$num];
					$files[$num]['imagesize'] = $imagesizes[$num];
					$files[$num]['mimetype'] = $mimetypes[$num];
					$files[$num]['t_w'] = $t_ws[$num];
					$files[$num]['t_h'] = $t_hs[$num];
					$num++;
				}
				$filenum = 0;
				foreach($files as $fileinfo)
				{
					if ($row['filename'] != "deleted")
					{
						if (substr($row['filename'], 0, 8) == "spoiler:")
						{
							$pinfo = pathinfo(substr($row['filename'], 8));
							$pinfoo = pathinfo($row['orig_filename']);
							$file = array();
							$file['tim'] = $pinfo['filename'];
							$file['filename'] = $pinfoo['filename'];
							$file['ext'] = ".".$pinfo['extension'];
							$file['fsize'] = $row['orig_filesize'];
							$file['mimetype'] = $row['mimetype'];
							$sze = explode("x", $row['imagesize']);
							$file['w'] = $sze[0];
							$file['h'] = $sze[1];
							$file['t_w'] = $row['t_w'];
							$file['t_h'] = $row['t_h'];
							$file['spoiler'] = 1;
							$post['files'][] = $file;
						} elseif (substr($row['filename'], 0, 6) == "embed:")
						{
							$file['embed'] = 1;
							$file['embed_url'] = substr($row['filename'], 6);
						} else {
							$pinfo = pathinfo($row['filename']);
							$pinfoo = pathinfo($row['orig_filename']);
							$file = array();
							$file['tim'] = $pinfo['filename'];
							$file['filename'] = $pinfoo['filename'];
							$file['ext'] = ".".$pinfo['extension'];
							$file['fsize'] = $row['orig_filesize'];
							$file['mimetype'] = $row['mimetype'];
							$sze = explode("x", $row['imagesize']);
							$file['w'] = $sze[0];
							$file['h'] = $sze[1];
							$file['t_w'] = $row['t_w'];
							$file['t_h'] = $row['t_h'];
							$post['files'][] = $file;
						}
					} else {
						$file = array();
						$file['filedeleted'] = 1;
						$post['files'][] = $file;
					}
				}
			} else {
				if ($row['filename'] != "deleted")
				{
					if (substr($row['filename'], 0, 8) == "spoiler:")
					{
						$pinfo = pathinfo(substr($row['filename'], 8));
						$pinfoo = pathinfo($row['orig_filename']);
						$file = array();
						$file['tim'] = $pinfo['filename'];
						$file['filename'] = $pinfoo['filename'];
						$file['ext'] = ".".$pinfo['extension'];
						$file['fsize'] = $row['orig_filesize'];
						$file['mimetype'] = $row['mimetype'];
						$sze = explode("x", $row['imagesize']);
						$file['w'] = $sze[0];
						$file['h'] = $sze[1];
						$file['t_w'] = $row['t_w'];
						$file['t_h'] = $row['t_h'];
						$file['spoiler'] = 1;
						$post['files'][] = $file;
					} elseif (substr($row['filename'], 0, 6) == "embed:")
					{
						$file['embed'] = 1;
						$file['embed_url'] = substr($row['filename'], 6);
					} else {
						$pinfo = pathinfo($row['filename']);
						$pinfoo = pathinfo($row['orig_filename']);
						$file = array();
						$file['tim'] = $pinfo['filename'];
						$file['filename'] = $pinfoo['filename'];
						$file['ext'] = ".".$pinfo['extension'];
						$file['fsize'] = $row['orig_filesize'];
						$file['mimetype'] = $row['mimetype'];
						$sze = explode("x", $row['imagesize']);
						$file['w'] = $sze[0];
						$file['h'] = $sze[1];
						$file['t_w'] = $row['t_w'];
						$file['t_h'] = $row['t_h'];
						$post['files'][] = $file;
					}
				} else {
					$post['filedeleted'] = 1;
				}
				
			}
		}
	return $post;
	}

	function rebuildBoardCache($board)
	{
		$this->updateThreads($board);
		$this->generateView($board);
		$this->regenIDs($board);
	}

	function regenIDs($board)
	{
		if ($this->mitsuba->common->isBoard($board))
		{
			$bdata = $this->mitsuba->common->getBoardData($board);
			if ($bdata['ids'] == 1)
			{
				$result = $this->conn->query("SELECT * FROM posts WHERE board='".$board."'");
				while ($row = $result->fetch_assoc())
				{
					$poster_id = "";
					if (empty($row['poster_id']))
					{
						if ($row['resto'] != 0)
						{
							$poster_id = $this->mitsuba->common->mkid($row['ip'], $row['resto'], $board);
						} else {
							$poster_id = $this->mitsuba->common->mkid($row['ip'], $row['id'], $board);
						}
						$this->conn->query("UPDATE posts SET poster_id='".$poster_id."' WHERE id=".$row['id']." AND board='".$board."'");
					}
				}
			}
		}
	}

	function generatePost($board, $id)
	{
		if ((empty($id)) || (!is_numeric($id)))
		{
			return -15;
		}
		if ((empty($id)) || (!$this->mitsuba->common->isBoard($board)))
		{
			return -16;
		}
		$result = $this->conn->query("SELECT * FROM posts WHERE id=".$id." AND board='".$board."'");
		if ($result->num_rows == 1)
		{
			$post = $result->fetch_assoc();
			if ($post['resto'] == 0)
			{
				$this->generateView($board, $post['id']);
			} else {
				$this->generateView($board, $post['resto']);
			}
			$this->generateView($board);
		}
	}
}
?>