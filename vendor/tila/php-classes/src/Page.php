<?php

namespace Tila;

use Rain\Tpl;

class Page 
{

	private $tpl;
	private $options = [];
	private $defaults = [
			"data"=>[]
	];

	public function __construct($opts = array()) 
	{

		$this->options = array_merge($this->defaults, $opts);

		// config
		$config = array(
    		"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/",
    		"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache",
    		"debug"         => false, // set to false to improve the speed
		);

		Tpl::configure($config);

		// create the Tpl object
		$tpl = new Tpl;

		$this->tpl = new Tpl;

		$this->setData($this->options["data"]);

		// em todas as páginas HTML vai existir o header
		$this->tpl->draw("header");

	}

	private function setData($data = array())
	{

		// assign variables
		foreach ($data as $key => $value) 
		{

			$this->tpl->assign($key, $value);
		
		}

	}

	// argumentos: nome do template, dados, se retorna o HTML ou se joga na tela
	public function setTpl($name, $data = array(), $returnHTML = false)
	{

		$this->setData($data);

		return $this->tpl->draw($name, $returnHTML);

	}

	public function __destruct() 
	{

		// quando a classe for finalizada, deve-se incluir o footer, que vai existir
		// em todas as páginas HTML
		$this->tpl->draw("footer");

	}
}

?>