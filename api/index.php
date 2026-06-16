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


// ----------- USERS ------------------
$route->group("/users");
$route->post("/register","Users:register"); // Registrar usuário comum
$route->post("/login","Users:auth"); // login de usuário comum
$route->put("/update","Users:update"); // update de usuário comum
$route->post("/register-admin","Users:registerAdmin"); // Registrar usuário admin NÃO IMPLEMENTADO
$route->post("/login-admin","Users:authAdmin"); // login de usuário admin
$route->put("/update-admin","Users:updateAdmin"); // update de usuário admin
$route->group(null);


//----------- PRODUCTS -----------------------
$route->get("/products/list", "Products:productsList");
$route->get("/products/list/{productId}", "Products:productsListById");
$route->post("/products", "Products:create");
$route->put("/products/{productID}","Products:update");
$route->delete("/products/{productID}","Products:delete");

$route->get("/products-categories/list", "ProductsCategories:productsCategoryList");
$route->get("/products-categories/list/{categoryId}", "ProductsCategories:productsCategoryListById");


/* ------------------ FAQS ------------------*/
$route->get("/faqs-categories/list", "Faqs\FaqsCategories:faqsCategoryList");
$route->get("/faqs-categories/list/{categoryId}", "Faqs\FaqsCategories:faqsCategoryListById");

$route->get("/faqs/list", "Faqs\Faqs:faqsListAll");
$route->get("/faqs/list/{faqId}", "Faqs\Faqs:faqsListById");

$route->post("/faqs-categories", "FaqsCategories:faqsCategoriesCreate");
$route->post("/faqs/insert", "Faqs:faqInsert");

$route->put("/faqs/update/{faqId}", "Faqs:faqUpdate");
$route->put("/faqs-categories/update/{categoryId}", "FaqsCategories:faqCategoryUpdate");

$route->delete("/faqs/{faqId}", "Faqs\Faqs:faqDelete");
$route->delete("/faqs-categories/{categoryId}", "Faqs\FaqsCategories:faqCategoryDelete");


//----------- COMPANIES -----------------------
$route->get("/companies/list", "Companies\Companies:companiesListAll");
$route->get("/companies/list/{companyId}", "Companies\Companies:companiesListById");
$route->post("/companies", "Companies\Companies:companyInsert");
$route->put("/companies/{companyId}", "Companies\Companies:companyUpdate");
$route->delete("/companies/{companyId}", "Companies\Companies:companyDelete");

//----------- EMPLOYEES -----------------------
$route->get("/employees/list", "Companies\Employees:employeesListAll");
$route->get("/employees/list/{employeeId}", "Companies\Employees:employeesListById");
$route->post("/employees", "Companies\Employees:employeeInsert");
$route->put("/employees/{employeeId}", "Companies\Employees:employeeUpdate");
$route->delete("/employees/{employeeId}", "Companies\Employees:employeeDelete");

//----------- DEVICES -----------------------
$route->get("/devices/list", "Devices\Devices:devicesListAll");
$route->get("/devices/list/{deviceId}", "Devices\Devices:devicesListById");
$route->post("/devices", "Devices\Devices:deviceInsert");
$route->put("/devices/{deviceId}", "Devices\Devices:deviceUpdate");
$route->delete("/devices/{deviceId}", "Devices\Devices:deviceDelete");

//----------- DEVICES CATEGORIES -----------------------
$route->get("/devices-categories/list", "Devices\DevicesCategories:devicesCategoryList");
$route->get("/devices-categories/list/{categoryId}", "Devices\DevicesCategories:devicesCategoryListById");
$route->post("/devices-categories", "Devices\DevicesCategories:devicesCategoriesCreate");
$route->put("/devices-categories/{categoryId}", "Devices\DevicesCategories:deviceCategoryUpdate");
$route->delete("/devices-categories/{categoryId}", "Devices\DevicesCategories:deviceCategoryDelete");

//----------- PLANS -----------------------
$route->get("/plans/list", "Plans:plansListAll");
$route->get("/plans/list/{planId}", "Plans:plansListById");
$route->post("/plans", "Plans:planInsert");
$route->put("/plans/{planId}", "Plans:planUpdate");
$route->delete("/plans/{planId}", "Plans:planDelete");


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