<?php

namespace source\Models;

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
        $this->companyId = $companyId;
        $this->defect = $defect;
        $this->status = $status;
        $this->price = $price;
        $this->photo = $photo;
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
    public function getCompanyId(): int
    {
        return $this->companyId;
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
    public function getPhoto(): string
    {
        return $this->photo;
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
    public function setCompanyId(int $companyId)
    {
        $this->companyId = $companyId;
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
    public function setPhoto(string $photo)
    {
        $this->photo = $photo;
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
        $query = "SELECT s.id, 
                         u.name as 'user_name', 
                         d.model as 'device_model', 
                         c.name as 'company_name', 
                         s.defect, 
                         s.status, 
                         s.price,
                         s.photo
                  FROM service_orders as s
                  JOIN users as u ON s.user_id = u.id
                  JOIN devices as d ON s.device_id = d.id
                  JOIN companies as c ON s.company_id = c.id
                  WHERE s.active = 1
                  ORDER BY s.creation_time DESC";
        $stmt = Connect::getInstance()->query($query);
        return $stmt->fetchAll();
    }

    public function listById(int $id): object|bool
    {
        $query = "SELECT s.* FROM service_orders as s
                  JOIN users as u ON s.user_id = u.id
                  JOIN devices as d ON s.device_id = d.id
                  JOIN companies as c ON s.company_id = c.id
                  WHERE s.active = 1
                  AND s.id = :id";
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
        $query = "INSERT INTO service_orders (user_id, device_id, company_id, defect, status, price, photo) 
                  VALUES ($this->userId, $this->deviceId, $this->companyId, $this->defect, $this->status, $this->price, $this->photo)";
        $stmt = Connect::getInstance()->query($query);
        if ($stmt->rowCount() > 0) {
            $this->id = Connect::getInstance()->lastInsertId();
            $query = "SELECT s.* FROM service_orders as s
                  JOIN users as u ON s.user_id = u.id
                  JOIN devices as d ON s.device_id = d.id
                  JOIN companies as c ON s.company_id = c.id
                  WHERE s.active = 1
                  AND s.id = :id";
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
        $query = "SELECT * FROM service_orders WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $data['serviceOrderId']);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            return false;
        }
        $stmt->fetch();

        $query = "UPDATE service_orders SET user_id = :user_id, 
                                            device_id = :device_id,
                                            company_id = :company_id,
                                            defect = :defect, 
                                            status = :status, 
                                            price = :price,
                                            photo = :photo
                  WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute([
            ":user_id" => $data['user_id'],
            ":device_id" => $data['device_id'],
            ":company_id" => $data['company_id'],
            ":defect" => $data['defect'],
            ":status" => $data['status'],
            ":price" => $data['price'],
            ":photo" => $data['photo'],
            ":id" => $data['serviceOrderId']
        ]);
        if ($stmt->rowCount() > 0) {
            $query = "SELECT s.* FROM service_orders as s
                  JOIN users as u ON s.user_id = u.id
                  JOIN devices as d ON s.device_id = d.id
                  JOIN companies as c ON s.company_id = c.id
                  WHERE s.active = 1
                  AND s.id = :id";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindParam(':id', $data['serviceOrderId']);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll();
            }
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $query = "UPDATE service_orders SET active = 0 WHERE id = :id AND active = 1";
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