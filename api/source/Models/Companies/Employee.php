<?php

namespace source\Models\Companies;

use Source\Core\Connect;

class Employee
{
    private ?int $id;
    private ?int $userId;
    private ?int $companyId;
    private ?int $active;

    public function __construct(
        ?int $id = null,
        ?int $userId = null,
        ?int $companyId = null,
        ?int $active = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->companyId = $companyId;
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
    public function getCompanyId(): int
    {
        return $this->companyId;
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
    public function setCompanyId(int $companyId)
    {
        $this->companyId = $companyId;
    }
    public function setActive(int $active)
    {
        $this->active = $active;
    }

    public function listAll(): array
    {
        $query = "SELECT e.id, u.name as 'user_name', c.name as 'company_name'
                  FROM employees as e
                  JOIN users as u ON e.user_id = u.id
                  JOIN companies as c ON e.company_id = c.id
                  WHERE e.active = 1";
        $stmt = Connect::getInstance()->query($query);
        return $stmt->fetchAll();
    }

    public function listById(int $id): object|bool
    {
        $query = "SELECT e.* FROM employees as e
                  JOIN users as u ON e.user_id = u.id
                  JOIN companies as c ON e.company_id = c.id
                  WHERE e.active = 1
                  AND e.id = :id";
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
        $query = "INSERT INTO employees (user_id, company_id) VALUES (:user_id, :company_id)";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute([
            ":user_id" => $this->userId,
            ":company_id" => $this->companyId
        ]);
        if ($stmt->rowCount() > 0) {
            $this->id = Connect::getInstance()->lastInsertId();
            $query = "SELECT e.* FROM employees as e
                  JOIN users as u ON e.user_id = u.id
                  JOIN companies as c ON e.company_id = c.id
                  WHERE e.id = :id";
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
        $query = "SELECT * FROM employees WHERE id = :id AND active = 1";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $this->id);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            return false;
        }

        $current = $stmt->fetch();

        $userId = $this->userId ?? $current->user_id;
        $companyId = $this->companyId ?? $current->company_id;

        $query = "UPDATE employees SET user_id = :user_id, company_id = :company_id WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute([
            ":user_id" => $userId,
            ":company_id" => $companyId,
            ":id" => $this->id
        ]);
        if ($stmt->rowCount() > 0) {
            $query = "SELECT e.* FROM employees as e
                      JOIN users as u ON e.user_id = u.id
                      JOIN companies as c ON e.company_id = c.id
                      WHERE e.id = :id";
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
        $query = "UPDATE employees SET active = 0 WHERE id = :id AND active = 1";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $this->id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
}