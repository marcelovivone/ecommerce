<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\App;
use \Tila\Page;
use \Tila\PageAdmin;
use \Tila\Model\User;

$app = new App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

// rota de Page
$app->get("/", function() {

	// __construct (header)
	$page = new Page();

	// body
	$page->setTpl("index");

});

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

// rota de Login
$app->get("/admin/login", function() {
	// __construct (header)
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	// body
	$page->setTpl("login");

});

// rota de formulário
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

// rota para tela de lista de usuários
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

// rota para excluir os dados no banco de dados
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


// rota para criar os dados no banco de dados
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

$app->run();

?>