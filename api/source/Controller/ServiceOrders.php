<?php

namespace source\Controller;

use Source\Controller\Api;
use Source\Models\ServiceOrder;

class ServiceOrders extends Api
{

    public function serviceOrdersListAll()
    {
        if (!$this->authToken(2)) {
            $this->call(
                401,
                "unauthorized",
                "Token de autenticação inválido ou expirado.",
                "error"
            )->back();
            return;
        }
        $serviceOrder = new ServiceOrder();
        $this->call(
            200,
            "success",
            "Lista de Ordens de Serviço",
            "success",
        )->back($serviceOrder->listAll());
    }

    public function serviceOrdersListById(array $data): void
    {
        if (!$this->authToken(2)) {
            $this->call(
                401,
                "unauthorized",
                "Token de autenticação inválido ou expirado.",
                "error"
            )->back();
            return;
        }
        if (!filter_var($data['serviceOrderId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID da ordem é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $serviceOrder = new ServiceOrder();
        $serviceOrder = $serviceOrder->listById($data['serviceOrderId']);

        if ($serviceOrder == false) {
            $this->call(
                404,
                "not_found",
                "Ordem de serviço não encontrada",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Ordem de serviço encontrada",
            "success"
        )->back($serviceOrder);
    }

    public function serviceOrderInsert(array $data): void
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
        $user_id = $data['user_id'];
        $device_id = $data['device_id'];
        $company_id = $data['company_id'];
        $defect = $data['defect'];
        $status = $data['status'];
        $price = $data['price'];
        $photo = $data['photo'];

        if (
            empty($user_id) || $user_id == null ||
            empty($device_id) || $device_id == null ||
            empty($company_id) || $company_id == null ||
            empty($defect) || $defect == null ||
            empty($status) || $status == null ||
            empty($price) || $price == null ||
            empty($photo) || $photo == null
        ) {
            $this->call(
                400,
                "bad_request",
                "Todos os campos são obrigatórios",
                "error"
            )->back();
            return;
        }

        $serviceOrder = new ServiceOrder(null, $user_id, $device_id, $company_id, $defect, $status, $price, $photo);

        if ($serviceOrder->insert() == false) {
            $this->call(
                500,
                "internal_server_error",
                "Não foi possível cadastrar a ordem",
                "error"
            )->back();
            return;
        }

        $response = $serviceOrder->insert();

        $this->call(
            201,
            "created",
            "Ordem de serviço registrada com sucesso",
            "success"
        )->back($response);
    }

    public function serviceOrderUpdate(array $data): void
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
        if (!filter_var($data['serviceOrderId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID inválido ou campos obrigatórios ausentes",
                "error"
            )->back();
            return;
        }

        $serviceOrder = new ServiceOrder();

        if ($serviceOrder->update($data) === false) {
            $this->call(
                404,
                "not_found",
                "Ordem de serviço não encontrada",
                "error"
            )->back();
            return;
        }

        $response = [
            "id" => $data['serviceOrderId'],
            "user_id" => $data['user_id'],
            "device_id" => $data['device_id'],
            "company_id" => $data['company_id'],
            "defect" => $data['defect'],
            "status" => $data['status'],
            "price" => $data['price'],
            "photo" => $data['photo']
        ];

        $this->call(
            200,
            "success",
            "Ordem de serviço atualizada com sucesso",
            "success"
        )->back($response);
    }

    public function serviceOrderDelete(array $data): void
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
        if (!filter_var($data['serviceOrderId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID da ordem é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $serviceOrder = new ServiceOrder();

        if ($serviceOrder->delete($data['serviceOrderId']) === false) {
            $this->call(
                404,
                "not_found",
                "Ordem de serviço não encontrada",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Ordem de serviço removida com sucesso",
            "success"
        )->back();
    }
}
