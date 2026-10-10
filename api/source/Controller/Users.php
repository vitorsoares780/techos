<?php

namespace source\Controller;

use Source\Controller\Api;
use Source\Models\User;

class Users extends Api
{
    public function register(array $data): void
    {
        $data = $this->getRequestBody($data);

        $data['name'] = trim((string) ($data['name'] ?? $data['fullname'] ?? ''));
        $data['email'] = trim((string) ($data['email'] ?? ''));
        $data['password'] = (string) ($data['password'] ?? '');
        $data['cpf'] = trim((string) ($data['cpf'] ?? ''));

        if (empty($data['password'])) {
            $this->call(
                400,
                "bad_request",
                "A senha é obrigatória.",
                "error"
            )->back();
            return;
        }

        if (!$this->validateNameEmail($data)) {
            $this->call(
                400,
                "bad_request",
                "Nome e e-mail são obrigatórios. O e-mail deve ser válido.",
                "error"
            )->back();
            return;
        }

        $user = new User(
            null,
            3,
            $data['cpf'],
            $data['name'],
            $data['email'],
            $data['password'],
            null
        );

        if (!$user->insert()) {
            $this->call(500, "internal_server_error", $user->getErrorMessage(), "error")->back();
            return;
        }

        $response = [
            "id" => $user->getId(),
            "name" => $user->getName(),
            "email" => $user->getEmail()
        ];

        $this->call(201, "success", "Usuário inserido com sucesso", "created")->back($response);
    }

    public function registerAdmin(array $data): void
    {
        $data = $this->getRequestBody($data);

        $data['name'] = trim((string) ($data['name'] ?? $data['fullname'] ?? ''));
        $data['email'] = trim((string) ($data['email'] ?? ''));
        $data['password'] = (string) ($data['password'] ?? '');
        $data['cpf'] = trim((string) ($data['cpf'] ?? ''));

        if (empty($data['password'])) {
            $this->call(
                400,
                "bad_request",
                "A senha é obrigatória.",
                "error"
            )->back();
            return;
        }

        if (!$this->validateNameEmail($data)) {
            $this->call(
                400,
                "bad_request",
                "Nome e e-mail são obrigatórios. O e-mail deve ser válido.",
                "error"
            )->back();
            return;
        }

        $user = new User(
            null,
            1,
            $data['cpf'],
            $data['name'],
            $data['email'],
            $data['password'],
            null,
        );

        if (!$user->insert()) {
            $this->call(500, "internal_server_error", $user->getErrorMessage(), "error")->back();
            return;
        }

        $response = [
            "id" => $user->getId(),
            "name" => $user->getName(),
            "email" => $user->getEmail()
        ];

        $this->call(201, "success", "Usuário admin inserido com sucesso", "created")->back($response);
    }

    public function auth(array $data): void
    {
        $data = $this->getRequestBody($data);
        if (
            !isset($data['email'], $data['password']) ||
            empty($data['email']) || empty($data['password']) ||
            !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $this->call(
                400,
                "bad_request",
                "E-mail e senha são obrigatórios. O e-mail deve ser válido.",
                "error"
            )->back();
            return;
        }

        $user = new User();
        if (!$user->login($data['email'], $data['password'])) {
            $this->call(
                401,
                "unauthorized",
                $user->getErrorMessage(),
                "error"
            )->back();
            return;
        }

        $response = [
            "id" => $user->getId(),
            "name" => $user->getName(),
            "photo" => $user->getPhoto(),
            "token" => $user->getToken(),
        ];

        $this->call(
            200,
            "success",
            "Usuário logado com sucesso",
            "success"
        )->back($response);
    }

    public function authAdmin(array $data): void
    {
        $data = $this->getRequestBody($data);
        
        if (
            !isset($data['email'], $data['password']) ||
            empty($data['email']) || empty($data['password']) ||
            !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $this->call(
                400,
                "bad_request",
                "E-mail e senha são obrigatórios. O e-mail deve ser válido.",
                "error"
            )->back();
            return;
        }

        $user = new User();
        if (!$user->login($data['email'], $data['password'], 1)) {
            $this->call(
                401,
                "unauthorized",
                $user->getErrorMessage(),
                "error"
            )->back();
            return;
        }

        $response = [
            "id" => $user->getId(),
            "name" => $user->getName(),
            "photo" => $user->getPhoto(),
            "token" => $user->getToken(),
        ];

        $this->call(
            200,
            "success",
            "Usuário logado com sucesso",
            "success"
        )->back($response);
    }

    public function usersListAll()
    {
        if (!$this->authToken(1)) {
            $this->call(
                401,
                "unauthorized",
                "Token de autenticação inválido ou expirado.",
                "error"
            )->back();
            return;
        }
        $user = new User();
        $this->call(
            200,
            "success",
            "Lista de Usuários",
            "success",
        )->back($user->listAll());
    }

    public function usersListById(array $data): void
    {
        if (!$this->authToken(1)) {
            $this->call(
                401,
                "unauthorized",
                "Token de autenticação inválido ou expirado.",
                "error"
            )->back();
            return;
        }
        if (!filter_var($data['userId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID do usuário é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $user = new user();
        $user = $user->listById($data['userId']);

        if ($user == false) {
            $this->call(
                404,
                "not_found",
                "Usuário não encontrado",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Usuário encontrado",
            "success"
        )->back($user);
    }

    public function update(array $data): void
    {
        if (!$this->authToken(1) && !$this->authToken(2) && !$this->authToken(3)) {
            $this->call(
                401,
                "unauthorized",
                "Usuário não está autenticado (sem token ou token inválido).",
                "error"
            )->back();
            return;
        }

        $data = $this->getRequestBody($data);
        $user = new User();
        $response = ["id" => $this->userAuthId];
        $hasUpdates = false;

        if (array_key_exists("name", $data)) {
            $name = trim((string) $data["name"]);
            if ($name === "") {
                $this->call(400, "bad_request", "O nome não pode ficar vazio.", "error")->back();
                return;
            }
            $user->setName($name);
            $response["name"] = $name;
            $hasUpdates = true;
        }

        if (array_key_exists("email", $data)) {
            $email = trim((string) $data["email"]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->call(400, "bad_request", "Informe um e-mail válido.", "error")->back();
                return;
            }
            $user->setEmail($email);
            $response["email"] = $email;
            $hasUpdates = true;
        }

        if (array_key_exists("photo", $data)) {
            if ($data["photo"] !== null && !is_string($data["photo"])) {
                $this->call(400, "bad_request", "O campo foto deve ser um texto ou nulo.", "error")->back();
                return;
            }
            $photo = $data["photo"] === null ? null : trim($data["photo"]);
            $user->setPhoto($photo);
            $response["photo"] = $photo;
            $hasUpdates = true;
        }

        if (array_key_exists("password", $data)) {
            $password = (string) $data["password"];
            if (strlen($password) < 6) {
                $this->call(400, "bad_request", "A senha deve possuir pelo menos 6 caracteres.", "error")->back();
                return;
            }
            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
            $hasUpdates = true;
        }

        if (!$hasUpdates) {
            $this->call(400, "bad_request", "Informe ao menos um campo para atualizar.", "error")->back();
            return;
        }

        if (!$user->update($this->userAuthId)) {
            $this->call(
                404,
                "not_found",
                "Usuário não encontrado ou nenhum dado foi alterado.",
                "error"
            )->back();
            return;
        }

        $this->call(200, "success", "Usuário atualizado com sucesso.", "success")->back($response);
    }

    public function updateAdmin(array $data): void
    {
        if (!$this->authToken(1)) {
            $this->call(
                401,
                "unauthorized",
                "Usuário não está autenticado (sem token ou token inválido).",
                "error"
            )->back();
            return;
        }
        // validar campos
        // fazer o update do usuário ADMIN agora autenticado
        $this->call(
            200,
            "success",
            "Usuário atualizado com sucesso",
            "success"
        )->back();

    }

    // Valida somente Nome e Email, mas pode ser alterada para validar mais campos
    private function validateNameEmail(array $data): bool
    {
        if (
            !isset($data["name"], $data["email"]) ||
            empty($data["name"]) || empty($data["email"]) ||
            !filter_var($data["email"], FILTER_VALIDATE_EMAIL)
        ) {
            return false;
        }
        return true;
    }
}