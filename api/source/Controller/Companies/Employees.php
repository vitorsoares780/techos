<?php

namespace Source\Controller\Companies;

use Source\Controller\Api;
use Source\Models\Companies\Employee;

class Employees extends Api
{
    public function employeesListAll(): void
    {
        $employee = new Employee();
        $this->call(200, "success", "Lista de funcionários", "success")->back($employee->listAll());
    }

    public function employeesListById(array $data): void
    {
        if (!isset($data['employeeId'])) {
            $this->call(400, "bad_request", "ID do funcionário obrigatório", "error")->back();
            return;
        }

        $employee = new Employee();
        $result = $employee->listById($data['employeeId']);

        if (!$result) {
            $this->call(404, "not_found", "Funcionário não encontrado", "error")->back();
            return;
        }

        $this->call(200, "success", "Funcionário encontrado", "success")->back($result);
    }

    public function employeeInsert(array $data): void
    {
        $data = $this->getRequestBody($data);

        if (!isset($data['user_id']) || !isset($data['company_id'])) {
            $this->call(400, "bad_request", "Os campos user_id e company_id são obrigatórios", "error")->back();
            return;
        }

        $employee = new Employee(null, (int)$data['user_id'], (int)$data['company_id']);
        $result = $employee->insert();

        if (!$result) {
            $this->call(500, "internal_server_error", "Erro ao inserir funcionário", "error")->back();
            return;
        }

        $this->call(201, "created", "Funcionário inserido com sucesso", "success")->back($result);
    }

    public function employeeUpdate(array $data): void
    {
        $data = $this->getRequestBody($data);

        if (!isset($data['employeeId'])) {
            $this->call(400, "bad_request", "ID do funcionário obrigatório", "error")->back();
            return;
        }

        $employee = new Employee(
            (int)$data['employeeId'],
            isset($data['user_id']) ? (int)$data['user_id'] : null,
            isset($data['company_id']) ? (int)$data['company_id'] : null
        );
        $result = $employee->update();

        if (!$result) {
            $this->call(500, "internal_server_error", "Erro ao atualizar funcionário", "error")->back();
            return;
        }

        $this->call(200, "success", "Funcionário atualizado com sucesso", "success")->back($result);
    }

    public function employeeDelete(array $data): void
    {
        if (!isset($data['employeeId'])) {
            $this->call(400, "bad_request", "ID do funcionário obrigatório", "error")->back();
            return;
        }

        $employee = new Employee((int)$data['employeeId']);
        if (!$employee->delete()) {
            $this->call(500, "internal_server_error", "Erro ao deletar funcionário", "error")->back();
            return;
        }

        $this->call(200, "success", "Funcionário deletado com sucesso", "success")->back();
    }
}