<?php

namespace source\Models;

use Source\Core\Connect;

class Product
{
    private ?int $id;
    private ?int $categoryId;
    private ?string $name;
    private ?float $price;

    public function __construct(?int $id = null, ?int $categoryId, ?string $name, ?float $price)
    {
        $this->id = $id;
        $this->categoryId = $categoryId;
        $this->name = $name;
        $this->price = $price;
    }
    public function getId(): int{
        return $this->id;
    }
    public function getCategoryId(): int{
        return $this->categoryId;
    }
    public function getName(): string{
        return $this->name;
    }
    public function getPrice(): float{
        return $this->price;
    }
    public function setId(int $id){
        $this->id = $id;
    }
    public function setCategoryId(int $categoryId){
        $this->categoryId = $categoryId;
    }
    public function setName(string $name){
        $this->name = $name;
    }
    public function setPrice(float $price){
        $this->price = $price;
    }

    public function listAll(): array{
        $sql = "SELECT * FROM products as p
                JOIN products_categories as pc ON p.category_id = pc.id";
        $stmt = Connect::getInstance()->query($sql);
        var_dump($stmt);
    }
}