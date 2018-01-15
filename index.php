<?php 

require_once("vendor/autoload.php");

use \Slim\App;
use \Tila\Page;

//$app = new \Slim\App;
//$app->config('debug', true);
$app = new App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

//use \Tila\DB\Sql;

// rota
$app->get('/', function() {

	// __construct (header)
	$page = new Page();

	// body
	$page->setTpl("index");

});

$app->run();

 ?>