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
    private ?string $diagnosis;
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
        ?string $diagnosis = null,
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
        $this->diagnosis = $diagnosis;
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
    public function getDiagnosis(): string
    {
        return $this->diagnosis;
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
    public function setDiagnosis(string $diagnosis)
    {
        $this->diagnosis = $diagnosis;
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

    public function listAll(?int $userId = null, ?int $userType = null): array
    {
        $visibility = "";
        $parameters = [];
        if ($userType === 3 && $userId !== null) {
            $visibility = " AND s.user_id = :user_id";
            $parameters[":user_id"] = $userId;
        } elseif ($userType === 2 && $userId !== null) {
            $visibility = " AND (
                EXISTS (
                    SELECT 1 FROM employees AS e
                    WHERE e.user_id = :employee_user_id
                      AND e.company_id = s.company_id
                      AND e.active = 1
                )
                OR EXISTS (
                    SELECT 1 FROM companies AS owned_company
                    WHERE owned_company.id = s.company_id
                      AND owned_company.owner_id = :owner_user_id
                      AND owned_company.active = 1
                )
            )";
            $parameters[":employee_user_id"] = $userId;
            $parameters[":owner_user_id"] = $userId;
        }

        $query = "SELECT s.id,
                 s.company_id,
                 c.name as 'company_name',
                         u.name as 'user_name', 
                         d.name as 'device_model', 
                         s.defect, 
                         s.status, 
                         s.price,
                         s.photo,
                         s.creation_time
                  FROM service_orders as s
                  JOIN users as u ON s.user_id = u.id
                  JOIN devices as d ON s.device_id = d.id
                  LEFT JOIN companies as c ON s.company_id = c.id
                  WHERE s.active = 1
                  {$visibility}
                  ORDER BY s.creation_time DESC";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute($parameters);
        return $stmt->fetchAll();
    }

    public function listById(int $id, ?int $userId = null, ?int $userType = null)
    {
        $visibility = "";
        $parameters = [":id" => $id];
        if ($userType === 3 && $userId !== null) {
            $visibility = " AND s.user_id = :user_id";
            $parameters[":user_id"] = $userId;
        } elseif ($userType === 2 && $userId !== null) {
            $visibility = " AND (
                EXISTS (
                    SELECT 1 FROM employees AS e
                    WHERE e.user_id = :employee_user_id
                      AND e.company_id = s.company_id
                      AND e.active = 1
                )
                OR EXISTS (
                    SELECT 1 FROM companies AS owned_company
                    WHERE owned_company.id = s.company_id
                      AND owned_company.owner_id = :owner_user_id
                      AND owned_company.active = 1
                )
            )";
            $parameters[":employee_user_id"] = $userId;
            $parameters[":owner_user_id"] = $userId;
        }

        $query = "SELECT s.*, u.name AS user_name, d.name AS device_model, c.name AS company_name
                  FROM service_orders as s
                  JOIN users as u ON s.user_id = u.id
                  JOIN devices as d ON s.device_id = d.id
                  JOIN companies as c ON s.company_id = c.id
                  WHERE s.active = 1
                  AND s.id = :id
                  {$visibility}";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute($parameters);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }

    public function insert()
    {
        $query = "INSERT INTO service_orders (user_id, device_id, company_id, defect, diagnosis, status, price, photo)
                  VALUES (:userId, :deviceId, :companyId, :defect, :diagnosis, :status, :price, :photo)";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':userId', $this->userId);
        $stmt->bindValue(':deviceId', $this->deviceId);
        $stmt->bindValue(':companyId', $this->companyId);
        $stmt->bindValue(':defect', $this->defect);
        $stmt->bindValue(':diagnosis', $this->diagnosis);
        $stmt->bindValue(':status', $this->status);
        $stmt->bindValue(':price', $this->price);
        $stmt->bindValue(':photo', $this->photo);
        $stmt->execute();
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
                return $stmt->fetch();
            }
        }
        return false;
    }

    public function update(array $data)
    {
        $id = (int) ($data['serviceOrderId'] ?? 0);
        if ($id < 1 || !$this->listById($id)) {
            return false;
        }

        $fields = [
            "user_id",
            "device_id",
            "company_id",
            "defect",
            "diagnosis",
            "status",
            "price",
        ];
        $updates = [];
        $values = [":id" => $id];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "{$field} = :{$field}";
                $values[":{$field}"] = $data[$field];
            }
        }

        if ($updates === []) {
            return false;
        }

        $query = "UPDATE service_orders SET " . implode(", ", $updates) . " WHERE id = :id AND active = 1";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute($values);

        return $this->listById($id);
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