<?php

namespace source\Models;

use PDO;
use Source\Core\Model;
use Source\Core\Connect;
use Source\Core\JWTToken;

class User extends Model
{
    private ?int $id;
    private ?int $typeId;
    private ?string $cpf;
    private ?string $name;
    private ?string $email;
    private ?string $password;
    private ?string $photo;
    private ?string $token = null;
    private ?string $active;

    public function __construct(?int $id = null, ?int $typeId = null, ?string $cpf = null, ?string $name = null, ?string $email = null, ?string $password = null, ?string $photo = null)
    {
        $this->id = $id;
        $this->typeId = $typeId;
        $this->cpf = $cpf;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->photo = $photo;

        $this->table = 'users';
        $this->primaryKey = 'id';
        $this->fillable = ['typeId', 'cpf', 'name', 'email', 'password', 'photo'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    public function setTypeId(?int $typeId): void
    {
        $this->typeId = $typeId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): void
    {
        $this->photo = $photo;
    }

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setCpf(?string $cpf): void
    {
        $this->cpf = $cpf;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function insert (): bool
    {
        $query = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        if($stmt->rowCount() > 0){
            $this->errorMessage = "Email já cadastrado";
            return false;
        }
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        if(!parent::insert()){
            $this->errorMessage = "Algo deu errado";
            return false;
        }
        return true;
    }

    protected function camelToSnake(string $field): string
    {
        return $field;
    }

    public function update(int $id): bool
    {
        if ($id < 1) {
            $this->errorMessage = "ID de usuário inválido.";
            return false;
        }

        if ($this->email !== null) {
            try {
                $query = "SELECT id FROM {$this->table} WHERE email = :email AND id <> :id LIMIT 1";
                $stmt = Connect::getInstance()->prepare($query);
                $stmt->bindValue(":email", $this->email, PDO::PARAM_STR);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);
                $stmt->execute();

                if ($stmt->fetchColumn() !== false) {
                    $this->errorMessage = "E-mail já cadastrado por outro usuário.";
                    return false;
                }
            } catch (\PDOException $exception) {
                $this->errorMessage = "Não foi possível validar o e-mail do usuário.";
                return false;
            }
        }

        if (parent::update($id)) {
            return true;
        }

        if ($this->errorMessage !== "Registro não encontrado ou sem alterações.") {
            return false;
        }

        $query = "SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() !== false) {
            $this->errorMessage = null;
            return true;
        }

        return false;
    }

    // public function listAll(): array
    // {
    //     $query = "SELECT id, cpf, name, email
    //               FROM users
    //               ORDER BY id DESC";
    //     $stmt = Connect::getInstance()->query($query);
    //     return $stmt->fetchAll();
    // }

    public function listById(int $id): object|bool
    {
        $query = "SELECT id, cpf, name, email
                  FROM users
                  WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }

    public function login (string $email, string $password): bool
    {
        $query = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        if($stmt->rowCount() == 0){
            $this->errorMessage = "Email não cadastrado";
            return false;
        }
        $user = $stmt->fetch();
        if(!password_verify($password, $user->password)){
            $this->errorMessage = "Senha incorreta";
            return false;
        }
        $this->id = $user->id;
        $this->typeId = $user->typeId;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->photo = $user->photo;
        $jwt = new JWTToken();
        // definir quais informações irão par o payload do token
        $this->token = $jwt->encode([
            "id" => $user->id,
            "typeId" => $user->typeId,
            "name" => $user->name,
            "email" => $user->email,
        ]);
        return true;
    }

    public function permissionVerify (string $email, $typeId): bool
    {
        $query = "SELECT * FROM {$this->table} WHERE email = :email AND typeId = :typeId";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":typeId", $typeId);
        $stmt->execute();
        if($stmt->rowCount() == 0) {
            return false;
        }
        return true;
    }

}