<?php

namespace source\Models\Faqs;

use Source\Core\Connect;

class FaqCategory
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
        $query = "SELECT * FROM faqs_categories";
        $stmt = Connect::getInstance()->query($query);
        return $stmt->fetchAll();
    }
    public function listById(int $id): object|bool
    {
        $query = "SELECT * FROM faqs_categories WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }

    public function insert(): bool
    {
        $query = "INSERT INTO faqs_categories (name) VALUES (:name)";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->execute();
        if ($stmt->rowCount() == 1) {
            $this->id = Connect::getInstance()->lastInsertId();
            return true;
        }
        return false;
    }

    public function update(array $data): bool
    {
        $query = "SELECT * FROM faqs_categories WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindParam(':id', $data['categoryId']);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            return false;
        }
        $stmt->fetch();

        $query = "UPDATE faqs_categories SET name = :name WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':id', $data['categoryId']);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $stmt->fetch();
            return true;
        }
        return false;

    }
}