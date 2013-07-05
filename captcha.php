<?php
session_start();

if (isset($_GET['t']))
{
	include("libs/cool-php-captcha/simplecaptcha.php");
	$captcha = new SimpleCaptcha();

	$captcha->lineWidth = 3;
	$captcha->scale = 3;
	$captcha->blur = true;
	if (extension_loaded('imagick'))
	{
		$captcha->reduceImageColors = true;
		$captcha->useImageMagick = true;
	}
	$captcha->CreateImage();
} elseif (isset($_GET['i'])) {
	echo "<a style='position: absolute; left: 0; top: 0;' href='?i=".time()."'><img src='?t=".time()."' alt='CAPTCHA'/></a>";
} else {
	echo "<a href='?i=".time()."'>Click here</a>";
}
?>