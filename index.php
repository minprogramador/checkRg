<?php

require('config.php');

if(isset($_POST['config'])) {

	if($_POST['config'] == 'list'){

		$config = parse_ini_file(".env");
		header("Content-type: application/json; charset=utf-8");
		echo json_encode($config);

	}elseif($_POST['config'] == 'edit'){

		$urlpath  = $_POST['url_path'] ?? false;
		$api_cpf  = $_POST['api_cpf'] ?? false;
		$proxy    = $_POST['proxy'] ?? false;
		$debug    = $_POST['debug'] ?? false;
		$ip_debug = $_POST['ip_debug'] ?? false;
		$status   = $_POST['status'] ?? false;
		$timeout  = $_POST['timeout'] ?? false;

		$config = parse_ini_file(".env");

		if($urlpath) { $config['url_path'] = $urlpath; }
		if($api_cpf) { $config['api_cpf'] = $api_cpf; }
		if($proxy) { $config['proxy'] = $proxy; }
		if($debug) { $config['debug'] = $debug; }
		if($ip_debug) { $config['ip_debug'] = $ip_debug; }
		if($status)  { $config['status'] = $status; }
		if($timeout) { $config['timeout'] = $timeout; }

		$config = arr2ini($config);

		$fp = fopen('.env', 'w');
		fwrite($fp, $config);
		fclose($fp);

		header("Content-type: application/json; charset=utf-8");
		$config = parse_ini_file(".env");
		echo json_encode($config);
	}
}

