<?php

namespace source\Models;

use Source\Core\Connect;

class Plan
{
    private ?int $id;
    private ?string $name;
    private ?float $price;
    private ?int $active;

    public function __construct( ?int $id = null, ?string $name = null, ?float $price = null, ?int $active = null) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->active = $active;
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getPrice(): float
    {
        return $this->price;
    }
    public function getActive(): string
    {
        return $this->active;
    }
    public function setId(int $id)
    {
        $this->id = $id;
    }
    public function setName(string $name)
    {
        $this->name = $name;
    }
    public function setPrice(float $price)
    {
        $this->price = $price;
    }
    public function setActive(int $active)
    {
        $this->active = $active;
    }

    public function listAll(): array
    {
        $query = "SELECT * FROM plans WHERE active = 1";
        $stmt = Connect::getInstance()->query($query);
        return $stmt->fetchAll();
    }

    public function listById(int $id): object|bool
    {
        $query = "SELECT * FROM plans WHERE active = 1 AND id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }

    public function insert(): array|bool
    {
        $query = "INSERT INTO plans (name, price) VALUES ($this->name, $this->price)";
        $stmt = Connect::getInstance()->query($query);
        if ($stmt->rowCount() > 0) {
            $this->id = Connect::getInstance()->lastInsertId();
            $query = "SELECT * FROM plans WHERE id = :id";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll();
            }
        }
        return false;
    }

    public function update(array $data): array|bool
    {
        $query = "SELECT * FROM plans WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $data['planId']);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            return false;
        }
        $stmt->fetch();

        $query = "UPDATE plans SET name = :name, price = :price WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute([
            ":name" => $data['name'],
            ":price" => $data['price'],
            ":id" => $data['planId']
        ]);
        if ($stmt->rowCount() > 0) {
            $query = "SELECT * FROM plans WHERE id = :id";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindParam(':id', $data['planId']);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll();
            }
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $query = "UPDATE plans SET active = 0 WHERE id = :id AND active = 1";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $stmt->fetch();
            return true;
        }
        return false;
    }
}