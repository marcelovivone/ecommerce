<?php

use \Tila\Model\User;

function formatBR(float $vlunformat)
{

	return number_format($vlunformat, 2, ",", ".");

}

function checkLogin($inadmin = true)
{

	return User::checkLogin($inadmin);

}

function getUserName()
{

	$user = User::getFromSession();
//var_dump($user);
//echo ' --- ';
//echo $user->getdesperson();
//exit;
	return $user->getdesperson();

}

?>