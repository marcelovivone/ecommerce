<?php

use \Tila\PageAdmin;
use \Tila\Model\User;

// rota de PageAdmin
$app->get("/admin", function() {

	// quando for acessar a página de admin, verificar se o usuário está logado
	// e se tem acesso à administração
	User::verifyLogin();

	// __construct (header)
	$page = new PageAdmin();

	// body
	$page->setTpl("index");

});

// rota de página de Login
$app->get("/admin/login", function() {
	// __construct (header)
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	// body
	$page->setTpl("login");

});

// rota de validação de login
$app->post("/admin/login", function() {

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;

});

// rota de Logout
$app->get("/admin/logout", function() {

	User::Logout();

	header("Location: /admin/login");
	exit;

});

// rota de recuperação de senha (forgot)
$app->get("/admin/forgot", function() {

	// __construct (header)
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	// body
	$page->setTpl("forgot");

});

// rota de salvar senha de recuperação no banco (forgot)
$app->post("/admin/forgot", function() {
	
	$user = User::getForgot($_POST["email"]);

	header("location: /admin/forgot/sent");
	exit;

});

// rota de janela de senha enviada (forgot)
$app->get("/admin/forgot/sent", function() {

	// __construct (header)
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	// body
	$page->setTpl("forgot-sent");

});

// rota de janela de reset de senha
$app->get("/admin/forgot/reset", function() {

	$user = User::validForgotDecrypt($_GET["code"], $_GET["iv"]);

	// __construct (header)
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	// body
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"],
		"iv"=>$_GET["iv"]
	));

});

// rota de salvar a senha de reset no banco
$app->post("/admin/forgot/reset", function() {

	$forgot = User::validForgotDecrypt($_POST["code"], $_POST["iv"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	// __construct (header)
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	// body
	$page->setTpl("forgot-reset-success");

});

?>