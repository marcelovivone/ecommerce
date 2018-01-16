<?php 

require_once("vendor/autoload.php");

use \Slim\App;
use \Tila\Page;
use \Tila\PageAdmin;

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

	// __construct (header)
	$page = new PageAdmin();

	// body
	$page->setTpl("index");

});

$app->run();

 ?>