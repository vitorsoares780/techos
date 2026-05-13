<?php

namespace source\Controller;

use Source\Controller\Api;
use Source\Models\Product;

class Products extends Api
{
    public function productsList (): void
    {
        $product = new Product();
        $this->call("200", "success", "Lista de produtos", "success")->back($response);
    }

    public function productsListById(array $data): void{
       if(!filter_var($data['productId'], FILTER_VALIDADE_INT)){
            $this->call(
                400,
                "bad_request",
                "ID do produto obrigatório e dever ser um número inteiro",
                "error"
            )->back();
            return;
       }
        
        $product = new Product();
        $product = $product->listById($data["productId"]);

        if(!$product){
            $this->call(
                404,
                "not_found",
                "produto não encontrado",
                "error"
            )->back();
            return;
        }

        $this->call(200,"success","Produto encontrado","success")->back($product);
    }
}