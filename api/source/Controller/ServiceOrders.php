<?php

namespace source\Controller;

use Source\Controller\Api;
use Source\Models\ServiceOrder;

class ServiceOrders extends Api
{

    public function devicesListAll()
    {
        $device = new Device();
        $this->call(
            200,
            "success",
            "Lista de Aparelhos",
            "success",
        )->back($device->listAll());
    }

    public function devicesListById(array $data): void
    {
        if (!filter_var($data['deviceId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID do aparelho é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $device = new Device();
        $device = $device->listById($data['deviceId']);

        if ($device == false) {
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
            "Aparelho encontrado",
            "success"
        )->back($device);
    }

    public function deviceInsert(array $data): void
    {
        $user_id = $data['user_id'];
        $cat_id = $data['category_id'];
        $serial_number = $data['serial_number'];
        $model = $data['model'];
        $brand = $data['brand'];

        if (
            empty($user_id) || $user_id == null ||
            empty($cat_id) || $cat_id == null ||
            empty($serial_number) || $serial_number == null ||
            empty($model) || $model == null ||
            empty($brand) || $brand == null
        ) {
            $this->call(
                400,
                "bad_request",
                "Todos os campos são obrigatórios",
                "error"
            )->back();
            return;
        }

        $device = new Device(null, $user_id, $cat_id, $serial_number, $model, $brand);

        if ($device->insert() == false) {
            $this->call(
                500,
                "internal_server_error",
                "Não foi possível cadastrar o aparelho",
                "error"
            )->back();
            return;
        }

        $response = $device->insert();

        $this->call(
            201,
            "created",
            "Aparelho registrado com sucesso",
            "success"
        )->back($response);
    }

    public function deviceUpdate(array $data): void
    {
        var_dump($data);  // DEBUG
        if (!filter_var($data['deviceId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID inválido ou campos obrigatórios ausentes",
                "error"
            )->back();
            return;
        }

        $device = new Device();

        if ($device->update($data) === false) {
            $this->call(
                404,
                "not_found",
                "Aparelho não encontrado",
                "error"
            )->back();
            return;
        }

        $response = [
            "id" => $data['deviceId'],
            "user_id" => $data['user_id'],
            "category_id" => $data['category_id'],
            "serial_number" => $data['serial_number'],
            "model" => $data['model'],
            "brand" => $data['brand']
        ];

        $this->call(
            200,
            "success",
            "Aparelho atualizado com sucesso",
            "success"
        )->back($response);
    }

    public function deviceDelete(array $data): void
    {
        if (!filter_var($data['deviceId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID do aparelho é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $device = new Device();

        if ($device->delete($data['deviceId']) === false) {
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
