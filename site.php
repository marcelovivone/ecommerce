<?php

use \Tila\Page;
//use \Tila\PageAdmin;
use \Tila\Model\Category;
use \Tila\Model\Product;
use \Tila\Model\Cart;

// rota de Page
$app->get("/", function() {

	$products = Product::listAll();

	// __construct (header)
	$page = new Page();

	// body
	// método checkList utilizado para incluir as fotos a cada produto existente no array
	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]);

});

// rota para alterar os dados no banco de dados
$app->get("/categories/{idcategory}", function($request, $response, $args) {

	$category = new Category();

	$category->get((int)$args["idcategory"]);

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>Product::checkList($category->getProducts())
	]);

});

$app->get("/cart", function() {

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart");
})

?>