<?php

namespace source\Controller;

use Source\Controller\Api;
use Source\Models\User;
use Source\Support\Upload;

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
        if (!$user->login($data['email'], $data['password'], 3) && !$user->login($data['email'], $data['password'], 2)) {
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
            "photo" => $this->photoUrl($user->getPhoto()),
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
            "photo" => $this->photoUrl($user->getPhoto()),
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

    public function delete(array $data): void
    {
        if (!$this->authToken(1)) {
            $this->call(401, "unauthorized", "Usuário não está autenticado.", "error")->back();
            return;
        }

        $userId = filter_var($data['userId'] ?? null, FILTER_VALIDATE_INT);
        if (!$userId || $userId < 1) {
            $this->call(400, "bad_request", "ID do usuário inválido.", "error")->back();
            return;
        }

        if ($userId === $this->userAuthId) {
            $this->call(400, "bad_request", "Não é possível excluir a própria conta.", "error")->back();
            return;
        }

        $user = new User();
        if (!$user->softDelete($userId)) {
            $this->call(404, "not_found", "Usuário não encontrado ou já inativo.", "error")->back();
            return;
        }

        $this->call(200, "success", "Usuário desativado com sucesso.", "success")->back([
            "id" => $userId,
            "active" => 0
        ]);
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
    public function usersListByEmail(array $data): void
    {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->call(
                400,
                "bad_request",
                "Email do usuário é obrigatório e deve ser um Email válido",
                "error"
            )->back();
            return;
        }

        $user = new user();
        $user = $user->listByEmail($data['email']);

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

        if (array_key_exists("photo", $data)) {
            $this->call(400, "bad_request", "Atualize a foto pelo endpoint POST /users/photo.", "error")->back();
            return;
        }

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

    public function uploadPhoto (array $data): void
    {
        if (!$this->authToken(1) && !$this->authToken(2) && !$this->authToken(3)) {
            $this->call(401, "unauthorized", "Usuário não está autenticado (sem token ou token inválido).", "error")->back();
            return;
        }

        if (!isset($_FILES["photo"]) || !is_array($_FILES["photo"])) {
            $this->call(400, "bad_request", "Selecione uma imagem para enviar.", "error")->back();
            return;
        }

        $user = new User();
        $profile = $user->findProfileById($this->userAuthId);
        if ($profile === false && $user->getErrorMessage() !== null) {
            $this->call(500, "internal_server_error", $user->getErrorMessage(), "error")->back();
            return;
        }
        if ($profile === false || (int) $profile->active !== 1) {
            $this->call(404, "not_found", "Perfil não encontrado.", "error")->back();
            return;
        }
        $oldPhoto = is_string($profile->photo) ? $profile->photo : null;

        $uploadDirectory = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "users";
        $uploader = new Upload($uploadDirectory);
        try {
            $filename = $uploader->image($_FILES["photo"]);
        } catch (\RuntimeException $exception) {
            $this->call(400, "bad_request", $exception->getMessage(), "error")->back();
            return;
        }

        $photoPath = "uploads/users/" . $filename;
        $photoUrl = $this->photoUrl($photoPath);
        if (!$user->updatePhoto($this->userAuthId, $photoPath)) {
            $savedPath = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
            if (is_file($savedPath) && !unlink($savedPath)) {
                error_log("Não foi possível remover a foto enviada após falha ao atualizar o perfil.");
            }
            $this->call(500, "internal_server_error", $user->getErrorMessage() ?? "Não foi possível atualizar a foto.", "error")->back();
            return;
        }

        $this->deletePreviousPhoto($oldPhoto, $uploadDirectory);

        $this->call(200, "success", "Foto atualizada com sucesso.", "success")->back([
            "photo" => $photoUrl,
        ]);
    }

    private function photoUrl(?string $photoPath): ?string
    {
        if ($photoPath === null || trim($photoPath) === "") {
            return null;
        }

        $path = $photoPath;
        if (filter_var($photoPath, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($photoPath);
            $applicationUrl = parse_url(url());
            if (
                !is_array($parsedUrl) ||
                !is_array($applicationUrl) ||
                ($parsedUrl["host"] ?? null) !== ($applicationUrl["host"] ?? null) ||
                !isset($parsedUrl["path"])
            ) {
                return null;
            }
            $path = $parsedUrl["path"];
        }

        $path = ltrim($path, "/");
        $applicationPath = trim((string) parse_url(url(), PHP_URL_PATH), "/");
        if ($applicationPath !== "" && strpos($path, $applicationPath . "/") === 0) {
            $path = substr($path, strlen($applicationPath) + 1);
        }

        if (!preg_match('/\Auploads\/users\/([a-f0-9]{32}\.(?:jpg|png|webp))\z/', $path, $matches)) {
            return null;
        }

        return url("uploads/users/" . $matches[1]);
    }

    private function deletePreviousPhoto(?string $photoUrl, string $uploadDirectory): void
    {
        if ($photoUrl === null || $photoUrl === "") {
            return;
        }

        $photoPath = parse_url($photoUrl, PHP_URL_PATH);
        $uploadsUrlPath = parse_url(url("uploads/users/"), PHP_URL_PATH);
        if (!is_string($photoPath) || !is_string($uploadsUrlPath)) {
            return;
        }

        $normalizedPath = ltrim($photoPath, "/");
        $managedPrefixes = ["uploads/users/", ltrim(rtrim($uploadsUrlPath, "/") . "/", "/")];
        $filename = null;
        foreach ($managedPrefixes as $managedPrefix) {
            if ($managedPrefix !== "" && strpos($normalizedPath, $managedPrefix) === 0) {
                $candidate = substr($normalizedPath, strlen($managedPrefix));
                if (preg_match('/\A[a-f0-9]{32}\.(?:jpg|png|webp)\z/', $candidate)) {
                    $filename = $candidate;
                    break;
                }
            }
        }

        if ($filename === null) {
            return;
        }

        $oldFilePath = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
        if (is_file($oldFilePath) && !unlink($oldFilePath)) {
            error_log("Não foi possível remover a foto antiga do perfil.");
        }
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