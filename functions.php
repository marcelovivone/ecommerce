<?php

use \Tila\Model\User;
use \Tila\Model\Cart;

function formatBR($vlunformat)
{

	if (!$vlunformat > 0) $vlunformat = 0;

	return number_format($vlunformat, 2, ",", ".");

}

function checkLogin($inadmin = true)
{

	return User::checkLogin($inadmin);

}

function getUserName()
{

	$user = User::getFromSession();

	return $user->getdesperson();

}

function getCartNrQtd() {

	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return $totals['nrqtd'];

}

function getCartVlSubTotal() {

	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return formatBR($totals['vlprice']);

}

?>