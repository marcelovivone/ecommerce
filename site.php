<?php

use \Tila\Page;
//use \Tila\PageAdmin;
use \Tila\Model\User;
use \Tila\Model\Category;
use \Tila\Model\Product;
use \Tila\Model\Cart;
use \Tila\Model\Address;
use \Tila\Model\Order;
use \Tila\Model\OrderStatus;

// rota de Page
$app->get("/", function() {

	$products = Product::listAll();

	// __construct (header)
	$page = new Page();

	// body
	// método checkList utilizado para incluir as fotos a cada produto existente no array
	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]);

});

// rota para alterar os dados no banco de dados
$app->get("/categories/{idcategory}", function($request, $response, $args) {

	$category = new Category();

	$category->get((int)$args["idcategory"]);

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>Product::checkList($category->getProducts())
	]);

});

// rota para página de detalhes do produto
$app->get("/products/{desurl}", function($request, $response, $args) {

	$product = new Product();

	$product->getFromURL($args["desurl"]);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);

});

// rota para página de carrinho
$app->get("/cart", function() {

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);

});

// rota para adicionar item do produto no carrinho na página e no banco de dados
$app->get("/cart/{idproduct}/plus", function($request, $response, $args) {

	$product = new Product();

	$product->get((int)$args["idproduct"]);

	// recupera ou inclui o carrinho na sessão
	$cart = Cart::getFromSession();

	// para o caso do usuário ter aumentado o quantitativo de compra do produto
	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i = 0; $i < $qtd; $i++) {

		$cart->addProduct($product);

	}

	header("Location: /cart");
	exit;

});

// rota para excluir um item do produto do carrinho na página e no banco de dados
$app->get("/cart/{idproduct}/minus", function($request, $response, $args) {

	$product = new Product();

	$product->get((int)$args["idproduct"]);

	// recupera ou inclui o carrinho na sessão
	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;

});

// rota para excluir todos os itens do produto do carrinho na página e no banco de dados
$app->get("/cart/{idproduct}/remove", function($request, $response, $args) {

	$product = new Product();

	$product->get((int)$args["idproduct"]);

	// recupera ou inclui o carrinho na sessão
	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;

});

// rota para cálculo de frete
$app->post("/cart/freight", function() {

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;

});

// rota para página da finalização da compra
$app->get("/checkout", function() {

	User::verifyLogin(false);

	$address = new Address();

	$cart = Cart::getFromSession();

	// se o CEP já existir no carrinho: carrega em $_GET o CEP do carrinho
	if (isset($_GET['zipcode'])) {

		$_GET['zipcode'] = $cart->getdeszipcode();

	}

	// verifica se o cep foi informado
	if (isset($_GET['zipcode'])){

		// lê os dados de endereço do cep
		$address->loadFromCEP($_GET['zipcode']);
		
		// atribui o novo cep ao carrinho
		$cart->setdeszipcode($_GET['zipcode']);

		// salva os novos dados no banco de dados
		$cart->save();

		// refaz o cálculo dos valores de frete
		$cart->getCalculateTotal();

	}

	// define os dados no carrinho, mesmo que com conteúdos vazios
	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getdeszipcode()) $address->setdeszipcode('');

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>$address::getMsgError()
	]);

});

// rota para salvar dados
$app->post("/checkout", function() {

	User::verifyLogin(false);

	// valida o preenchimento dos campos do formulário
	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === ''){
		Address::setMsgError('Informe o CEP.');
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === ''){
		Address::setMsgError('Informe o endereço.');
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === ''){
		Address::setMsgError('Informe o bairro.');
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['descity']) || $_POST['descity'] === ''){
		Address::setMsgError('Informe a cidade.');
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['desstate']) || $_POST['desstate'] === ''){
		Address::setMsgError('Informe o estado.');
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['descountry']) || $_POST['descountry'] === ''){
		Address::setMsgError('Informe o país.');
		header("Location: /checout");
		exit;
	}

	$user = User::getFromSession();
	$user->get($user->getiduser());

	// sobrescrevendo o zipcode porque o campo no formulário não está com o mesmo nome do banco
	$_POST['deszipcode'] = $_POST['zipcode'];

	$_POST['idperson'] = $user->getidperson();

	$address = new Address();
	$address->setData($_POST);

	$address->save();

	$cart = Cart::getFromSession();

	$totals = $cart->getCalculateTotal();

	$order = new Order();

	// não consegui fazer o getidaddress retornar o sequencial do novo endereço
	// get de qualquer atributo da tabela está vazio na volta do save(), embora
	// o $this no método contenha todos os atributos e seus respectivos valores
	$order->setData([
		'idcart'=>$cart->getidcart(),
		//'idaddress'=>$address->getidaddress(),
		'idaddress'=>$address->getValues()['idaddress'],
		'iduser'=>$user->getiduser(),
		'idstatus'=>OrderStatus::EM_ABERTO,
		'vltotal'=>$cart->getvltotal()
	]);

	$order->save();

	header('Location: /order/'.$order->getidorder());
	exit;

});

// rota para a página de login
$app->get("/login", function() {

	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>isset($_SESSION['registerValues']) ? $_SESSION['registerValues'] : [
			'name'=>'',
			'email'=>'',
			'phone'=>''
		]
	]);

});

// rota para a validação do login
$app->post("/login", function() {

	try {

		User::login($_POST['login'], $_POST['password']);

	} catch(Exception $e) {
//echo 'www ----- ';
//echo $e->getMessage();
//echo ' ----- www';
		User::setError($e->getMessage());

	}

	header("Location: /checkout");
	exit;

});

// rota para a página de login
$app->get("/logout", function() {

	User::logout();
	$page = new Page();

	header("Location: /login");
	exit;

});

// rota para o cadastro de um novo usuário
$app->post("/register", function() {

	// guardar os dados digitados pelo usuário em uma sessão
	// utilizada para o caso de ter algum erro no cadastro e não limpar os campos da página
	$_SESSION['registerValues'] = $_POST;

	// validação de campos obrigatórios da página
	if (!isset($_POST['name']) || $_POST['name'] == '') {

		User::setErrorRegister("Preencha o seu nome.");
		header('Location: /login');
		exit;

	}

	if (!isset($_POST['email']) || $_POST['email'] == '') {

		User::setErrorRegister("Preencha o seu e-mail.");
		header('Location: /login');
		exit;

	}

	if (!isset($_POST['password']) || $_POST['password'] == '') {

		User::setErrorRegister("Preencha a senha.");
		header('Location: /login');
		exit;

	}

	// verifica se o usuário já existe
	if (User::checkLoginExist($_POST['email']) === true) {

		User::setErrorRegister("Esse endereço de e-mail já está sendo utilizado por outro usuário.");
		header('Location: /login');
		exit;

	}

	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'desperson'=>$_POST['name'],
		'deslogin'=>$_POST['email'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	// autentica o usuário
	// caso isso não seja feito, a rota de checkout irá redirecionar para a rota de login
	// (o usuário precisa estar logado para acessar a rota de checkout)
	$user->insert();

	User::login($_POST['email'], $_POST['password']);

	header('Location: /checkout');
	exit;

});

// rota de recuperação de senha (forgot)
$app->get("/forgot", function() {

	// __construct (header)
	$page = new Page();

	// body
	$page->setTpl("forgot");

});

// rota de salvar senha de recuperação no banco (forgot)
$app->post("/forgot", function() {
	
	$user = User::getForgot($_POST["email"], false);

	header("location: /forgot/sent");
	exit;

});

// rota de janela de senha enviada (forgot)
$app->get("/forgot/sent", function() {

	// __construct (header)
	$page = new Page();

	// body
	$page->setTpl("forgot-sent");

});

// rota de janela de reset de senha
$app->get("/forgot/reset", function() {

	$user = User::validForgotDecrypt($_GET["code"], $_GET["iv"]);

	// __construct (header)
	$page = new Page();

	// body
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"],
		"iv"=>$_GET["iv"]
	));

});

// rota para salvar a senha de reset no banco
$app->post("/forgot/reset", function() {

	$forgot = User::validForgotDecrypt($_POST["code"], $_POST["iv"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	// __construct (header)
	$page = new Page();

	// body
	$page->setTpl("forgot-reset-success");

});

// rota para página de edição dos dados do usuário
$app->get("/profile", function() {

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);

});

// rota para salvar dados alterados no banco
$app->post("/profile", function() {

	User::verifyLogin(false);

	// validação de campos obrigatórios da página
	if (!isset($_POST['desperson']) || $_POST['desperson'] === '') {

		User::setError("Preencha o seu nome.");
		header('Location: /profile');
		exit;

	}

	if (!isset($_POST['desemail']) || $_POST['desemail'] === '') {

		User::setErrorRegister("Preencha o seu e-mail.");
		header('Location: /profile');
		exit;

	}

	$user = User::getFromSession();

	// verifica se o usuário já existe
	if ($_POST['desemail'] !== $user->getdesemail()) {

		if (User::checkLoginExist($_POST['desemail']) === true) {

			// retorna os valores informados pelo usuário para a sessão
			// no get do profile, esses valores serão lidos e reexibidos nos campos da página
			$_SESSION[User::SESSION] = $_POST;

			User::setError("Esse endereço de e-mail já está sendo utilizado por outro usuário.");
			header('Location: /profile');
			exit;

		}

	}

	// evita command injection para alterar o usuário para administrador
	// sobrescrevendo uma possível alteração pelo inadmin e pela senha
	// originais salvas no banco de dados
	$_POST['iduser'] = $user->getiduser();
	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->gedespassword();

	// o login é o mesmo que o e-mail para os usuários do site
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->update();

	$_SESSION[User::SESSION] = $user->getValues();

	User::setSuccess("Dados alterados com sucesso!");

	header("location: /profile");
	exit;

});

$app->get("/order/{idorder}", function($request, $response, $args) {

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$args["idorder"]);

	$page = new Page();

	$page->setTpl("payment", [
		'order'=>$order->getValues()
	]);

});

$app->get("/boleto/{idorder}", function($request, $response, $args) {

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$args["idorder"]);

	// dados de configuração de boleto do banco Itaú (copiados de /res/boletophp/boleto_itau.php) 

	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = formatBR($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(".", "",$valor_cobrado);
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress() . " - " . $order->getdesdistrict(). " ";
	$dadosboleto["endereco2"] = $order->getdescity(). " - " . $order->getdesstate(). " - " . $order->getdescountry() . " - CEP: " . $order->getdeszipcode();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Tila E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "Tila Treinamentos";
	$dadosboleto["cpf_cnpj"] = "11.111.111/0001-01";
	$dadosboleto["endereco"] = "Rua Voluntário da Pátria, 37 - Botafogo, 22270-000";
	$dadosboleto["cidade_uf"] = "Rio de Janeiro - RJ";
	$dadosboleto["cedente"] = "TILA TREINAMENTOS LTDA - ME";

	// NÃO ALTERAR!
	$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp". DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;
	require_once($path . "funcoes_itau.php"); 
	require_once($path . "layout_itau.php");
});

$app->get("/profile/orders", function() {

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile-orders", [
		'orders'=>$user->getOrders()
	]);

});

$app->get("/profile/orders/{idorder}", function($request, $response, $args) {

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$args["idorder"]);

	$cart = new Cart();

	$cart->get((int)$order->getidcart());

	$cart->getCalculateTotal();

	$page = new Page();

	$page->setTpl("profile-orders-detail", [
		'cart'=>$cart->getValues(),
		'order'=>$order->getValues(),
		'products'=>$cart->getProducts()
	]);

});

$app->get("/profile/change-password", function() {

	User::verifyLogin(false);

	$page = new Page();

	$page->setTpl("profile-change-password", [
		'changePassError'=>User::getError(),
		'changePassSuccess'=>User::getSuccess()
	]);

});

$app->post("/profile/change-password", function() {

	User::verifyLogin(false);

	if (!isset($_POST['current_pass']) || $_POST['current_pass'] === '') {

		User::setError('Digite a senha atual.');
		
		header('Location: /profile/change-password');
		exit;

	}

	if (!isset($_POST['new_pass']) || $_POST['new_pass'] === '') {

		User::setError('Digite a nova senha.');
		
		header('Location: /profile/change-password');
		exit;

	}

	if (!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] === '') {

		User::setError('Confirme a nova senha.');
		
		header('Location: /profile/change-password');
		exit;

	}

	if ($_POST['current_pass'] === $_POST['new_pass']) {

		User::setError('A nova senha deve ser diferente da atual.');
		
		header('Location: /profile/change-password');
		exit;

	}

	if ($_POST['new_pass'] !== $_POST['new_pass_confirm']) {

		User::setError('A senha de confirmação deve ser igual à nova senha.');
		
		header('Location: /profile/change-password');
		exit;

	}

	$user = User::getFromSession();
	$user->get($user->getiduser());

	if(!password_verify($_POST['current_pass'], $user->getdespassword())) {

		User::setError('A senha atual está inválida.');
		
		header('Location: /profile/change-password');
		exit;

	}

	$user->setdespassword($_POST['new_pass']);

	$user->update();

	User::setSuccess('Senha alterada com sucesso.');

	header('Location: /profile/change-password');
	exit;

});

?>