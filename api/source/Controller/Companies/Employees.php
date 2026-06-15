<?php

namespace source\Controller\Companies;

use Source\Controller\Api;
use Source\Models\Companies\Employee;

class Employees extends Api
{
    public function employeesListAll()
    {
        $employee = new Employee();
        $this->call(
            200,
            "success",
            "Lista de Funcionários",
            "success",
        )->back($employee->listAll());
    }

    public function employeesListById(array $data): void
    {
        if (!filter_var($data['employeeId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID do funcionário é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $employee = new Employee();
        $employee = $employee->listById($data['employeeId']);

        if ($employee == false) {
            $this->call(
                404,
                "not_found",
                "Funcionário não encontrado",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Funcionário encontrado",
            "success"
        )->back($employee);
    }

    public function employeeInsert(array $data): void
    {
        $user_id = $data['user_id'];
        $company_id = $data['company_id'];

        if (
            empty($user_id) || $user_id == null ||
            empty($company_id) || $company_id == null
        ) {
            $this->call(
                400,
                "bad_request",
                "Todos os campos são obrigatórios",
                "error"
            )->back();
            return;
        }

        $employee = new Employee(null, $user_id, $company_id);

        if ($employee->insert() == false) {
            $this->call(
                500,
                "internal_server_error",
                "Não foi possível cadastrar o funcionário",
                "error"
            )->back();
            return;
        }

        $response = $employee->insert();

        $this->call(
            201,
            "created",
            "Funcionário registrado com sucesso",
            "success"
        )->back($response);
    }

    public function employeeUpdate(array $data): void
    {
        var_dump($data);  // DEBUG
        if (!filter_var($data['employeeId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID inválido ou campos obrigatórios ausentes",
                "error"
            )->back();
            return;
        }

        $employee = new Employee();

        if ($employee->update($data) === false) {
            $this->call(
                404,
                "not_found",
                "Funcionário não encontrado",
                "error"
            )->back();
            return;
        }

        $response = [
            "id" => $data['employeeId'],
            "user_id" => $data['user_id'],
            "company_id" => $data['company_id'],
        ];

        $this->call(
            200,
            "success",
            "Funcionário atualizado com sucesso",
            "success"
        )->back($response);
    }

    public function employeeDelete(array $data): void
    {
        if (!filter_var($data['employeeId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID do funcionário é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $employee = new Employee();

        if ($employee->delete($data['employeeId']) === false) {
            $this->call(
                404,
                "not_found",
                "Funcionário não encontrado",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Funcionário removido com sucesso",
            "success"
        )->back();
    }
}