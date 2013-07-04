<?php
namespace Mitsuba;
class Common {
	private $conn;
	private $mitsuba;

	function __construct($connection, &$mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
	}
	
	function getEmbed($url, $embed_table = null, $s = 250) {
		foreach ($embed_table as $row)
		{
			if (preg_match($row['regex'], $url, $vresult))
			{
				$vresult[0] = $s;
				foreach($vresult as $k => $v) {
					$vresult[$k] = htmlspecialchars($v);
				}
				return vsprintf($row['code'], $vresult);
			}
		}
		return 0;
		
	}

	function addSystemBan($ip, $reason, $note, $expires, $boards)
	{
		if (!empty($ip))
		{
			$ip = $this->conn->real_escape_string($ip);
			$reason = $this->conn->real_escape_string($reason);
			$note = $this->conn->real_escape_string($note);
			$boards = $this->conn->real_escape_string($boards);
			$created = time();
			$perma = 1;
			if (($expires == "0") || ($expires == "never") || ($expires == "") || ($expires == "perm") || ($expires == "permaban"))
			{
				$expires = 0;
				$perma = 1;
			} else {
				$expires = $this->parse_time($expires);
				$perma = 0;
			}
			if (($expires == false) && ($perma == 0))
			{
				return -2;
			}
			$this->conn->query("INSERT INTO bans (ip, mod_id, reason, note, created, expires, boards) VALUES ('".$ip."', 0, '".$reason."', '".$note."', ".$created.", ".$expires.", '".$boards."');");
			return 1;
		}
	}

	function isEmbed($url, $embed_table = null) {
		foreach ($embed_table as $row)
		{
			if (preg_match($row['regex'], $url, $vresult))
			{
				return 1;
			}
		}
		return 0;
	}

	function human_filesize($bytes, $decimals = 2) {
	  $sz = 'BKMGTP';
	  $factor = floor((strlen($bytes) - 1) / 3);
	  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}

	function mkid($ip, $topic, $board, $junk = "")
	{
	    return substr(crypt(md5($ip.'t'.$board.$topic.$junk),'h!'),-8);
	}

	function getGraphicsExtension()
	{
		if (extension_loaded('imagick'))
		{
			return "imagick";
		} elseif (extension_loaded('gd'))
		{
			return "gd";
		} else {
			return 0;
		}
	}

	function getBoardData($short)
	{
		$result = $this->conn->query("SELECT * FROM boards WHERE short='".$this->conn->real_escape_string($short)."'");
		if ($result->num_rows == 1)
		{
			return $result->fetch_assoc();
		} else {
			return 0; //board not found
		}
	}

	function isBoard($short)
	{
		$result = $this->conn->query("SELECT * FROM boards WHERE short='".$this->conn->real_escape_string($short)."'");
		if ($result->num_rows == 1)
		{
			return 1;
		} else {
			return 0;
		}
	}

	function thumb($board,$filename,$s=250){
		$extension = $this->getGraphicsExtension();
		
		if ($extension == "imagick")
		{
			$fname='./'.$board.'/src/'.$filename;
			$thumb_dir = './'.$board.'/src/thumb/'; //thumbnail directory
			$width = $s; //output width
			$height = $s; //output height
			$img = new \Imagick($fname);
			$most_width = 0;
			$most_height = 0;
			$oig = $img->getImageGeometry();
			if (($oig['width'] > $s) || ($oig['height'] > $s))
			{
				foreach($img as $frame)
				{
					$frame->thumbnailImage($width, $height, true);
					$geo2 = $frame->getImageGeometry();
					if ($geo2['width']>$most_width) { $most_width = $geo2['width']; }
					if ($geo2['height']>$most_height) { $most_height = $geo2['height']; }
					$frame->setImagePage($geo2['width'], $geo2['height'], 0, 0);
				}
			} else {
				$most_width = $oig['width'];
				$most_height = $oig['height'];
			}
			//$img->setImageCompressionQuality(60); 
			$img->writeImages($thumb_dir.$filename, true);
			$img->destroy();
			$ig['width'] = $most_width;
			$ig['height'] = $most_height;
			return $ig;
		} elseif ($extension == "gd")
		{
			if(!function_exists("ImageCreate")||!function_exists("ImageCreateFromJPEG"))return;
			$fname='./'.$board.'/src/'.$filename;
			$thumb_dir = './'.$board.'/src/thumb/';	 //thumbnail directory
			$width	 = $s;			//output width
			$height	= $s;			//output height
			// width, height, and type are aquired
			$size = GetImageSize($fname);
			$type = "jpg";
			try {
				switch ($size[2]) {
					case 1 :
						if(!function_exists("ImageCreateFromGIF"))return;
						$im_in = ImageCreateFromGIF($fname);
						$type = "gif";
						if(!$im_in){return -1;}
						break;
					case 2 : $im_in = ImageCreateFromJPEG($fname);
						$type = "jpg";
						if(!$im_in){return -1;}
						break;
					case 3 :
						if(!function_exists("ImageCreateFromPNG"))return;
						$im_in = ImageCreateFromPNG($fname);
						$type = "png";
						if(!$im_in){return -1;}
						break;
					default : return -2;
				}
			} catch (Exception $e)
			{
				return -1;
			}
			// Resizing
			if ($size[0] > $width || $size[1] >$height) {
				$key_w = $width / $size[0];
				$key_h = $height / $size[1];
				($key_w < $key_h) ? $keys = $key_w : $keys = $key_h;
				$out_w = ceil($size[0] * $keys) +1;
				$out_h = ceil($size[1] * $keys) +1;
			} else {
				$out_w = $size[0];
				$out_h = $size[1];
			}
			// the thumbnail is created
			if(function_exists("ImageCreateTrueColor")){
				$im_out = ImageCreateTrueColor($out_w, $out_h);
			}else{$im_out = ImageCreate($out_w, $out_h);}
			// copy resized original
			ImageCopyResized($im_out, $im_in, 0, 0, 0, 0, $out_w, $out_h, $size[0], $size[1]);
			// thumbnail saved
			switch ($type)
			{
				case "jpg":
					ImageJPEG($im_out, $thumb_dir.$filename, 70);
					break;
				case "png":
					ImagePNG($im_out, $thumb_dir.$filename, 9);
					break;
				case "gif":
					ImageGIF($im_out, $thumb_dir.$filename);
					break;
			}
			chmod($thumb_dir.$filename,0666);
			// created image is destroyed
			ImageDestroy($im_in);
			ImageDestroy($im_out);
			return array("width" => $out_w, "height" => $out_h);
		}
	}

	function delTree($dir) { 
	   $files = array_diff(scandir($dir), array('.','..')); 
	    foreach ($files as $file) { 
	      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
	    } 
	    return rmdir($dir); 
	  } 


	function isWhitelisted($ip)
	{
		$whitelist = $this->conn->query("SELECT * FROM whitelist WHERE ip='".$ip."' ORDER BY id DESC LIMIT 0, 1");
		if ($whitelist->num_rows >= 1)
		{
			$wlistdata = $whitelist->fetch_assoc();
			if ($wlistdata['nolimits'] == 1)
			{
				return 2;
			}
			return 1;
		} else {
			return 0;
		}
	}
	  
	function startsWith($haystack, $needle)
	{
		return !strncmp($haystack, $needle, strlen($needle));
	}

	function endsWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}

	function isBanned($ip, $board)
	{
		
		$ipbans = $this->conn->query("SELECT * FROM bans WHERE ip='".$ip."' AND (expires>".time()." OR expires=0) ORDER BY expires DESC LIMIT 0, 1;");
		$rangebans = $this->conn->query("SELECT * FROM rangebans ORDER BY expires DESC;");
		$ipbandata = null;
		$rangebandata = null;
		$bandata = null;
		while ($row = $rangebans->fetch_assoc())
		{
			$range = str_replace('*','(.*)', $row['ip']);
			if ($this->startsWith($range, "."))
			{
				if ((strpos($ip, $range) !== FALSE))
				{
					$rangebandata = $row;
					$rangebandata['range'] = 1;
					break;
				}
			} elseif ($this->startsWith($ip, $range))
			{
				$rangebandata = $row;
				$rangebandata['range'] = 1;
				break;
			} elseif (preg_match('/'.$range.'/', $ip))
			{
				$rangebandata = $row;
				$rangebandata['range'] = 1;
				break;
			}
		}
		
		if ($ipbans->num_rows == 1)
		{
			$ipbandata = $ipbans->fetch_assoc();
		}
		
		if (($ipbandata != null) && ($rangebandata != null))
		{
			if (($ipbandata['expires'] == 0) || ($ipbandata['expires'] > $rangebandata['expires'])) {
				$bandata = $ipbandata;
			} elseif (($rangebandata['expires'] == 0) || ($rangebandata['expires'] > $ipbandata['expires'])) {
				$bandata = $rangebandata;
			} else {
				$bandata = $ipbandata;
			}
		} elseif (($ipbandata != null) || ($rangebandata != null))
		{
			if ($ipbandata != null) {
				$bandata = $ipbandata;
			} elseif ($rangebandata != null) {
				$bandata = $rangebandata;
			} else {
				return 0;
			}
		} else {
			return 0; //not banned
		}

		if ($bandata['boards'] == "*")
		{
			return $bandata;
		} else {
			if ($board == "*")
			{
				return $bandata;
			} else {
				$boards = explode(",", $bandata['boards']);
				if (in_array($board, $boards))
				{
					return $bandata;
				} else {
					return 0;
				}
			}
		}
		return 0;
	}

	function isWarned($ip)
	{
		
		$warns = $this->conn->query("SELECT * FROM warnings WHERE ip='".$ip."' AND shown=0 ORDER BY created ASC LIMIT 0, 1;");
		
		if ($warns->num_rows == 1)
		{
			$warndata = $warns->fetch_assoc();
			return $warndata;
		} else {
			return 0;
		}
		return 0;
	}

	function randomPassword() {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array();
		$alphaLength = strlen($alphabet) - 1;
		for ($i = 0; $i < 8; $i++) {
			$n = mt_rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass);
	}

	function getsecuretripcode($pwd)
	{
		$striphash = $this->mktripcode(substr($pwd, 1));
		$strips = $this->conn->query("SELECT * FROM tripcodes WHERE hash='".$striphash."' AND secure=1");
		if ($strips->num_rows >= 1)
		{
			$row = $strips->fetch_assoc();
			return $row['replace'];
		} else {
			$strip = $this->mksecuretripcode($pwd);
			$this->conn->query("INSERT INTO tripcodes (`hash`, `replace`, `secure`) VALUES ('".$striphash."', '".$strip."', 1);");
			return $strip;
		}
	}

	function processName($string)
	{
		$arr = array();
		$new = $string;
		//$new = str_replace("##", "#", $new);
		$exploded = explode("#", $new, 2);
		$arr['trip'] = "";
		$arr['name'] = "";
		$arr['strip'] = "";
		//$arr['striphash'] = "";
		if (count($exploded)>1)
		{
			$arr['name'] = $exploded[0];
			if (substr($exploded[1], 0, 1) == "#")
			{
				$moretrips = explode("#", substr($exploded[1], 1), 2);
				if (count($moretrips)>1)
				{
					$arr['strip'] = $this->getsecuretripcode($moretrips[0]);
					$arr['trip'] = $this->mktripcode($moretrips[1]);
				} else {
					$arr['strip'] = $this->getsecuretripcode(substr($exploded[1], 1));
				}
			} else {
				$moretrips = explode("#", $exploded[1], 2);
				if (count($moretrips)>1)
				{
					if (substr($moretrips[1], 0, 1) == "#")
					{
						$arr['strip'] = $this->getsecuretripcode(substr($moretrips[1], 1));
					} else {
						$arr['strip'] = $this->getsecuretripcode($moretrips[1]);
					}
					$arr['trip'] = $this->mktripcode($moretrips[0]);
				} else {
					$arr['trip'] = $this->mktripcode($exploded[1]);
				}
			}
			
		} else {
			$arr['name'] = $new;
			$arr['trip'] = "";
		}
		$arr['name'] = $this->conn->real_escape_string($arr['name']);
		$arr['name'] = htmlspecialchars($arr['name']);
		return $arr;
	}

	function processString($string)
	{
		$new = $string;
		$new = $this->conn->real_escape_string($new);
		$new = htmlspecialchars($new);
		return $new;
	}

	function preprocessComment($string)
	{
		$new = str_replace("\r", "", $string);
		$new = $this->conn->real_escape_string($new);
		return $new;
	}

	function isFile($path, $board_files = "*")
	{
		$mime = "";
		if (empty($board_files)) { $board_files = "*"; }
		if (function_exists("finfo_file"))
		{
			$finfo = finfo_open();
			$mime = finfo_file($finfo, $path, FILEINFO_MIME_TYPE);
		} elseif (function_exists("mime_content_type"))
		{
			$mime = mime_content_type($path);
		} else {
			if (function_exists("getimagesize")) {
				$a = getimagesize($path);
				$image_type = $a[2];
				if ($image_type == IMAGETYPE_GIF)
				{
					$mime = "image/gif";
				}
				if ($image_type == IMAGETYPE_PNG)
				{
					$mime = "image/png";
				}
				if ($image_type == IMAGETYPE_JPEG)
				{
					$mime = "image/jpeg";
				}
			} else {
				return false;
			}
		}
		$extensions = $this->conn->query("SELECT * FROM extensions WHERE mimetype='".$this->conn->real_escape_string($mime)."'");
		if ($extensions->num_rows == 1)
		{
			$ext = $extensions->fetch_assoc();
			if (($board_files == "*") || (in_array($ext['ext'], explode(",", $board_files))))
			{
				$nfo['extension'] = $ext['ext'];
				$nfo['image'] = $ext['image'];
				$nfo['mimetype'] = $mime;
				return $nfo;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function mktripcode($pw)
	{
		$pw=mb_convert_encoding($pw,'SJIS','UTF-8');
		$salt=substr($pw.'H.',1,2);
		$salt=preg_replace('/[^\.-z]/', '.', $salt);
		$salt=strtr($salt,':;<=>?@[\]^_`','ABCDEFGabcdef');
		$trip=substr(crypt($pw,$salt),-10);
		return $trip;
	}

	function mksecuretripcode($pw, $junk = "r3volution")
	{
		$pw=mb_convert_encoding($pw,'SJIS','UTF-8');
		$pw=str_replace('&','&amp;',$pw);
		$pw=str_replace('"','&quot;',$pw);
		$pw=str_replace("'",'&#39;',$pw);
		$pw=str_replace('<','&lt;',$pw);
		$pw=str_replace('>','&gt;',$pw);
		$randomstring="";
		$poststring="";
		foreach ($_POST as $key => $value)
		{
			$poststring .= $key."!".$value;
		}
		$poststring=strtr($poststring,':;<=>?@[\]^_`','ABCDEFGabcdef');
		$randomstring=md5($poststring).time().mt_rand(90, 1681018501).$junk;

		$salt=substr($pw.'H!'.$randomstring,1,2);
		$salt=preg_replace('/[^.\/0-9:;<=>?@A-Z\[\\\]\^_`a-z]/','.',$salt);
		$salt=strtr($salt,':;<=>?@[\]^_`','ABCDEFGabcdef');

		$trip=substr(crypt($pw.$randomstring,$salt),-10);
		return $trip;
	}

	function banMessage($board = "*")
	{
	$bandata = $this->isBanned($_SERVER['REMOTE_ADDR'], $board);
				if ($bandata != 0)
				{
				if ($bandata['boards']=="*")
				{
				$boards = 1;
				} else {
				$boards = 0;
				}
				if ($bandata['expires'] != 0)
				{
				$left = floor($bandata['expires'] - time()/(60*60*24));
				} else {
				$left = -1;
				}
				?>
				<html>
	<head>
	<title>Banned</title>
	<link rel="stylesheet" href="./styles/index.css" />
	<link rel="stylesheet" href="./styles/global.css" />
	<link rel="stylesheet" href="./styles/postform.css" />
	</head>
	<body>
	<div id="doc">
	<br /><br />
	<div class="box-outer top-box">
	<div class="box-inner">
	<div class="boxbar"><h2>You are banned ;_;</h2></div>
	<div class="boxcontent">
	<?php
	$imagesDir = './rnd/banned/';
	if (is_dir($imagesDir))
	{
		$images = glob($imagesDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
		$randomImage = $images[array_rand($images)]; 
		if ($return == 1)
		{
			$file .= '<img style="float: right;" src="'.$randomImage.'" alt="Mitsuba" />';
		}
	}
	?>
	<p>You have been <?php if ($left == -1) { echo "<b>permamently</b>"; } ?> <?php if (!empty($bandata['range'])) { echo "<b>range-</b>"; } ?>banned from <b><?php if ($boards == 1) { echo "all "; } else { echo "few "; } ?></b>boards for the following reason:</p>
	<p><?php echo $bandata['reason']; ?></p>
	<p>You were banned on <b><?php echo date("d/m/Y (D) H:i:s", $bandata['created']); ?></b> and your ban expires  
	<b><?php if ($left != -1) { echo " on ".date("d/m/Y (D) H:i:s", $bandata['expires']).", which is <b>".$left."</b> days from now."; } else { echo " never"; }; ?></b>.</p>
	<?php
	$range = 0;
	if (!empty($bandata['range_ip'])) { $range = 1; }
	$appeals = $this->conn->query("SELECT * FROM appeals WHERE ban_id=".$bandata['id']." AND rangeban=".$range);
	if ((($left > 3) || ($left == -1)) && ($appeals->num_rows == 0))
	{
		//You'll be able to appeal this ban in x days.
		//Your appeal has been sent and is waiting until review, you can change it here.
	?>
	<p>According to our server your IP is: <b><?php echo $_SERVER['REMOTE_ADDR']; ?></b></p>
	<p>Because your ban is longer than 3 days in length, you may appeal it in the form below. Please explain why you deserve to be unbanned. Poorly writen, rude or offensive appeals may be declined. E-mail address is optional.</p>
	<p><form action="./imgboard.php" method="POST">
	<input type="hidden" name="mode" value="usrapp" />
	<input type="hidden" name="board" value="<?php echo $board; ?>" />
	<table class="postform">
	<tbody>
	<tr><td class="postBlock">E-mail</td><td><input type="text" name="email" /><input type="submit" value="Submit"></td></tr>
	<tr><td class="postBlock">Message</td><td><textarea style="width: 100%;" rows=6 name="msg"></textarea></td></tr>
	</tbody>
	</table>
	</form></p>
	<?php
	}
	?>
	</div>
	</div>
	</div>
	</body>
	</html>
	<?php
	die();
	}
	}

	function warningMessage()
	{
	$warndata = $this->isWarned($_SERVER['REMOTE_ADDR']);
				if ($warndata != 0)
				{
				?>
				<html>
	<head>
	<title>Banned</title>
	<link rel="stylesheet" href="./styles/index.css" />
	<link rel="stylesheet" href="./styles/global.css" />
	<link rel="stylesheet" href="./styles/postform.css" />
	</head>
	<body>
	<div id="doc">
	<br /><br />
	<div class="box-outer top-box">
	<div class="box-inner">
	<div class="boxbar"><h2>You were issued a warning! ;_;</h2></div>
	<div class="boxcontent">
	<?php
	$imagesDir = './rnd/banned/';
	if (is_dir($imagesDir))
	{
		$images = glob($imagesDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
		$randomImage = $images[array_rand($images)]; 
		if ($return == 1)
		{
			$file .= '<img style="float: right;" src="'.$randomImage.'" alt="Mitsuba" />';
		}
	}
	?>
	<p>You were issued <b>a warning</b> with the following message:</p>
	<p><?php echo $warndata['reason']; ?></p>
	<p>Your warning was issued on <b><?php echo date("d/m/Y (D) H:i:s", $warndata['created']); ?></b>.</p>
	<p>Now that you have seen this message, you should be able to post again. Click <a href="javascript:history.back()">here</a> to return.</p>
	</div>
	</div>
	</div>
	</body>
	</html>
	<?php
	$this->conn->query("UPDATE warnings SET shown=1 WHERE id=".$warndata['id']);
	die();
	}
	}

	function pruneOld($board)
	{
		$board = $this->conn->real_escape_string($board);
		if (!$this->isBoard($board))
		{
			return -16;
		}
		$bdata = $this->getBoardData($board);
		$threads = $this->conn->query("SELECT * FROM posts WHERE resto=0 AND board='".$board."' ORDER BY sticky DESC, lastbumped DESC LIMIT ".(($bdata['pages']+2)*10).", 2000");
		while ($row = $threads->fetch_assoc())
		{
			$files = $this->conn->query("SELECT * FROM posts WHERE filename != '' AND resto=".$row['id']." AND board='".$board."'");
			while ($file = $files->fetch_assoc())
			{
				unlink("./".$board."/src/".$file['filename']);
				unlink("./".$board."/src/thumb/".$file['filename']);
			}
			unlink("./".$board."/src/".$row['filename']);
			unlink("./".$board."/src/thumb/".$row['filename']);
			
			$this->conn->query("DELETE FROM posts WHERE resto=".$row['id']." AND board='".$board."'");
			$this->conn->query("DELETE FROM posts WHERE id=".$row['id']." AND board='".$board."'");
			if ($bdata['hidden'] == 0)
			{
				unlink("./".$board."/res/".$row['id'].".html");
			}
		}
	}

	function parse_time($str) {
		if (empty($str))
			return false;

		if (($time = @strtotime($str)) !== false)
			return $time;

		if (!preg_match('/^((\d+)\s?ye?a?r?s?)?\s?+((\d+)\s?mon?t?h?s?)?\s?+((\d+)\s?we?e?k?s?)?\s?+((\d+)\s?da?y?s?)?((\d+)\s?ho?u?r?s?)?\s?+((\d+)\s?mi?n?u?t?e?s?)?\s?+((\d+)\s?se?c?o?n?d?s?)?$/', $str, $matches))
			return false;

		$expire = 0;

		if (isset($matches[2])) {
			// Years
			$expire += $matches[2]*60*60*24*365;
		}
		if (isset($matches[4])) {
			// Months
			$expire += $matches[4]*60*60*24*30;
		}
		if (isset($matches[6])) {
			// Weeks
			$expire += $matches[6]*60*60*24*7;
		}
		if (isset($matches[8])) {
			// Days
			$expire += $matches[8]*60*60*24;
		}
		if (isset($matches[10])) {
			// Hours
			$expire += $matches[10]*60*60;
		}
		if (isset($matches[12])) {
			// Minutes
			$expire += $matches[12]*60;
		}
		if (isset($matches[14])) {
			// Seconds
			$expire += $matches[14];
		}

		return time() + $expire;
	}
}
?>