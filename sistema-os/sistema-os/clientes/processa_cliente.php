<?php
session_start();
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'excluir':
        excluirCliente($db);
        break;
    default:
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Ação inválida.'
        ];
        header('Location: lista.php');
        break;
}

function excluirCliente($db) {
    $id = $_GET['id'] ?? 0;
    
    if (!$id) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'ID do cliente não informado.'
        ];
        header('Location: lista.php');
        exit;
    }
    
    try {
        // Verificar se cliente tem ordens
        $query_check = "SELECT COUNT(*) as total FROM ordens_servico WHERE cliente_id = :id";
        $stmt_check = $db->prepare($query_check);
        $stmt_check->bindParam(':id', $id);
        $stmt_check->execute();
        $result = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            throw new Exception('Não é possível excluir cliente que possui ordens de serviço.');
        }
        
        // Excluir cliente
        $query = "DELETE FROM clientes WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Cliente excluído com sucesso!'
            ];
        } else {
            throw new Exception('Erro ao excluir cliente.');
        }
        
    } catch (Exception $e) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Erro: ' . $e->getMessage()
        ];
    }
    
    header('Location: lista.php');
    exit;
}
?>