<?php

use \Tila\PageAdmin;

// rota de Page
$app->get("/", function() {

	// __construct (header)
	$page = new Page();

	// body
	$page->setTpl("index");

});




?>