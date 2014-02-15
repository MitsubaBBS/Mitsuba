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
	echo "<a style='position: absolute; left: 0; top: 0;' href='?i=".time()."'><img style='width: 100%;' src='?t=".time()."' alt='CAPTCHA'/></a>";
} else {
	echo "<a style='display: block; padding-top: 10px; text-align: center; text-decoration: none; color: #FFF; font-size: 30px; text-shadow: -1px 0px #000, 0px 1px #000, 1px 0px #000, 0px -1px #000' href='?i=".time()."'>Click here</a>";
}
?>