<?php

namespace Source\Controller\Devices;

use Source\Controller\Api;
use Source\Models\Devices\DeviceCategory;

class DevicesCategories extends Api
{
    public function devicesCategoryList(): void
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
        $category = new DeviceCategory();
        $this->call(200, "success", "Lista de categorias", "success")->back($category->listAll());
    }

    public function devicesCategoryListById(array $data): void
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
        if (!isset($data['categoryId'])) {
            $this->call(400, "bad_request", "ID da categoria obrigatório", "error")->back();
            return;
        }

        $category = new DeviceCategory();
        $result = $category->listById($data['categoryId']);

        if (!$result) {
            $this->call(404, "not_found", "Categoria não encontrada", "error")->back();
            return;
        }

        $this->call(200, "success", "Categoria encontrada", "success")->back($result);
    }

    public function devicesCategoriesCreate(array $data): void
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
        $data = $this->getRequestBody($data);

        if (!isset($data['name'])) {
            $this->call(400, "bad_request", "O campo name é obrigatório", "error")->back();
            return;
        }

        $category = new DeviceCategory(null, $data['name']);
        if (!$category->insert()) {
            $this->call(500, "internal_server_error", "Erro ao inserir categoria", "error")->back();
            return;
        }

        $this->call(201, "created", "Categoria inserida com sucesso", "success")->back([
            "id" => $category->getId(),
            "name" => $category->getName()
        ]);
    }

    public function deviceCategoryUpdate(array $data): void
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
        $data = $this->getRequestBody($data);

        if (!isset($data['categoryId'])) {
            $this->call(400, "bad_request", "ID da categoria obrigatório", "error")->back();
            return;
        }

        $category = new DeviceCategory((int)$data['categoryId'], $data['name'] ?? null);
        $result = $category->update();

        if ($result === false) {
            $this->call(500, "internal_server_error", "Erro ao atualizar categoria", "error")->back();
            return;
        }

        $this->call(200, "success", "Categoria atualizada com sucesso", "success")->back();
    }

    public function deviceCategoryDelete(array $data): void
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
        if (!isset($data['categoryId'])) {
            $this->call(400, "bad_request", "ID da categoria obrigatório", "error")->back();
            return;
        }

        $category = new DeviceCategory((int)$data['categoryId']);
        $result = $category->delete();

        if ($result === 400) {
            $this->call(400, "bad_request", "Existem dispositivos vinculados a esta categoria", "error")->back();
            return;
        }

        if (!$result) {
            $this->call(500, "internal_server_error", "Erro ao deletar categoria", "error")->back();
            return;
        }

        $this->call(200, "success", "Categoria deletada com sucesso", "success")->back();
    }
}