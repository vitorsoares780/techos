<?php

namespace Source\Controller\Companies;

use Source\Controller\Api;
use Source\Models\Companies\Company;

class Companies extends Api
{
    public function companiesListAll(): void
    {
        $company = new Company();
        $this->call(200, "success", "Lista de empresas", "success")->back($company->listAll());
    }

    public function companiesListById(array $data): void
    {
        if (!isset($data['companyId'])) {
            $this->call(400, "bad_request", "ID da empresa obrigatório", "error")->back();
            return;
        }

        $company = new Company();
        $result = $company->listById($data['companyId']);

        if (!$result) {
            $this->call(404, "not_found", "Empresa não encontrada", "error")->back();
            return;
        }

        $this->call(200, "success", "Empresa encontrada", "success")->back($result);
    }

    public function companyInsert(array $data): void
    {
        $data = $this->getRequestBody($data);

        if (!isset($data['cnpj']) || !isset($data['name']) || !isset($data['email']) || !isset($data['owner_id']) || !isset($data['plan_id'])) {
            $this->call(400, "bad_request", "Os campos cnpj, name, email, owner_id e plan_id são obrigatórios", "error")->back();
            return;
        }

        $company = new Company(null, $data['cnpj'], $data['name'], $data['email'], (int)$data['owner_id'], (int)$data['plan_id']);
        $result = $company->insert();

        if (!$result) {
            $this->call(500, "internal_server_error", "Erro ao inserir empresa", "error")->back();
            return;
        }

        $this->call(201, "created", "Empresa inserida com sucesso", "success")->back($result);
    }

    public function companyUpdate(array $data): void
    {
        $data = $this->getRequestBody($data);

        if (!isset($data['companyId'])) {
            $this->call(400, "bad_request", "ID da empresa obrigatório", "error")->back();
            return;
        }

        $company = new Company(
            (int)$data['companyId'],
            $data['cnpj'] ?? null,
            $data['name'] ?? null,
            $data['email'] ?? null,
            isset($data['owner_id']) ? (int)$data['owner_id'] : null,
            isset($data['plan_id']) ? (int)$data['plan_id'] : null
        );
        $result = $company->update();

        if (!$result) {
            $this->call(500, "internal_server_error", "Erro ao atualizar empresa", "error")->back();
            return;
        }

        $this->call(200, "success", "Empresa atualizada com sucesso", "success")->back($result);
    }

    public function companyDelete(array $data): void
    {
        if (!isset($data['companyId'])) {
            $this->call(400, "bad_request", "ID da empresa obrigatório", "error")->back();
            return;
        }

        $company = new Company((int)$data['companyId']);
        if (!$company->delete()) {
            $this->call(500, "internal_server_error", "Erro ao deletar empresa", "error")->back();
            return;
        }

        $this->call(200, "success", "Empresa deletada com sucesso", "success")->back();
    }
}