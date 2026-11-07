<?php

namespace Source\Models;

use PDO;
use PDOException;
use Source\Core\Connect;

class ServiceOrderImage
{
    private ?string $errorMessage = null;

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function serviceOrderExists(int $serviceOrderId): bool
    {
        try {
            $query = "SELECT id FROM service_orders WHERE id = :id AND active = 1 LIMIT 1";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindValue(':id', $serviceOrderId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch() !== false;
        } catch (PDOException $exception) {
            $this->errorMessage = "Não foi possível validar a ordem de serviço.";
            return false;
        }
    }

    public function insert(int $serviceOrderId, string $imagePath)
    {
        try {
            $query = "INSERT INTO service_order_images (service_order_id, image_path)
                      VALUES (:service_order_id, :image_path)";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindValue(':service_order_id', $serviceOrderId, PDO::PARAM_INT);
            $stmt->bindValue(':image_path', $imagePath);
            $stmt->execute();

            $id = (int) Connect::getInstance()->lastInsertId();
            if ($id < 1) {
                $this->errorMessage = "A imagem foi recebida, mas seu identificador não foi gerado.";
                return false;
            }

            return $id;
        } catch (PDOException $exception) {
            $this->errorMessage = "Não foi possível registrar a imagem da ordem de serviço.";
            return false;
        }
    }

    public function listByServiceOrder(int $serviceOrderId)
    {
        try {
            $query = "SELECT id, service_order_id, image_path, alt_text, position, created_at
                      FROM service_order_images
                      WHERE service_order_id = :service_order_id
                      ORDER BY position ASC, id ASC";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindValue(':service_order_id', $serviceOrderId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $exception) {
            $this->errorMessage = "Não foi possível listar as imagens da ordem de serviço.";
            return false;
        }
    }

    public function findById(int $serviceOrderId, int $imageId)
    {
        try {
            $query = "SELECT id, service_order_id, image_path
                      FROM service_order_images
                      WHERE service_order_id = :service_order_id AND id = :id
                      LIMIT 1";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindValue(':service_order_id', $serviceOrderId, PDO::PARAM_INT);
            $stmt->bindValue(':id', $imageId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch() ?: false;
        } catch (PDOException $exception) {
            $this->errorMessage = "Não foi possível localizar a imagem da ordem de serviço.";
            return false;
        }
    }

    public function delete(int $serviceOrderId, int $imageId): bool
    {
        try {
            $query = "DELETE FROM service_order_images
                      WHERE service_order_id = :service_order_id AND id = :id";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindValue(':service_order_id', $serviceOrderId, PDO::PARAM_INT);
            $stmt->bindValue(':id', $imageId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() === 1;
        } catch (PDOException $exception) {
            $this->errorMessage = "Não foi possível remover a imagem da ordem de serviço.";
            return false;
        }
    }

    public function removeAllForServiceOrder(int $serviceOrderId): bool
    {
        try {
            $connection = Connect::getInstance();
            $query = "SELECT image_path FROM service_order_images WHERE service_order_id = :service_order_id";
            $stmt = $connection->prepare($query);
            $stmt->bindValue(':service_order_id', $serviceOrderId, PDO::PARAM_INT);
            $stmt->execute();
            $imagePaths = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($imagePaths as $imagePath) {
                if (!preg_match('/\Auploads\/service-orders\/[a-f0-9]{32}\.(?:jpg|png|webp)\z/', $imagePath)) {
                    $this->errorMessage = "A galeria contém um caminho de arquivo inválido.";
                    return false;
                }
            }

            $delete = $connection->prepare(
                "DELETE FROM service_order_images WHERE service_order_id = :service_order_id"
            );
            $delete->bindValue(':service_order_id', $serviceOrderId, PDO::PARAM_INT);
            $delete->execute();

            $uploadDirectory = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "uploads" .
                DIRECTORY_SEPARATOR . "service-orders";
            $filesRemoved = true;
            foreach ($imagePaths as $imagePath) {
                $filename = basename($imagePath);
                $filePath = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
                if (is_file($filePath) && !unlink($filePath)) {
                    error_log("Não foi possível excluir uma imagem da ordem de serviço removida.");
                    $filesRemoved = false;
                }
            }

            if (!$filesRemoved) {
                $this->errorMessage = "Os registros da galeria foram removidos, mas alguns arquivos não puderam ser excluídos.";
            }

            return $filesRemoved;
        } catch (PDOException $exception) {
            $this->errorMessage = "Não foi possível limpar as imagens da ordem de serviço.";
            return false;
        }
    }
}
