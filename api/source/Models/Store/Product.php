<?php

namespace Source\Models\Store;

use Source\Core\Model;
use Source\Core\Connect;

class Product extends Model
{
    private ?int $id;
    private ?int $categoryId;
    private ?string $name;
    private ?float $price;
    private ?int $active;
    private ?string $dataCadastro;

    public function __construct(?int $id = null, ?int $categoryId = null, ?string $name = null, ?float $price = null, ?int $active = null, ?string $dataCadastro = null)
    {
        $this->id = $id;
        $this->categoryId = $categoryId;
        $this->name = $name;
        $this->price = $price;
        $this->active = $active;
        $this->dataCadastro = $dataCadastro;

        $this->table = 'products';
        $this->primaryKey = 'id';
        $this->fillable = ['categoryId', 'name', 'price', 'active', 'dataCadastro'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getActive(): ?int
    {
        return $this->active;
    }

    public function setActive(int $active): void
    {
        $this->active = $active;
    }

    public function getDataCadastro(): ?string
    {
        return $this->dataCadastro;
    }

    public function setDataCadastro(string $dataCadastro): void
    {
        $this->dataCadastro = $dataCadastro;
    }

    public function listAll(): array
    {
        $query = "SELECT products.id, products.name, products.price, products.data_cadastro, products.active, products_categories.name as 'category_name'
                  FROM products
                  JOIN products_categories ON products.category_id = products_categories.id";
        $stmt = Connect::getInstance()->query($query);
        return $stmt->fetchAll();
    }

    public function listById(int $id): object|bool
    {
        $query = "SELECT * FROM products WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if($stmt->rowCount() > 0){
            return $stmt->fetch();
        }
        return false;
    }
}