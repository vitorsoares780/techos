<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/helpers.php';

$database = new Database();
$db = $database->getConnection();

// Consultar estatísticas
$query = "SELECT 
            COUNT(*) as total_ordens,
            SUM(CASE WHEN status = 'Concluída' THEN 1 ELSE 0 END) as concluidas,
            SUM(CASE WHEN status = 'Em andamento' THEN 1 ELSE 0 END) as em_andamento,
            SUM(CASE WHEN status = 'Em orçamento' THEN 1 ELSE 0 END) as em_orcamento,
            SUM(CASE WHEN prazo_entrega < CURDATE() 
                     AND status NOT IN ('Concluída', 'Entregue') 
                     THEN 1 ELSE 0 END) as atrasadas
          FROM ordens_servico";
$stmt = $db->prepare($query);
$stmt->execute();
$estatisticas = $stmt->fetch(PDO::FETCH_ASSOC);

// Últimas ordens
$query_ordens = "SELECT os.*, c.nome_completo 
                FROM ordens_servico os 
                JOIN clientes c ON os.cliente_id = c.id 
                ORDER BY os.data_entrada DESC 
                LIMIT 10";
$stmt_ordens = $db->prepare($query_ordens);
$stmt_ordens->execute();
$ultimas_ordens = $stmt_ordens->fetchAll(PDO::FETCH_ASSOC);

// Ordens atrasadas
$query_atrasadas = "SELECT os.*, c.nome_completo 
                   FROM ordens_servico os 
                   JOIN clientes c ON os.cliente_id = c.id 
                   WHERE os.prazo_entrega < CURDATE() 
                   AND os.status NOT IN ('Concluída', 'Entregue')
                   ORDER BY os.prazo_entrega ASC 
                   LIMIT 5";
$stmt_atrasadas = $db->prepare($query_atrasadas);
$stmt_atrasadas->execute();
$ordens_atrasadas = $stmt_atrasadas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SISTEMA_NOME; ?> - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/header.php'; ?>
        
        <main class="dashboard">
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <div class="dashboard-time">
                    <i class="far fa-clock"></i>
                    <span id="relogio"><?php echo date('H:i'); ?></span>
                    <span class="date"><?php echo date('d/m/Y'); ?></span>
                </div>
            </div>
            
            <!-- Cards de estatísticas -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total de Ordens</h3>
                        <span class="stat-number"><?php echo $estatisticas['total_ordens'] ?? 0; ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Concluídas</h3>
                        <span class="stat-number"><?php echo $estatisticas['concluidas'] ?? 0; ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Em Andamento</h3>
                        <span class="stat-number"><?php echo $estatisticas['em_andamento'] ?? 0; ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Em Orçamento</h3>
                        <span class="stat-number"><?php echo $estatisticas['em_orcamento'] ?? 0; ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Atrasadas</h3>
                        <span class="stat-number"><?php echo $estatisticas['atrasadas'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <!-- Ações Rápidas -->
                <div class="dashboard-card">
                    <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
                    <div class="action-buttons">
                        <a href="ordens/cadastro.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Nova Ordem
                        </a>
                        <a href="clientes/cadastro.php" class="btn btn-secondary">
                            <i class="fas fa-user-plus"></i> Novo Cliente
                        </a>
                        <a href="ordens/lista.php" class="btn btn-info">
                            <i class="fas fa-list"></i> Todas as Ordens
                        </a>
                        <a href="clientes/lista.php" class="btn">
                            <i class="fas fa-users"></i> Todos os Clientes
                        </a>
                    </div>
                </div>
                
                <!-- Ordens Atrasadas -->
                <?php if ($ordens_atrasadas): ?>
                <div class="dashboard-card alert-card">
                    <h2><i class="fas fa-exclamation-triangle"></i> Ordens Atrasadas</h2>
                    <div class="alert-list">
                        <?php foreach ($ordens_atrasadas as $ordem): ?>
                            <div class="alert-item">
                                <div class="alert-info">
                                    <strong>#<?php echo $ordem['numero_ordem']; ?></strong>
                                    <span><?php echo htmlspecialchars($ordem['nome_completo']); ?></span>
                                </div>
                                <div class="alert-details">
                                    <span class="prazo">
                                        Prazo: <?php echo exibirDataSimples($ordem['prazo_entrega']); ?>
                                    </span>
                                    <span class="atraso">
                                        <?php echo calcularDiasAtraso($ordem['prazo_entrega']); ?> dia(s) atrasado
                                    </span>
                                </div>
                                <div class="alert-actions">
                                    <a href="ordens/visualizar.php?id=<?php echo $ordem['id']; ?>" class="btn-action">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="ordens/editar.php?id=<?php echo $ordem['id']; ?>" class="btn-action">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Últimas Ordens -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-history"></i> Últimas Ordens</h2>
                    <a href="ordens/lista.php" class="btn btn-sm">Ver todas</a>
                </div>
                
                <?php if ($ultimas_ordens): ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Aparelho</th>
                                <th>Status</th>
                                <th>Data Entrada</th>
                                <th>Prazo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimas_ordens as $ordem): ?>
                                <tr class="<?php echo estaAtrasado($ordem['prazo_entrega'], $ordem['status']) ? 'atrasado' : ''; ?>">
                                    <td><strong><?php echo $ordem['numero_ordem']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($ordem['nome_completo']); ?></td>
                                    <td><?php echo htmlspecialchars($ordem['marca_aparelho'] . ' ' . $ordem['modelo_aparelho']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo classeStatus($ordem['status']); ?>">
                                            <i class="<?php echo iconeStatus($ordem['status']); ?>"></i>
                                            <?php echo $ordem['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo exibirData($ordem['data_entrada']); ?></td>
                                    <td>
                                        <?php if ($ordem['prazo_entrega']): ?>
                                            <?php echo exibirDataSimples($ordem['prazo_entrega']); ?>
                                            <?php if (estaAtrasado($ordem['prazo_entrega'], $ordem['status'])): ?>
                                                <span class="badge-danger">ATRASADO</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <a href="ordens/visualizar.php?id=<?php echo $ordem['id']; ?>" class="btn-action" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="ordens/editar.php?id=<?php echo $ordem['id']; ?>" class="btn-action" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="ordens/imprimir.php?id=<?php echo $ordem['id']; ?>" target="_blank" class="btn-action" title="Imprimir">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">Nenhuma ordem cadastrada ainda.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
    <script>
        // Relógio em tempo real
        function atualizarRelogio() {
            const agora = new Date();
            const horas = agora.getHours().toString().padStart(2, '0');
            const minutos = agora.getMinutes().toString().padStart(2, '0');
            const segundos = agora.getSeconds().toString().padStart(2, '0');
            
            document.getElementById('relogio').textContent = `${horas}:${minutos}:${segundos}`;
        }
        
        // Atualizar a cada segundo
        setInterval(atualizarRelogio, 1000);
        atualizarRelogio(); // Inicializar
        
        // Auto-refresh do dashboard a cada 30 segundos (opcional)
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
    
    <style>
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .dashboard-time {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
        }
        
        .dashboard-time i {
            color: #667eea;
        }
        
        #relogio {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #2d3748;
        }
        
        .dashboard-time .date {
            color: #718096;
            font-size: 0.9rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .dashboard-card.alert-card {
            border-left: 5px solid #f56565;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        
        .alert-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .alert-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #fff5f5;
            border-radius: 5px;
            border-left: 3px solid #f56565;
        }
        
        .alert-info {
            flex: 1;
        }
        
        .alert-details {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            margin: 0 15px;
        }
        
        .prazo {
            font-size: 0.9rem;
            color: #718096;
        }
        
        .atraso {
            font-size: 0.8rem;
            color: #c53030;
            font-weight: bold;
        }
        
        .alert-actions {
            display: flex;
            gap: 5px;
        }
        
        tr.atrasado {
            background-color: #fff5f5 !important;
        }
        
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-cards {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .alert-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .alert-details {
                align-items: flex-start;
                margin: 0;
            }
            
            .alert-actions {
                align-self: flex-end;
            }
        }
    </style>
</body>
</html>