<?php

namespace Source\Models;

use Source\Core\Model;
use Source\Core\Connect;

class ServiceOrder extends Model
{
    private ?int $id;
    private ?int $userId;
    private ?int $deviceId;
    private ?string $defect;
    private ?string $diagnosis;
    private ?string $status;
    private ?float $price;
    private ?string $photo;
    private ?string $creationTime;

    public function __construct(?int $id = null, ?int $userId = null, ?int $deviceId = null, ?string $defect = null, ?string $diagnosis = null, ?string $status = null, ?float $price = null, ?string $photo = null, ?string $creationTime = null)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->deviceId = $deviceId;
        $this->defect = $defect;
        $this->diagnosis = $diagnosis;
        $this->status = $status;
        $this->price = $price;
        $this->photo = $photo;
        $this->creationTime = $creationTime;

        $this->table = 'service_orders';
        $this->primaryKey = 'id';
        $this->fillable = ['userId', 'deviceId', 'defect', 'diagnosis', 'status', 'price', 'photo'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function getDeviceId(): ?int
    {
        return $this->deviceId;
    }

    public function setDeviceId(?int $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    public function getDefect(): ?string
    {
        return $this->defect;
    }

    public function setDefect(?string $defect): void
    {
        $this->defect = $defect;
    }

    public function getDiagnosis(): ?string
    {
        return $this->diagnosis;
    }

    public function setDiagnosis(?string $diagnosis): void
    {
        $this->diagnosis = $diagnosis;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): void
    {
        $this->photo = $photo;
    }

    public function getCreationTime(): ?string
    {
        return $this->creationTime;
    }

    public function setCreationTime(?string $creationTime): void
    {
        $this->creationTime = $creationTime;
    }

    public function countOpenOrders(): int
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE status != 'concluida' AND status != 'cancelada'";
        try {
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return (int)($result->total ?? 0);
        } catch (\PDOException $e) {
            $this->errorMessage = $e->getMessage();
            return 0;
        }
    }

    public function countOpenOrdersByUser(int $userId): int
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE user_id = :userId AND status != 'concluida' AND status != 'cancelada'";
        try {
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            return (int)($result->total ?? 0);
        } catch (\PDOException $e) {
            $this->errorMessage = $e->getMessage();
            return 0;
        }
    }
}