<?php

use \Tila\Page;
use \Tila\PageAdmin;
use \Tila\Model\User;
use \Tila\Model\Category;

/* rotas para categorias */

// rota para tela de lista
$app->get("/admin/categories", function() {

	User::verifyLogin();

	$categories = Category::listAll();

	// __construct (header)
	$page = new PageAdmin();

	// body
	$page->setTpl("categories", array(
		"categories"=>$categories
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
$app->get("/categories/{idcategory}", function($request, $response, $args) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$args["idcategory"]);

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>[]
	]);

});

?>