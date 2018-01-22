<?php

namespace Tila\Model;

use \Tila\DB\Sql;
use \Tila\Model;

class Product extends Model
{
	
	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

	}

	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_products_save (:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", 
			array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		));

		// atribui o resultado no próprio objeto, para o caso de quem chamou necessite do resultado
		$this->setData($results[0]);

	}

	public function get($idproduct)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
			":idproduct"=>$idproduct
		));

		$this->setData($results[0]);

	}

	public function delete()
	{

		$sql = new Sql();

		$results = $sql->select("DELETE FROM tb_products WHERE idproduct = :idproduct ", 
			array(
			":idproduct"=>$this->getidproduct()
		));

		// atribui o resultado no próprio objeto, para o caso de quem chamou necessite do resultado
		$this->setData($results[0]);

	}

	public function checkPhoto()
	{

		if (file_exists(
			$_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR .
			"site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg"
		)) {

			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";

		} else {

			$url = "/res/site/img/product.jpg";

		}

		return $this->setdesphoto($url);

	}

	// melhor forma de implementação seria com a criação de um atributo para as imagens
	// como não existe a coluna, foram criados os métodos getValues e checkPhoto
	public function getValues()
	{

		$this->checkPhoto();

		$values = parent::getValues();

		return $values;

	}

	public function setPhoto($file)
	{

		// extrai a extensão da foto
		$extension = explode(".", $file['name']);

		$extension = end($extension);

		// verifica a extensão da foto e cria o jpeg dessa imagem
		switch($extension) {

			case "jpg":
			case "jpeg":
			echo 'A: '.$file["tmp_name"];
				$image = imagecreatefromjpeg($file["tmp_name"]);
				break;

			case "gif":
				$image = imagecreatefromgif($file["tmp_name"]);
				break;

			case "png":
				$image = imagecreatefrompng($file["tmp_name"]);
				break;
		}

		$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR .
				"site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR .
				"products" . DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg";
		
		imagejpeg($image, $dist);

		imagedestroy($image);

		// para carregar a foto no objeto Product
		$this->checkPhoto();

	}
}

?>