<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
// timezone para São Paulo América
date_default_timezone_set('America/Sao_Paulo');

ob_start();

require  __DIR__ . "/vendor/autoload.php";

// os headers abaixo são necessários para permitir o acesso a API por clientes externos ao domínio
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Access-Control-Allow-Credentials: true'); // Permitir credenciais

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use CoffeeCode\Router\Router;
// localhost/acme-3am/api
$route = new Router(url("api"),":");

$route->namespace("source\Controller");



// --------------- Início - Exercícios e Desafios ----------------------------------

/* ============== $route->get("endereço", "ação"); ===============================*/

//ROTAS PARA ACESSAR UMA FUNÇÃO DA CLASSE "PRODUCTS" (USAR URL NO NAVEGADOR)
$route->get("/products/list", "Products:productsList");
$route->get("/products/list/{productId}", "Products:productsListById");

$route->get("/products-categories/list", "ProductsCategories:productsCategoryList");
$route->get("/products-categories/list/{categoryId}", "ProductsCategories:productsCategoryListById");
$route->post("/products", "Products:create");
$route->put("/products/{productID}","Products:update");
$route->delete("/products/{productID}","Products:delete");

/* ============== FAQS ================*/
$route->get("/faqs-categories/list", "Faqs\FaqsCategories:faqsCategoryList");
$route->get("/faqs-categories/list/{categoryId}", "Faqs\FaqsCategories:faqsCategoryListById");

$route->get("/faqs/list", "Faqs\Faqs:faqsListAll");
$route->get("/faqs/list/{faqId}", "Faqs\Faqs:faqsListById");

$route->post("/faqs-categories", "FaqsCategories:faqsCategoriesCreate");
$route->post("/faqs/insert", "Faqs:faqInsert");

$route->put("/faqs/update/{faqId}", "Faqs:faqUpdate");
$route->put("/faqs-categories/update/{categoryId}", "FaqsCategories:faqCategoryUpdate");

$route->delete("/faqs/{faqId}", "Faqs:faqDelete");
$route->delete("/faqs-categories/{categoryId}", "FaqsCategories:faqCategoryDelete");
// --------------- Fim - Exercícios - Desafios ---------------

// localhost/acme-3am/api/hello
$route->get("/hello", "Api:hello");
$route->get("/users/list", "Users:usersList");

$route->dispatch();

/** ERROR REDIRECT */
if ($route->error()) {
    header('Content-Type: application/json; charset=UTF-8');
    //http_response_code(404);

    echo json_encode([
        "code" => 404,
        "status" => "not_found",
        "message" => "URL não encontrada"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

}

ob_end_flush();