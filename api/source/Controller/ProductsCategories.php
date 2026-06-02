<?php

namespace source\Controller;

use Source\Controller\Api;
use Source\Models\Store\ProductCategory;

class ProductsCategories extends Api
{
    public function productsCategoryList ()
    {
        $productsCategories = new ProductCategory();
        $this->call(200,"success","Lista de Categorias de Produtos","success")->back($productsCategories->listAll());
    }
    public function productsCategoryListById (array $data):void
    {
        if(!filter_var($data['categoryId'], FILTER_VALIDATE_INT)){
            $this->call(
                400,
                "bad_request",
                "ID da categoria do produto é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $productCategory = new ProductCategory();
        $productCategory = $productCategory->listById($data["categoryId"]);
        
        if(!$data['categoryId']){
            $this->call(
                404,
                "not_found",
                "Categoria não encontrada",
                "error"
            )->back();
            return;
        }

        $this->call(200,"success","Categoria encontrada","success")->back($productCategory);
    }
}