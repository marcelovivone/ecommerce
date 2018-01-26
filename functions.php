<?php

use \Tila\Model\User;

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

?>