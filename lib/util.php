<?php

function save($dados) {
	$dados = str_replace(array(' ', "\n", "\r", "\t"), '', $dados);
	$name = '.cache';
	$file = fopen($name, 'w+');
	fwrite($file, $dados);
	fclose($file);
}

function ler() {
	$res = file_get_contents('.cache');
	return $res;
}

function getProxy() {
	return false;
}


function curl($url,$cookies,$post,$referer=null,$header=true, $follow=false,$proxy=true) {
	$user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:54.0) Gecko/20100101 Firefox/54.0';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, $header);
	if(strlen($cookies) > 5) {
		curl_setopt($ch, CURLOPT_COOKIE, $cookies);
	}
	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

	if(isset($referer)){ curl_setopt($ch, CURLOPT_REFERER,$referer); }
	else{ curl_setopt($ch, CURLOPT_REFERER,$url); }
	if ($post){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
	}
	
	if(stristr($url, 'gov.br')) {
		if(!stristr(proxy, ':')) {
			die('sem rede');
		}
		curl_setopt($ch, CURLOPT_PROXY, proxy);
	}

	curl_setopt ($ch, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  FALSE); 
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, timeout);
	$res = curl_exec( $ch);
	curl_close($ch);
	return $res;
}

function getCookies($get) {
	preg_match_all('/Set-Cookie: (.*);/U',$get,$temp);
	$cookie = $temp[1];
	$cookies = implode('; ',$cookie);
	return $cookies;
}

function corta($str, $left, $right) {
	$str = substr ( stristr ( $str, $left ), strlen ( $left ) );
	$leftLen = strlen ( stristr ( $str, $right ) );
	$leftLen = $leftLen ? - ($leftLen) : strlen ( $str );
	$str = substr ( $str, 0, $leftLen );
	return $str;
}

function parseForm($data) {
	$post = array();
	if(preg_match_all('/<input(.*)>/U', $data, $matches))
	{
		foreach($matches[0] as $input)
		{
			if(!stristr($input, "name=")) continue;
			if(preg_match('/name=(".*"|\'.*\')/U', $input, $name))
			{
				$key = substr($name[1], 1, -1);
				if(preg_match('/value=(".*"|\'.*\')/U', $input, $value)) $post[$key] = substr($value[1], 1, -1);
				else $post[$key] = "";
			}
		}
	}
	return $post;
}
