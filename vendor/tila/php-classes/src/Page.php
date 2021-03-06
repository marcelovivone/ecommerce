<?php

namespace Tila;

use Rain\Tpl;

class Page 
{

	private $tpl;
	private $options = [];
	private $defaults = [
			"header"=>true,
			"footer"=>true,
			"data"=>[]
	];

	// segundo parâmetro devido à PageAdmin estender Page
	public function __construct($opts = array(), $tpl_dir = "/views/") 
	{

		$this->options = array_merge($this->defaults, $opts);

		// config
		$config = array(
			// utilização de $tpl_dir devido à PageAdmin estender Page
//    		"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/",
    		"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
    		"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
    		"debug"         => false, // set to false to improve the speed
		);

		Tpl::configure($config);

		// create the Tpl object
		$tpl = new Tpl;

		$this->tpl = new Tpl;

		$this->setData($this->options["data"]);

		// em todas as páginas HTML vai existir o header (exceto login)
		if ($this->options["header"] === true) $this->tpl->draw("header");

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
		// em todas as páginas HTML (exceto login)
		if ($this->options["footer"] === true) $this->tpl->draw("footer");

	}
}

?>