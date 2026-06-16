<?php

namespace Source\Controller\Devices;

use Source\Controller\Api;
use Source\Models\Devices\Device;

class Devices extends Api
{
    public function devicesListAll(): void
    {
        $device = new Device();
        $this->call(200, "success", "Lista de dispositivos", "success")->back($device->listAll());
    }

    public function devicesListById(array $data): void
    {
        if (!isset($data['deviceId'])) {
            $this->call(400, "bad_request", "ID do dispositivo obrigatório", "error")->back();
            return;
        }

        $device = new Device();
        $result = $device->listById($data['deviceId']);

        if (!$result) {
            $this->call(404, "not_found", "Dispositivo não encontrado", "error")->back();
            return;
        }

        $this->call(200, "success", "Dispositivo encontrado", "success")->back($result);
    }

    public function deviceInsert(array $data): void
    {
        $data = $this->getRequestBody($data);

        if (!isset($data['user_id']) || !isset($data['category_id']) || !isset($data['serial_number']) || !isset($data['model']) || !isset($data['brand'])) {
            $this->call(400, "bad_request", "Os campos user_id, category_id, serial_number, model e brand são obrigatórios", "error")->back();
            return;
        }

        $device = new Device(null, (int)$data['user_id'], (int)$data['category_id'], $data['serial_number'], $data['model'], $data['brand']);
        $result = $device->insert();

        if (!$result) {
            $this->call(500, "internal_server_error", "Erro ao inserir dispositivo", "error")->back();
            return;
        }

        $this->call(201, "created", "Dispositivo inserido com sucesso", "success")->back($result);
    }

    public function deviceUpdate(array $data): void
    {
        $data = $this->getRequestBody($data);

        if (!isset($data['deviceId'])) {
            $this->call(400, "bad_request", "ID do dispositivo obrigatório", "error")->back();
            return;
        }

        $device = new Device(
            (int)$data['deviceId'],
            isset($data['user_id']) ? (int)$data['user_id'] : null,
            isset($data['category_id']) ? (int)$data['category_id'] : null,
            $data['serial_number'] ?? null,
            $data['model'] ?? null,
            $data['brand'] ?? null
        );
        $result = $device->update();

        if (!$result) {
            $this->call(500, "internal_server_error", "Erro ao atualizar dispositivo", "error")->back();
            return;
        }

        $this->call(200, "success", "Dispositivo atualizado com sucesso", "success")->back($result);
    }

    public function deviceDelete(array $data): void
    {
        if (!isset($data['deviceId'])) {
            $this->call(400, "bad_request", "ID do dispositivo obrigatório", "error")->back();
            return;
        }

        $device = new Device((int)$data['deviceId']);
        if (!$device->delete()) {
            $this->call(500, "internal_server_error", "Erro ao deletar dispositivo", "error")->back();
            return;
        }

        $this->call(200, "success", "Dispositivo deletado com sucesso", "success")->back();
    }
}