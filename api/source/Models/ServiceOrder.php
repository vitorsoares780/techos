<?php

namespace source\statuss;

use Source\Core\Connect;

class ServiceOrder
{
    private ?int $id;
    private ?int $userId;
    private ?int $deviceId;
    private ?int $companyId;
    private ?string $defect;
    private ?string $status;
    private ?float $price;
    private ?string $photo;
    private ?string $creationTime;
    private ?int $active;

    public function __construct(
        ?int $id = null,
        ?int $userId = null,
        ?int $deviceId = null,
        ?int $companyId = null,
        ?string $defect = null,
        ?string $status = null,
        ?float $price = null,
        ?string $photo = null,
        ?string $creationTime = null,
        ?int $active = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->deviceId = $deviceId;
        $this->deviceId = $deviceId;
        $this->defect = $defect;
        $this->status = $status;
        $this->price = $price;
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
    public function getDeviceId(): int
    {
        return $this->deviceId;
    }
    public function getDefect(): string
    {
        return $this->defect;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function getPrice(): float
    {
        return $this->price;
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
    public function setDeviceId(int $deviceId)
    {
        $this->deviceId = $deviceId;
    }
    public function setDefect(string $defect)
    {
        $this->defect = $defect;
    }
    public function setStatus(string $status)
    {
        $this->status = $status;
    }
    public function setPrice(float $price)
    {
        $this->price = $price;
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
        $query = "SELECT d.id, d.serial_number, d.status, d.price, c.name as 'category_name', u.name as 'user_name'
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
        $query = "INSERT INTO devices (user_id, category_id, serial_number, status, price) VALUES ($this->userId, $this->categoryId, $this->defect, $this->status, $this->price)";
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

        $query = "UPDATE devices SET user_id = :user_id, category_id = :cat_id, serial_number = :serial_number, status = :status, price = :price WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute([
            ":user_id" => $data['user_id'],
            ":cat_id" => $data['category_id'],
            ":serial_number" => $data['serial_number'],
            ":status" => $data['status'],
            ":price" => $data['price'],
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