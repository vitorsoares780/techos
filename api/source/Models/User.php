<?php

namespace source\Models;

use PDO;
use Source\Core\Model;
use Source\Core\Connect;
use Source\Core\JWTToken;

class User extends Model
{
    private ?int $id;
    private ?int $type_id;
    private ?string $cpf;
    private ?string $name;
    private ?string $email;
    private ?string $password;
    private ?string $photo;
    private ?string $token = null;
    private ?string $active;

    public function __construct(?int $id = null, ?int $type_id = null, ?string $cpf = null, ?string $name = null, ?string $email = null, ?string $password = null, ?string $photo = null)
    {
        $this->id = $id;
        $this->type_id = $type_id;
        $this->cpf = $cpf;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->photo = $photo;

        $this->table = 'users';
        $this->primaryKey = 'id';
        $this->fillable = ['type_id', 'cpf', 'name', 'email', 'password', 'photo'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function gettype_id(): ?int
    {
        return $this->type_id;
    }

    public function settype_id(?int $type_id): void
    {
        $this->type_id = $type_id;
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

    public function listAll(array $filters = [], ?string $orderBy = 'name', string $direction = 'ASC'): array
    {
        $query = "SELECT id, type_id, cpf, name, email, photo, active FROM users ORDER BY name ASC";
        $stmt = Connect::getInstance()->query($query);
        return $stmt->fetchAll();
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

    public function listById(int $id)
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

    public function findProfileById(int $id)
    {
        try {
            $query = "SELECT id, photo, active FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch() ?: false;
        } catch (\PDOException $exception) {
            $this->errorMessage = "Não foi possível consultar o perfil.";
            return false;
        }
    }

    public function listByEmail(string $email)
    {
        $query = "SELECT *
                  FROM users
                  WHERE email = :email";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }

    public function login (string $email, string $password, int $type_id): bool
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
        $this->type_id = $user->type_id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->photo = $user->photo;
        $jwt = new JWTToken();
        // definir quais informações irão par o payload do token
        $this->token = $jwt->encode([
            "id" => $user->id,
            "type_id" => $user->type_id,
            "name" => $user->name,
            "email" => $user->email,
        ]);
        return true;
    }

    public function updatePhoto(int $id, string $photo): bool
    {
        try {
            $query = "UPDATE {$this->table} SET photo = :photo WHERE id = :id AND active = 1";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindValue(":photo", $photo);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() !== 1) {
                $this->errorMessage = "Perfil não encontrado ou foto não alterada.";
                return false;
            }

            $this->id = $id;
            $this->photo = $photo;
            return true;
        } catch (\PDOException $exception) {
            $this->errorMessage = "Não foi possível atualizar a foto do perfil.";
            return false;
        }
    }

    public function permissionVerify (string $email, $type_id): bool
    {
        $query = "SELECT * FROM {$this->table} WHERE email = :email AND type_id = :type_id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":type_id", $type_id);
        $stmt->execute();
        if($stmt->rowCount() == 0) {
            return false;
        }
        return true;
    }

}