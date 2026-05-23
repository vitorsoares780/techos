<?php

namespace source\Controller\Faqs;

use Source\Controller\Api;
use Source\Models\Faqs\Faq;

class Faqs extends Api
{
    public function faqsListAll()
    {
        $faq = new Faq();
        $this->call(
            200,
            "success",
            "Lista de FAQs",
            "success",
        )->back($faq->listAll());
    }

    public function faqsListById(array $data): void
    {
        if (!filter_var($data['faqId'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "bad_request",
                "ID do FAQ é obrigatório e deve ser um número inteiro",
                "error"
            )->back();
            return;
        }

        $faq = new Faq();
        $faq = $faq->listById($data['faqId']);

        if ($faq == false) {
            $this->call(
                404,
                "not_found",
                "FAQ não encontrado",
                "error"
            )->back();
            return;
        }

        $this->call(
            200,
            "success",
            "FAQ encontrado",
            "success"
        )->back($faq);
    }

    public function faqInsert(array $data): void
    {
        $faqs_cat_id = $data['faqs_category_id'];
        $question = $data['question'];
        $answer = $data['answer'];

        if (
            empty($faqs_cat_id) || $faqs_cat_id == null ||
            empty($question) || $question == null ||
            empty($answer) || $answer == null
        ) {
            $this->call(
                400,
                "bad_request",
                "Os campos question, answer e faqs_category_id são obrigatórios",
                "error"
            )->back();
            return;
        }

        $faq = new Faq(null, $faqs_cat_id, $question, $answer);

        if($faq->insert() == false){
            $this->call(
                500,
                "internal_server_error",
                "Não foi possível cadastrar o FAQ",
                "error"
            )->back();
            return;
        }

        $response = $faq->insert();

        $this->call(
            201,
            "created",
            "FAQ criado com sucesso",
            "success"
        )->back($response);
    }

    public function faqUpdate (array $data): void{
        var_dump($data);  // DEBUG
        if(!filter_var($data['faqId'], FILTER_VALIDATE_INT)){
            $this->call(
                400,
                "bad_request",
                "ID inválido ou campos obrigatórios ausentes",
                "error"
            )->back();
            return;
        }

        $faq = new Faq();

        if($faq->update($data) === false){
            $this->call(
                404,
                "not_found",
                "FAQ não encontrado",
                "error"
            )->back();
            return;
        }

        $response = [
            "id" => $data['faqId'],
            "faqs_category_id" => $data['faqs_category_id'],
            "question" => $data['question'],
            "answer" => $data['answer']
        ];

        $this->call(
            200,
            "success",
            "FAQ atualizado com sucesso",
            "success"
        )->back($response);
    }
}