<?php

namespace source\Controller\Devices;

use Source\Controller\Api;
use Source\Models\Devices\DeviceCategory;

class DevicesCategories extends Api{
    public function devicesCategoryList()
    {
        $devicesCategories = new DeviceCategory();
        $this->call(
            200,
            "success",
            "Lista de Categorias de FAQ",
            "success"
        )->back($devicesCategories->listAll());
    }

    public function devicesCategoryListById(array $data): void
    {
        if (!filter_var($data['categoryId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID da categoria é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $deviceCategory = new DeviceCategory();
        $deviceCategory = $deviceCategory->listById($data['categoryId']);

        if ($deviceCategory == false) {
            $this->call(
                404,
                "not_found",
                "Categoria não encontrada",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Categoria encontrada",
            "success"
        )->back($deviceCategory);
        return;
    }

    public function devicesCategoriesCreate(array $data): void
    {
        if (empty(trim($data['name']))) {
            $this->call(
                400,
                "bad_request",
                "O campo name é obrigatório",
                "error"
            )->back();
            return;
        }

        $deviceCategory = new DeviceCategory(null, $data['name']);

        if (!$deviceCategory->insert()) {
            $this->call(
                500,
                "internal_server_error",
                "Não foi possível cadastrar a categoria",
                "error"
            )->back();
            return;
        }

        $response = [
            "id" => $deviceCategory->getId(),
            "name" => $deviceCategory->getName()
        ];

        $this->call(
            201,
            "success",
            "Categoria de aparelho criada com sucesso",
            "created"
        )->back($response);
    }

    public function deviceCategoryUpdate(array $data): void
    {
        if(!filter_var($data['categoryId'], FILTER_VALIDATE_INT)){
            $this->call(
                400,
                "bad_request",
                "ID inválido ou campo name é obrigatório",
                "error"
            )->back();
            return;
        }

        $deviceCategory = new DeviceCategory();

        if($deviceCategory->update($data) === false){
            $this->call(
                404,
                "not_found",
                "Categoria não encontrada",
                "error"
            )->back();
            return;
        }

        $response = [
            "id" => $data['categoryId'],
            "name" => $data['name']
        ];

        $this->call(
            200,
            "success",
            "Categoria atualizada com sucesso",
            "success"
        )->back($response);
    }

    public function deviceCategoryDelete(array $data): void
    {
        if(!filter_var($data['categoryId'], FILTER_VALIDATE_INT)){
            $this->call(
                400,
                "bad_request",
                "ID da categoria é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $deviceCategory = new DeviceCategory();
        $deviceCategory = $deviceCategory->delete($data['categoryId']);

        if($deviceCategory === 400){
            $this->call(
                400,
                "bad_request",
                "Não é possível remover uma categoria que possui aparelhos ativos",
                "error"
            )->back();
            return;
        }else if($deviceCategory === false){
            $this->call(
                404,
                "not_found",
                "Aparelho não encontrado",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Aparelho removido com sucesso",
            "success"
        )->back();
    }
}