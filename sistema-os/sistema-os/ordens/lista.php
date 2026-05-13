<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/helpers.php';

$database = new Database();
$db = $database->getConnection();

// Processar filtros
$filtro_status = $_GET['status'] ?? 'todos';
$filtro_cliente = $_GET['cliente'] ?? '';
$filtro_data_inicio = $_GET['data_inicio'] ?? '';
$filtro_data_fim = $_GET['data_fim'] ?? '';

// Construir query com filtros
$query = "SELECT os.*, c.nome_completo 
          FROM ordens_servico os 
          JOIN clientes c ON os.cliente_id = c.id 
          WHERE 1=1";

$params = [];

if ($filtro_status !== 'todos') {
    $query .= " AND os.status = :status";
    $params[':status'] = $filtro_status;
}

if (!empty($filtro_cliente)) {
    $query .= " AND (c.nome_completo LIKE :cliente OR os.numero_ordem LIKE :cliente)";
    $params[':cliente'] = "%{$filtro_cliente}%";
}

if (!empty($filtro_data_inicio)) {
    $query .= " AND DATE(os.data_entrada) >= :data_inicio";
    $params[':data_inicio'] = $filtro_data_inicio;
}

if (!empty($filtro_data_fim)) {
    $query .= " AND DATE(os.data_entrada) <= :data_fim";
    $params[':data_fim'] = $filtro_data_fim;
}

$query .= " ORDER BY os.data_entrada DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$ordens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas por status
$query_stats = "SELECT status, COUNT(*) as total FROM ordens_servico GROUP BY status";
$stmt_stats = $db->prepare($query_stats);
$stmt_stats->execute();
$stats = $stmt_stats->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Ordens de Serviço - <?php echo SISTEMA_NOME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main>
            <div class="page-header">
                <h1><i class="fas fa-clipboard-list"></i> Ordens de Serviço</h1>
                <a href="cadastro.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Nova Ordem
                </a>
            </div>
            
            <!-- Filtros -->
            <div class="filter-section">
                <h3><i class="fas fa-filter"></i> Buscar Ordens</h3>
                <form method="GET" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-item">
                            <label for="filtro_status">Status</label>
                            <select id="filtro_status" name="status" class="form-control">
                                <option value="todos" <?php echo $filtro_status === 'todos' ? 'selected' : ''; ?>>Todos os Status</option>
                                <option value="<?php echo STATUS_EM_ORCAMENTO; ?>" <?php echo $filtro_status === STATUS_EM_ORCAMENTO ? 'selected' : ''; ?>><?php echo STATUS_EM_ORCAMENTO; ?></option>
                                <option value="<?php echo STATUS_AGUARDANDO; ?>" <?php echo $filtro_status === STATUS_AGUARDANDO ? 'selected' : ''; ?>><?php echo STATUS_AGUARDANDO; ?></option>
                                <option value="<?php echo STATUS_APROVADO; ?>" <?php echo $filtro_status === STATUS_APROVADO ? 'selected' : ''; ?>><?php echo STATUS_APROVADO; ?></option>
                                <option value="<?php echo STATUS_ANDAMENTO; ?>" <?php echo $filtro_status === STATUS_ANDAMENTO ? 'selected' : ''; ?>><?php echo STATUS_ANDAMENTO; ?></option>
                                <option value="<?php echo STATUS_CONCLUIDA; ?>" <?php echo $filtro_status === STATUS_CONCLUIDA ? 'selected' : ''; ?>><?php echo STATUS_CONCLUIDA; ?></option>
                                <option value="<?php echo STATUS_ENTREGUE; ?>" <?php echo $filtro_status === STATUS_ENTREGUE ? 'selected' : ''; ?>><?php echo STATUS_ENTREGUE; ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-item">
                            <label for="filtro_cliente">Cliente/Número</label>
                            <input type="text" id="filtro_cliente" name="cliente" 
                                   value="<?php echo htmlspecialchars($filtro_cliente); ?>" 
                                   placeholder="Nome ou número da ordem" class="form-control">
                        </div>
                        
                        <div class="filter-item">
                            <label for="filtro_data_inicio">Data Início</label>
                            <input type="date" id="filtro_data_inicio" name="data_inicio" 
                                   value="<?php echo $filtro_data_inicio; ?>" class="form-control">
                        </div>
                        
                        <div class="filter-item">
                            <label for="filtro_data_fim">Data Fim</label>
                            <input type="date" id="filtro_data_fim" name="data_fim" 
                                   value="<?php echo $filtro_data_fim; ?>" class="form-control">
                        </div>
                        
                        <div class="filter-item">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="lista.php" class="btn">
                                <i class="fas fa-redo"></i> Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Estatísticas rápidas -->
            <div class="quick-stats">
                <h3>Resumo por Status</h3>
                <div class="stats-tags">
                    <?php foreach ($stats as $stat): ?>
                        <span class="stat-tag">
                            <span class="status-badge <?php echo classeStatus($stat['status']); ?>">
                                <i class="<?php echo iconeStatus($stat['status']); ?>"></i>
                                <?php echo $stat['status']; ?>
                            </span>
                            <span class="stat-count"><?php echo $stat['total']; ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Tabela de ordens -->
            <div class="table-responsive">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Aparelho</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th>Data Entrada</th>
                            <th>Prazo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ordens): ?>
                            <?php foreach ($ordens as $ordem): ?>
                                <tr class="<?php echo estaAtrasado($ordem['prazo_entrega'], $ordem['status']) ? 'atrasado' : ''; ?>">
                                    <td><strong><?php echo $ordem['numero_ordem']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($ordem['nome_completo']); ?></td>
                                    <td><?php echo htmlspecialchars($ordem['marca_aparelho'] . ' ' . $ordem['modelo_aparelho']); ?></td>
                                    <td><?php echo exibirValor($ordem['valor_total']); ?></td>
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
                                        <a href="visualizar.php?id=<?php echo $ordem['id']; ?>" class="btn-action" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="editar.php?id=<?php echo $ordem['id']; ?>" class="btn-action" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="imprimir.php?id=<?php echo $ordem['id']; ?>" target="_blank" class="btn-action" title="Imprimir">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <?php if ($ordem['status'] === STATUS_CONCLUIDA): ?>
                                            <a href="processa_ordem.php?action=marcar_entregue&id=<?php echo $ordem['id']; ?>" 
                                               class="btn-action btn-success" title="Marcar como Entregue"
                                               onclick="return confirm('Marcar ordem como entregue?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="no-data">
                                    <i class="fas fa-inbox"></i>
                                    <p>Nenhuma ordem encontrada com os filtros selecionados.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Exportar dados -->
            <div class="export-section">
                <h3>Exportar Dados</h3>
                <div class="export-options">
                    <a href="exportar.php?tipo=pdf&status=<?php echo urlencode($filtro_status); ?>&cliente=<?php echo urlencode($filtro_cliente); ?>" class="btn" target="_blank">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                    <a href="exportar.php?tipo=excel&status=<?php echo urlencode($filtro_status); ?>&cliente=<?php echo urlencode($filtro_cliente); ?>" class="btn">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
    <script>
        // Auto-submit para ordenação
        document.getElementById('filtro_status').addEventListener('change', function() {
            document.querySelector('.filter-form').submit();
        });
        
        // Busca em tempo real (opcional)
        let buscaTimeout;
        document.getElementById('filtro_cliente').addEventListener('input', function() {
            clearTimeout(buscaTimeout);
            buscaTimeout = setTimeout(() => {
                document.querySelector('.filter-form').submit();
            }, 500);
        });
    </script>
    
    <style>
        tr.atrasado {
            background-color: #fff5f5 !important;
            border-left: 3px solid #f56565;
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
        
        .text-muted {
            color: #a0aec0;
            font-style: italic;
        }
        
        .status-badge i {
            margin-right: 5px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px !important;
            color: #718096;
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            color: #cbd5e0;
        }
        
        .stats-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .stat-tag {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f7fafc;
            padding: 8px 12px;
            border-radius: 5px;
        }
        
        .stat-count {
            background: #4299e1;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
        }
    </style>
</body>
</html>