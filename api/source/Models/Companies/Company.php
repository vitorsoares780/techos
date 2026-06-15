<?php

namespace source\Models\Companies;

use Source\Core\Connect;

class Company
{
    private ?int $id;
    private ?string $cnpj;
    private ?string $name;
    private ?string $email;
    private ?int $ownerId;
    private ?int $planId;
    private ?string $creationTime;
    private ?int $active;

    public function __construct(
        ?int $id = null,
        ?string $cnpj = null,
        ?string $name = null,
        ?string $email = null,
        ?int $ownerId = null,
        ?int $planId = null,
        ?string $creationTime = null,
        ?int $active = null
    ) {
        $this->id = $id;
        $this->cnpj = $cnpj;
        $this->name = $name;
        $this->email = $email;
        $this->ownerId = $ownerId;
        $this->planId = $planId;
        $this->creationTime = date('d/m/Y H:i:s', $creationTime);
        $this->active = $active;
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCnpj(): ?string
    {
        return $this->cnpj;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function getOwnerId(): ?int
    {
        return $this->ownerId;
    }
    public function getPlanId(): ?int
    {
        return $this->planId;
    }
    public function getCreationTime(): ?string
    {
        return $this->creationTime;
    }
    public function getActive(): ?int
    {
        return $this->active;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function setCnpj(string $cnpj): void
    {
        $this->cnpj = $cnpj;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
    public function setOwnerId(int $ownerId): void
    {
        $this->ownerId = $ownerId;
    }
    public function setPlanId(int $planId): void
    {
        $this->planId = $planId;
    }
    public function setCreationTime(string $creationTime): void
    {
        $this->creationTime = $creationTime;
    }
    public function setActive(int $active): void
    {
        $this->active = $active;
    }

    public function listAll(): array
    {
        $query = "SELECT c.id, c.cnpj, c.name, c.email, u.name as 'owner_name', p.name as 'plan_name'
                  FROM companies as c
                  JOIN users as u ON c.owner_id = u.id
                  JOIN plans as p ON c.plan_id = p.id
                  WHERE c.active = 1
                  GROUP BY c.name
                  ORDER BY c.creation_time DESC";
        $stmt = Connect::getInstance()->query($query);
        return $stmt->fetchAll();
    }

    public function listById(int $id): object|bool
    {
        $query = "SELECT c.id, c.cnpj, c.name, c.email, u.name as 'owner_name', p.name as 'plan_name'
                  FROM companies as c
                  JOIN users as u ON c.owner_id = u.id
                  JOIN plans as p ON c.plan_id = p.id
                  WHERE c.active = 1
                  AND c.id = :id";
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
        $query = "INSERT INTO companies (cnpj, name, email, owner_id, plan_id) VALUES ($this->cnpj, $this->name, $this->email, $this->ownerId, $this->planId)";
        $stmt = Connect::getInstance()->query($query);
        if ($stmt->rowCount() > 0) {
            $this->id = Connect::getInstance()->lastInsertId();
            $query = "SELECT * FROM companies as c
                      JOIN users as u ON c.owner_id = u.id
                      JOIN plans as p ON c.plan_id = p.id
                      WHERE c.id = :id";
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
        $query = "SELECT * FROM companies WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $data['companyId']);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            return false;
        }
        $stmt->fetch();

        $query = "UPDATE companies SET cnpj = :cnpj, name = :name, email = :email, owner_id = :owner_id, plan_id = :plan_id WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute([
            ":cnpj" => $data['cnpj'],
            ":name" => $data['name'],
            ":email" => $data['email'],
            ":owner_id" => $data['owner_id'],
            ":plan_id" => $data['plan_id'],
            ":id" => $data['companyId']
        ]);
        if ($stmt->rowCount() > 0) {
            $query = "SELECT * FROM companies as c
                      JOIN users as u ON c.owner_id = u.id
                      JOIN plans as p ON c.plan_id = p.id
                      WHERE c.id = :id";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindParam(':id', $data['companyId']);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll();
            }
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $query = "UPDATE companies SET active = 0 WHERE id = :id AND active = 1";
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