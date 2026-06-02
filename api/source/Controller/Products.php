<?php

namespace source\Controller;

use Source\Controller\Api;
use Source\Models\Store\Product;

$product = '{
  "name": "Headset HyperX Cloud II",
  "price": 349.90,
  "category_id": 2
}';

class Products extends Api
{
    public function productsList (): void
    {
        $product = new Product();
        $this->call("200", "success", "Lista de produtos", "success")->back($product->listAll());
    }

    public function productsListById(array $data): void{
       if(!filter_var($data['productId'], FILTER_VALIDATE_INT)){
            $this->call(
                400,
                "bad_request",
                "ID do produto obrigatório e deve ser um número inteiro",
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

    public function create(array $data):void{
        echo "Criar produto";
        var_dump($data);
    }
    public function update(array $data):void{
        echo "Atualizar produto";
        var_dump($data);
    }
    public function delete(array $data):void{
        echo "Deletar produto";
        var_dump($data);
    }
}