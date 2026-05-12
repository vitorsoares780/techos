<?php

namespace source\Controller;

use source\Controller\Api;
use source\Models\Product;

class Products extends Api
{
    public function productsList (): void
    {
        $product = new Product();
        $product->findById(); // passar id?
        $response = $product->listAll();
        $this->call("200", "success", "Lista de produtos", "success")->back($response);
    }

    public function productById(array $data): void{

    }

}