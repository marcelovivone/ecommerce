<?php

use \Tila\PageAdmin;
use \Tila\Model\User;

/* rotas para usuários */

// rota para página de alteração de senha
$app->get("/admin/users/{iduser}/password", function($request, $response, $args) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$args["iduser"]);

	// __construct (header)
	$page = new PageAdmin();

	$page->setTpl("users-password", array(
		'user'=>$user->getValues(),
		'msgError'=>User::getError(),
		'msgSuccess'=>User::getSuccess()
	));

});

// rota para salvar a nova senha no banco de dados
$app->post("/admin/users/{iduser}/password", function($request, $response, $args) {

	User::verifyLogin();

	// valida o preenchimento dos campos
	if (!isset($_POST['despassword']) || $_POST['despassword'] === '') {

		User::setError('Preencha a nova senha.');

		header('Location: /admin/users/' . $args["iduser"] . '/password');
		exit;

	}

	if (!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm'] === '') {

		User::setError('Preencha a senha de confirmação.');

		header('Location: /admin/users/' . $args["iduser"] . '/password');
		exit;

	}

	if ($_POST['despassword'] !== $_POST['despassword-confirm']) {

		User::setError('As senhas nova e de confirmação devem ser iguais.');

		header('Location: /admin/users/' . $args["iduser"] . '/password');
		exit;

	}

	$user = new User();

	$user->get((int)$args["iduser"]);

	$user->setPassword(User::getPasswordHash($_POST['despassword']));

	User::setSuccess('Senha alterada com sucesso.');

	header('Location: /admin/users/' . $args["iduser"] . '/password');
	exit;

});

// rota para página de lista
$app->get("/admin/users", function() {

	User::verifyLogin();

	$search = isset($_GET['search']) ? $_GET['search'] : "";

	$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

	if($search == '') {

		$pagination = User::getPage($page);

	} else {

		$pagination = User::getPageSearch($search, $page);

	}

	$pages = [];

	for ($pageNumber = 1; $pageNumber <= $pagination['pages']; $pageNumber++)
	{

		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$pageNumber,
				'search'=>$search
			]),
			'text'=>$pageNumber
		]);

	}

	// __construct (header)
	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
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