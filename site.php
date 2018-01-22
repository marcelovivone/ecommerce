<?php

use \Tila\Page;
use \Tila\PageAdmin;
use \Tila\Model\Product;

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




?>