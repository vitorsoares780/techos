<?php

namespace source\Controller;

use Source\Controller\Api;
use Source\Models\ServiceOrder;
use Source\Models\ServiceOrderImage;

class ServiceOrders extends Api
{

    public function serviceOrdersListAll()
    {
        $userType = $this->authenticatedUserType();
        if ($userType === null) {
            $this->call(
                401,
                "unauthorized",
                "Token de autenticação inválido ou expirado.",
                "error"
            )->back();
            return;
        }
        $serviceOrder = new ServiceOrder();
        $orders = $serviceOrder->listAll($this->userAuthId, $userType);
        $this->call(
            200,
            "success",
            "Lista de Ordens de Serviço",
            "success",
        )->back($orders);
    }

    public function serviceOrdersListById(array $data): void
    {
        $userType = $this->authenticatedUserType();
        if ($userType === null) {
            $this->call(
                401,
                "unauthorized",
                "Token de autenticação inválido ou expirado.",
                "error"
            )->back();
            return;
        }
        $serviceOrderId = filter_var($data["serviceOrderId"] ?? null, FILTER_VALIDATE_INT);
        if ($serviceOrderId === false || $serviceOrderId < 1) {
            $this->call(
                400,
                "bad_request",
                "ID da ordem é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $serviceOrder = new ServiceOrder();
        $serviceOrder = $serviceOrder->listById($serviceOrderId, $this->userAuthId, $userType);

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
        $userType = null;
        foreach ([1, 2, 3] as $typeId) {
            if ($this->authToken($typeId)) {
                $userType = $typeId;
                break;
            }
        }

        if ($userType === null) {
            $this->call(
                401,
                "unauthorized",
                "Token de autenticação inválido ou expirado.",
                "error"
            )->back();
            return;
        }

        $data = $this->getRequestBody($data);
        $userId = $userType === 3
            ? $this->userAuthId
            : filter_var($data["user_id"] ?? null, FILTER_VALIDATE_INT);
        $deviceId = filter_var($data["device_id"] ?? null, FILTER_VALIDATE_INT);
        $companyId = filter_var($data["company_id"] ?? null, FILTER_VALIDATE_INT);
        $defect = trim((string) ($data["defect"] ?? ""));
        $diagnosis = trim((string) ($data["diagnosis"] ?? ""));
        $price = filter_var($data["price"] ?? null, FILTER_VALIDATE_FLOAT);

        if (
            $userId === false || $userId < 1 ||
            $deviceId === false || $deviceId < 1 ||
            $companyId === false || $companyId < 1 ||
            $defect === "" || mb_strlen($defect) > 150 ||
            $diagnosis === "" || mb_strlen($diagnosis) > 150 ||
            $price === false || $price <= 0
        ) {
            $this->call(
                400,
                "bad_request",
                "Informe cliente, aparelho, empresa, defeito, diagnóstico e um preço válido.",
                "error"
            )->back();
            return;
        }

        $serviceOrder = new ServiceOrder(
            null,
            $userId,
            $deviceId,
            $companyId,
            $defect,
            $diagnosis,
            "aberta",
            (float) $price
        );

        $response = $serviceOrder->insert();

        if ($response == false) {
            $this->call(
                500,
                "internal_server_error",
                "Não foi possível cadastrar a ordem",
                "error"
            )->back();
            return;
        }

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
        $routeServiceOrderId = $data["serviceOrderId"] ?? null;
        $data = $this->getRequestBody($data);
        $serviceOrderId = filter_var($routeServiceOrderId, FILTER_VALIDATE_INT);
        if ($serviceOrderId === false || $serviceOrderId < 1) {
            $this->call(
                400,
                "bad_request",
                "Informe um ID válido para a ordem de serviço.",
                "error"
            )->back();
            return;
        }

        $updates = [];
        if (array_key_exists("defect", $data)) {
            if (!is_string($data["defect"])) {
                $this->call(400, "bad_request", "O defeito informado é inválido.", "error")->back();
                return;
            }
            $defect = trim($data["defect"]);
            if ($defect === "" || mb_strlen($defect) > 150) {
                $this->call(400, "bad_request", "O defeito deve conter até 150 caracteres.", "error")->back();
                return;
            }
            $updates["defect"] = $defect;
        }
        if (array_key_exists("diagnosis", $data)) {
            if (!is_string($data["diagnosis"])) {
                $this->call(400, "bad_request", "O diagnóstico informado é inválido.", "error")->back();
                return;
            }
            $diagnosis = trim($data["diagnosis"]);
            if ($diagnosis === "" || mb_strlen($diagnosis) > 150) {
                $this->call(400, "bad_request", "O diagnóstico deve conter até 150 caracteres.", "error")->back();
                return;
            }
            $updates["diagnosis"] = $diagnosis;
        }
        if (array_key_exists("status", $data)) {
            $allowedStatuses = ["aberta", "aguardando_peca", "em_andamento", "cancelada", "concluida"];
            if (!in_array($data["status"], $allowedStatuses, true)) {
                $this->call(400, "bad_request", "Informe um status válido para a ordem.", "error")->back();
                return;
            }
            $updates["status"] = $data["status"];
        }
        if (array_key_exists("price", $data)) {
            $price = filter_var($data["price"], FILTER_VALIDATE_FLOAT);
            if ($price === false || $price <= 0) {
                $this->call(400, "bad_request", "Informe um preço maior que zero.", "error")->back();
                return;
            }
            $updates["price"] = (float) $price;
        }
        if ($updates === []) {
            $this->call(400, "bad_request", "Informe ao menos um campo para atualizar.", "error")->back();
            return;
        }

        $serviceOrder = new ServiceOrder();
        $updatedOrder = $serviceOrder->update($updates + ["serviceOrderId" => $serviceOrderId]);

        if ($updatedOrder === false) {
            $this->call(
                404,
                "not_found",
                "Ordem de serviço não encontrada ou inativa.",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Ordem de serviço atualizada com sucesso",
            "success"
        )->back($updatedOrder);
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

        $images = new ServiceOrderImage();
        if (!$images->removeAllForServiceOrder((int) $data['serviceOrderId'])) {
            $this->call(500, "internal_server_error", $images->getErrorMessage(), "error")->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Ordem de serviço removida com sucesso",
            "success"
        )->back();
    }

    private function authenticatedUserType(): ?int
    {
        foreach ([1, 2, 3] as $userType) {
            if ($this->authToken($userType)) {
                return $userType;
            }
        }

        return null;
    }
}
