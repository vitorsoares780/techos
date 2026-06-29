<?php

namespace source\Models\Devices;

use Source\Core\Connect;

class Device
{
    private ?int $id;
    private ?int $userId;
    private ?int $categoryId;
    private ?string $serialNumber;
    private ?string $name;
    private ?string $creationTime;
    private ?int $active;

    public function __construct(
        ?int $id = null,
        ?int $userId = null,
        ?int $categoryId = null,
        ?string $serialNumber = null,
        ?string $name = null,
        ?string $creationTime = null,
        ?int $active = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->categoryId = $categoryId;
        $this->serialNumber = $serialNumber;
        $this->name = $name;
        $this->creationTime = date('d/m/Y H:i:s', $creationTime);
        $this->active = $active;
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getUserId(): int
    {
        return $this->userId;
    }
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }
    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }
    public function getname(): string
    {
        return $this->name;
    }
    public function getCreationTime(): string
    {
        return $this->creationTime;
    }
    public function getActive(): string
    {
        return $this->active;
    }
    public function setId(int $id)
    {
        $this->id = $id;
    }
    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }
    public function setCategoryId(int $categoryId)
    {
        $this->categoryId = $categoryId;
    }
    public function setSerialNumber(string $serialNumber)
    {
        $this->serialNumber = $serialNumber;
    }
    public function setname(string $name)
    {
        $this->name = $name;
    }
    public function setCreationTime(string $creationTime)
    {
        $this->creationTime = $creationTime;
    }
    public function setActive(int $active)
    {
        $this->active = $active;
    }

    public function listAll(): array
    {
        $query = "SELECT d.id, d.serial_number, d.name, c.name as 'category_name', u.name as 'user_name'
                  FROM devices as d
                  JOIN users as u ON d.user_id = u.id
                  JOIN devices_categories as c ON d.category_id = c.id
                  WHERE d.active = 1
                  ORDER BY d.creation_time DESC";
        $stmt = Connect::getInstance()->query($query);
        return $stmt->fetchAll();
    }

    public function listById(int $id): object|bool
    {
        $query = "SELECT d.* FROM devices as d
                  JOIN users as u ON d.user_id = u.id
                  JOIN devices_categories as c ON d.category_id = c.id
                  WHERE d.active = 1
                  AND d.id = :id";
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
        $query = "INSERT INTO devices (user_id, category_id, serial_number, name) VALUES (:user_id, :cat_id, :serial_number, :name)";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute([
            ":user_id" => $this->userId,
            ":cat_id" => $this->categoryId,
            ":serial_number" => $this->serialNumber,
            ":name" => $this->name
        ]);
        if ($stmt->rowCount() > 0) {
            $this->id = Connect::getInstance()->lastInsertId();
            $query = "SELECT d.* FROM devices as d
                      JOIN users as u ON d.user_id = u.id
                      JOIN devices_categories as c ON d.category_id = c.id
                      WHERE d.id = :id";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll();
            }
        }
        return false;
    }

    public function update(): array|bool
    {
        $query = "SELECT * FROM devices WHERE id = :id AND active = 1";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $this->id);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            return false;
        }

        $current = $stmt->fetch();

        $userId = $this->userId ?? $current->user_id;
        $catId = $this->categoryId ?? $current->category_id;
        $serial = $this->serialNumber ?? $current->serial_number;
        $name = $this->name ?? $current->name;

        $query = "UPDATE devices SET user_id = :user_id, category_id = :cat_id, serial_number = :serial_number, name = :name WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute([
            ":user_id" => $userId,
            ":cat_id" => $catId,
            ":serial_number" => $serial,
            ":name" => $name,
            ":id" => $this->id
        ]);
        if ($stmt->rowCount() > 0) {
            $query = "SELECT d.* FROM devices as d
                      JOIN users as u ON d.user_id = u.id
                      JOIN devices_categories as c ON d.category_id = c.id
                      WHERE d.id = :id";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll();
            }
        }
        return false;
    }

    public function delete(): bool
    {
        $query = "UPDATE devices SET active = 0 WHERE id = :id AND active = 1";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $this->id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
}