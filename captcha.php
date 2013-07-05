<?php
session_start();

include("libs/cool-php-captcha/simplecaptcha.php");

$captcha = new SimpleCaptcha();

$captcha->lineWidth = 3;
$captcha->scale = 3;
$captcha->blur = true;
if (extension_loaded('imagick'))
{
	$captcha->useImageMagick = true;
}
$captcha->CreateImage();
?>