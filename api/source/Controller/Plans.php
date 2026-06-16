<?php

namespace Source\Controller;

use Source\Models\Plan;

class Plans extends Api
{
    public function plansListAll(): void
    {
        $plan = new Plan();
        $this->call(200, "success", "Lista de planos", "success")->back($plan->listAll());
    }

    public function plansListById(array $data): void
    {
        if (!isset($data['planId'])) {
            $this->call(400, "bad_request", "ID do plano obrigatório", "error")->back();
            return;
        }

        $plan = new Plan();
        $result = $plan->listById($data['planId']);

        if (!$result) {
            $this->call(404, "not_found", "Plano não encontrado", "error")->back();
            return;
        }

        $this->call(200, "success", "Plano encontrado", "success")->back($result);
    }

    public function planInsert(array $data): void
    {
        $data = $this->getRequestBody($data);

        if (!isset($data['name']) || !isset($data['price'])) {
            $this->call(400, "bad_request", "Os campos name e price são obrigatórios", "error")->back();
            return;
        }

        $plan = new Plan(null, $data['name'], (float)$data['price']);

        $result = $plan->insert();
        if (!$result) {
            $this->call(500, "internal_server_error", "Erro ao inserir plano", "error")->back();
            return;
        }

        $this->call(201, "created", "Plano inserido com sucesso", "success")->back($result);
    }

    public function planUpdate(array $data): void
    {
        $data = $this->getRequestBody($data);

        if (!isset($data['planId'])) {
            $this->call(400, "bad_request", "ID do plano obrigatório", "error")->back();
            return;
        }

        $plan = new Plan((int)$data['planId'], $data['name'] ?? null, isset($data['price']) ? (float)$data['price'] : null);
        $result = $plan->update();

        if (!$result) {
            $this->call(500, "internal_server_error", "Erro ao atualizar plano", "error")->back();
            return;
        }

        $this->call(200, "success", "Plano atualizado com sucesso", "success")->back($result);
    }

    public function planDelete(array $data): void
    {
        if (!isset($data['planId'])) {
            $this->call(400, "bad_request", "ID do plano obrigatório", "error")->back();
            return;
        }

        $plan = new Plan((int)$data['planId']);
        if (!$plan->delete()) {
            $this->call(500, "internal_server_error", "Erro ao deletar plano", "error")->back();
            return;
        }

        $this->call(200, "success", "Plano deletado com sucesso", "success")->back();
    }
}