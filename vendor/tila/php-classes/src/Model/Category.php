<?php

namespace Tila\Model;

use \Tila\DB\Sql;
use \Tila\Model;

// essa classe User é um model. Todo classe model tem getters e setters
// Classe Model contém os getters e setters, para serem utilizados em todas as classes model
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

var_dump($html);
//exit;
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode("", $html));

	}

}

?>