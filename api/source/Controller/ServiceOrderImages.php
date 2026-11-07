<?php

namespace Source\Controller;

use Source\Models\ServiceOrderImage;
use Source\Support\Upload;

class ServiceOrderImages extends Api
{
    public function list(array $data): void
    {
        $userType = $this->authenticatedUserType();
        if ($userType === null) {
            $this->call(401, "unauthorized", "Usuário não está autenticado ou não tem permissão.", "error")->back();
            return;
        }

        $serviceOrderId = $this->serviceOrderId($data);
        if ($serviceOrderId === null) {
            $this->call(400, "bad_request", "Informe um ID válido para a ordem de serviço.", "error")->back();
            return;
        }

        $images = new ServiceOrderImage();
        if (!$images->canAccessServiceOrder($serviceOrderId, $this->userAuthId, $userType)) {
            if ($images->getErrorMessage() !== null) {
                $this->call(500, "internal_server_error", $images->getErrorMessage(), "error")->back();
                return;
            }
            $this->call(404, "not_found", "Ordem de serviço não encontrada ou sem acesso.", "error")->back();
            return;
        }

        $records = $images->listByServiceOrder($serviceOrderId);
        if ($records === false) {
            $this->call(500, "internal_server_error", $images->getErrorMessage(), "error")->back();
            return;
        }

        $data = array_map(static function (object $image): array {
            return [
                "id" => (int) $image->id,
                "service_order_id" => (int) $image->service_order_id,
                "image_url" => url(ltrim($image->image_path, "/")),
                "alt_text" => $image->alt_text,
                "position" => (int) $image->position,
                "created_at" => $image->created_at,
            ];
        }, $records);

        $this->call(200, "success", "Imagens da ordem de serviço.", "success")->back($data);
    }

    public function upload(array $data): void
    {
        $userType = $this->authenticatedUserType();
        if ($userType === null) {
            $this->call(401, "unauthorized", "Usuário não está autenticado ou não tem permissão.", "error")->back();
            return;
        }

        $serviceOrderId = $this->serviceOrderId($data);
        if ($serviceOrderId === null) {
            $this->call(400, "bad_request", "Informe um ID válido para a ordem de serviço.", "error")->back();
            return;
        }

        if (!isset($_FILES["image"]) || !is_array($_FILES["image"])) {
            $this->call(400, "bad_request", "Selecione uma imagem para enviar.", "error")->back();
            return;
        }

        $images = new ServiceOrderImage();
        if (!$images->canAccessServiceOrder($serviceOrderId, $this->userAuthId, $userType)) {
            if ($images->getErrorMessage() !== null) {
                $this->call(500, "internal_server_error", $images->getErrorMessage(), "error")->back();
                return;
            }
            $this->call(404, "not_found", "Ordem de serviço não encontrada ou sem acesso.", "error")->back();
            return;
        }

        $uploadDirectory = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "service-orders";
        try {
            $filename = (new Upload($uploadDirectory))->image($_FILES["image"]);
        } catch (\RuntimeException $exception) {
            $this->call(400, "bad_request", $exception->getMessage(), "error")->back();
            return;
        }

        $imagePath = "uploads/service-orders/" . $filename;
        $imageId = $images->insert($serviceOrderId, $imagePath);
        if ($imageId === false) {
            $savedPath = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
            if (is_file($savedPath) && !unlink($savedPath)) {
                error_log("Não foi possível remover o arquivo de imagem após falha ao persistir a galeria.");
            }
            $this->call(500, "internal_server_error", $images->getErrorMessage(), "error")->back();
            return;
        }

        $this->call(201, "created", "Imagem adicionada à ordem de serviço.", "success")->back([
            "id" => $imageId,
            "service_order_id" => $serviceOrderId,
            "image_url" => url($imagePath),
            "alt_text" => "Imagem da ordem de serviço",
        ]);
    }

    public function delete(array $data): void
    {
        if (!$this->authToken(1)) {
            $this->call(401, "unauthorized", "Usuário não está autenticado ou não tem permissão.", "error")->back();
            return;
        }

        $serviceOrderId = $this->serviceOrderId($data);
        $imageId = filter_var($data["imageId"] ?? null, FILTER_VALIDATE_INT);
        if ($serviceOrderId === null || $imageId === false || $imageId === null || $imageId < 1) {
            $this->call(400, "bad_request", "Informe IDs válidos para a ordem e a imagem.", "error")->back();
            return;
        }

        $images = new ServiceOrderImage();
        $image = $images->findById($serviceOrderId, $imageId);
        if ($image === false) {
            if ($images->getErrorMessage() !== null) {
                $this->call(500, "internal_server_error", $images->getErrorMessage(), "error")->back();
                return;
            }
            $this->call(404, "not_found", "Imagem não encontrada nesta ordem de serviço.", "error")->back();
            return;
        }

        $managedFile = $this->managedImagePath((string) $image->image_path);
        if ($managedFile === null) {
            $this->call(500, "internal_server_error", "O caminho da imagem armazenada é inválido.", "error")->back();
            return;
        }

        if (!$images->delete($serviceOrderId, $imageId)) {
            $this->call(500, "internal_server_error", $images->getErrorMessage() ?? "Não foi possível remover a imagem.", "error")->back();
            return;
        }

        if (is_file($managedFile) && !unlink($managedFile)) {
            error_log("O registro da imagem foi removido, mas o arquivo correspondente não pôde ser excluído.");
            $this->call(500, "internal_server_error", "O registro foi removido, mas não foi possível excluir o arquivo.", "error")->back();
            return;
        }

        $this->call(200, "success", "Imagem removida da ordem de serviço.", "success")->back();
    }

    private function serviceOrderId(array $data): ?int
    {
        $id = filter_var($data["serviceOrderId"] ?? null, FILTER_VALIDATE_INT);
        return $id !== false && $id !== null && $id > 0 ? $id : null;
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

    private function managedImagePath(string $imagePath): ?string
    {
        if (!preg_match('/\Auploads\/service-orders\/([a-f0-9]{32}\.(?:jpg|png|webp))\z/', $imagePath, $matches)) {
            return null;
        }

        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR .
            "service-orders" . DIRECTORY_SEPARATOR . $matches[1];
    }
}
