<?php
session_start();
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

// Buscar dados do cliente
$query = "SELECT * FROM clientes WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Cliente não encontrado.'
    ];
    header('Location: lista.php');
    exit;
}

// Buscar ordens do cliente
$query_ordens = "SELECT * FROM ordens_servico 
                 WHERE cliente_id = :cliente_id 
                 ORDER BY data_entrada DESC";
$stmt_ordens = $db->prepare($query_ordens);
$stmt_ordens->bindParam(':cliente_id', $id);
$stmt_ordens->execute();
$ordens = $stmt_ordens->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas do cliente
$query_stats = "SELECT 
                COUNT(*) as total_ordens,
                SUM(CASE WHEN status = 'Concluída' THEN 1 ELSE 0 END) as ordens_concluidas,
                SUM(CASE WHEN status = 'Entregue' THEN 1 ELSE 0 END) as ordens_entregues,
                SUM(valor_total) as total_gasto
                FROM ordens_servico 
                WHERE cliente_id = :cliente_id";
$stmt_stats = $db->prepare($query_stats);
$stmt_stats->bindParam(':cliente_id', $id);
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente: <?php echo htmlspecialchars($cliente['nome_completo']); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="client-details">
            <!-- Cabeçalho do cliente -->
            <div class="client-header">
                <div class="client-info">
                    <h1><?php echo htmlspecialchars($cliente['nome_completo']); ?></h1>
                    <div class="client-meta">
                        <span class="client-id">ID: #<?php echo str_pad($cliente['id'], 4, '0', STR_PAD_LEFT); ?></span>
                        <span class="client-since">
                            <i class="far fa-calendar-alt"></i>
                            Cadastrado em: <?php echo date('d/m/Y', strtotime($cliente['data_cadastro'])); ?>
                        </span>
                    </div>
                </div>
                
                <div class="client-actions">
                    <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="../ordens/cadastro.php?cliente_id=<?php echo $id; ?>" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Nova Ordem
                    </a>
                    <a href="lista.php" class="btn">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
            
            <!-- Estatísticas do cliente -->
            <div class="client-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total de Ordens</h3>
                        <span class="stat-number"><?php echo $stats['total_ordens'] ?? 0; ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Concluídas</h3>
                        <span class="stat-number"><?php echo $stats['ordens_concluidas'] ?? 0; ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%);">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Entregues</h3>
                        <span class="stat-number"><?php echo $stats['ordens_entregues'] ?? 0; ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Gasto</h3>
                        <span class="stat-number">R$ <?php echo number_format($stats['total_gasto'] ?? 0, 2, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Informações do cliente -->
            <div class="info-panels">
                <!-- Dados Pessoais -->
                <div class="info-panel">
                    <h3><i class="fas fa-id-card"></i> Dados Pessoais</h3>
                    <div class="panel-content">
                        <p><strong>Nome Completo:</strong> <?php echo htmlspecialchars($cliente['nome_completo']); ?></p>
                        <p><strong>CPF/RG:</strong> <?php echo $cliente['cpf_rg']; ?></p>
                        <p><strong>Cadastrado em:</strong> <?php echo date('d/m/Y H:i', strtotime($cliente['data_cadastro'])); ?></p>
                    </div>
                </div>
                
                <!-- Contato -->
                <div class="info-panel">
                    <h3><i class="fas fa-phone-alt"></i> Contato</h3>
                    <div class="panel-content">
                        <p>
                            <strong>Telefone:</strong> 
                            <a href="https://wa.me/55<?php echo preg_replace('/\D/', '', $cliente['telefone']); ?>" 
                               target="_blank" class="whatsapp-link">
                                <i class="fab fa-whatsapp"></i> <?php echo $cliente['telefone']; ?>
                            </a>
                        </p>
                        <p>
                            <strong>E-mail:</strong> 
                            <?php if ($cliente['email']): ?>
                                <a href="mailto:<?php echo $cliente['email']; ?>" class="email-link">
                                    <?php echo $cliente['email']; ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Não informado</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <!-- Endereço -->
                <div class="info-panel full-width">
                    <h3><i class="fas fa-map-marker-alt"></i> Endereço</h3>
                    <div class="panel-content">
                        <?php if ($cliente['endereco']): ?>
                            <p><?php echo nl2br(htmlspecialchars($cliente['endereco'])); ?></p>
                        <?php else: ?>
                            <p class="text-muted">Endereço não informado</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Histórico de ordens -->
            <div class="orders-history">
                <h3><i class="fas fa-history"></i> Histórico de Ordens de Serviço</h3>
                
                <?php if ($ordens): ?>
                    <div class="table-responsive">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Aparelho</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Data Entrada</th>
                                    <th>Prazo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ordens as $ordem): ?>
                                    <tr>
                                        <td><strong><?php echo $ordem['numero_ordem']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($ordem['marca_aparelho'] . ' ' . $ordem['modelo_aparelho']); ?></td>
                                        <td>R$ <?php echo number_format($ordem['valor_total'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo str_replace(' ', '-', strtolower($ordem['status'])); ?>">
                                                <?php echo $ordem['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($ordem['data_entrada'])); ?></td>
                                        <td>
                                            <?php if ($ordem['prazo_entrega']): ?>
                                                <?php echo date('d/m/Y', strtotime($ordem['prazo_entrega'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="actions">
                                            <a href="../ordens/visualizar.php?id=<?php echo $ordem['id']; ?>" class="btn-action" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="../ordens/editar.php?id=<?php echo $ordem['id']; ?>" class="btn-action" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../ordens/imprimir.php?id=<?php echo $ordem['id']; ?>" target="_blank" class="btn-action" title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-clipboard"></i>
                        <p>Este cliente ainda não possui ordens de serviço.</p>
                        <a href="../ordens/cadastro.php?cliente_id=<?php echo $id; ?>" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Criar Primeira Ordem
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
    <style>
        .client-details {
            padding: 20px 0;
        }
        
        .client-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .client-meta {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .client-id {
            background: #e2e8f0;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .client-since {
            background: #f7fafc;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .client-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .client-stats .stat-card {
            margin: 0;
        }
        
        .info-panels {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
        }
        
        .panel-content p {
            margin-bottom: 10px;
        }
        
        .orders-history {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .orders-history h3 {
            margin-bottom: 20px;
            color: #4a5568;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
        }
        
        .no-orders {
            text-align: center;
            padding: 40px;
            color: #718096;
        }
        
        .no-orders i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }
        
        .no-orders p {
            margin-bottom: 20px;
            font-size: 1.1rem;
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
        }
        
        @media (max-width: 768px) {
            .client-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .client-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .info-panels {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>