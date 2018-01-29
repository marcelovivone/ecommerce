<?php

use \Tila\PageAdmin;
use \Tila\Model\User;
use \Tila\Model\Category;
use \Tila\Model\Product;

/* rotas para categorias */

// rota para tela de lista
$app->get("/admin/categories", function() {

	User::verifyLogin();

	$search = isset($_GET['search']) ? $_GET['search'] : "";

	$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

	if($search == '') {

		$pagination = Category::getPage($page);

	} else {

		$pagination = Category::getPageSearch($search, $page);

	}

	$pages = [];

	for ($pageNumber = 1; $pageNumber <= $pagination['pages']; $pageNumber++)
	{

		array_push($pages, [
			'href'=>'/admin/categories?'.http_build_query([
				'page'=>$pageNumber,
				'search'=>$search
			]),
			'text'=>$pageNumber
		]);

	}

	// __construct (header)
	$page = new PageAdmin();

	// body
	$page->setTpl("categories", array(
		"categories"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	));

});

// rota para tela de cadastro
$app->get("/admin/categories/create", function() {

	User::verifyLogin();

	// __construct (header)
	$page = new PageAdmin();

	// body
	$page->setTpl("categories-create");

});

// rota para criar registro no banco de dados
$app->post("/admin/categories/create", function() {

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->insert();

	header("Location: /admin/categories");
	exit;

});

// rota para tela de alteração
$app->get("/admin/categories/{idcategory}", function($request, $response, $args) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$args["idcategory"]);

	// __construct (header)
	$page = new PageAdmin();

	$page->setTpl("categories-update", array(
		"category"=>$category->getValues()
	));

});

// rota para alterar os dados no banco de dados
$app->post("/admin/categories/{idcategory}", function($request, $response, $args) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$args["idcategory"]);

	$category->setData($_POST);

	$category->update();

	header("Location: /admin/categories");
	exit;

});

// rota para excluir o registro do banco de dados
$app->get("/admin/categories/delete/{idcategory}", function($request, $response, $args) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$args["idcategory"]);

	$category->delete();

	header("Location: /admin/categories");
	exit;

});

// rota para alterar os dados no banco de dados
$app->get("/admin/categories/{idcategory}/products", function($request, $response, $args) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$args["idcategory"]);

	$page = new PageAdmin();

	$page->setTpl("categories-products", [
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
	]);

});

// rota para incluir relação entre categorias e produtos no banco de dados
$app->get("/admin/categories/{idcategory}/products/{idproduct}/add", function($request, $response, $args) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$args["idcategory"]);
	
	$product = new Product();

	$product->get((int)$args["idproduct"]);

	$category->addProduct($product);
	
	header("Location: /admin/categories/".(int)$args["idcategory"]."/products");
	exit;

});

// rota para excluir relação entre categorias e produtos do banco de dados
$app->get("/admin/categories/{idcategory}/products/{idproduct}/remove", function($request, $response, $args) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$args["idcategory"]);

	$product = new Product();

	$product->get((int)$args["idproduct"]);

	$category->removeProduct($product);
	
	header("Location: /admin/categories/".(int)$args["idcategory"]."/products");
	exit;

});

?>