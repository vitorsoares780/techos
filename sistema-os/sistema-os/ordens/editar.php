<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/helpers.php';

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

// Buscar dados da ordem
$query = "SELECT os.*, c.* 
          FROM ordens_servico os 
          JOIN clientes c ON os.cliente_id = c.id 
          WHERE os.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$ordem = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ordem) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Ordem não encontrada.'
    ];
    header('Location: lista.php');
    exit;
}

// Buscar lista de clientes
$query_clientes = "SELECT id, nome_completo FROM clientes ORDER BY nome_completo";
$stmt_clientes = $db->prepare($query_clientes);
$stmt_clientes->execute();
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $query_update = "UPDATE ordens_servico SET 
                        cliente_id = :cliente_id,
                        marca_aparelho = :marca_aparelho,
                        modelo_aparelho = :modelo_aparelho,
                        imei = :imei,
                        estado_fisico = :estado_fisico,
                        defeito_relatado = :defeito_relatado,
                        servico_realizar = :servico_realizar,
                        pecas_utilizadas = :pecas_utilizadas,
                        valor_servico = :valor_servico,
                        valor_pecas = :valor_pecas,
                        valor_total = :valor_total,
                        forma_pagamento = :forma_pagamento,
                        prazo_entrega = :prazo_entrega,
                        status = :status,
                        observacoes_tecnicas = :observacoes_tecnicas,
                        data_conclusao = :data_conclusao
                        WHERE id = :id";
        
        $stmt_update = $db->prepare($query_update);
        
        // Calcular total
        $valor_servico = valorParaFloat($_POST['valor_servico']);
        $valor_pecas = valorParaFloat($_POST['valor_pecas'] ?? '0');
        $valor_total = $valor_servico + $valor_pecas;
        
        // Converter prazo de entrega
        $prazo_entrega = null;
        if (!empty($_POST['prazo_entrega'])) {
            $prazo_entrega = $_POST['prazo_entrega'] . ' 23:59:59';
        }
        
        // Definir data_conclusao se status for Concluída ou Entregue
        $data_conclusao = $ordem['data_conclusao'];
        if (in_array($_POST['status'], [STATUS_CONCLUIDA, STATUS_ENTREGUE]) && empty($ordem['data_conclusao'])) {
            $data_conclusao = dataAtual();
        }
        
        $stmt_update->bindParam(':cliente_id', $_POST['cliente_id']);
        $stmt_update->bindParam(':marca_aparelho', $_POST['marca_aparelho']);
        $stmt_update->bindParam(':modelo_aparelho', $_POST['modelo_aparelho']);
        $stmt_update->bindParam(':imei', $_POST['imei']);
        $stmt_update->bindParam(':estado_fisico', $_POST['estado_fisico']);
        $stmt_update->bindParam(':defeito_relatado', $_POST['defeito_relatado']);
        $stmt_update->bindParam(':servico_realizar', $_POST['servico_realizar']);
        $stmt_update->bindParam(':pecas_utilizadas', $_POST['pecas_utilizadas']);
        $stmt_update->bindParam(':valor_servico', $valor_servico);
        $stmt_update->bindParam(':valor_pecas', $valor_pecas);
        $stmt_update->bindParam(':valor_total', $valor_total);
        $stmt_update->bindParam(':forma_pagamento', $_POST['forma_pagamento']);
        $stmt_update->bindParam(':prazo_entrega', $prazo_entrega);
        $stmt_update->bindParam(':status', $_POST['status']);
        $stmt_update->bindParam(':observacoes_tecnicas', $_POST['observacoes_tecnicas']);
        $stmt_update->bindParam(':data_conclusao', $data_conclusao);
        $stmt_update->bindParam(':id', $id);
        
        if ($stmt_update->execute()) {
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Ordem atualizada com sucesso!'
            ];
            header('Location: visualizar.php?id=' . $id);
            exit;
        } else {
            throw new Exception('Erro ao atualizar ordem.');
        }
    } catch (Exception $e) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Erro: ' . $e->getMessage()
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ordem #<?php echo $ordem['numero_ordem']; ?> - <?php echo SISTEMA_NOME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="form-container">
            <h1><i class="fas fa-edit"></i> Editar Ordem #<?php echo $ordem['numero_ordem']; ?></h1>
            
            <div class="client-header-info">
                <p><strong>ID:</strong> #<?php echo str_pad($ordem['id'], 4, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Cadastrado em:</strong> <?php echo exibirData($ordem['data_entrada']); ?></p>
                <?php if ($ordem['data_conclusao']): ?>
                    <p><strong><?php echo $ordem['status'] === STATUS_ENTREGUE ? 'Entregue em' : 'Concluída em'; ?>:</strong> <?php echo exibirData($ordem['data_conclusao']); ?></p>
                <?php endif; ?>
            </div>
            
            <form id="form_ordem" method="POST">
                <input type="hidden" name="action" value="atualizar">
                
                <div class="form-section">
                    <h2>Informações da Ordem</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="numero_ordem">Número da Ordem</label>
                            <input type="text" id="numero_ordem" value="<?php echo htmlspecialchars($ordem['numero_ordem']); ?>" readonly class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="data_entrada">Data de Entrada</label>
                            <input type="datetime-local" id="data_entrada" name="data_entrada" 
                                   value="<?php echo !empty($ordem['data_entrada']) ? date('Y-m-d\TH:i', strtotime($ordem['data_entrada'])) : ''; ?>" 
                                   class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Dados do Cliente</h2>
                    <div class="form-group">
                        <label for="cliente_id">Cliente *</label>
                        <select id="cliente_id" name="cliente_id" required class="form-control">
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id']; ?>" <?php echo $cliente['id'] == $ordem['cliente_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cliente['nome_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Cliente não encontrado? <a href="../clientes/cadastro.php">Cadastrar novo cliente</a></small>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Dados do Aparelho</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="marca_aparelho">Marca *</label>
                            <input type="text" id="marca_aparelho" name="marca_aparelho" 
                                   value="<?php echo htmlspecialchars($ordem['marca_aparelho']); ?>" 
                                   required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="modelo_aparelho">Modelo *</label>
                            <input type="text" id="modelo_aparelho" name="modelo_aparelho" 
                                   value="<?php echo htmlspecialchars($ordem['modelo_aparelho']); ?>" 
                                   required class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="imei">IMEI (opcional)</label>
                        <input type="text" id="imei" name="imei" 
                               value="<?php echo htmlspecialchars($ordem['imei']); ?>" 
                               class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="estado_fisico">Estado Físico do Aparelho</label>
                        <textarea id="estado_fisico" name="estado_fisico" rows="3" 
                                  class="form-control"><?php echo htmlspecialchars($ordem['estado_fisico']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Descrição do Serviço</h2>
                    <div class="form-group">
                        <label for="defeito_relatado">Defeito Relatado pelo Cliente *</label>
                        <textarea id="defeito_relatado" name="defeito_relatado" rows="4" 
                                  required class="form-control"><?php echo htmlspecialchars($ordem['defeito_relatado']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="servico_realizar">Serviço a Ser Realizado *</label>
                        <textarea id="servico_realizar" name="servico_realizar" rows="4" 
                                  required class="form-control"><?php echo htmlspecialchars($ordem['servico_realizar']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="pecas_utilizadas">Peças Utilizadas (opcional)</label>
                        <textarea id="pecas_utilizadas" name="pecas_utilizadas" rows="3" 
                                  class="form-control"><?php echo htmlspecialchars($ordem['pecas_utilizadas']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Valores e Prazo</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="valor_servico">Valor do Serviço (R$) *</label>
                            <input type="text" id="valor_servico" name="valor_servico" 
                                   value="<?php echo exibirValor($ordem['valor_servico']); ?>" 
                                   required class="form-control money-input">
                        </div>
                        
                        <div class="form-group">
                            <label for="valor_pecas">Valor das Peças (R$)</label>
                            <input type="text" id="valor_pecas" name="valor_pecas" 
                                   value="<?php echo exibirValor($ordem['valor_pecas']); ?>" 
                                   class="form-control money-input">
                        </div>
                        
                        <div class="form-group">
                            <label for="valor_total">Valor Total (R$)</label>
                            <input type="text" id="valor_total" name="valor_total" 
                                   value="<?php echo exibirValor($ordem['valor_total']); ?>" 
                                   readonly class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="forma_pagamento">Forma de Pagamento</label>
                            <select id="forma_pagamento" name="forma_pagamento" class="form-control">
                                <option value="">Selecione...</option>
                                <?php foreach ($FORMAS_PAGAMENTO as $forma): ?>
                                    <option value="<?php echo $forma; ?>" <?php echo $ordem['forma_pagamento'] == $forma ? 'selected' : ''; ?>>
                                        <?php echo $forma; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="prazo_entrega">Prazo de Entrega</label>
                            <input type="date" id="prazo_entrega" name="prazo_entrega" 
                                   value="<?php echo !empty($ordem['prazo_entrega']) ? date('Y-m-d', strtotime($ordem['prazo_entrega'])) : ''; ?>" 
                                   class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Status e Observações</h2>
                    <div class="form-group">
                        <label for="status">Status da Ordem *</label>
                        <select id="status" name="status" required class="form-control">
                            <?php foreach ($STATUS_OPCOES as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo $ordem['status'] == $status ? 'selected' : ''; ?>>
                                    <?php echo $status; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacoes_tecnicas">Observações Técnicas</label>
                        <textarea id="observacoes_tecnicas" name="observacoes_tecnicas" rows="4" 
                                  class="form-control"><?php echo htmlspecialchars($ordem['observacoes_tecnicas']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Atualizar Ordem
                    </button>
                    <a href="visualizar.php?id=<?php echo $id; ?>" class="btn">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmarExclusao()">
                        <i class="fas fa-trash"></i> Excluir Ordem
                    </button>
                </div>
            </form>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
    <script>
        // Formatação de valores monetários
        document.querySelectorAll('.money-input').forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = (parseInt(value) || 0) / 100;
                e.target.value = value.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                calcularTotal();
            });
            
            // Inicializar com valor formatado
            let value = input.value.replace(/\D/g, '');
            value = (parseInt(value) || 0) / 100;
            input.value = value.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        });
        
        function calcularTotal() {
            const valorServico = parseFloat(document.getElementById('valor_servico').value.replace(/\./g, '').replace(',', '.')) || 0;
            const valorPecas = parseFloat(document.getElementById('valor_pecas').value.replace(/\./g, '').replace(',', '.')) || 0;
            const total = valorServico + valorPecas;
            
            document.getElementById('valor_total').value = total.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        // Calcular no carregamento da página
        document.addEventListener('DOMContentLoaded', calcularTotal);
        
        function confirmarExclusao() {
            if (confirm('ATENÇÃO: Tem certeza que deseja excluir esta ordem de serviço?\nEsta ação não pode ser desfeita!')) {
                window.location.href = 'processa_ordem.php?action=excluir&id=<?php echo $id; ?>';
            }
        }
        
        // Validação do formulário
        document.getElementById('form_ordem').addEventListener('submit', function(e) {
            const camposObrigatorios = [
                'cliente_id',
                'marca_aparelho', 
                'modelo_aparelho',
                'defeito_relatado',
                'servico_realizar',
                'valor_servico',
                'status'
            ];
            
            let valido = true;
            
            camposObrigatorios.forEach(campo => {
                const element = document.getElementById(campo);
                if (!element.value.trim()) {
                    element.style.borderColor = '#f56565';
                    valido = false;
                } else {
                    element.style.borderColor = '';
                }
            });
            
            if (!valido) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
                return false;
            }
            
            // Converter valores monetários para formato numérico
            const valorServico = document.getElementById('valor_servico');
            const valorPecas = document.getElementById('valor_pecas');
            
            valorServico.value = valorServico.value.replace(/\./g, '').replace(',', '.');
            valorPecas.value = valorPecas.value.replace(/\./g, '').replace(',', '.');
            
            return true;
        });
    </script>
    
    <style>
        .client-header-info {
            background: #f7fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .client-header-info p {
            margin: 0;
            color: #4a5568;
        }
        
        .btn-danger {
            background-color: #f56565;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #e53e3e;
        }
        
        .form-control:read-only {
            background-color: #f7fafc;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            .client-header-info {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</body>
</html>