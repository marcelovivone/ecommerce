<?php

use \Tila\PageAdmin;
use \Tila\Model\User;

/* rotas para usuários */

// rota para tela de lista
$app->get("/admin/users", function() {

	User::verifyLogin();

	$users = User::listAll();

	// __construct (header)
	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$users
	));

});

// rota para tela de criação
$app->get("/admin/users/create", function() {

	User::verifyLogin();

	// __construct (header)
	$page = new PageAdmin();

	$page->setTpl("users-create");

});

// método não foi chamado delete porque para o slim receber um método com esse nome,
// ele tem que ser enviado via post e ter um campo a mais chamado _method, escrito delete.
// além disso, na maioria dos servidores WEB, o método delete é desabilitado por padrão
//$app->delete('/admin/users/:iduser', function($iduser) {

// rota para excluir o registro do banco de dados
// tem que estar acima do método de alteração (método abaixo) porque a rota é a mesma,
// somente acrescentando o delete no fim. Caso esse método estivesse abaixo do método
// de alteração, o slim pararia no :iduser também para o delete
// ou seja, as rotas com maior caminho têm obrigatoriamente que preceder as de caminho
// mais curto
$app->get("/admin/users/delete/{iduser}", function($request, $response, $args) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$args["iduser"]);

	$user->delete();

	header("Location: /admin/users");
	exit;

});

// rota para tela de alteração
// por boa prática, já deve passar o id do usuário na rota
$app->get("/admin/users/{iduser}", function($request, $response, $args) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$args["iduser"]);

	// __construct (header)
	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});

// rota para criar o registro no banco de dados
$app->post("/admin/users/create", function() {

	User::verifyLogin();

	$user = new User();

	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, ["cost"=>12]);
	
	$_POST["inadmin"] = isset($_POST["inadmin"]) ? 1 : 0;

	$user->setData($_POST);

	$user->insert();

	header("Location: /admin/users");
	exit;

});

// rota para alterar os dados no banco de dados
//$app->post("/admin/users/:iduser", function($iduser) {
$app->post("/admin/users/{iduser}", function($request, $response, $args) {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = isset($_POST["inadmin"]) ? 1 : 0;

	$user->get((int)$args["iduser"]);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;

});

?>