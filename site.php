<?php

use \Tila\Page;
//use \Tila\PageAdmin;
use \Tila\Model\User;
use \Tila\Model\Category;
use \Tila\Model\Product;
use \Tila\Model\Cart;
use \Tila\Model\Address;

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

// rota para a finalização da compra
$app->get("/checkout", function() {

	User::verifyLogin(false);

	$cart = Cart::getFromSession();

	$address = new Address();

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);

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

?>