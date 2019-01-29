<?php

require('config.php');

if(isset($_POST['captcha'])) {
	$cpf     = $_POST['cpf'];
	$captcha = $_POST['captcha'];
	$cookie  = $_POST['cookie'];
	$token   = $_POST['token'];
	$rescap  = resolveCaptcha($cpf, $captcha, $cookie, $token);
	echo $rescap;

} else {
	$captcha = runCaptcha();
	echo $captcha;
}