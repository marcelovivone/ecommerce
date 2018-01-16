<?php

namespace Tila\Model;

use \Tila\DB\Sql;
use \Tila\Model;

// essa classe User é um model. Todo classe model tem getters e setters
// Classe Model contém os getters e setters, para serem utilizados em todas as classes model
class User extends Model
{
	
	const SESSION = "User";

	public static function login($login, $password)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if (count($results) ===0)
		{
			// contrabarra é necessária porque a exceção está no escopo principal (no namespace principal
			// do PHP) e não dentro do namespace corrente (\Tila\Model)
			throw new \Exception("Usuário inexistente ou senha inválida.", 1);
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"]))
		{
			
			$user = new User();

			$user->setData($data);
			
			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else 
		{
			// contrabarra é necessária porque a exceção está no escopo principal (no namespace principal
			// do PHP) e não dentro do namespace corrente (\Tila\Model)
			throw new \Exception("Usuário inexistente ou senha inválida.", 1);
		}

	}

	public static function verifyLogin($inadmin = true)
	{

		if (
			// se a sessão não está definida
			!isset($_SESSION[User::SESSION])
			||
			// se a sessão está vazia
			!$_SESSION[User::SESSION]
			||
			// se o usuário é válido
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
			||
			// se o usuário é administrador
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
		) {
			
			header("Location: /admin/login");
			exit;

		}

	}


	public static function logout() 
	{
		$_SESSION[User::SESSION] = NULL;
	}

}

?>