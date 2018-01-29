<?php

use \Tila\PageAdmin;
use \Tila\Model\User;
use \Tila\Model\Order;
use \Tila\Model\OrderStatus;

$app->get("/admin/orders/{idorder}/status", function($request, $response, $args) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$args["idorder"]);

	$page = new PageAdmin();

	$page->setTpl("order-status", [
		'order'=>$order->getValues(),
		'status'=>OrderStatus::listAll(),
		'msgSuccess'=>Order::getSuccess(),
		'msgError'=>Order::getError()
	]);

});

$app->post("/admin/orders/{idorder}/status", function($request, $response, $args) {

	User::verifyLogin();

	if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0) {
		
		Order::setError("Informe o status atual");

		header("Location: /admin/orders/" . $args["idorder"] . "/status");
		exit;

	}

	$order = new Order();

	$order->get((int)$args["idorder"]);

	$order->setidstatus((int)$_POST['idstatus']);

	$order->save();

	Order::setSuccess("Status atualizado.");

	header("Location: /admin/orders/" . $args["idorder"] . "/status");
	exit;

});

$app->get("/admin/orders/{idorder}/delete", function($request, $response, $args) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$args["idorder"]);

	$order->delete();

	header("Location: /admin/orders");
	exit;

});

$app->get("/admin/orders/{idorder}", function($request, $response, $args) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$args["idorder"]);

	$cart = $order->getCart();

	$page = new PageAdmin();

	$page->setTpl("order", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);

});

$app->get("/admin/orders", function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("orders", [
		'orders'=>Order::listAll()
	]);

});

?>