<?php

namespace source\Controller\Faqs;

use Source\Controller\Api;
use Source\Models\Faqs\FaqCategory;

class FaqsCategories extends Api
{
    public function faqsCategoryList()
    {
        $faqsCategories = new FaqCategory();
        $this->call(
            200,
            "success",
            "Lista de Categorias de FAQ",
            "success"
        )->back($faqsCategories->listAll());
    }

    public function faqsCategoryListById(array $data): void
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

        $faqCategory = new FaqCategory();
        $faqCategory = $faqCategory->listById($data['categoryId']);

        if ($faqCategory == false) {
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
        )->back($faqCategory);
        return;
    }

    public function faqsCategoriesCreate(array $data): void
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

        $faqCategory = new FaqCategory(null, $data['name']);

        if (!$faqCategory->insert()) {
            $this->call(
                500,
                "internal_server_error",
                "Não foi possível cadastrar a categoria",
                "error"
            )->back();
            return;
        }

        $response = [
            "id" => $faqCategory->getId(),
            "name" => $faqCategory->getName()
        ];

        $this->call(
            201,
            "success",
            "Categoria de FAQ criada com sucesso",
            "created"
        )->back($response);
    }

    public function faqCategoryUpdate(array $data): void
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

        $faqCategory = new FaqCategory();

        if($faqCategory->update($data) === false){
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
            "categoria atualizada com sucesso",
            "success"
        )->back($response);
    }
}