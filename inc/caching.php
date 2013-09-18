<?php
namespace Mitsuba;
class Caching
{
	private $conn;
	private $config;
	private $mitsuba;

	function __construct($connection, &$mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
		$this->config = $this->mitsuba->config;
	}

	function generateBoardLinks($location = "board")
	{
		$links = '<div id="boardLinks">';
		$links .= $this->generateLinks(-1, $location);
		$links .= '</div>';
		return $links;
	}

	function rebuildBoardLinks()
	{
		$this->mitsuba->updateConfigValue("boardLinks", $this->generateBoardLinks("index"));
		$this->mitsuba->updateConfigValue("boardLinks_thread", $this->generateBoardLinks("thread"));
		$this->mitsuba->updateConfigValue("boardLinks_board", $this->generateBoardLinks("board"));
	}

	function generateLinks($id, $location = "board")
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
				if ($row['relative'] == 1)
				{
					$links .= '<a href="'.$this->mitsuba->getPath($row['url'], $location, 1).'" title="'.$row['title'].'">'.$row['short'].'</a>';
				} elseif ($row['relative'] == 2)
				{
					$links .= '<a href="'.$this->mitsuba->getPath("./".$row['url']."/", $location, 1).'" title="'.$row['title'].'">'.$row['short'].'</a>';
				} else {
					$links .= '<a href="'.$row['url'].'" title="'.$row['title'].'">'.$row['short'].'</a>';
				}
				
			}
			$l2 = $this->generateLinks($row['id'], $location);
			if (!empty($l2))
			{
				$links .= "[".$l2."] ";
			}
			$no = 1;
		}
		return $links;
	}

	function getBoardLinks($location = "board")
	{
		if ($location == "board")
		{
			return $this->config['boardLinks_board'];
		} elseif ($location == "thread") {
			return $this->config['boardLinks_thread'];
		} elseif ($location == "index") {
			return $this->config['boardLinks'];
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
				$new .= "<br />";
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
					return "<a class='postlink' href='".$match[0]."'>".$match[0]."</a>";
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
		$new = preg_replace_callback("/<\+(.*?)\+>/",
			function ($match)
			{
				return htmlspecialchars_decode($match[1]);
			},
			$new);
		$new = preg_replace_callback("/&lt;\+(.*?)\+&gt;/",
			function ($match)
			{
				return htmlspecialchars_decode($match[1]);
			},
			$new);
		$new = str_replace('&lt;\+', "&lt;+", $new);
		$new = str_replace('+\&gt;', "+&gt;", $new);
		$new = str_replace('<\+', "<+", $new);
		$new = str_replace('+\>;', "+>", $new);
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
			$result = $this->conn->query("SELECT * FROM posts WHERE id='".substr($link, 2)."' AND board='".$board."' AND deleted=0;");
			if ($result->num_rows == 1)
			{
				
				$row = $result->fetch_assoc();
				if ($row['resto'] != 0)
				{
					if ($thread == 1)
					{
						$return = '<a href="../../'.$board.'/res/'.$row['resto'].'.html#p'.$row['id'].'" class="quotelink">'.$link.'</a>';
					} elseif ($thread == 0) {
						$return = '<a href="../'.$board.'/res/'.$row['resto'].'.html#p'.$row['id'].'" class="quotelink">'.$link.'</a>';
					} else {
						$return = '<a href="?/board&b='.$board.'&t='.$row['resto'].'#p'.$row['id'].'" class="quotelink">'.$link.'</a>';
					}
				} else {
					if ($thread == 1)
					{
						$return = '<a href="../../'.$board.'/res/'.$row['id'].'.html#p'.$row['id'].'" class="quotelink">'.$link.'</a>';
					} elseif ($thread == 0) {
						$return = '<a href="../'.$board.'/res/'.$row['id'].'.html#p'.$row['id'].'" class="quotelink">'.$link.'</a>';
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
					$result = $this->conn->query("SELECT * FROM posts WHERE id='".$parts[2]."' AND board='".$parts[1]."' AND deleted=0;");
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

	function getHtmlDefinition()
	{
		$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">';
		return $file;
	}

	function getAds($board, $position)
	{
		$ads = $this->conn->query("SELECT * FROM ads WHERE board=('".$this->conn->real_escape_string($board)."' OR '*' OR '') AND position='".$position."' AND `show`=1;");
		$text = "";
		while ($ad = $ads->fetch_assoc())
		{
			$text .= $ad['text'];
		}
		return $text;
	}

	function getBoardHeader($board, $boarddata, $location, $catalog = 0)
	{
		$file = $this->getHtmlDefinition();
		$file .= "<head><title>/".$boarddata['short']."/ - ".$boarddata['name']."</title>";
		$first_default = 1;
		$styles = $this->conn->query("SELECT * FROM styles ORDER BY `default` DESC");
		while ($row = $styles->fetch_assoc())
		{
			if ($first_default == 1)
			{
				$file .= '<link rel="stylesheet" id="switch" href="'.$this->mitsuba->getPath($row['path'], $location, $row['relative']).'">';
				$first_default = 0;
			}
			$file .= '<link rel="alternate stylesheet" style="text/css" href="'.$this->mitsuba->getPath($row['path'], $location, $row['relative']).'" title="'.$row['name'].'">';
		}
		if ($catalog == 1)
		{
			$file .= '<link rel="stylesheet" href="'.$this->mitsuba->getPath("./styles/catalog.css", $location, 1).'">';
		}
		$file .= "<script type='text/javascript' src='".$this->mitsuba->getPath("./js/jquery.js", $location, 1)."'></script>";
		$file .= "<script type='text/javascript' src='".$this->mitsuba->getPath("./js/jquery.cookie.js", $location, 1)."'></script>";
		if ($catalog == 1)
		{
			$file .= "<script type='text/javascript' src='".$this->mitsuba->getPath("./js/catalog.js", $location, 1)."'></script>";
		} else {
			$file .= "<script type='text/javascript' src='".$this->mitsuba->getPath("./js/style.js", $location, 1)."'></script>";
			$file .= "<script type='text/javascript' src='".$this->mitsuba->getPath("./js/common.js", $location, 1)."'></script>";
			if ($location == "index")
			{
				$file .= "<script type='text/javascript' src='".$this->mitsuba->getPath("./js/admin.js", $location, 1)."'></script>";
			}
		}
		$file .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$file .= '<meta property="og:boardname" content="'.$boarddata['short'].'" />';
		$file .= $this->getAds($boarddata['short'], "head");
		if ($location == "index")
		{
			$file .= "</head><body class='modPanel'>";
		} else {
			$file .= "</head><body>";
		}
		if ($this->config['enable_meny']==1)
		{
			$file .= '<div class="meny">';
			$file .= $this->getMenu($location);
			$file .= '</div>';
			$file .= '<div class="meny-arrow"></div>';
			$file .= '<div class="contents">';
		}
		$file .= $this->getBoardLinks($location);
		$file .= '<div class="boardBanner">';
		$imagesDir = './rnd/';
		$images = glob($imagesDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
		$imagesDirBoard = './rnd/'.$board.'/';
		if (is_dir($imagesDirBoard))
		{
			$images = array_merge($images, glob($imagesDirBoard . '*.{jpg,jpeg,png,gif}', GLOB_BRACE));
		}
		$randomImage = $images[array_rand($images)]; 
		$file .= '<img class="title" src="'.$this->mitsuba->getPath($randomImage, $location, 1).'" alt="Mitsuba" />';
		$file .= '<div class="boardTitle">/'.$boarddata['short'].'/ - '.$boarddata['name'].'</div>';
		$file .= '<div class="boardSubtitle">'.$boarddata['des'].'</div>';
		$file .= '</div>';
		$file .= '<br />';
		$file .= '<hr />';
		$file .= $this->getAds($boarddata['short'], "aboveform");
		return $file;
	}

	function generateView($board, $threadno = 0, $return = 0, $mode = 0, $adm_type = 0)
	{
		global $lang;
		$overboard = 0;
		$overboard_boards = array();
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
		if ($boarddata['type']=="overboard")
		{
			$overboard = 1;
			$overboard_boards = explode(",", $boarddata['overboard_boards']);
		}

		$wfresult = $this->conn->query("SELECT * FROM wordfilter WHERE active=1");
		$replace_array = array();
		if ($overboard==0)
		{
			$replace_array[$board] = array();
		} else {
			foreach ($overboard_boards as $short) {
				$replace_array[$short] = array();
			}
		}
		while ($row = $wfresult->fetch_assoc())
		{
			if ($row['boards'] != "%")
			{
				$boards = explode(",", $row['boards']);
				if ($overboard==0)
				{
					if (in_array($board, $boards))
					{
						$replace_array[$board][$row['search']] = $row['replace'];
					}
				} else {
					foreach ($overboard_boards as $short) {
						if (in_array($short, $boards))
						{
							$replace_array[$short][$row['search']] = $row['replace'];
						}
					}
				}
			} else {
				if ($overboard==0)
				{
					$replace_array[$board][$row['search']] = $row['replace'];
				} else {
					foreach ($overboard_boards as $short) {
						$replace_array[$short][$row['search']] = $row['replace'];
					}
				}
			}
		}
		$max_pages = $boarddata['pages'];
		$all_pages = $max_pages+1;
		$pages = $max_pages+1;
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
		
		require_once( "libs/jbbcode/Parser.php" );
		$parser = new \JBBCode\Parser();
		if (($boarddata['bbcode']==1) || ($overboard == 1))
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
			$cnt = $this->conn->query("SELECT id FROM posts WHERE resto=0 AND board='".$board."' AND deleted=0");
			$all_pages = ceil(($cnt->num_rows)/10);
			if ($all_pages == 0) { $all_pages = 1; }
			if (($max_pages+1) < $all_pages)
			{
				$all_pages = $max_pages;
			}
			if ($return == 0)
			{
				$pages = $all_pages;
			}
		}
		$location = "";
		if ($return == 1)
		{
			$location = "index";
		} elseif ($threadno != 0)
		{
			$location = "thread";
		} else {
			$location = "board";
		}
		$header = $this->getBoardHeader($board, $boarddata, $location);
		$rules_ads = $this->getAds($boarddata['short'], "rules");
		$underform_ads = $this->getAds($boarddata['short'], "underform");
		$footer_ads = $this->getAds($boarddata['short'], "footer");
		$bottom_ads = $this->getAds($boarddata['short'], "bottom");
		$meny_config = "";
		$locked = 0;
		$postform = "";
		$global = "";
		$sql_boardlist = "";
		if ($this->config['enable_meny']==1)
		{
			$meny_config = $this->getMenyConfig($location);
		}
			
			
		if (($return == 1) && ($adm_type >= 1))
		{
			
		} elseif ($threadno != 0)
		{
			$result = $this->conn->query("SELECT * FROM posts WHERE id=".$threadno." AND board='".$board."' AND deleted=0;");
			if ($result->num_rows == 1)
			{
				$tdata = $result->fetch_assoc();
				$locked = $tdata['locked'];
			} else {
				return;
			}
		}

		if (($locked == 0) && ($overboard == 0) && ($boarddata['allow_replies']==1))
		{
			if ($threadno != 0)
			{
				$postform .= '<div class="postingMode">'.$lang['img/posting_mode'].'</div>';
				if ($return == 1)
				{
					$postform .= '<div class="navLinks">[<a href="?/board&b='.$board.'" accesskey="a">'.$lang['img/return_c'].'</a>] [<a href="#bottom">'.$lang['img/bottom'].'</a>]</div>';
					$postform .= '<form id="postform" action="./imgboard.php?mod=1" method="post" enctype="multipart/form-data">';
				} else {
					$postform .= '<div class="navLinks">[<a href=".././" accesskey="a">'.$lang['img/return_c'].'</a>] [<a href="#bottom">'.$lang['img/bottom'].'</a>]';
					if ($boarddata['catalog']==1) { $postform .= ' [<a href="../catalog.html">Catalog</a>]'; }
					$postform .= '</div>';
					$postform .= '<form id="postform" action="../../imgboard.php" method="post" enctype="multipart/form-data">';
				}
			} else {
				if ($return == 1)
				{
					$postform .= '<form id="postform" action="./imgboard.php?mod=1" method="post" enctype="multipart/form-data">';
				} else {
					$postform .= '<form id="postform" action="../imgboard.php" method="post" enctype="multipart/form-data">';
				}
			}
			if ($adm_type <= 0)
			{
				$postform .= '<input type="hidden" name="MAX_FILE_SIZE" value="'.$boarddata['filesize'].'" />';
			}
			$postform .= '<input type="hidden" name="mode" value="regist" />
				<table class="postForm" id="postForm">
				<tbody>';
			if (($boarddata['noname'] == 0) || ($adm_type >= 2))
			{
				$postform .= '<tr>
					<td>'.$lang['img/name'].'</td>
					<td><input class="board-input" name="name" type="text" /></td>
					</tr>';
			}
			if (($boarddata['ids'] == 1) && ($adm_type >= 2))
			{
				$postform .= '<tr>
					<td>'.$lang['img/fake_id'].'</td>
					<td><input class="board-input" name="fake_id" type="text" /></td>
					</tr>';
			}
			$postform .= '<tr>
				<td>'.$lang['img/email'].'</td>
				<td><input class="board-input" name="email" type="text" /></td>
				</tr>
				<tr>
				<td>'.$lang['img/subject'].'</td>
				<td><input class="board-input" name="sub" type="text" />';
			$postform .= '<input type="hidden" name="board" value="'.$board.'" />';
			if ($threadno != 0)
			{
				$postform .= '<input type="hidden" name="resto" value="'.$threadno.'" />';
			}
			$postform .= '<input id="submit" type="submit" value="'.$lang['img/submit'].'" /></td>
				</tr>
				<tr>
				<td>'.$lang['img/comment'].'</td>
				<td><textarea name="com" cols="35" rows="4"></textarea></td>
				</tr>';
			$captchaUrl = "";
			if ($boarddata['captcha']==1)
			{
				$captchaUrl = $this->mitsuba->getPath("./captcha.php", $location, 1);
				$postform .= '<tr id="captcha">
					<td>'.$lang['img/captcha'].'</td>
					<td>
					<noscript><iframe src="'.$captchaUrl.'" style="overflow: hidden; width: 300px; height: 70px; border: 1px solid #000000; display: block;"></iframe></noscript>
					<input id="captchaField" name="captcha" style="width: 300px;" type="text" placeholder="Type the word from the image"/>
					</td>
					</tr>';
			}
			if ((($boarddata['type']=="imageboard") || ($boarddata['type']=="fileboard")) && ($boarddata['file_replies']==1))
			{
				$postform .= '<tr>
					<td>'.$lang['img/file'].'</td>
					<td id="embed"><input id="postFile" name="upfile" type="file" />';
				$postform .= '</td>
					</tr>';
				$fspecials = "";
				if ($boarddata['spoilers'] == 1)
				{
					$fspecials .= '<label><input id="spoiler" type="checkbox" name="spoiler" value="1">'.$lang['img/spoiler'].'</label>';
				}
				if ($boarddata['nofile'] == 1)
				{
					$fspecials .= '<label><input id="nofile" type="checkbox" name="nofile" value="1">'.$lang['img/mod_nofile'].'</label>';
				}
				if (!empty($fspecials))
				{
					$postform .= '<tr>
						<td></td>
						<td>'.$fspecials.'</td>
						</tr>';
				}
				if ($boarddata['embeds'] == 1)
				{
					$postform .= '<tr>
						<td>'.$lang['img/embed'].'</td>
						<td><input type="text" name="embed"/></td>
						</tr>';
				}
			}
			if ($boarddata['type']=="linkboard")
			{
				$postform .= '<tr>
					<td>'.$lang['img/url'].'</td>
					<td><input type="text" name="url"/></td>
					</tr>';
			}
			$postform .= '<tr>
				<td>'.$lang['img/password'].'</td>
				<td><input id="postPassword" name="pwd" type="password" maxlength="8" /> <span class="password">'.$lang['img/password_used'].'</span></td>
				</tr>';
			if ($adm_type >= 2)
			{
				$postform .='<tr>
					<td>'.$lang['img/mod'].'</td>
					<td><input type="checkbox" name="raw" value=1 />'.$lang['img/mod_raw'].'<input type="checkbox" name="sticky" value=1 />'.$lang['img/mod_sticky'].'<input type="checkbox" name="lock" value=1 />'.$lang['img/mod_lock'].'<br />';
				$postform .= '<input type="checkbox" name="nolimit" value=1 selected/>'.$lang['img/mod_nolimit'].'<input type="checkbox" name="ignoresizelimit" value=1 />'.$lang['img/mod_nosizelimit'].'<input type="checkbox" name="nofile" value=1 />'.$lang['img/mod_nofile'].'</td>';
				$postform .='<tr>
					<td>'.$lang['img/mod_capcode'].'</td>
					<td id="capcode_td"><input type="radio" name="capcode" value=0 checked />'.$lang['img/mod_nocapcode'].'<input type="radio" name="capcode" value=1 />'.$lang['img/mod_capcode'];
				if ($adm_type == 3)
				{	
					$postform .= '<input type="radio" name="capcode" value=2 id="custom_cc" />'.$lang['img/mod_customcapcode'];
					$postform .= '<div style="display: none;" id="cc_fields" value="#FF0000">'.$lang['img/text'].': <input type="text" name="cc_text" /><br />
					'.$lang['img/color'].': <input type="text" name="cc_color" /></div>';
					$postform .= "<script type=\"text/javascript\">
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
				$postform .= "</td></tr>";
			}
			$unique = $this->conn->query("SELECT DISTINCT ip FROM posts WHERE board='".$boarddata['short']."';")->num_rows;
			$postform .= '<tr class="rules">
				<td colspan="2">
				<ul class="rules">
				<li>'.$lang['img/supported_types'].$boarddata['extensions'].'</li>
				<li>'.sprintf($lang['img/max_filesize'], $this->mitsuba->common->human_filesize($boarddata['filesize'])).'</li>
				<li>'.$lang['img/thumbnail'].'</li>
				<li>'.sprintf($lang['img/unique_user_posts'], $unique).'</li>
				'.$rules_ads.'</ul>
				</td>
				</tr>
				</tbody>
				</table>
				</form>';
		if ($boarddata['captcha']==1)
		{
			$postform .= '<script type="text/javascript">
				$("#captchaField").before("<div style=\'width: 300px; height: 70px; background-color: white;\'><a href=\'#\' id=\'captchaClickHere\' style=\'vertical-align: middle; align: center;\'>'.$lang['img/click_here'].'</a></div>");
				$("#captchaClickHere").click(function (event) {
					event.preventDefault();
					d = new Date();
					$(this).parent().after("<a style=\'display: block; width: 300px; height: 70px; border: 1px solid #000000;\' href=\'#\' id=\'reloadCaptcha\'><img id=\'captchaImage\' src=\''.$captchaUrl.'?t="+d.getTime()+"\' /></a>");
					$("#reloadCaptcha").click(function (ev) {
						ev.preventDefault();
						d = new Date();
						$("#captchaImage").attr("src", "'.$captchaUrl.'?t="+d.getTime());
					});
					$(this).parent().hide();
				});
				</script>';
		}
		} elseif ($overboard == 1) {
			//TODO: Overboard stuff
		} else {
			$postform .= "<div class='closed'><h1>".$lang['img/locked']."</h1></div>";
		}
		$postform .= $underform_ads."<hr />";
		if (!empty($this->config['global_message']))
		{
			$global = '<div class="globalMessage" id="globalMessage">';
			$global .= $this->config['global_message'];
			$global .= '</div>';
		}
		if ($overboard == 1)
		{
				$sql_boardlist = "(";
				$first = 1;
				foreach ($overboard_boards as $short) {
					if ($first == 1)
					{
						$sql_boardlist .= "'".$short."'";
						$first = 0;
					} else {
						$sql_boardlist .= " OR '".$short."'";
					}
					$sql_boardlist = ")";
				}
		}
		for ($pg = $page; $pg <= $pages; $pg++)
		{
			$file = $header;
			$file .= $postform;
			$file .= $global;
			$file .= '<hr />';
			if ($return == 1)
			{
				$file .= '<form id="delform" action="./imgboard.php" method="post"><div class="board">';
			} elseif ($threadno != 0)
			{
				$file .= '<form id="delform" action="../../imgboard.php" method="post"><div class="board">';
			} else {
				$file .= '<form id="delform" action="../imgboard.php" method="post"><div class="board">';
			}
			if ($overboard == 1)
			{
				$result = $this->conn->query("SELECT * FROM posts WHERE resto=0 AND board='".$sql_boardlist."' AND deleted=0 ORDER BY lastbumped DESC LIMIT ".($pg*10).",10");
			} elseif ($threadno != 0) {
				$result = $this->conn->query("SELECT * FROM posts WHERE id=".$threadno." AND board='".$board."' AND deleted=0;");
			} else {
				$result = $this->conn->query("SELECT * FROM posts WHERE resto=0 AND board='".$board."' AND deleted=0 ORDER BY sticky DESC, lastbumped DESC LIMIT ".($pg*10).",10");
			}
			if ($threadno == 0)
			{
				if ($boarddata['type']=="fileboard")
				{
					$file .= '<table class="fileListing">
							<thead>
							<tr>
								<td class="postblock">
									No.
								</td>
								<td class="postblock">
									Name
								</td>
								<td class="postblock">
									File
								</td>
								<td class="postblock">
									Size
								</td>
								<td class="postblock">
									Subject
								</td>
								<td class="postblock">
									Date
								</td>
								<td class="postblock">
								</td>
							</tr>
							</thead>
							<tbody>';
				} elseif ($boarddata['type']=="linkboard")
				{
					$file .= '<table class="linkListing">
							<thead>
							<tr>
								<td class="postblock">
									No.
								</td>
								<td class="postblock">
									Name
								</td>
								<td class="postblock">
									Link
								</td>
								<td class="postblock">
									Subject
								</td>
								<td class="postblock">
									Date
								</td>
								<td class="postblock">
								</td>
							</tr>
							</thead>
							<tbody>';

				}
			}

			while ($row = $result->fetch_assoc())
			{
				if (($threadno == 0) && (($boarddata['type']=="linkboard") || ($boarddata['type']=="fileboard")))
				{
					$file .= '<tr>';
					$file .= "<td>".$row['id']."</td>";
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
					if ((!empty($row['poster_id'])) && ($boarddata['ids']==1) && (empty($row['capcode_text'])))
					{
						$poster_id = '<span class="posteruid">(ID: '.$row['poster_id'].')</span>';
					}
					$c_image = "";
					if (!empty($row['capcode_icon']))
					{
						if (substr($row['capcode_icon'], 0, 1)==".")
						{
							$c_image = ' <img src="'.$this->mitsuba->getPath($row['capcode_icon'], $location, 1).'" alt="Capcode" style="margin-bottom: -3px;" />';
						} else {
							$c_image = ' <img src="'.$row['capcode_icon'].'" alt="Capcode" style="margin-bottom: -3px;" />';
						}
					}
					$email_a = "";
					$email_b = "";
					if (!empty($row['email'])) {
						$email_a = '<a href="mailto:'.$row['email'].'" class="useremail">';
						$email_b = '</a>';
					}
					$file .= "<td>";
					if (!empty($row['capcode_text']))
					{
						$file .= $email_a.'<span class="name"><span style="'.$row['capcode_style'].'">'.$row['name'].'</span></span>'.$email_b.$trip.' <span class="commentpostername"><span style="'.$row['capcode_style'].'">## '.$row['capcode_text'].'</span>'.$c_image.'</span>';
					} else {
						$file .= $email_a.'<span class="name">'.$row['name'].'</span>'.$email_b.$trip.' '.$poster_id;
					}
					$file .= "</td>";
					if (empty($row['filename']))
					{
						if ($boarddata['type']=="linkboard")
						{
							$file .= '<td></td>';
						} else {
							$file .= '<td></td><td></td>';
						}
					} elseif ($row['filename']=="deleted")
					{
						if ($boarddata['type']=="linkboard")
						{
							$file .= '<td>Link deleted</td>';
						} else {
							$file .= '<td>File deleted</td><td></td>';
						}
					} else {
						if ($boarddata['type']=="linkboard")
						{
							$file .= '<td>[<a href="'.substr($row['filename'], 0, 4).'">'.htmlspecialchars($row['orig_filename']).'</a>]</td>';
						} else {
							$file .= '<td>';
							if ($return == 1)
							{
								$file .= '[<a href="./'.$board.'/src/'.$row['filename'].'" target="_blank">'.htmlspecialchars($row['orig_filename']).'</a>]';
							} elseif ($threadno != 0)
							{
								$file .= '[<a href="../../'.$board.'/src/'.$row['filename'].'" target="_blank">'.htmlspecialchars($row['orig_filename']).'</a>]';
							} else {
								$file .= '[<a href="../'.$board.'/src/'.$row['filename'].'" target="_blank">'.htmlspecialchars($row['orig_filename']).'</a>]';
							}
							$file .= '</td>';
							$file .= '<td>'.$row['filesize'].'</td>';
						}
					}
					$file .= '<td><span class="subject">'.htmlspecialchars($row['subject']).'</span></td>';
					$file .= '<td>'.date("d/m/Y(D)H:i:s", $row['date']).'</td>';
					$file .= '<td>[<a href="../'.$row['board'].'/res/'.$row['id'].'.html" class="replylink">'.$lang['img/reply'].'</a>]</td>';
					$file .= '</tr>';
					
				} else {
					if ($overboard == 1)
					{
						$file .= "<h2><a href='../".$row['board']."/'>/".$row['board']."/</a></h2>";
					}
					$file .= $this->getThread($row['board'], $threadno, $return, $adm_type, $parser, $boarddata, $replace_array[$row['board']], $embed_table, $row, $extensions);
				}
			
			}
			if (($boarddata['type']=="linkboard") || ($boarddata['type']=="fileboard"))
			{
				$file .= '</tbody></table>';
			}
			$file .= "</div>";
			if ($threadno != 0)
			{
				if ($return == 1)
				{
					$file .= '<div class="navLinks">[<a href="?/board&b='.$board.'" accesskey="a">'.$lang['img/return_c'].'</a>] [<a href="#top">'.$lang['img/top'].'</a>]</div>';
				} else {
					$file .= '<div class="navLinks">[<a href=".././" accesskey="a">'.$lang['img/return_c'].'</a>] [<a href="#top">'.$lang['img/top'].'</a>]';
					if ($boarddata['catalog']==1) { $file .= ' [<a href="../catalog.html">Catalog</a>]'; }
					$file .= '</div>';
				}
			}
			$file .= '<div class="deleteform">
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
			$file .= $footer_ads;
			if (($boarddata['type']=="imageboard") || ($boarddata['type']=="textboard") || ($boarddata['type']=="overboard"))
			{
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
						if ($i > $all_pages)
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
					if ($page != $all_pages)
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
							if ($i == 0)
							{
								$file .= "[<a href='./index.html'><strong>".$i."</strong></a>] ";	
							} else {
								$file .= "[<a href='./".$i.".html'><strong>".$i."</strong></a>] ";	
							}
						} else {
							if ($i > $pages)
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
					if ($pg != $all_pages)
					{
						$file .= '<form action="./'.($pg+1).'.html" onsubmit="location=this.action; return false;"><input type="submit" value="'.$lang['img/next'].'" /></form>';
					} else {
						$file .= '<span>'.$lang['img/next'].'</span>';
					}
					$file .= '</div>';
					if ($boarddata['catalog']==1)
					{
						$file .= '<div class="pages cataloglink"><a href="./catalog.html">Catalog</a></div>';
					}
					$file .= '</div>';
				}
			}
			$file .= '<div style="text-align: center; font-size: x-small!important; padding-bottom: 4px; padding-top: 10px; color: #333;"><span class="absBotDisclaimer">- <a href="http://github.com/MitsubaBBS/Mitsuba" target="_top" rel="nofollow">mitsuba</a> -</span></div>';
			$file .= '<div id="bottom"></div>';
			if ($this->config['enable_meny']==1)
			{
				$file .= $meny_config;
			}
			$file .= $bottom_ads;
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

	function getMenyConfig($location = "board")
	{
		$file = "</div>";
		$file .= '<link rel="stylesheet" href="'.$this->mitsuba->getPath("./styles/meny.css", $location, 1).'">';
		$file .= "<script type='text/javascript' src='".$this->mitsuba->getPath("./js/meny.min.js", $location, 1)."'></script>\n";
		$file .= '<script type="text/javascript">'."\n";
		$file .= 'if ( window.self === window.top ) {'."\n";
		$file .= "var meny = Meny.create({
		    menuElement: document.querySelector( '.meny' ),
		    contentsElement: document.querySelector( '.contents' ),
		    position: 'left',
		    width: 260,
		    mouse: true,
		    touch: true
		});\n";
		$file .= '} else {'."\n";
		$file .= '$(".meny").css("display", "none");'."\n";
		$file .= '}'."\n";
		$file .= "</script>\n";
		return $file;
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
				<title>'.$title.'</title>';
			$first_default = 1;
			$styles = $this->conn->query("SELECT * FROM styles ORDER BY `default` DESC");
			while ($row = $styles->fetch_assoc())
			{
				if ($first_default == 1)
				{
					$file .= '<link rel="stylesheet" id="switch" href="'.$this->mitsuba->getPath($row['path'], "index", $row['relative']).'">';
					$first_default = 0;
				}
				$file .= '<link rel="alternate stylesheet" style="text/css" href="'.$this->mitsuba->getPath($row['path'], "index", $row['relative']).'" title="'.$row['name'].'">';
			}
			$file .= "
	<script type='text/javascript' src='./js/style.js'></script>
	</head>
				<body>";
			$file .= '<div id="doc">
				<br /><br />';
			$file .= '<div class="box-outer top-box">
				<div class="box-inner">
				<div class="boxbar"><h2>'.$title.'</h2></div>
				<div class="boxcontent">';
			require_once("libs/Michelf/Markdown.php");
			$file .= \Michelf\Markdown::defaultTransform($text);
			$file .= '</div>
				</div>
				</div>
				</div>';
			if ($this->config['enable_meny']==1)
			{
				$file .= '</div>';
			}
			$file .= '</body>
				</html>';
		}
		$handle = fopen("./".$name.".html", "w");
		fwrite($handle, $file);
		fclose($handle);
	}

	function forceGetThread($board, $threadno)
	{
		global $lang;
		if ($boarddata = $this->mitsuba->common->isBoard($board))
		{
			$result = $this->conn->query("SELECT * FROM posts WHERE id=".$threadno." AND board='".$board."' AND deleted=0");
			if ($result->num_rows == 1)
			{
				$trow = $result->fetch_assoc();
				$wfresult = $this->conn->query("SELECT * FROM wordfilter WHERE active=1");
				$replace_array = array();
				while ($row = $wfresult->fetch_assoc())
				{
					if ($row['boards'] != "%")
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
				require_once( "libs/jbbcode/Parser.php" );
				$parser = new \JBBCode\Parser();
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

				$file = $this->getThread($trow['board'], 0, 0, 0, $parser, $boarddata, $replace_array, $embed_table, $trow, $extensions, 1);
			
			}
		}
	}

	function getThread($board, $threadno, $return, $adm_type, $parser, $boarddata, $replace_array, $embed_table, $row, $extensions, $force = 0)
	{
		global $lang;
		if (($this->config['caching_mode']==1) && ($threadno == 0) && ($return == 0) && ($force == 0) && (file_exists("./".$row['board']."/res/".$row['id']."_index.html")))
		{
			return file_get_contents("./".$row['board']."/res/".$row['id']."_index.html");
		}
		if ($return == 1)
		{
			$location = "index";
		} elseif ($threadno != 0)
		{
			$location = "thread";
		} else {
			$location = "board";
		}
		$file = "";
		$file .= '<div class="thread" id="t'.$row['id'].'">';
		$file .= '<div class="postContainer opContainer" id="pc'.$row['id'].'">';
		$file .= '<div id="p'.$row['id'].'" class="post op">';
		$file .= '<div class="postInfo" id="pi'.$row['id'].'">';
		$file .= '<input type="checkbox" name="del%'.$row['board'].'%'.$row['id'].'" value="delete" />';
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
		if ((!empty($row['poster_id'])) && ($boarddata['ids']==1) && (empty($row['capcode_text'])))
		{
			$poster_id = '<span class="posteruid">(ID: '.$row['poster_id'].')</span>';
		}
		$c_image = "";
		if (!empty($row['capcode_icon']))
		{
			if (substr($row['capcode_icon'], 0, 1)==".")
			{
				$c_image = ' <img src="'.$this->mitsuba->getPath($row['capcode_icon'], $location, 1).'" alt="Capcode" style="margin-bottom: -3px;" />';
			} else {
				$c_image = ' <img src="'.$row['capcode_icon'].'" alt="Capcode" style="margin-bottom: -3px;" />';
			}
		}
		$email_a = "";
		$email_b = "";
		if (!empty($row['email'])) {
			$email_a = '<a href="mailto:'.$row['email'].'" class="useremail">';
			$email_b = '</a>';
		}
		$file .= "<td>";
		$file .= '<span class="nameBlock">';
		if (!empty($row['capcode_text']))
		{
			$file .= $email_a.'<span class="name"><span style="'.$row['capcode_style'].'">'.$row['name'].'</span></span>'.$email_b.$trip.' <span class="commentpostername"><span style="'.$row['capcode_style'].'">## '.$row['capcode_text'].'</span>'.$c_image.'</span>';
		} else {
			$file .= $email_a.'<span class="name">'.$row['name'].'</span>'.$email_b.$trip.' '.$poster_id;
		}
		$file .= '</span>';
		$opip = $row['ip'];
		if (($adm_type >= 2) && ($return == 1))
		{
			$file .= ' <span class="posterIp">(<a href="http://whatismyipaddress.com/ip/'.$row['ip'].'" target="_blank">'.$row['ip'].'</a>)</span>';
			$file .= ' [<a href="?/info&ip='.$row['ip'].'">N</a>] <b style="color: red;">[ OP ]</b>';
		}
		$file .= ' <span class="dateTime">'.date("d/m/Y(D)H:i:s", $row['date']).'</span> ';

		if ($return == 1)
		{
			$file .= '<span class="postNum"><a href="?/board&b='.$row['board'].'&t='.$row['id'].'#p'.$row['id'].'" title="Highlight this post">No.</a><a href="?/board&b='.$row['board'].'&t='.$row['id'].'#p'.$row['id'].'#q'.$row['id'].'" class="quotePost" id="z'.$row['id'].'" title="Quote this post">'.$row['id'].'</a></span>';
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
				$file .= ' <span class="adminControls">[<a href="?/bans/add&b='.$row['board'].'&p='.$row['id'].'">B</a> / <a href="?/bans/add&b='.$row['board'].'&p='.$row['id'].'&d=1">&</a> / <a href="?/delete_post&b='.$row['board'].'&p='.$row['id'].'">D</a>';
				if (!empty($row['filename']))
				{
					$file .= ' / <a href="?/delete_post&b='.$row['board'].'&p='.$row['id'].'&f=1">F</a>]';
				} else {
					$file .= ']';
				}
				if ($adm_type >= 3)
				{
					$file .= ' [<a href="?/edit_post&b='.$row['board'].'&p='.$row['id'].'" class="edit">E</a>]';
				}
			} else {
				$file .= ' <span class="adminControls">[<a href="?/bans/add&b='.$row['board'].'&p='.$row['id'].'">B</a>]';
			}
			if ($adm_type >= 2)
			{
				$file .= ' [<a href="?/sticky/toggle&b='.$row['board'].'&t='.$row['id'].'">S</a> / <a href="?/locked/toggle&b='.$row['board'].'&t='.$row['id'].'">L</a> / <a href="?/antibump/toggle&b='.$row['board'].'&t='.$row['id'].'">A</a>]';
			}
			if ($threadno == 0)
			{
				$file .= '&nbsp; <span>[<a href="?/board&b='.$row['board'].'&t='.$row['id'].'" class="replylink">'.$lang['img/reply'].'</a>]</span>';
			}
			$file .= '</span>';
		} elseif ($threadno != 0)
		{
			$file .= '<span class="postNum"><a href="../../'.$row['board'].'/res/'.$row['id'].'.html#p'.$row['id'].'" title="Highlight this post">No.</a><a href="../../'.$row['board'].'/res/'.$row['id'].'.html#q'.$row['id'].'" class="quotePost" id="z'.$row['id'].'" title="Quote this post">'.$row['id'].'</a>';
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
			$file .= '<span class="postNum"><a href="../'.$row['board'].'/res/'.$row['id'].'.html#p'.$row['id'].'" title="Highlight this post">No.</a><a href="../'.$row['board'].'/res/'.$row['id'].'.html#q'.$row['id'].'" class="quotePost" id="z'.$row['id'].'" title="Quote this post">'.$row['id'].'</a> ';
			if ($row['locked']==1)
			{
				$file .= '<img src="../img/closed.gif" alt="Closed" title="Closed" class="stickyIcon" />';
			}
			if ($row['sticky']==1)
			{
				$file .= '<img src="../img/sticky.gif" alt="Sticky" title="Sticky" class="stickyIcon" />';
			}
			$file .= '&nbsp; <span>[<a href="../'.$row['board'].'/res/'.$row['id'].'.html" class="replylink">'.$lang['img/reply'].'</a>]</span></span>';
		}
		$file .= '</div>';
		$file .= $this->getFiles($row, $row['board'], $return, $threadno, $embed_table, $extensions);
		$file .= '<blockquote class="postMessage" id="m'.$row['id'].'">';
		$wf = 1;
		
		if (!empty($row['capcode_text']))
		{
			$wf = 0;
		}
		if ($row['raw'] != 1)
		{
			if ($row['raw'] == 2)
			{
				if ($return == 1)
				{
					$file .= $this->processComment($row['board'], $row['comment'], $parser, 2, 0, $boarddata['bbcode'], $row['id'], $row['resto'], $wf, $replace_array);
				} else {
					$file .= $this->processComment($row['board'], $row['comment'], $parser, $threadno != 0, 0, $boarddata['bbcode'], $row['id'], $row['resto'], $wf, $replace_array);
				}
			} else {
				if ($return == 1)
				{
					$file .= $this->processComment($row['board'], $row['comment'], $parser, 2, 1, $boarddata['bbcode'], $row['id'], $row['resto'], $wf, $replace_array);
				} else {
					$file .= $this->processComment($row['board'], $row['comment'], $parser, $threadno != 0, 1, $boarddata['bbcode'], $row['id'], $row['resto'], $wf, $replace_array);
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
			$posts = $this->conn->query("SELECT * FROM posts WHERE resto=".$row['id']." AND board='".$row['board']."' AND deleted=0 ORDER BY id ASC");
		} else {
		$postnos = $this->conn->query("SELECT COUNT(*) FROM posts WHERE resto=".$row['id']." AND board='".$row['board']."' AND deleted=0");
		$row1 = $postnos->fetch_row();
		if ($row1[0] == 0)
		{
			$file .= '</div><hr />';
			if (($this->config['caching_mode'] == 1) && ($threadno == 0) && ($return == 0))
			{
				$handle = fopen("./".$row['board']."/res/".$row['id']."_index.html", "w");
				fwrite($handle, $file);
				fclose($handle);
			}
			return $file;
		}
		if ($row1[0] > 3)
		{
			if ($return == 1)
			{
				$file .= '<span class="summary">'.sprintf($lang['img/posts_omitted'], ($row1[0]-3), '<a href="?/board&b='.$row['board'].'&t='.$row['id'].'" class="replylink">', '</a>').'</span>';
			} else {
				$file .= '<span class="summary">'.sprintf($lang['img/posts_omitted'], ($row1[0]-3), '<a href="./res/'.$row['id'].'.html" class="replylink">', '</a>').'</span>';
			}
		}
		$offset = 0;
		if ($row1[0] > 3)
		{
			$offset = $row1[0] - 3;
			
		}
		$posts = $this->conn->query("SELECT * FROM posts WHERE resto=".$row['id']." AND board='".$row['board']."' AND deleted=0 ORDER BY id ASC LIMIT ".$offset.",3");
			
		}
		while ($row2 = $posts->fetch_assoc())
		{
			$file .= '<div class="postContainer replyContainer" id="pc'.$row2['id'].'">';
			$file .= '<div class="sideArrows" id="sa'.$row2['id'].'">&gt;&gt;</div>';
			$file .= '<div id="p'.$row2['id'].'" class="post reply">';
			$file .= '<div class="postInfo" id="pi'.$row2['id'].'">';
			$file .= '<input type="checkbox" name="del%'.$row2['board'].'%'.$row2['id'].'" value="delete" />';
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
			$poster_id = "";
			if ((!empty($row2['poster_id'])) && ($boarddata['ids']==1) && (empty($row2['capcode_text'])))
			{
				$poster_id = '<span class="posteruid">(ID: '.$row2['poster_id'].')</span>';
			}
			$c_image = "";
			if (!empty($row2['capcode_icon']))
			{
				if (substr($row2['capcode_icon'], 0, 1)==".")
				{
					$c_image = ' <img src="'.$this->mitsuba->getPath($row2['capcode_icon'], $location, 1).'" alt="Capcode" style="margin-bottom: -3px;" />';
				} else {
					$c_image = ' <img src="'.$row2['capcode_icon'].'" alt="Capcode" style="margin-bottom: -3px;" />';
				}
			}
			$email_a = "";
			$email_b = "";
			if (!empty($row2['email'])) {
				$email_a = '<a href="mailto:'.$row2['email'].'" class="useremail">';
				$email_b = '</a>';
			}
			$file .= "<td>";
			$file .= '<span class="nameBlock">';
			if (!empty($row2['capcode_text']))
			{
				$file .= $email_a.'<span class="name"><span style="'.$row2['capcode_style'].'">'.$row2['name'].'</span></span>'.$email_b.$trip.' <span class="commentpostername"><span style="'.$row2['capcode_style'].'">## '.$row2['capcode_text'].'</span>'.$c_image.'</span>';
			} else {
				$file .= $email_a.'<span class="name">'.$row2['name'].'</span>'.$email_b.$trip.' '.$poster_id;
			}
			$file .= '</span>';
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
				$file .= '<span class="postNum"><a href="?/board&b='.$row['board'].'&t='.$row['id'].'#p'.$row2['id'].'" title="Highlight this post">No.</a><a href="?/board&b='.$row['board'].'&t='.$row['id'].'#q'.$row2['id'].'" class="quotePost" id="z'.$row2['id'].'" title="Quote this post">'.$row2['id'].'</a></span>';
				$file .= ' <span class="adminControls">[<a href="?/bans/add&b='.$row['board'].'&p='.$row2['id'].'">B</a> / <a href="?/bans/add&b='.$row['board'].'&p='.$row2['id'].'&d=1">&</a> / <a href="?/delete_post&b='.$row['board'].'&p='.$row2['id'].'">D</a>';
				
				
				if (!empty($row2['filename']))
				{
					$file .= ' / <a href="?/delete_post&b='.$row['board'].'&p='.$row2['id'].'&f=1">F</a>] ';
				} else {
					$file .= ']';
				}
				if ($adm_type >= 3)
				{
					$file .= ' [<a href="?/edit_post&b='.$row['board'].'&p='.$row2['id'].'" class="edit">E</a>]';
				}
				$file .= "</span>";
			} elseif ($threadno != 0)
			{
				$file .= '<span class="postNum"><a href="../res/'.$row2['resto'].'.html#p'.$row2['id'].'" title="Highlight this post">No.</a><a href="../res/'.$row2['resto'].'.html#q'.$row2['id'].'" class="quotePost" id="z'.$row2['id'].'" title="Quote this post">'.$row2['id'].'</a> &nbsp;</span>';
			} else {
				$file .= '<span class="postNum"><a href="./res/'.$row2['resto'].'.html#p'.$row2['id'].'" title="Highlight this post">No.</a><a href="./res/'.$row2['resto'].'.html#q'.$row2['id'].'" class="quotePost" id="z'.$row2['id'].'" title="Quote this post">'.$row2['id'].'</a> &nbsp;</span>';
			}
			$file .= '</div>';
			$file .= $this->getFiles($row2, $row['board'], $return, $threadno, $embed_table, $extensions);
			$file .= '<blockquote class="postMessage" id="m'.$row2['id'].'">';
			$wf = 1;
			if (!empty($row2['capcode_text']))
			{
				$wf = 0;
			}
			if ($row2['raw'] != 1)
			{
				if ($row2['raw'] == 2)
				{
					if ($return == 1)
					{
						$file .= $this->processComment($row['board'], $row2['comment'], $parser, 2, 0, $boarddata['bbcode'], $row2['id'], $row2['resto'], $wf, $replace_array);
					} else {
						$file .= $this->processComment($row['board'], $row2['comment'], $parser, $threadno != 0, 0, $boarddata['bbcode'], $row2['id'], $row2['resto'], $wf, $replace_array);
					}
				} else {
					if ($return == 1)
					{
						$file .= $this->processComment($row['board'], $row2['comment'], $parser, 2, 1, $boarddata['bbcode'], $row2['id'], $row2['resto'], $wf, $replace_array);
					} else {
						$file .= $this->processComment($row['board'], $row2['comment'], $parser, $threadno != 0, 1, $boarddata['bbcode'], $row2['id'], $row2['resto'], $wf, $replace_array);
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
				$handle = fopen("./".$row['board']."/res/".$row['id']."_index.html", "w");
				fwrite($handle, $file);
				fclose($handle);
		}
		return $file;
		
	}

	function generateCatalog($board)
	{
		global $lang;
		$board = $this->conn->real_escape_string($board);
		if (!$this->mitsuba->common->isBoard($board))
		{
			return -16;
		}
		$boarddata = $this->mitsuba->common->getBoardData($board);
		if ($boarddata['hidden'] == 1)
		{
			return -666;
		}
		if ($boarddata['catalog'] == 0)
		{
			return -1;
		}
		$extensions = array();
		$result = $this->conn->query("SELECT * FROM extensions;");
		while ($row = $result->fetch_assoc())
		{
			$extensions[$row['mimetype']]['image'] = $row['image'];
		}
		$wfresult = $this->conn->query("SELECT * FROM wordfilter WHERE active=1");
		$replace_array = array();
		while ($row = $wfresult->fetch_assoc())
		{
			if ($row['boards'] != "%")
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
		$file = $this->getBoardHeader($board, $boarddata, "board", 1);
		$file .= $this->getAds($boarddata['short'], "underform");
		$location = "board";
		$threads = $this->conn->query("SELECT *, (SELECT COUNT(*) FROM posts AS replies WHERE replies.resto=posts.id AND replies.deleted=0) as 'replies', (SELECT COUNT(*) FROM posts AS replies WHERE replies.resto=posts.id AND replies.filename != \"\" AND replies.deleted=0) AS 'img_replies' FROM posts WHERE resto=0 AND board='".$this->conn->real_escape_string($board)."' AND deleted=0 ORDER BY sticky DESC, lastbumped DESC");
		$file .= '<div class="navLinks">[<a href="./" accesskey="a">'.$lang['img/return_c'].'</a>] [<a href="#bottom">'.$lang['img/bottom'].'</a>]</div>';
		$file .= '<div id="content">';
		$file .= '<div id="threads" class="extended-small">';
		$bumporder = 0;
		while ($row = $threads->fetch_assoc())
		{
			$bumporder++;
			$file .= '<div id="thread-'.$row['id'].'" data-bumporder="'.$bumporder.'" data-lastbumped="'.$row['lastbumped'].'" data-started="'.$row['date'].'" data-replycount="'.$row['replies'].'" class="thread">';
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
						
						$file .= '<a href="./res/'.$row['id'].'.html">';
						$file .= '<img alt="" id="thumb-'.$row['id'].'-'.$filenum.'" class="thumb" width="127" height="13" src="'.$this->mitsuba->getPath("./img/deleted.gif", $location, 1).'">';
						$file .= '</a>';
					} elseif (substr($fileinfo['filename'], 0, 8) == "spoiler:")
					{
						$file .= '<a href="./res/'.$row['id'].'.html">';
						$file .= '<img alt="" id="thumb-'.$row['id'].'-'.$filenum.'" class="thumb" width="100" height="100" src="'.$this->mitsuba->getPath("./img/spoiler.png", $location, 1).'">';
						$file .= '</a>';
					} elseif (substr($fileinfo['filename'], 0, 6) == "embed:")
					{
						$file .= '<a href="./res/'.$row['id'].'.html">';
						$file .= '<b>Embed</b>';
						$file .= '</a>';
					} else {
						$imgsize = "";
						if ((isset($extensions[$fileinfo['mimetype']]['image'])) && ($extensions[$fileinfo['mimetype']]['image']==1))
						{
							$imgsize = ', '.$fileinfo['imagesize'];
						}
						$thumbpath = './src/thumb/'.$fileinfo['filename'];

						if (isset($extensions[$fileinfo['mimetype']]['image']))
						{
							if ($extensions[$fileinfo['mimetype']]['image']==1)
							{
								$w = $fileinfo['t_w'];
								$h = $fileinfo['t_h'];
								$new_w = 0;
								$new_h = 0;
								if (($w > 150) || ($h > 150))
								{
									if ($w > $h)
									{
										$new_w = 150;
										$new_h = ($new_w/$w)*$h;
									} elseif ($w < $h) {
										$new_h = 150;
										$new_w = ($new_h/$h)*$w;
									} elseif ($w == $h) {
										$new_h = 150;
										$new_w = 150;
									}
								}
								$file .= '<a href="./res/'.$row['id'].'.html">';
								$file .= '<img alt="" id="thumb-'.$row['id'].'-'.$filenum.'" class="thumb" width="'.$new_w.'" height="'.$new_h.'" src="'.$thumbpath.'">';
								$file .= '</a>';
							} elseif ($extensions[$fileinfo['mimetype']]['image']!=0)
							{
								$file .= '<a href="./res/'.$row['id'].'.html">';
								$file .= '<b>Other file</b>';
								$file .= '</a>';
							}
						}
					}
					$filenum++;
				}
			} else {
				$file .= '<a href="./res/'.$row['id'].'.html">';
				$file .= '<b>No file</b>';
				$file .= '</a>';
			}
			
			$file .= '<div title="(R)eplies / (I)mages" id="meta-'.$row['id'].'" class="meta">R: <b>'.$row['replies'].'</b> / I: <b>'.$row['img_replies'].'</b></div>';
			$subject = "";
			if (!empty($row['subject']))
			{
				$subject = "<b>".$row['subject']."</b>: ";
			}
			$file .= '<div class="teaser">'.$subject.htmlspecialchars(strtr($row['comment'], $replace_array)).'&nbsp;</div>';
			$file .= '</div>';
		}
		$file .= '</div>';
		$file .= '</div>';
		$file .= '<div class="navLinks">[<a href="./" accesskey="a">'.$lang['img/return_c'].'</a>] [<a href="#top">'.$lang['img/top'].'</a>]</div>';
		$file .= '<div class="stylechanger" id="stylechangerDiv" style="display:none;">'.$lang['img/style'].' <select id="stylechanger"></select></div>
			</div>';
		$file .= $this->getAds($boarddata['short'], "footer");
		$file .= '<div style="text-align: center; font-size: x-small!important; padding-bottom: 4px; padding-top: 10px; color: #333;"><span class="absBotDisclaimer">- <a href="http://github.com/MitsubaBBS/Mitsuba" target="_top" rel="nofollow">mitsuba</a> -</span></div>';
		$file .= '<div id="bottom"></div>';
		if ($this->config['enable_meny']==1)
		{
			$file .= $this->getMenyConfig("board");
		}
		$file .= $this->getAds($boarddata['short'], "bottom");
		$file .= "</body></html>";
		$handle = fopen("./".$board."/catalog.html", "w");
		fwrite($handle, $file);
		fclose($handle);
	}

	function updateThreads($board)
	{
		$board = $this->conn->real_escape_string($board);
		if (!$this->mitsuba->common->isBoard($board))
		{
			return -16;
		}
		$result = $this->conn->query("SELECT id FROM posts WHERE resto=0 AND board='".$board."' AND deleted=0");
		while ($row = $result->fetch_assoc())
		{
			if ($this->config['caching_mode']==1)
			{
				$this->forceGetThread($board, $row['id']);
			}
			$this->generateView($board, $row['id']);
			if ($this->config['enable_api']==1)
			{
				$this->serializeThread($board, $row['id']);
			}
		}
	}

	function serializeBoard($board)
	{
		if ($this->config['enable_api']==0)
		{
			return;
		}
		if ($this->mitsuba->common->isBoard($board))
		{
			$boardposts = $this->conn->query("SELECT * FROM posts WHERE board='".$board."' AND deleted=0");
			$api_posts = array();
			require_once( "libs/jbbcode/Parser.php" );
			$parser = new \JBBCode\Parser();
			$boarddata = $this->mitsuba->common->getBoardData($board);
			if ($boarddata['bbcode']==1)
			{
				$bbcode = $this->conn->query("SELECT * FROM bbcodes;");
				
				while ($row = $bbcode->fetch_assoc())
				{
					$parser->addBBCode($row['name'], $row['code']);
				}
			}
			while ($row = $boardposts->fetch_assoc())
			{
				$api_posts[] = $this->serializePost($row, $boarddata, $parser);
			}
			$api_handle = fopen("./".$board."/board.json", "w");
			$api['posts'] = $api_posts;
			fwrite($api_handle, json_encode($api));
			fclose($api_handle);
		}
	}

	function serializeThread($board, $thread_id)
	{
		if ($this->mitsuba->common->isBoard($board))
		{
			$thread = $this->conn->query("SELECT * FROM posts WHERE board='".$board."' AND id=".$thread_id." AND deleted=0");
			if ($thread->num_rows == 1)
			{
				$row = $thread->fetch_assoc();
				require_once( "libs/jbbcode/Parser.php" );
				$parser = new \JBBCode\Parser();
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

	function getMenu($location, $target = "")
	{
		if (!empty($target)) { $target = ' target="'.$target.'"'; }
		$menu = "<h1>".$this->config['sitename']."</h1>";
		$cats = $this->conn->query("SELECT * FROM links WHERE parent=-1;");
		while ($row = $cats->fetch_assoc())
		{
			$menu .= '<h2>'.$row['title'].'</h2>';
			$menu .= '<div id="'.$row['short'].'" style="">
				<ul>';
			$children = $this->conn->query("SELECT * FROM links WHERE parent=".$row['id']);
			while ($child = $children->fetch_assoc())
			{
				if ($child['relative'] == 1)
				{
					$menu .= '<li><a href="'.$this->mitsuba->getPath($child['url'], $location, 1).'"'.$target.'>/'.$child['short'].'/ - '.$child['title'].'</a></li>';
				} elseif ($row['relative'] == 2)
				{
					$menu .= '<li><a href="'.$this->mitsuba->getPath("./".$child['url']."/", $location, 1).'"'.$target.'>/'.$child['short'].'/ - '.$child['title'].'</a></li>';
				} else {
					$menu .= '<li><a href="'.$child['url'].'"'.$target.'>/'.$child['short'].'/ - '.$child['title'].'</a></li>';
				}
			}
			$menu .= '</ul></div>';
		}
		return $menu;
	}

	function generateFrontpage($action = "none")
	{
		if (file_exists("./inc/frontpage/".$this->config['frontpage_style']))
		{
			require_once("./inc/frontpage/".$this->config['frontpage_style']);
			$fpage = new Frontpage($this->conn, $this->mitsuba);
			$fpage->generateFrontpage($action);
		}
	}

	function generateNews()
	{
		if (file_exists("./inc/frontpage/".$this->config['frontpage_style']))
		{
			require_once("./inc/frontpage/".$this->config['frontpage_style']);
			$fpage = new Frontpage($this->conn, $this->mitsuba);
			$fpage->generateNews();
		}
	}

	function getFiles($row, $board, $return, $threadno, $embed_table, $extensions)
	{
		$file = "";
		if ($return == 1)
		{
			$location = "index";
		} elseif ($threadno != 0)
		{
			$location = "thread";
		} else {
			$location = "board";
		}
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
				if (substr($fileinfo['filename'], 0, 4) == "url:")
				{
					$file .= '<div class="file" id="f'.$row['id']."_".$filenum.'">';
					$file .= '<div class="fileInfo">';
					$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'">File: <a href="'.substr($fileinfo['filename'],4).'">'.htmlspecialchars($fileinfo['orig_filename']).'</a></span>';
					$file .= '</div>';
				} elseif ($fileinfo['filename'] == "deleted")
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
						$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'">File: <a href="../../'.$board.'/src/'.substr($fileinfo['filename'],8).'" target="_blank"><b>Spoiler</b></a></span>';
					} else {
						$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'">File: <a href="../'.$board.'/src/'.substr($fileinfo['filename'],8).'" target="_blank"><b>Spoiler</b></a></span>';
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
						$filepath = '../../'.$board.'/src/'.substr($fileinfo['filename'],8);
						$thumbpath = '../../'.$board.'/src/thumb/'.substr($fileinfo['filename'],8);
					} else {
						$filepath = './../'.$board.'/src/'.substr($fileinfo['filename'],8);
						$thumbpath = './../'.$board.'/src/thumb/'.substr($fileinfo['filename'],8);
					}

					$file .= '<a class="fileThumb" href="'.$filepath.'" target="_blank"><img src="'.$this->mitsuba->getPath("./img/spoiler.png", $location, 1).'" alt="Spoiler image" style="width: 100px; height: 100px"/></a>';
					$file .= '</div>';
				} elseif (substr($fileinfo['filename'], 0, 6) == "embed:")
				{
					$file .= '<div class="file" id="f'.$row['id']."_".$filenum.'">';
					$file .= '<div class="fileInfo">';
					$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'">File: <b>Embed</b></span>';
					
					$file .= '</div>';
					$file .= '<a class="fileThumb">'.$this->mitsuba->common->getEmbed(substr($fileinfo['filename'], 6), $embed_table).'</a>';
					
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
						$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'"><a href="../../'.$board.'/src/'.$fileinfo['filename'].'" target="_blank">File</a>: ('.$fileinfo['filesize'].$imgsize.', <span title="'.$fileinfo['orig_filename'].'">'.$fileinfo['orig_filename'].'</span>)</span>';
					} else {
						$file .= '<span class="fileText" id="fT'.$row['id']."_".$filenum.'"><a href="../'.$board.'/src/'.$fileinfo['filename'].'" target="_blank">File</a>: ('.$fileinfo['filesize'].$imgsize.', <span title="'.$fileinfo['orig_filename'].'">'.$fileinfo['orig_filename'].'</span>)</span>';
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
						$filepath = '../../'.$board.'/src/'.$fileinfo['filename'];
						$thumbpath = '../../'.$board.'/src/thumb/'.$fileinfo['filename'];
					} else {
						$filepath = '../'.$board.'/src/'.$fileinfo['filename'];
						$thumbpath = '../'.$board.'/src/thumb/'.$fileinfo['filename'];
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
		if (!empty($row['capcode_text']))
		{
			$post['id'] = $row['capcode_text'];
		} else {
			if ((!empty($row['poster_id'])) && ($boarddata['ids']==1))
			{
				$post['id'] = $row['poster_id'];
			}
		}
		if (!empty($row['capcode_text']))
		{
			$post['capcode'] = $row['capcode_text'];
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
		$this->regenIDs($board);
		$this->updateThreads($board);
		$this->generateView($board);
		$this->generateCatalog($board);
		$this->serializeBoard($board);
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
		$result = $this->conn->query("SELECT * FROM posts WHERE id=".$id." AND board='".$board."' AND deleted=0");
		if ($result->num_rows == 1)
		{
			$post = $result->fetch_assoc();
			if ($post['resto'] == 0)
			{
				$this->generateCatalog($board);
				$this->generateView($board, $post['id']);
			} else {
				$this->generateView($board, $post['resto']);
			}
			$this->generateView($board);
			$this->serializeBoard($board);
		}
	}
}
?>
