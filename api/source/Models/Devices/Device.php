<?php

namespace source\Models\Devices;

use Source\Core\Connect;

class Device
{
    private ?int $id;
    private ?int $userId;
    private ?int $categoryId;
    private ?string $serialNumber;
    private ?string $model;
    private ?string $brand;
    private ?string $creationTime;
    private ?int $active;

    public function __construct(
        ?int $id = null,
        ?int $userId = null,
        ?int $categoryId = null,
        ?string $serialNumber = null,
        ?string $model = null,
        ?string $brand = null,
        ?string $creationTime = null,
        ?int $active = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->categoryId = $categoryId;
        $this->serialNumber = $serialNumber;
        $this->model = $model;
        $this->brand = $brand;
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
    public function getModel(): string
    {
        return $this->model;
    }
    public function getBrand(): string
    {
        return $this->brand;
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
    public function setModel(string $model)
    {
        $this->model = $model;
    }
    public function setBrand(string $brand)
    {
        $this->brand = $brand;
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
        $query = "SELECT d.id, d.serial_number, d.model, d.brand, c.name as 'category_name', u.name as 'user_name'
                  FROM devices as d
                  JOIN users as u ON d.user_id = u.id
                  JOIN devices_categories as c ON d.category_id = c.id
                  WHERE d.active = 1
                  GROUP BY c.name
                  ORDER BY d.creation_time DESC";
        $stmt = Connect::getInstance()->query($query);
        return $stmt->fetchAll();
    }

    public function listById(int $id): object|bool
    {
        $query = "SELECT * FROM devices as d
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
        $query = "INSERT INTO devices (user_id, category_id, serial_number, model, brand) VALUES ($this->userId, $this->categoryId, $this->serialNumber, $this->model, $this->brand)";
        $stmt = Connect::getInstance()->query($query);
        if ($stmt->rowCount() > 0) {
            $this->id = Connect::getInstance()->lastInsertId();
            $query = "SELECT * FROM devices as d
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

    public function update(array $data): array|bool
    {
        $query = "SELECT * FROM devices WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $data['deviceId']);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            return false;
        }
        $stmt->fetch();

        $query = "UPDATE devices SET user_id = :user_id, category_id = :cat_id, serial_number = :serial_number, model = :model, brand = :brand WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute([
            ":user_id" => $data['user_id'],
            ":cat_id" => $data['category_id'],
            ":serial_number" => $data['serial_number'],
            ":model" => $data['model'],
            ":brand" => $data['brand'],
            ":id" => $data['deviceId']
        ]);
        if ($stmt->rowCount() > 0) {
            $query = "SELECT * FROM devices as d
                      JOIN users as u ON d.user_id = u.id
                      JOIN devices_categories as c ON d.category_id = c.id
                      WHERE d.id = :id";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindParam(':id', $data['deviceId']);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll();
            }
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $query = "UPDATE devices SET active = 0 WHERE id = :id AND active = 1";
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