<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\App;
use \Tila\Page;
use \Tila\PageAdmin;
use \Tila\Model\User;

//$app = new \Slim\App;
//$app->config('debug', true);
$app = new App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

//use \Tila\DB\Sql;

// rota de Page
$app->get('/', function() {

	// __construct (header)
	$page = new Page();

	// body
	$page->setTpl("index");

});

// rota de PageAdmin
$app->get('/admin', function() {

	// quando for acessar a página de admin, verificar se o usuário está logado
	User::verifyLogin();

	// __construct (header)
	$page = new PageAdmin();

	// body
	$page->setTpl("index");

});

// rota de Login
$app->get('/admin/login', function() {

	// __construct (header)
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	// body
	$page->setTpl("login");

});

// rota de formulário
$app->post('/admin/login', function() {

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;

});

// rota de Logout
$app->get('/admin/logout', function() {

	User::Logout();

	header("Location: /admin/login");
	exit;

});


$app->run();

 ?>