<?php


$config = parse_ini_file(".env");

define('url_path', $config['url_path']);
define('api_cpf',  $config['api_cpf']);
define('proxy',    $config['proxy']);
define('debug',    $config['debug']);
define('ip_debug', $config['ip_debug']);
define('status',   $config['status']);
define('timeout',  $config['timeout']);

if($_SERVER['REMOTE_ADDR'] == ip_debug) {
	if(debug === true) {
		error_reporting(E_ALL);
	}else{
		error_reporting(0);		
	}
}else{
	error_reporting(0);
}

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

function getInfos($nit, $nome, $mae, $data, $cpf) {
	$url     = 'https://www2.dataprev.gov.br/sabiweb/agendamento/escolherAPS.view#sabiweb';
	$post    = 'org.apache.struts.taglib.html.TOKEN=eac4e4f60faa5d724c82a86d84092083&acao=buscaAPSMantenedoras&agenciaAtestadoEletronico=false&ufEscolhida=AC&municipioEscolhido=24003&idAPSEscolhida=7847';
	$ref     = null;
	$cookie  = '';
	$result  = curl($url, null, $post, null, true,false,true);
	$cookie  = getCookies($result);
	$frm     = parseForm($result);
	$data    = str_replace('/','',$data);

	$url     = 'https://www2.dataprev.gov.br/sabiweb/agendamento/formalizarRequerimento.view';
	$ref     = 'https://www2.dataprev.gov.br/sabiweb/agendamento/escolherAPS.view';
	$post    = 'org.apache.struts.taglib.html.TOKEN='.$frm['org.apache.struts.taglib.html.TOKEN'].'&acao=formalizaRequerimento&requerimento.aps.nmUnidadeOrganica=BRASILEIA&requerimento.aps.teEndereco=RUA+ODILOM+PRATAGY%2C+78+&requerimento.aps.nmBairro=CENTRO&requerimento.aps.id=7847&requerimento.aps.apsBI=false&requerimento.aps.municipio.idMunicipioPrev=24003&requerimento.aps.sgUF=AC&requerimento.aps.prevCidade=false&requerimento.aps.csCategoria=4&requerimento.apsBI.nmUnidadeOrganica=&requerimento.apsBI.teEndereco=&requerimento.apsBI.nmBairro=&requerimento.apsBI.id=&requerimento.apsBI.apsBI=false&requerimento.apsBI.municipio.idMunicipioPrev=0&requerimento.apsBI.sgUF=&requerimento.apsBI.prevCidade=false&requerimento.apsBI.csCategoria=0&requerimento.origemRequerimento=4&requerimento.requerente.nit='.$nit.'&requerimento.requerente.nome='.$nome.'&requerimento.requerente.nomeDaMae='.$mae.'&dataNascimento='.$data.'&dac=&valorCNPJouCEI=&dataUltimoTrabalho=&cid=&categoriaTrabalhador=0&requerimento.requerente.qtdDependentes=0: undefined';
	$result = curl($url,$cookie,$post,$ref, true, false, true);
	$dados  = parseForm($result);	
	$exped  = $dados['requerente.orgaoExpedidor.id'];

	if($exped == '1')     { $exped = 'SSP'; }
	elseif($exped == '2') { $exped = 'MIN.AERONAUT'; }
	elseif($exped == '3') { $exped = 'MIN.EXERCITO'; }
	elseif($exped == '4') { $exped = 'MIN.MARINHA'; }
	elseif($exped == '5') { $exped = 'SE/DPMAF'; }
	elseif($exped == '6') { $exped = 'CRA'; }
	elseif($exped == '7') { $exped = 'CRAS'; }
	elseif($exped == '8') { $exped = 'CRB'; }
	elseif($exped == '9') { $exped = 'CRC'; }
	elseif($exped == '10'){ $exped = 'CRECI'; }
	elseif($exped == '11'){ $exped = 'CORECON'; }
	elseif($exped == '12'){ $exped = 'COREN'; }
	elseif($exped == '13'){ $exped = 'CREA'; }
	elseif($exped == '14'){ $exped = 'CONRE'; }
	elseif($exped == '15'){ $exped = 'CRF'; }
	elseif($exped == '16'){ $exped = 'CREFITO'; }
	elseif($exped == '17'){ $exped = 'CRM'; }
	elseif($exped == '18'){ $exped = 'CRMV'; }
	elseif($exped == '19'){ $exped = 'OMBCRE'; }
	elseif($exped == '20'){ $exped = 'CRN'; }
	elseif($exped == '21'){ $exped = 'CRO'; }
	elseif($exped == '22'){ $exped = 'CONRERP'; }
	elseif($exped == '23'){ $exped = 'CRP'; }
	elseif($exped == '24'){ $exped = 'CRQ'; }
	elseif($exped == '25'){ $exped = 'CORE'; }
	elseif($exped == '26'){ $exped = 'OAB'; }
	elseif($exped == '27'){ $exped = 'CRBIO'; }
	elseif($exped == '28'){ $exped = 'CRFA'; }
	elseif($exped == '29'){ $exped = 'CRESS'; }
	elseif($exped == '30'){ $exped = 'CRTR'; }
	elseif($exped == '31'){ $exped = 'DETRAN'; }
	elseif($exped == '32'){ $exped = 'PM'; }
	elseif($exped == '33'){ $exped = 'CBM'; }
	elseif($exped == '99'){ $exped = 'OUTROS'; }
	elseif($exped == '90'){ $exped = 'DOCUMENT. EXPED. EXT'; }
	elseif($exped == '34'){ $exped = 'IBA'; }
	elseif($exped == '35'){ $exped = 'SEDS'; }
	elseif($exped == '36'){ $exped = 'SMDS'; }
	else{$exped = 'Nao definido'; }
				
	$nome      = $dados['requerimento.requerente.nome'];
	$rg        = $dados['requerente.identidade'];
	if(strlen($rg)  < 1) { $rg = '-'; }
	$sexo      = $dados['requerente.sexo'];
	if(strlen($sexo)  < 1) { $sexo = '-'; }
	$endereco  = $dados['requerente.endereco'];
	if(strlen($endereco)  < 1) { $endereco = '-'; }
	$bairro    = $dados['requerente.bairro'];
	if(strlen($bairro)  < 1) { $bairro = '-'; }
	$cep       = $dados['requerente.cep'];
	if(strlen($cep)  < 1) { $cep = '-'; }
	$municipio = '-';
	$uf        = $dados['requerente.ufCT'];
	if(strlen($uf)  < 2) { $uf = '-'; }
	$ufRg      = $dados['requerente.ufRG'];
	if(strlen($ufRg)  < 1) { $urRg = '-'; }
	$orexp     = $exped;
	if(strlen($orgexp)  < 1) { $orgexp = '-'; }
	$carTrab   = $dados['requerente.carteiraTrabalho'];
	if(strlen($cartTrab)  < 1) { $cartTrab = '-'; }
	$carSeri   = $dados['requerente.serie'];
	if(strlen($carSeri)  < 1) { $carSeri = '-'; }
	$ufCt      = $dados['requerente.ufCT'];
	if(strlen($ufCt)  < 1) { $ufCt = '-'; }
	$nome      = $dados['requerimento.requerente.nome'];
	$mae       = $dados['requerimento.requerente.nomeDaMae'];
	if(strlen($mae)  < 2) { $mae = '-'; }

	if(strlen($nome) < 5){ die('Ocorreu um erro, tente novamente em breve. code[002]'); }

	echo "<dados>
			<cpf>{$cpf}</cpf>
			<nit>{$nit}</nit>
			<nome>{$nome}</nome>
			<mae>{$mae}</mae>
			<rg>{$rg}</rg>
			<ufrg>{$ufRg}</ufrg>
			<orgaorg>{$orexp}</orgaorg>
			<sexo>{$sexo}</sexo>
			<endereco>
				<rua>{$endereco}</rua>
				<bairro>{$bairro}</bairro>
				<cep>{$cep}</cep>
				<municipio>{$municipio}</municipio>
				<uf>{$uf}</uf>
			</endereco>
			<carteiratrab>
				<carteira>{$carTrab}</carteira>
				<nserie>{$carSeri}</nserie>
				<uf>{$ufCt}</uf>
			</carteiratrab>
		  </dados>
		 ";
	die;
}

function getCaptcha($url, $url_dois, $url_tres, $url_cinco, $cookie) {

	$um = curl($url, null, null, null);

	if(strlen($um) < 10) {
		$um = curl($url, null, null, null);
		if(strlen($um) < 10) {
			die('rede ou offline');
		}
	}
	
	$cookie    = getCookies($um);
	$frm 	   = parseForm($um);
	$dois	   = curl($url, $cookie, null, $url);

	$post = array(
		'formEscolhaPerfis' => 'formEscolhaPerfis',
		'csrfsalt' 				=> $frm['csrfsalt'],
		'javax.faces.ViewState' => $frm['javax.faces.ViewState'],
		'formEscolhaPerfis:perfilCidadao' => 'formEscolhaPerfis:perfilCidadao'
	);

	$dois = curl($url, $cookie, http_build_query($post), $url);
	$tres = curl($url_tres, $cookie, null, $url);
	$frm  = parseForm($tres);
	$post = array(
			'menu' => 'menu',
			'csrfsalt' => $frm['csrfsalt'],
			'javax.faces.ViewState' => $frm['javax.faces.ViewState'],
			'menu:inscricaoFiliadoCidadao' => 'menu:inscricaoFiliadoCidadao'
	);

	$quatro = curl($url_tres, $cookie, $post, $url_dois);
	$cinco  = $url_cinco;
	$result = curl($url_cinco, $cookie, null, null, true, true, true);

	$img = corta($result, 'cnisinternet/api/imagem?d=', '"');
	$img = substr($img, 0, -1);
	$frm['captcha_campo_desafio'] = $img;
	$frm = json_encode($frm);
	$url = url_path . 'cnisinternet/api/imagem?d=' .$img;

	$result = curl($url, $cookie, null, null, true, true, true);
	
	$remove = corta($result, 'HTTP/1.1', 'Content-Language: en');
	$result = str_replace($remove, '', $result);
	$result = str_replace('HTTP/1.1Content-Language: en', '', $result);		
	$result = str_replace('HTTP/1.0 200 Connection established', '', $result);
	$result = trim(rtrim($result));
	$img    = chunk_split(base64_encode($result));
	$img    = "data:image/png;base64,".$img;
	if(strlen($img) < 50) {
		save('');
	}
	
	$result = array('captcha'=>$img, 'cookie'=> $cookie, 'frm'=> base64_encode($frm));
	return $result;
}

$url 	   = url_path . 'cnisinternet/faces/pages/perfil.xhtml';
$url_dois  = url_path . 'cnisinternet/faces/pages/index.xhtml';
$url_tres  = url_path . 'cnisinternet/faces/pages/index.xhtml';
$url_cinco = url_path . 'cnisinternet/captcha-load/';

if(isset($_POST['captcha'])) {
	$cpf     = $_POST['cpf'];
	$cpf     = str_replace(array('.', '-', ' '), '', $cpf);
	$captcha = $_POST['captcha'];
	$cookie  = base64_decode($_POST['cookie']);
	$post 	 = json_decode(base64_decode($_POST['token']), true);

    $url = api_cpf . $cpf;

	$infos = curl($url, null, null, null, false,false,false);

	$infos = simplexml_load_string($infos);
	$infos = json_encode($infos);


	$infos = json_decode($infos, TRUE);
	if(!array_key_exists('cpf', $infos)) {
		die('nada encontrado para o cpf informado.');
	}

	$url     = url_path . 'cnisinternet/faces/pages/inscricao/filiado/identificar.xhtml';
	
	$nome    = trim(rtrim($infos['nome']));
	$mae     = trim(rtrim($infos['mae']));
	$cpf     = trim(rtrim($infos['cpf']));
	$data    = trim(rtrim($infos['nascimento']));
	
	$post = array(
		'formse'   => 'formse',
		'csrfsalt' => $post['csrfsalt'],
		'formse:nomeFiliado' => ($nome),
		'formse:nomeMae' 	 => ($mae),
		'formse:dataNascimento_input' => ($data),
		'formse:cpf'		 => $cpf,
		'captcha_campo_desafio'  => $post['captcha_campo_desafio'],
		'captcha_campo_resposta' => $captcha,
		'javax.faces.ViewState'	 => $post['javax.faces.ViewState'],
		'formse:continuar'		 => 'formse:continuar'
	);


	$result = curl($url, $cookie, http_build_query($post), $url, false,true);

	if(stristr($result, 'para efetuar recolhimentos. NIT:'))
	{
		$nit   = corta($result, 'para efetuar recolhimentos. NIT:', '<');
		if(stristr($nit, '"'))
		{
			$nit = explode('"', $nit);
			$nit = $nit[0];
		}

		$nit   = trim(rtrim($nit));
		$dados = array('cpf'=> $cpf, 'nome'=>$nome, 'mae'=> $mae, 'data'=> $data, 'nit'=> $nit);
	}
	elseif(stristr($result, 'Erro na valida'))
	{
		$dados = array('error'=>'Erro na validação do captcha.');
	}
	elseif(stristr($result, 'o corresponde ao c'))
	{
		$dados = array('error'=>'Erro na validação do captcha.');
	}
	elseif(stristr($result, 'existe uma inscri'))
	{
		$dados = array('error'=> 'ja existe uma inscricao para o cpf.');
	}
	elseif(stristr($result, 'A resposta não corresponde ao desafio gerado.'))
	{
		$dados = array('error' => 'captcha invalido.');
	}
	elseif(stristr($result, 'lido ou expirou.')) {
		$dados = array('error' => 'captcha invalido.');		
	} elseif(stristr($result, 'Erro no processamento')) {
		$dados = array('error' => 'Erro no processamento, tente novamente.');		
	}

	if(isset($dados['cpf']))
	{
		$dados = getInfos($dados['nit'], $dados['nome'], $dados['mae'], $dados['data'], $dados['cpf']);
	}
	elseif(isset($dados['error']))
	{
		echo $dados['error'];
	}
}
else
{
	$result = getCaptcha($url, $url_dois, $url_tres, $url_cinco, null);

	if(isset($result['captcha'])) {
		if(!stristr($result['captcha'], 'data:image/png;base')) {
			$result = getCaptcha($url, $url_dois, $url_tres, $url_cinco, null);
		}
	}

	if($_SERVER['REMOTE_ADDR'] == ip_debug and debug == true) {
		echo '
		<form action="" method="POST">
			<input type="text" name="cpf">
			<br>
			<input type="hidden" name="token" value="'.$result['frm'].'"/>

			<input type="hidden" name="cookie" value="'.base64_encode($result['cookie']).'"/>
			<img src="'.($result['captcha']).'"/>
			<br/>
			<input type="text" name="captcha">
			<br/>
			<input type="submit">
		</form>
		';		
		die;
	}

	if($result != false) {
		echo "<captcha><cookie>".base64_encode($result['cookie'])."</cookie><captcha>{$result['captcha']}</captcha><token>{$result['frm']}</token></captcha>";
	
	}else {
		echo "<captcha><error>.</error></captcha>";
	}		
	die;

}