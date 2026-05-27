<?php

namespace source\Models;

use Source\Core\Connect;

class ProductCategory
{
    private ?int $id;
    private ?string $name;

    public function __construct(?int $id = null, ?string $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function setId(int $id)
    {
        $this->id = $id;
    }
    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function listAll(): array
    {
        $query = "SELECT * FROM products_categories";
        $stmt = Connect::getInstance()->query($query);
        return $stmt->fetchAll();
    }
    public function listById(int $id): object|bool
    {
        $query = "SELECT * FROM products_categories WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }
}