<?php

namespace source\Controller;

use Source\Controller\Api;
use Source\Models\Product;

class Products extends Api
{
    public function productsList ()
    {
        echo "Lista de produtos.. <br>";
        $product = new Product();
    }

}