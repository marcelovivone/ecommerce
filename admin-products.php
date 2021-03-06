<?php

use \Tila\Page;
use \Tila\PageAdmin;
use \Tila\Model\User;
use \Tila\Model\Product;

/* rotas para produtos */

// rota para tela de lista
$app->get("/admin/products", function() {

	User::verifyLogin();

	$search = isset($_GET['search']) ? $_GET['search'] : "";

	$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

	if($search == '') {

		$pagination = Product::getPage($page);

	} else {

		$pagination = Product::getPageSearch($search, $page);

	}

	$pages = [];

	for ($pageNumber = 1; $pageNumber <= $pagination['pages']; $pageNumber++)
	{

		array_push($pages, [
			'href'=>'/admin/products?'.http_build_query([
				'page'=>$pageNumber,
				'search'=>$search
			]),
			'text'=>$pageNumber
		]);

	}

	// __construct (header)
	$page = new PageAdmin();

	// body
	$page->setTpl("products", array(
		"products"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	));

});

// rota para tela de cadastro
$app->get("/admin/products/create", function() {

	User::verifyLogin();

	// __construct (header)
	$page = new PageAdmin();

	// body
	$page->setTpl("products-create");

});

// rota para criar registro no banco de dados
$app->post("/admin/products/create", function() {

	User::verifyLogin();

	$product = new Product();

	$product->setData($_POST);

	$product->save();

	header("Location: /admin/products");
	exit;

});

// rota para tela de alteração
$app->get("/admin/products/{idproduct}", function($request, $response, $args) {

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$args["idproduct"]);

	// __construct (header)
	$page = new PageAdmin();

	$page->setTpl("products-update", array(
		"product"=>$product->getValues()
	));

});

/*
// rota para alterar os dados no banco de dados
$app->get("/products/{idproduct}", function($request, $response, $args) {

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$args["idproduct"]);

	$page = new Page();

	$page->setTpl("product", [
		'product'=>$product->getValues()
	]);

});
*/
// rota para alterar os dados no banco de dados
$app->post("/admin/products/{idproduct}", function($request, $response, $args) {

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$args["idproduct"]);

	$product->setData($_POST);

	$product->update();

	// faz o upload do arquivo anexo
	$product->setPhoto($_FILES["file"]);

	header("Location: /admin/products");
	exit;

});

// rota para excluir o registro do banco de dados
$app->get("/admin/products/delete/{idproduct}", function($request, $response, $args) {

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$args["idproduct"]);

	$product->delete();

	header("Location: /admin/products");
	exit;

});

?>