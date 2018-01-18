<?php

namespace tila;

class Model {

	// array contém todos valore dos campos do objeto (atributos das tabelas)
	private $values = [];

	// executado toda vez que o método for chamado
	// $name: nome do método que foi chamado
	// $args = parâmetros passados para o método
	public function __call($name, $args)
	{

		// lê posições que informam se o método chamado é get ou set
		$method = substr($name, 0, 3);

		// lê nome do campo
		$fieldName = substr($name, 3, strlen($name));

		switch ($method) {

			case 'get':
				return $this->values[$fieldName];
				break;
			
			case 'set':
				$this->values[$fieldName] = $args[0];
				break;
			
		}
		
	}

	public function setData($data = array())
	{

		foreach ($data as $key => $value) {

			// cada nome de método é criado dinamicamente
			// chaves {} e como string é o formato para criação dinâmica
			$this->{"set".$key}($value);

		}

	}

	public function getValues()
	{
		return $this->values;
	}

}

?>