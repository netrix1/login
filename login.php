<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

header('Content-Type: text/html; charset=utf-8');

// Constante com a quantidade de tentativas aceitas
define('TENTATIVAS_ACEITAS', 50); 

// Constante com a quantidade minutos para bloqueio
define('MINUTOS_BLOQUEIO', 120); 

// Constante para HABILITAR ou não a vefificação da origem do login (1 para habilitar; 0 para desabilitar)
define('VERIFICA_PAGINA', 0);

// Constante contendo pagina que pode receber o formulário de login
define('PAGINA', 'http://localhost:8090/login/index.php');

// Constante contendo pagina que pode receber o formulário de login
define('PAGINA2', 'http://localhost:8090/login/');

// Require da classe de conexão
require 'conexao.php';


// Dica 1 - Verifica se a origem da requisição é do mesmo domínio da aplicação
if (VERIFICA_PAGINA==1){
	if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != PAGINA){
		//$retorno = array('codigo' => "0", 'mensagem' => 'Origem da requisição não autorizada!');
		//echo json_encode($retorno);

		exit();
	};
};

// Instancia Conexão PDO
$conexao = Conexao::getInstance();

// Recebe os dados do formulário
$login = (isset($_POST['login'])) ? $_POST['login'] : '' ;
$senha = (isset($_POST['pass'])) ? $_POST['pass'] : '' ;


// Dica 2 - Validações de preenchimento e-mail e senha se foi preenchido o e-mail
if (empty($login)){
	//$retorno = array('codigo' => "0", 'mensagem' => 'Preencha seu e-mail!');
	//echo json_encode($retorno);
	exit();
};

if (empty($senha)){
	//$retorno = array('codigo' => "0", 'mensagem' => 'Preencha sua senha!');
	//echo json_encode($retorno);
	exit();
};


// Dica 3 - Verifica se o usuário já excedeu a quantidade de tentativas erradas do dia
$sql = "SELECT count(*) AS tentativas, MINUTE(TIMEDIFF(NOW(), MAX(data_hora))) AS minutos ";
$sql .= "FROM tab_userlogin_error WHERE ip = ? and DATE_FORMAT(data_hora,'%Y-%m-%d') = ? AND bloqueado = ?";
$stm = $conexao->prepare($sql);
$stm->bindValue(1, $_SERVER['REMOTE_ADDR']);
$stm->bindValue(2, date('Y-m-d'));
$stm->bindValue(3, 'SIM');
$stm->execute();
$retorno = $stm->fetch(PDO::FETCH_OBJ);

if (!empty($retorno->tentativas) && intval($retorno->minutos) <= MINUTOS_BLOQUEIO){
	$_SESSION['tentativas'] = 0;
	//$retorno = array('codigo' => "0", 'mensagem' => 'Você excedeu o limite de '.TENTATIVAS_ACEITAS.' tentativas, login bloqueado por '.MINUTOS_BLOQUEIO.' minutos!');
	//echo json_encode($retorno);
	$error='Você excedeu o limite de '.TENTATIVAS_ACEITAS.' tentativas, login bloqueado por '.MINUTOS_BLOQUEIO.' minutos!';
	setcookie("msgerro", $error, time()+3600);
	header('Location: /login/index.php');
	exit();
};


// Dica 4 - Válida os dados do usuário com o banco de dados
$sql = 'SELECT id, nome, senha, email FROM tab_usuario WHERE nome = ? AND status = ? LIMIT 1';
$stm = $conexao->prepare($sql);
$stm->bindValue(1, $login);
$stm->bindValue(2, 'A');
$stm->execute();
$retorno = $stm->fetch(PDO::FETCH_OBJ);


// Dica 5 - Válida a senha utlizando a API Password Hash
if(!empty($retorno) && password_verify($senha, $retorno->senha)){
	$_SESSION['id'] = $retorno->id;
	$_SESSION['nome'] = $retorno->nome;
	$_SESSION['email'] = $retorno->email;
	$_SESSION['tentativas'] = 0;
	$_SESSION['logado'] = 'SIM';
}else{
	$_SESSION['logado'] = 'NAO';
	$_SESSION['tentativas'] = (isset($_SESSION['tentativas'])) ? $_SESSION['tentativas'] += 1 : 1;
	$bloqueado = ($_SESSION['tentativas'] == TENTATIVAS_ACEITAS) ? 'SIM' : 'NAO';

	// Dica 6 - Grava a tentativa independente de falha ou não
	$sql = 'INSERT INTO tab_userlogin_error (ip, email, senha, origem, bloqueado) VALUES (?, ?, ?, ?, ?)';
	$stm = $conexao->prepare($sql);
	$stm->bindValue(1, $_SERVER['REMOTE_ADDR']);
	$stm->bindValue(2, $login);
	$stm->bindValue(3, $senha);
	$stm->bindValue(4, $_SERVER['HTTP_REFERER']);
	$stm->bindValue(5, $bloqueado);
	$stm->execute();
};


// Se logado envia código 1, senão retorna mensagem de erro para o login
if ($_SESSION['logado'] == 'SIM'){
	//$retorno = array('codigo' => "1", 'mensagem' => 'Logado com sucesso!');
	//echo json_encode($retorno);
	$sql = 'INSERT INTO `tab_userlogin_sucess` (`ip`, `login`, `origem`) VALUES (?, ?, ?)';

	$stm = $conexao->prepare($sql);
	$stm->bindValue(1, $_SERVER['REMOTE_ADDR']);
	$stm->bindValue(2, $login);
	$stm->bindValue(3, $_SERVER['HTTP_REFERER']);
	$stm->execute();
	//	header('Location: /login/home.php');

	var_dump($_SESSION);
	echo "Consegiu Logar!!!";
	//exit();
}else{
	if ($_SESSION['tentativas'] == TENTATIVAS_ACEITAS){
		//$retorno = array('codigo' => "0", 'mensagem' => 'Você excedeu o limite de '.TENTATIVAS_ACEITAS.' tentativas, login bloqueado por '.MINUTOS_BLOQUEIO.' minutos!');
		//echo json_encode($retorno);
		$error='Você excedeu o limite de '.TENTATIVAS_ACEITAS.' tentativas, login bloqueado por '.MINUTOS_BLOQUEIO.' minutos!';
		setcookie("msgerro", $error, time()+3600);
		header('Location: /login/index.php');
		exit();
	}else{
		//$retorno = array('codigo' => "0", 'mensagem' => 'Usuário não autorizado, você tem mais '. (TENTATIVAS_ACEITAS - $_SESSION['tentativas']) .' tentativa(s) antes do bloqueio!');
		//echo json_encode($retorno);
		$error='Usuário não autorizado, você tem mais '. (TENTATIVAS_ACEITAS - $_SESSION['tentativas']) .' tentativa(s) antes do bloqueio!';
		setcookie("msgerro", $error, time()+3600);
		header('Location: /login/index.php');
		exit();
	};
};