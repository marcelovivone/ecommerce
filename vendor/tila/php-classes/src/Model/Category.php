<?php

namespace Tila\Model;

use \Tila\DB\Sql;
use \Tila\Model;

class Category extends Model
{
	
	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

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

	public function get($idcategory)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$idcategory
		));

		$this->setData($results[0]);

	}

	public function update()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save (:idcategory, :descategory)", 
			array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));

		// atribui o resultado no próprio objeto, para o caso de quem chamou necessite do resultado
		$this->setData($results[0]);

		// refaz o menu de categoria para contemplar a atualização
		Category::updateFile();

	}

	public function delete()
	{

		$sql = new Sql();

		$results = $sql->select("DELETE FROM tb_categories WHERE idcategory = :idcategory ", 
			array(
			":idcategory"=>$this->getidcategory()
		));

		// atribui o resultado no próprio objeto, para o caso de quem chamou necessite do resultado
		$this->setData($results[0]);

		// refaz o menu de categoria para contemplar a exclusão
		Category::updateFile();

	}

	public static function updateFile() {

		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode("", $html));

	}

	public function getProducts($related = true)
	{

		$sql = new Sql();

		if ($related) {

			$returns = $sql->select("
				SELECT * 
				  FROM tb_products 
				 WHERE idproduct IN (
				 	SELECT p.idproduct
				 	  FROM tb_products p
				 	 INNER JOIN tb_productscategories c ON p.idproduct = c.idproduct
				 	 WHERE c.idcategory = :idcategory
				 );
			", [
				":idcategory"=>$this->getidcategory()
			]);

		} else {

			$returns = $sql->select("
				SELECT * 
				  FROM tb_products 
				 WHERE idproduct NOT IN (
				 	SELECT p.idproduct
				 	  FROM tb_products p
				 	 INNER JOIN tb_productscategories c ON p.idproduct = c.idproduct
				 	 WHERE c.idcategory = :idcategory
				 );
			", [
				":idcategory"=>$this->getidcategory()
			]);

		}

		return $returns;

	}

	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$o = $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)", [
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		]);

	}

	public function removeProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		]);

	}

}

?>