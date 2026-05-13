<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/helpers.php';

$database = new Database();
$db = $database->getConnection();

// Verificar ação
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'cadastrar':
        cadastrarOrdem($db);
        break;
    case 'alterar_status':
        alterarStatus($db);
        break;
    case 'excluir':
        excluirOrdem($db);
        break;
    case 'marcar_entregue':
        marcarEntregue($db);
        break;
    default:
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Ação inválida.'
        ];
        header('Location: lista.php');
        exit;
}

function cadastrarOrdem($db) {
    try {
        // Validar dados obrigatórios
        $required = ['numero_ordem', 'data_entrada', 'cliente_id', 'marca_aparelho', 
                    'modelo_aparelho', 'defeito_relatado', 'servico_realizar', 
                    'valor_servico', 'status'];
        
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("O campo {$field} é obrigatório.");
            }
        }
        
        // Converter valores monetários
        $valor_servico = valorParaFloat($_POST['valor_servico']);
        $valor_pecas = valorParaFloat($_POST['valor_pecas'] ?? '0');
        $valor_total = $valor_servico + $valor_pecas;
        
        // Converter data de entrada para formato do banco
        $data_entrada = inputParaBanco($_POST['data_entrada']);
        if (!$data_entrada) {
            $data_entrada = dataAtual();
        }
        
        // Converter prazo de entrega se existir
        $prazo_entrega = null;
        if (!empty($_POST['prazo_entrega'])) {
            $prazo_entrega = $_POST['prazo_entrega'] . ' 23:59:59';
        }
        
        // Preparar query
        $query = "INSERT INTO ordens_servico (
                    numero_ordem, data_entrada, cliente_id, marca_aparelho, 
                    modelo_aparelho, imei, estado_fisico, defeito_relatado, 
                    servico_realizar, pecas_utilizadas, valor_servico, valor_pecas, 
                    valor_total, forma_pagamento, prazo_entrega, status, observacoes_tecnicas
                  ) VALUES (
                    :numero_ordem, :data_entrada, :cliente_id, :marca_aparelho,
                    :modelo_aparelho, :imei, :estado_fisico, :defeito_relatado,
                    :servico_realizar, :pecas_utilizadas, :valor_servico, :valor_pecas,
                    :valor_total, :forma_pagamento, :prazo_entrega, :status, :observacoes_tecnicas
                  )";
        
        $stmt = $db->prepare($query);
        
        // Bind dos parâmetros
        $stmt->bindParam(':numero_ordem', $_POST['numero_ordem']);
        $stmt->bindParam(':data_entrada', $data_entrada);
        $stmt->bindParam(':cliente_id', $_POST['cliente_id']);
        $stmt->bindParam(':marca_aparelho', $_POST['marca_aparelho']);
        $stmt->bindParam(':modelo_aparelho', $_POST['modelo_aparelho']);
        $stmt->bindParam(':imei', $_POST['imei']);
        $stmt->bindParam(':estado_fisico', $_POST['estado_fisico']);
        $stmt->bindParam(':defeito_relatado', $_POST['defeito_relatado']);
        $stmt->bindParam(':servico_realizar', $_POST['servico_realizar']);
        $stmt->bindParam(':pecas_utilizadas', $_POST['pecas_utilizadas']);
        $stmt->bindParam(':valor_servico', $valor_servico);
        $stmt->bindParam(':valor_pecas', $valor_pecas);
        $stmt->bindParam(':valor_total', $valor_total);
        $stmt->bindParam(':forma_pagamento', $_POST['forma_pagamento']);
        $stmt->bindParam(':prazo_entrega', $prazo_entrega);
        $stmt->bindParam(':status', $_POST['status']);
        $stmt->bindParam(':observacoes_tecnicas', $_POST['observacoes_tecnicas']);
        
        // Executar
        if ($stmt->execute()) {
            $ordem_id = $db->lastInsertId();
            
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Ordem de serviço cadastrada com sucesso! Número: ' . $_POST['numero_ordem']
            ];
            
            // Verificar se deve avançar para impressão
            if (isset($_POST['avancar']) && $_POST['avancar'] === 'imprimir') {
                header('Location: imprimir.php?id=' . $ordem_id);
            } else {
                header('Location: visualizar.php?id=' . $ordem_id);
            }
            exit;
        } else {
            throw new Exception('Erro ao cadastrar ordem de serviço.');
        }
        
    } catch (Exception $e) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Erro: ' . $e->getMessage()
        ];
        header('Location: cadastro.php');
        exit;
    }
}

function alterarStatus($db) {
    try {
        $id = $_POST['id'] ?? $_GET['id'] ?? 0;
        $novo_status = $_POST['status'] ?? $_GET['status'] ?? '';
        
        if (!$id || !$novo_status) {
            throw new Exception('ID ou status não informados.');
        }
        
        // Verificar se o status é válido
        global $STATUS_OPCOES;
        if (!in_array($novo_status, $STATUS_OPCOES)) {
            throw new Exception('Status inválido.');
        }
        
        // Definir data_conclusao se status for Concluída ou Entregue
        $data_conclusao = null;
        if (in_array($novo_status, [STATUS_CONCLUIDA, STATUS_ENTREGUE])) {
            // Verificar se já tem data_conclusao
            $query_check = "SELECT data_conclusao FROM ordens_servico WHERE id = :id";
            $stmt_check = $db->prepare($query_check);
            $stmt_check->bindParam(':id', $id);
            $stmt_check->execute();
            $ordem = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            if (empty($ordem['data_conclusao'])) {
                $data_conclusao = dataAtual();
            }
        }
        
        // Atualizar status
        if ($data_conclusao) {
            $query = "UPDATE ordens_servico SET status = :status, data_conclusao = :data_conclusao WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':status', $novo_status);
            $stmt->bindParam(':data_conclusao', $data_conclusao);
            $stmt->bindParam(':id', $id);
        } else {
            $query = "UPDATE ordens_servico SET status = :status WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':status', $novo_status);
            $stmt->bindParam(':id', $id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Status atualizado para: ' . $novo_status
            ];
        } else {
            throw new Exception('Erro ao atualizar status.');
        }
        
    } catch (Exception $e) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Erro: ' . $e->getMessage()
        ];
    }
    
    header('Location: visualizar.php?id=' . $id);
    exit;
}

function excluirOrdem($db) {
    try {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            throw new Exception('ID da ordem não informado.');
        }
        
        // Verificar se existe
        $query_check = "SELECT numero_ordem FROM ordens_servico WHERE id = :id";
        $stmt_check = $db->prepare($query_check);
        $stmt_check->bindParam(':id', $id);
        $stmt_check->execute();
        
        if ($stmt_check->rowCount() === 0) {
            throw new Exception('Ordem não encontrada.');
        }
        
        $ordem = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Excluir ordem
        $query = "DELETE FROM ordens_servico WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Ordem ' . $ordem['numero_ordem'] . ' excluída com sucesso!'
            ];
        } else {
            throw new Exception('Erro ao excluir ordem.');
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

function marcarEntregue($db) {
    try {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            throw new Exception('ID da ordem não informado.');
        }
        
        // Verificar se a ordem está concluída
        $query_check = "SELECT status FROM ordens_servico WHERE id = :id";
        $stmt_check = $db->prepare($query_check);
        $stmt_check->bindParam(':id', $id);
        $stmt_check->execute();
        $ordem = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if (!$ordem) {
            throw new Exception('Ordem não encontrada.');
        }
        
        if ($ordem['status'] !== STATUS_CONCLUIDA) {
            throw new Exception('Apenas ordens concluídas podem ser marcadas como entregues.');
        }
        
        // Marcar como entregue
        $query = "UPDATE ordens_servico SET status = :status, data_conclusao = :data_conclusao WHERE id = :id";
        $stmt = $db->prepare($query);
        $status_entregue = STATUS_ENTREGUE;
        $data_entrega = dataAtual();
        
        $stmt->bindParam(':status', $status_entregue);
        $stmt->bindParam(':data_conclusao', $data_entrega);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Ordem marcada como entregue!'
            ];
        } else {
            throw new Exception('Erro ao marcar como entregue.');
        }
        
    } catch (Exception $e) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Erro: ' . $e->getMessage()
        ];
    }
    
    header('Location: visualizar.php?id=' . $id);
    exit;
}
?>