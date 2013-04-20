<?php
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

function getBoardData($conn, $short)
{
	$result = $conn->query("SELECT * FROM boards WHERE short='".$conn->real_escape_string($short)."'");
	if ($result->num_rows == 1)
	{
		return $result->fetch_assoc();
	} else {
		return 0; //board not found
	}
}

function isBoard($conn, $short)
{
	$result = $conn->query("SELECT * FROM boards WHERE short='".$conn->real_escape_string($short)."'");
	if ($result->num_rows == 1)
	{
		return 1;
	} else {
		return 0;
	}
}

function getConfig($conn)
{
	$result = $conn->query("SELECT * FROM config;");
	$array = array();
	while ($row = $result->fetch_assoc())
	{
		$array[$row['name']] = $row['value'];
	}
	return $array;
}

function updateConfigValue($conn, $name, $value)
{
	$name = $conn->real_escape_string($name);
	$value = $conn->real_escape_string($value);
	$conn->query("UPDATE config SET value='".$value."' WHERE name='".$name."';");
}

function getConfigValue($conn, $name)
{
	$name = $conn->real_escape_string($name);
	$result = $conn->query("SELECT * FROM config WHERE name='".$name."';");
	if ($result->num_rows == 1)
	{
		return $result->fetch_assoc();
	} else {
		return 0;
	}
}

function thumb($board,$filename,$s=250){
	$extension = getGraphicsExtension();
	
	if ($extension == "gd")
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
		$rcolor = imagecolorallocate($im_out, 255, 255, 238);
		imagecolortransparent($im_out, $rcolor);
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
	} elseif ($extension == "imagick")
	{
		$fname='./'.$board.'/src/'.$filename;
		$thumb_dir = './'.$board.'/src/thumb/'; //thumbnail directory
		$width = $s; //output width
		$height = $s; //output height
		$img = new Imagick($fname);
		foreach($img as $frame)
		{
			$frame->scaleImage($width, $height, true);
		}
		$img->setImageCompressionQuality(60); 
		$img->writeImages($thumb_dir.$filename, true);
		$ig = $img->getImageGeometry();
		$img->destroy();
		return $ig;
	}
}

function delTree($dir) { 
   $files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) { 
      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
    } 
    return rmdir($dir); 
  } 


function isWhitelisted($conn, $ip)
{
	$whitelist = $conn->query("SELECT * FROM whitelist WHERE ip='".$ip."' ORDER BY id DESC LIMIT 0, 1");
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
  
function isBanned($conn, $ip, $board)
{
	if (isWhitelisted($conn, $ip) >= 1)
	{
		return 0;
	}
	
	$ipbans = $conn->query("SELECT * FROM bans WHERE ip='".$ip."' AND (expires>".time()." OR expires=0) ORDER BY expires DESC LIMIT 0, 1;");
	$rangebans = $conn->query("SELECT * FROM rangebans WHERE INET_ATON('".$ip."') BETWEEN start_ip AND end_ip AND (expires>".time()." OR expires=0) ORDER BY expires DESC LIMIT 0, 1;");
	$ipbandata = null;
	$rangebandata = null;
	$bandata = null;
	
	if ($ipbans->num_rows == 1)
	{
		$ipbandata = $ipbans->fetch_assoc();
	}
	
	if ($rangebans->num_rows == 1)
	{
		$rangebandata = $rangebans->fetch_assoc();
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
		$boards = explode(",", $bandata['boards']);
		if (in_array($board, $boards))
		{
			return $bandata;
		} else {
			return 0;
		}
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

function processName($conn, $string)
{
	$arr = array();
	$new = $string;
	$new = str_replace("##", "#", $new);
	$exploded = explode("#", $new, 2);
	if (count($exploded)>1)
	{
		$arr['name'] = $exploded[0];
		$arr['trip'] = mktripcode($exploded[1]);
	} else {
		$arr['name'] = $new;
		$arr['trip'] = "";
	}
	$arr['name'] = $conn->real_escape_string($arr['name']);
	$arr['name'] = htmlspecialchars($arr['name']);
	return $arr;
}

function processString($conn, $string)
{
	$new = $string;
	$new = $conn->real_escape_string($new);
	$new = htmlspecialchars($new);
	
	if ($name == 1)
	{
		$new = str_replace("##", "#", $new);
		$exploded = explode("#", $new, 2);
		if (count($exploded)>1)
		{
			$arr = array();
			$arr['name'] = $exploded[0];
			$arr['trip'] = mktripcode($exploded[1]);
			return $arr;
		}
	}
	return $new;
}

function preprocessComment($conn, $string)
{
	$new = str_replace("\r", "", $string);
	$new = $conn->real_escape_string($new);
	return $new;
}

function isImage($path)
{
	if (function_exists("finfo_file"))
	{
		$finfo = finfo_open();
		$mime = finfo_file($finfo, $path, FILEINFO_MIME_TYPE);
		if (($mime == "image/jpeg") || ($mime == "image/png") || ($mime == "image/gif"))
		{
			return true;
		}
	} elseif (function_exists("mime_content_type"))
	{
		$mime = mime_content_type($path);
		if (($mime == "image/jpeg") || ($mime == "image/png") || ($mime == "image/gif"))
		{
			return true;
		}
	} elseif (function_exists("getimagesize")) {
		$a = getimagesize($path);
		$image_type = $a[2];

		if(in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG)))
		{
			return true;
		}
	} else {
		return false;
	}
	return false;
}


function mktripcode($pw)
{
    $pw=mb_convert_encoding($pw,'SJIS','UTF-8');
    $pw=str_replace('&','&amp;',$pw);
    $pw=str_replace('"','&quot;',$pw);
    $pw=str_replace("'",'&#39;',$pw);
    $pw=str_replace('<','&lt;',$pw);
    $pw=str_replace('>','&gt;',$pw);
    
    $salt=substr($pw.'H.',1,2);
    $salt=preg_replace('/[^.\/0-9:;<=>?@A-Z\[\\\]\^_`a-z]/','.',$salt);
    $salt=strtr($salt,':;<=>?@[\]^_`','ABCDEFGabcdef');
    
    $trip=substr(crypt($pw,$salt),-10);
    return $trip;
}

function banMessage($conn, $board)
{
$bandata = isBanned($conn, $_SERVER['REMOTE_ADDR'], $board);
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
<p>You have been <?php if ($left == -1) { echo "<b>permamently</b>"; } ?> banned from <b><?php if ($boards == 1) { echo "all "; } else { echo "few "; } ?></b>boards for the following reason:</p>
<p><?php echo $bandata['reason']; ?></p>
<p>You were <?php if (!empty($bandata['start_ip'])) { echo "<b>range-</b>"; } ?>banned on <b><?php echo date("d/m/Y (D) H:i:s", $bandata['created']); ?></b> and your ban expires  
<b><?php if ($left != -1) { echo " on ".date("d/m/Y (D) H:i:s", $bandata['expires']).", which is <b>".$left."</b> days from now."; } else { echo " never"; }; ?></b>.</p>
<?php
$range = 0;
if (!empty($bandata['start_ip'])) { $range = 1; }
$appeals = $conn->query("SELECT * FROM appeals WHERE ban_id=".$bandata['id']." AND rangeban=".$range);
if ((($left > 3) || ($left == -1)) && ($appeals->num_rows == 0))
{
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


function pruneOld($conn, $board)
{
	$board = $conn->real_escape_string($board);
	if (!isBoard($conn, $board))
	{
		return -16;
	}
	$bdata = getBoardData($conn, $board);
	$threads = $conn->query("SELECT * FROM posts WHERE resto=0 AND board='".$board."' ORDER BY sticky DESC, lastbumped DESC LIMIT ".(($bdata['pages']+2)*10).", 2000");
	while ($row = $threads->fetch_assoc())
	{
		$files = $conn->query("SELECT * FROM posts WHERE filename != '' AND resto=".$row['id']." AND board='".$board."'");
		while ($file = $files->fetch_assoc())
		{
			unlink("./".$board."/src/".$file['filename']);
			unlink("./".$board."/src/thumb/".$file['filename']);
		}
		unlink("./".$board."/src/".$row['filename']);
		unlink("./".$board."/src/thumb/".$row['filename']);
		
		$conn->query("DELETE FROM posts WHERE resto=".$row['id']." AND board='".$board."'");
		$conn->query("DELETE FROM posts WHERE id=".$row['id']." AND board='".$board."'");
		if ($bdata['hidden'] == 0)
		{
			unlink("./".$board."/res/".$row['id'].".html");
		}
	}
}
?>