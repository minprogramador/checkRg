<?php

$config = parse_ini_file(".env");

define('url_path', $config['url_path']);
define('api_cpf',  $config['api_cpf']);
define('proxy',    $config['proxy']);
define('debug',    $config['debug']);
define('ip_debug', $config['ip_debug']);
define('status',   $config['status']);
define('timeout',  $config['timeout']);

if($_SERVER['REMOTE_ADDR'] == ip_debug and debug == true) {
	error_reporting(E_ALL);
}else{
	error_reporting(0);
}

require('./lib/util.php');
require('./lib/functions.php');
