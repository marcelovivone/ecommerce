<?php

namespace Tila\Model;

use \Tila\DB\Sql;
use \Tila\Model;
use \Tila\Mailer;

// essa classe User é um model. Todo classe model tem getters e setters
// Classe Model contém os getters e setters, para serem utilizados em todas as classes model
class User extends Model
{
	
	const SESSION = "User";
	const SECRET = "1019019018452124";
	const CIPHER = "aes-128-cbc";

	public static function getFromSession()
	{

		$user = new User();

		// verifica se a sessão está definida e se o usuário existe dentro da sessão
		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {

			$user->setData($_SESSION[User::SESSION]);

		}

		return $user;

	}

	public static function checkLogin($inadmin = true)
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
		) {

			// usuário não está logado
			return false;
			
		} else {

			// I - se a rota é de administração
			// II - se o usuario logado tem permissão para acessar a área de administração
			//  ------- I -------    ----------------------- II -----------------------
			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {

				return true;

			// a rota não é de administração
			} else if ($inadmin === false) {

				return true;

			} else {

				return false;

			}


		}

	}

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

		if (User::checkLogin($inadmin)) {
			
			header("Location: /admin/login");
			exit;

		}

	}

	public static function logout() 
	{
		$_SESSION[User::SESSION] = NULL;
	}

	public static function listAll()
	{

		$sql = new Sql();

		return $results = $sql->select("SELECT * FROM tb_users INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

	}

	public function insert()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save (:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
			array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		// atribui o resultado no próprio objeto, para o caso de quem chamou necessite do resultado
		$this->setData($results[0]);

	}

	public function get($iduser)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));

		$this->setData($results[0]);

	}

	public function update()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save (:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
			array(
			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		// atribui o resultado no próprio objeto, para o caso de quem chamou necessite do resultado
		$this->setData($results[0]);

	}

	public function delete()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_delete (:iduser)", 
			array(
			":iduser"=>$this->getiduser()
		));

		// atribui o resultado no próprio objeto, para o caso de quem chamou necessite do resultado
		$this->setData($results[0]);

	}

	public static function getForgot($email)
	{

		$sql = new Sql();

		$results1 = $sql->select("
			SELECT * 
			  FROM tb_persons a
			 INNER JOIN tb_users b USING(idperson)
			 WHERE a.desemail = :email;",
			 array(
			 	":email"=>$email
			 ));

		if (count($results1) === 0)
		{

			throw new \Exception("Não foi possível recuperar a senha.", 1);
			
		} else {

			$data = $results1[0];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create (:iduser, :desip)", 
				array(
					":iduser"=>$data["iduser"],
					":desip"=>$_SERVER["REMOTE_ADDR"]
			));			

		}

		if (count($results2) === 0)
		{

			throw new \Exception("Não foi possível recuperar a senha.", 1);
			
		} else {

			$dataRecovery = $results2[0];

			// chave ou fixa ou passando randomizada e na base 64 pelo get (= ao IV)
			// pesquisar qual maneira é a mais segura
//			$key = openssl_random_pseudo_bytes(USER::SECRET);
			$key = USER::SECRET;

			$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(USER::CIPHER));

			$code = base64_encode(openssl_encrypt($dataRecovery["idrecovery"], USER::CIPHER, $key, 0, $iv));
/*
echo 'Code: '.$code;
//echo ' --- ';
//echo base64_encode($code);
echo ' --- ';
echo 'IV: '.$iv;
*/
			$iv = base64_encode($iv);
/*
echo ' --- ';
echo 'IV64: '.$iv;
//exit;
*/

			$link = "http://www.tilacommerce.com.br/admin/forgot/reset?code=$code&iv=$iv";

			$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Tila Store", "forgot", 
				array(
					"name"=>$data["desperson"],
					"link"=>$link
			));

			$mailer->send();

			return $data;

		}

	}

	public static function validForgotDecrypt($code,$iv) {

		// chave fixa ou recebendo randomizada e na base 64 pelo get (= ao IV)
		// pesquisar qual maneira é a mais segura
		$key = USER::SECRET;

/*
echo 'Code: '.$code;
//echo ' --- ';
//echo base64_encode($code);
echo ' --- ';
echo 'IV: '.$iv;
*/

		$iv = base64_decode($iv);

/*
echo ' --- ';
echo 'IV64: '.$iv;
echo ' --- ';
*/

		$idrecovery = openssl_decrypt(base64_decode($code), USER::CIPHER, $key, 0, $iv);

/*
echo $idrecovery;
*/

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			  FROM tb_userspasswordsrecoveries r
			  INNER JOIN tb_users u USING(iduser)
			  INNER JOIN tb_persons p USING(idperson)
			 WHERE r.idrecovery = :idrecovery AND
			 	   r.dtrecovery IS NULL AND
			 	   DATE_ADD(r.dtregister, INTERVAL 1 HOUR) >= NOW();
			", array(
				":idrecovery"=>$idrecovery
			));

		if (count($results) === 0) {

			throw new \Exception("Não foi possível recuperar a senha.", 1);

		} else {

			return $results[0];

		}

	}

	public static function setForgotUsed($idrecovery)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));

	}

	public function setPassword($password)
	{
		
		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));

	}

}

?>