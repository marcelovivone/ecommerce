<?php

namespace Tila\Model;

use \Tila\DB\Sql;
use \Tila\Model;
use \Tila\Model\User;

class Cart extends Model
{
	// sessão precisa ser criada para conter o id do carrinho
	const SESSION = "Cart";

	public static function getFromSession()
	{

		$cart = new Cart();

		// verifica se a sessão está definida e se o carrinho que está sendo criado já está na sessão 
//		if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {
		if (isset($_SESSION[Cart::SESSION]) && isset($_SESSION[Cart::SESSION]['idcart'])) {

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

		// se a sessão não está definida ou o carrinho ainda não foi incluído nela
		} else {

			// lê o ID do carrinho da sessão
			$cart->getFromSessionID();

			// se o ID do carrinho da sessão ainda não existe
			if (!(int)$cart->getidcart() > 0) {

				// criar carrinho novo
				$data = [
					'dessessionid'=>session_id()
				];

				// verifica se a rota não é de administração
				// se retornar true, significa que o usuário está logado
				if (User::checkLogin(false)) {

					//lê o usuário da sessão
					$user = User::getFromSession();

					$data['iduser'] = $user->getiduser();

				}

				$cart->setData($data);

				$cart->save();

				$cart->setToSession();

			}

		}

		return $cart;

	}

	public function setToSession()
	{

		$_SESSION[Cart::SESSION] = $this->getValues();		

	}

	public function getFromSessionID()
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			':dessessionid'=>session_id()
		]);

		if (count($results) > 0) {

			$this->setData($results[0]);

		}

	}

	public function get(int $idcart)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			':idcart'=>$idcart
		]);

		if (count($results)) {

			$this-setData($results[0]);

		}

	}

	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			':idcart'=>$this->getidcart(), 
			':dessessionid'=>$this->getdessessionid(), 
			':iduser'=>$this->getiduser(), 
			':deszipcode'=>$this->getdeszipcode(), 
			':vlfreight'=>$this->getvlfreight(), 
			':nrdays'=>$this->getnrdays()
		]);

		$this->setData($results);

	}

	public function insert()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save (:idcategory, :descategory)", 
			array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));

		// atribui o resultado no próprio objeto, para o caso de quem chamou necessite do resultado
		$this->setData($results[0]);

		// refaz o menu de categoria para contemplar a inclusão
		Category::updateFile();

	}

	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", [
			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()
		]);

	}

	public function removeProduct(Product $product, $all = false)
	{

		$sql = new Sql();

		// não há exclusão de registro para que os dados possam ser utilizados para análises futuras
		$query = "UPDATE tb_cartsproducts 
					 SET dtremoved = NOW() 
				   WHERE idcart = :idcart AND 
				   		 idproduct = :idproduct AND 
				   		 dtremoved IS NULL";

		// $all:
		// true - remove todos os itens do produto do carrinho (o produto some do carrinho na página)
		// false - remove um item do produto do carrinho (diminui uma quantidade do mostrador da página)
		if (!$all) {
			
			$query .= ' LIMIT 1';

		}
		
		$sql->query($query, [
			':idcart'=>$this->getidcart(),
			'idproduct'=>$product->getidproduct()
		]);

	}

	public function getProducts(){

		$sql = new Sql();

		$rows = $sql->select("
			SELECT p.idproduct,
		  		   p.desproduct,
		  		   p.vlprice,
		  		   p.vlwidth, 
		  		   p.vlheight, 
		  		   p.vllength,
		  		   p.vlweight,
		  		   p.desurl,
		  		   COUNT(*) AS nrqtd,
		  		   SUM(p.vlprice) AS vltotal
			  FROM tb_cartsproducts c
			 INNER JOIN tb_products p ON c.idproduct = p.idproduct
			 WHERE c.idcart = :idcart AND
			 	   c.dtremoved IS NULL
		  GROUP BY p.idproduct,
		  		   p.desproduct,
		  		   p.vlprice,
		  		   p.vlwidth, 
		  		   p.vlheight, 
		  		   p.vllength,
		  		   p.vlweight,
		  		   p.desurl
		  ORDER BY p.desproduct
		  ", [
		  	':idcart'=>$this->getidcart()
		  ]);

		// inclui as fotos do produto às linhas do array
		return Product::checkList($rows);
exit;

	}

}

?>