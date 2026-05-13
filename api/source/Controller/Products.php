<?php

namespace source\Controller;

use Source\Controller\Api;
use Source\Models\Product;

class Products extends Api
{
    public function productsList (): void
    {
        $products = new Product();
        $this->call(200,"success","Lista de Produtos","success")->back($products->listAll());
    }

    public function productsListById (array $data): void
    {
        if(!filter_var($data["productId"], FILTER_VALIDATE_INT)){
            $this->call(
                400,
                "bad_request",
                "ID do produto é obrigatório e deve ser um número inteiro",
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
                "Produto não encontrado",
                "error"
            )->back();
            return;
        }

        $this->call(200,"success","Produto encontrado","success")
            ->back($product);

    }

}