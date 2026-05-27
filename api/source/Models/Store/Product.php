<?php

namespace source\Models;

use Source\Core\Connect;

class Product
{
    private ?int $id;
    private ?int $categoryId;
    private ?string $name;
    private ?float $price;

    public function __construct(?int $id = null, ?int $categoryId = null, ?string $name = null, ?float $price = null)
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
        $query = "SELECT products.id, products.name, products.price, products_categories.name as 'category_name' 
                  FROM products
                  JOIN products_categories ON products.category_id = products_categories.id";
        $stmt = Connect::getInstance()->query($query);
        return $stmt->fetchAll();
    }
    public function listById(int $id): object | bool 
    {
        $query = "SELECT * FROM products WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if($stmt->rowCount() > 0){
            return $stmt->fetch();
        }
        return false;