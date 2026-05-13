<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/helpers.php';

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço #<?php echo $ordem['numero_ordem']; ?> - <?php echo SISTEMA_NOME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="order-details">
            <!-- Cabeçalho da ordem -->
            <div class="order-header">
                <div class="order-info">
                    <h1>Ordem de Serviço #<?php echo $ordem['numero_ordem']; ?></h1>
                    <div class="order-meta">
                        <span class="status-badge <?php echo classeStatus($ordem['status']); ?>">
                            <i class="<?php echo iconeStatus($ordem['status']); ?>"></i>
                            <?php echo $ordem['status']; ?>
                        </span>
                        <span class="order-date">
                            <i class="far fa-calendar-alt"></i>
                            Entrada: <?php echo exibirData($ordem['data_entrada']); ?>
                        </span>
                        <?php if ($ordem['data_conclusao']): ?>
                            <span class="order-date">
                                <i class="far fa-calendar-check"></i>
                                <?php echo $ordem['status'] === STATUS_ENTREGUE ? 'Entrega' : 'Conclusão'; ?>: <?php echo exibirData($ordem['data_conclusao']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="order-actions">
                    <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="imprimir.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-info">
                        <i class="fas fa-print"></i> Imprimir
                    </a>
                    <a href="lista.php" class="btn">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
            
            <!-- Painel de informações -->
            <div class="info-panels">
                <!-- Painel do Cliente -->
                <div class="info-panel">
                    <h3><i class="fas fa-user"></i> Cliente</h3>
                    <div class="panel-content">
                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($ordem['nome_completo']); ?></p>
                        <p><strong>CPF/RG:</strong> <?php echo formatarDocumento($ordem['cpf_rg']); ?></p>
                        <p><strong>Telefone:</strong> 
                            <a href="<?php echo linkWhatsApp($ordem['telefone']); ?>" target="_blank" class="whatsapp-link">
                                <i class="fab fa-whatsapp"></i> <?php echo formatarTelefone($ordem['telefone']); ?>
                            </a>
                        </p>
                        <p><strong>E-mail:</strong> 
                            <?php if ($ordem['email']): ?>
                                <a href="<?php echo linkEmail($ordem['email']); ?>" class="email-link">
                                    <?php echo $ordem['email']; ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Não informado</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Endereço:</strong> <?php echo nl2br(htmlspecialchars($ordem['endereco'] ?: 'Não informado')); ?></p>
                    </div>
                </div>
                
                <!-- Painel do Aparelho -->
                <div class="info-panel">
                    <h3><i class="fas fa-mobile-alt"></i> Aparelho</h3>
                    <div class="panel-content">
                        <p><strong>Marca:</strong> <?php echo htmlspecialchars($ordem['marca_aparelho']); ?></p>
                        <p><strong>Modelo:</strong> <?php echo htmlspecialchars($ordem['modelo_aparelho']); ?></p>
                        <p><strong>IMEI:</strong> <?php echo $ordem['imei'] ?: '<span class="text-muted">Não informado</span>'; ?></p>
                        <p><strong>Estado Físico:</strong></p>
                        <div class="estado-fisico">
                            <?php echo nl2br(htmlspecialchars($ordem['estado_fisico'] ?: 'Não especificado')); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Painel do Serviço -->
                <div class="info-panel">
                    <h3><i class="fas fa-tools"></i> Serviço</h3>
                    <div class="panel-content">
                        <p><strong>Defeito Relatado:</strong></p>
                        <div class="servico-desc">
                            <?php echo nl2br(htmlspecialchars($ordem['defeito_relatado'])); ?>
                        </div>
                        
                        <p><strong>Serviço a Ser Realizado:</strong></p>
                        <div class="servico-desc">
                            <?php echo nl2br(htmlspecialchars($ordem['servico_realizar'])); ?>
                        </div>
                        
                        <?php if ($ordem['pecas_utilizadas']): ?>
                            <p><strong>Peças Utilizadas:</strong></p>
                            <div class="pecas-list">
                                <?php echo nl2br(htmlspecialchars($ordem['pecas_utilizadas'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Painel Financeiro -->
                <div class="info-panel">
                    <h3><i class="fas fa-dollar-sign"></i> Financeiro</h3>
                    <div class="panel-content">
                        <table class="values-table">
                            <tr>
                                <td>Valor do Serviço:</td>
                                <td><?php echo exibirValor($ordem['valor_servico']); ?></td>
                            </tr>
                            <?php if ($ordem['valor_pecas'] > 0): ?>
                                <tr>
                                    <td>Valor das Peças:</td>
                                    <td><?php echo exibirValor($ordem['valor_pecas']); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="total-row">
                                <td><strong>Valor Total:</strong></td>
                                <td><strong><?php echo exibirValor($ordem['valor_total']); ?></strong></td>
                            </tr>
                        </table>
                        
                        <div class="financial-details">
                            <p><strong>Forma de Pagamento:</strong> <?php echo $ordem['forma_pagamento'] ?: '<span class="text-muted">Não definida</span>'; ?></p>
                            <p><strong>Prazo de Entrega:</strong> 
                                <?php if ($ordem['prazo_entrega']): ?>
                                    <?php echo exibirDataSimples($ordem['prazo_entrega']); ?>
                                    <?php if (estaAtrasado($ordem['prazo_entrega'], $ordem['status'])): ?>
                                        <span class="badge-danger">ATRASADO</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Não definido</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Painel de Observações -->
                <div class="info-panel full-width">
                    <h3><i class="fas fa-sticky-note"></i> Observações Técnicas</h3>
                    <div class="panel-content">
                        <?php if ($ordem['observacoes_tecnicas']): ?>
                            <div class="observacoes">
                                <?php echo nl2br(htmlspecialchars($ordem['observacoes_tecnicas'])); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Nenhuma observação técnica registrada.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Ações rápidas de status -->
            <div class="quick-status-actions">
                <h3>Alterar Status</h3>
                <div class="status-buttons">
                    <form action="processa_ordem.php" method="POST" style="display: inline;">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <input type="hidden" name="action" value="alterar_status">
                        
                        <button type="submit" name="status" value="<?php echo STATUS_EM_ORCAMENTO; ?>" 
                                class="btn btn-outline <?php echo $ordem['status'] === STATUS_EM_ORCAMENTO ? 'active' : ''; ?>">
                            <i class="fas fa-file-invoice-dollar"></i> Em Orçamento
                        </button>
                        
                        <button type="submit" name="status" value="<?php echo STATUS_AGUARDANDO; ?>"
                                class="btn btn-outline <?php echo $ordem['status'] === STATUS_AGUARDANDO ? 'active' : ''; ?>">
                            <i class="fas fa-clock"></i> Aguardando Resposta
                        </button>
                        
                        <button type="submit" name="status" value="<?php echo STATUS_APROVADO; ?>"
                                class="btn btn-outline <?php echo $ordem['status'] === STATUS_APROVADO ? 'active' : ''; ?>">
                            <i class="fas fa-check-circle"></i> Aprovado
                        </button>
                        
                        <button type="submit" name="status" value="<?php echo STATUS_ANDAMENTO; ?>"
                                class="btn btn-outline <?php echo $ordem['status'] === STATUS_ANDAMENTO ? 'active' : ''; ?>">
                            <i class="fas fa-tools"></i> Em Andamento
                        </button>
                        
                        <button type="submit" name="status" value="<?php echo STATUS_CONCLUIDA; ?>"
                                class="btn btn-outline <?php echo $ordem['status'] === STATUS_CONCLUIDA ? 'active' : ''; ?>"
                                onclick="return confirm('Deseja marcar esta ordem como concluída?')">
                            <i class="fas fa-check-double"></i> Concluir
                        </button>
                        
                        <button type="submit" name="status" value="<?php echo STATUS_ENTREGUE; ?>"
                                class="btn btn-outline <?php echo $ordem['status'] === STATUS_ENTREGUE ? 'active' : ''; ?>"
                                onclick="return confirm('Deseja marcar esta ordem como entregue?')">
                            <i class="fas fa-box"></i> Entregue
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Histórico de alterações -->
            <div class="history-section">
                <h3><i class="fas fa-history"></i> Histórico</h3>
                <div class="history-list">
                    <div class="history-item">
                        <span class="history-date"><?php echo exibirData($ordem['data_entrada']); ?></span>
                        <span class="history-action">Ordem criada</span>
                        <span class="history-user">Sistema</span>
                    </div>
                    <?php if ($ordem['data_conclusao']): ?>
                        <div class="history-item">
                            <span class="history-date"><?php echo exibirData($ordem['data_conclusao']); ?></span>
                            <span class="history-action">Ordem <?php echo $ordem['status'] === STATUS_ENTREGUE ? 'entregue' : 'concluída'; ?></span>
                            <span class="history-user">Sistema</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
    <style>
        .order-details {
            padding: 20px 0;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .order-meta {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .order-date {
            background: #f7fafc;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .order-date i {
            color: #667eea;
        }
        
        .info-panels {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-panel {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .info-panel.full-width {
            grid-column: 1 / -1;
        }
        
        .info-panel h3 {
            margin-bottom: 15px;
            color: #4a5568;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .panel-content {
            font-size: 0.95rem;
        }
        
        .panel-content p {
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .servico-desc, .estado-fisico, .pecas-list, .observacoes {
            background: #f7fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .values-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .values-table td {
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .values-table tr:last-child td {
            border-bottom: none;
        }
        
        .total-row td {
            font-size: 1.1rem;
            font-weight: bold;
            color: #2d3748;
            padding-top: 15px;
        }
        
        .quick-status-actions {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .quick-status-actions h3 {
            margin-bottom: 15px;
            color: #4a5568;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #e2e8f0;
            color: #4a5568;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-outline:hover {
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-2px);
        }
        
        .btn-outline.active {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }
        
        .history-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .history-section h3 {
            margin-bottom: 15px;
            color: #4a5568;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .history-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .history-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px;
            background: #f7fafc;
            border-radius: 5px;
        }
        
        .history-date {
            min-width: 150px;
            font-weight: 500;
            color: #4a5568;
        }
        
        .history-action {
            flex: 1;
        }
        
        .history-user {
            color: #718096;
            font-style: italic;
        }
        
        .badge-danger {
            background-color: #fed7d7;
            color: #c53030;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
            margin-left: 5px;
            font-weight: bold;
        }
        
        .whatsapp-link {
            color: #25D366;
            text-decoration: none;
        }
        
        .whatsapp-link:hover {
            text-decoration: underline;
        }
        
        .email-link {
            color: #4299e1;
            text-decoration: none;
        }
        
        .email-link:hover {
            text-decoration: underline;
        }
        
        .text-muted {
            color: #a0aec0;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .info-panels {
                grid-template-columns: 1fr;
            }
            
            .status-buttons {
                flex-direction: column;
            }
            
            .history-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .history-date {
                min-width: auto;
            }
        }
    </style>
</body>
</html>