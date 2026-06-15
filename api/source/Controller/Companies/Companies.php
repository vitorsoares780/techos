<?php

namespace source\Controller\Companies;

use Source\Controller\Api;
use Source\Models\Companies\Company;

class Companies extends Api
{
    public function companiesListAll()
    {
        $company = new Company();
        $this->call(
            200,
            "success",
            "Lista de Empresas",
            "success",
        )->back($company->listAll());
    }

    public function companiesListById(array $data): void
    {
        if (!filter_var($data['companyId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID da empresa é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $company = new Company();
        $company = $company->listById($data['companyId']);

        if ($company == false) {
            $this->call(
                404,
                "not_found",
                "Empresa não encontrada",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Empresa encontrada",
            "success"
        )->back($company);
    }

    public function companyInsert(array $data): void
    {
        $cnpj = $data['cnpj'];
        $name = $data['name'];
        $email = $data['email'];
        $owner_id = $data['owner_id'];
        $plan_id = $data['plan_id'];

        if (
            empty($cnpj) || $cnpj == null ||
            empty($name) || $name == null ||
            empty($email) || $email == null ||
            empty($owner_id) || $owner_id == null ||
            empty($plan_id) || $plan_id == null
        ) {
            $this->call(
                400,
                "bad_request",
                "Todos os campos são obrigatórios",
                "error"
            )->back();
            return;
        }

        $company = new Company(null, $cnpj, $name, $email, $owner_id, $plan_id);

        if ($company->insert() == false) {
            $this->call(
                500,
                "internal_server_error",
                "Não foi possível cadastrar a empresa",
                "error"
            )->back();
            return;
        }

        $response = $company->insert();

        $this->call(
            201,
            "created",
            "Empresa registrado com sucesso",
            "success"
        )->back($response);
    }

    public function companyUpdate(array $data): void
    {
        var_dump($data);  // DEBUG
        if (!filter_var($data['companyId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID inválido ou campos obrigatórios ausentes",
                "error"
            )->back();
            return;
        }

        $company = new Company();

        if ($company->update($data) === false) {
            $this->call(
                404,
                "not_found",
                "Empresa não encontrada",
                "error"
            )->back();
            return;
        }

        $response = [
            "id" => $data['companyId'],
            "cnpj" => $data['cnpj'],
            "name" => $data['name'],
            "email" => $data['email'],
            "owner_id" => $data['owner_id'],
            "plan_id" => $data['plan_id']
        ];

        $this->call(
            200,
            "success",
            "Empresa atualizada com sucesso",
            "success"
        )->back($response);
    }

    public function companyDelete(array $data): void
    {
        if (!filter_var($data['companyId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID da empresa é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $company = new Company();

        if ($company->delete($data['companyId']) === false) {
            $this->call(
                404,
                "not_found",
                "Empresa não encontrada",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Empresa removida com sucesso",
            "success"
        )->back();
    }
}