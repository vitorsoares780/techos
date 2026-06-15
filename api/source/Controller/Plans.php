<?php

namespace source\Controller;

use Source\Controller\Api;
use Source\Models\Plan;

class Plans extends Api
{
    public function plansListAll()
    {
        $plan = new Plan();
        $this->call(
            200,
            "success",
            "Lista de planos",
            "success",
        )->back($plan->listAll());
    }

    public function plansListById(array $data): void
    {
        if (!filter_var($data['planId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID do plano é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $plan = new Plan();
        $plan = $plan->listById($data['planId']);

        if ($plan == false) {
            $this->call(
                404,
                "not_found",
                "Plano não encontrado",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Plano encontrado",
            "success"
        )->back($plan);
    }

    public function planInsert(array $data): void
    {
        $name = $data['name'];
        $price = $data['price'];

        if (
            empty($name) || $name == null ||
            empty($price) || $price == null
        ) {
            $this->call(
                400,
                "bad_request",
                "Os campos name e price são obrigatórios",
                "error"
            )->back();
            return;
        }

        $plan = new Plan(null, $name, $price);

        if ($plan->insert() == false) {
            $this->call(
                500,
                "internal_server_error",
                "Não foi possível cadastrar o plano",
                "error"
            )->back();
            return;
        }

        $response = $plan->insert();

        $this->call(
            201,
            "created",
            "Plano criado com sucesso",
            "success"
        )->back($response);
    }

    public function planUpdate(array $data): void
    {
        if (!filter_var($data['planId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID inválido ou campos obrigatórios ausentes",
                "error"
            )->back();
            return;
        }

        $plan = new Plan();

        if ($plan->update($data) === false) {
            $this->call(
                404,
                "not_found",
                "Plano não encontrado",
                "error"
            )->back();
            return;
        }

        $response = [
            "id" => $data['planId'],
            "name" => $data['name'],
            "price" => $data['price']
        ];

        $this->call(
            200,
            "success",
            "Plano atualizado com sucesso",
            "success"
        )->back($response);
    }

    public function planDelete(array $data): void
    {
        if (!filter_var($data['planId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID do plano é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $plan = new Plan();

        if ($plan->delete($data['planId']) === false) {
            $this->call(
                404,
                "not_found",
                "Plano não encontrado",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "Plano removido com sucesso",
            "success"
        )->back();
    }
}