<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/helpers.php';

$database = new Database();
$db = $database->getConnection();

// Processar filtros
$busca = $_GET['busca'] ?? '';
$ordenar_por = $_GET['ordenar_por'] ?? 'nome_completo';
$direcao = $_GET['direcao'] ?? 'ASC';

// Construir query com filtros
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM ordens_servico WHERE cliente_id = c.id) as total_ordens,
          (SELECT COUNT(*) FROM ordens_servico WHERE cliente_id = c.id AND status = 'Concluída') as ordens_concluidas
          FROM clientes c 
          WHERE 1=1";

$params = [];

if (!empty($busca)) {
    $query .= " AND (c.nome_completo LIKE :busca OR c.cpf_rg LIKE :busca OR c.telefone LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}

// Ordenação segura
$colunas_validas = ['nome_completo', 'data_cadastro', 'total_ordens'];
$ordenar_por = in_array($ordenar_por, $colunas_validas) ? $ordenar_por : 'nome_completo';
$direcao = strtoupper($direcao) === 'DESC' ? 'DESC' : 'ASC';

$query .= " ORDER BY {$ordenar_por} {$direcao}";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$query_stats = "SELECT 
                COUNT(*) as total_clientes,
                COUNT(DISTINCT os.cliente_id) as clientes_com_ordens
                FROM clientes c
                LEFT JOIN ordens_servico os ON c.id = os.cliente_id";
$stmt_stats = $db->prepare($query_stats);
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Clientes - <?php echo SISTEMA_NOME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main>
            <div class="page-header">
                <h1><i class="fas fa-users"></i> Clientes</h1>
                <div class="header-actions">
                    <a href="cadastro.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Novo Cliente
                    </a>
                    <a href="cadastro.php?quick=true" class="btn btn-secondary">
                        <i class="fas fa-bolt"></i> Cadastro Rápido
                    </a>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="filter-section">
                <h3><i class="fas fa-filter"></i> Buscar Clientes</h3>
                <form method="GET" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-item" style="flex: 2;">
                            <label for="busca">Nome, CPF/RG ou Telefone</label>
                            <input type="text" id="busca" name="busca" 
                                   value="<?php echo htmlspecialchars($busca); ?>" 
                                   placeholder="Digite para buscar..." class="form-control">
                        </div>
                        
                        <div class="filter-item">
                            <label for="ordenar_por">Ordenar por</label>
                            <select id="ordenar_por" name="ordenar_por" class="form-control">
                                <option value="nome_completo" <?php echo $ordenar_por == 'nome_completo' ? 'selected' : ''; ?>>Nome</option>
                                <option value="data_cadastro" <?php echo $ordenar_por == 'data_cadastro' ? 'selected' : ''; ?>>Data Cadastro</option>
                                <option value="total_ordens" <?php echo $ordenar_por == 'total_ordens' ? 'selected' : ''; ?>>Total de Ordens</option>
                            </select>
                        </div>
                        
                        <div class="filter-item">
                            <label for="direcao">Direção</label>
                            <select id="direcao" name="direcao" class="form-control">
                                <option value="ASC" <?php echo $direcao == 'ASC' ? 'selected' : ''; ?>>Crescente</option>
                                <option value="DESC" <?php echo $direcao == 'DESC' ? 'selected' : ''; ?>>Decrescente</option>
                            </select>
                        </div>
                        
                        <div class="filter-item">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <a href="lista.php" class="btn">
                                <i class="fas fa-redo"></i> Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Estatísticas -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total de Clientes</h3>
                        <span class="stat-number"><?php echo $stats['total_clientes'] ?? 0; ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Clientes com Ordens</h3>
                        <span class="stat-number"><?php echo $stats['clientes_com_ordens'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Tabela de clientes -->
            <div class="table-responsive">
                <table class="clients-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CPF/RG</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Total Ordens</th>
                            <th>Cadastrado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($clientes): ?>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($cliente['nome_completo']); ?></strong>
                                    </td>
                                    <td><?php echo formatarDocumento($cliente['cpf_rg']); ?></td>
                                    <td>
                                        <a href="<?php echo linkWhatsApp($cliente['telefone']); ?>" 
                                           target="_blank" class="whatsapp-link" title="Enviar WhatsApp">
                                            <i class="fab fa-whatsapp"></i> <?php echo formatarTelefone($cliente['telefone']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($cliente['email']): ?>
                                            <a href="<?php echo linkEmail($cliente['email']); ?>" class="email-link">
                                                <?php echo $cliente['email']; ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $cliente['total_ordens'] > 0 ? 'badge-info' : 'badge-secondary'; ?>">
                                            <?php echo $cliente['total_ordens']; ?> ordem(ns)
                                        </span>
                                        <?php if ($cliente['total_ordens'] > 0): ?>
                                            <small class="text-success">
                                                <?php echo $cliente['ordens_concluidas']; ?> concluída(s)
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo exibirDataSimples($cliente['data_cadastro']); ?></td>
                                    <td class="actions">
                                        <a href="visualizar.php?id=<?php echo $cliente['id']; ?>" class="btn-action" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="editar.php?id=<?php echo $cliente['id']; ?>" class="btn-action" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../ordens/cadastro.php?cliente_id=<?php echo $cliente['id']; ?>" class="btn-action btn-success" title="Nova Ordem">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>
                                        <?php if ($cliente['total_ordens'] == 0): ?>
                                            <a href="processa_cliente.php?action=excluir&id=<?php echo $cliente['id']; ?>" 
                                               class="btn-action btn-danger" title="Excluir"
                                               onclick="return confirmarExclusaoCliente()">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">
                                    <i class="fas fa-user-slash"></i>
                                    <p>Nenhum cliente encontrado.</p>
                                    <?php if (!empty($busca)): ?>
                                        <a href="lista.php" class="btn btn-sm">Limpar busca</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Exportar dados -->
            <div class="export-section">
                <h3>Exportar Lista de Clientes</h3>
                <div class="export-options">
                    <a href="exportar.php?tipo=csv&busca=<?php echo urlencode($busca); ?>" class="btn" target="_blank">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
    <script>
        // Auto-submit para ordenação
        document.getElementById('ordenar_por').addEventListener('change', function() {
            document.querySelector('.filter-form').submit();
        });
        
        document.getElementById('direcao').addEventListener('change', function() {
            document.querySelector('.filter-form').submit();
        });
        
        // Busca em tempo real (opcional)
        let buscaTimeout;
        document.getElementById('busca').addEventListener('input', function() {
            clearTimeout(buscaTimeout);
            buscaTimeout = setTimeout(() => {
                document.querySelector('.filter-form').submit();
            }, 500);
        });
        
        function confirmarExclusaoCliente() {
            return confirm('Tem certeza que deseja excluir este cliente?\nEsta ação não pode ser desfeita.');
        }
    </script>
    
    <style>
        .clients-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .clients-table thead {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
        }
        
        .clients-table th,
        .clients-table td {
            padding: 12px 15px;
            text-align: left;
            vertical-align: middle;
        }
        
        .clients-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }
        
        .clients-table tbody tr:hover {
            background-color: #f7fafc;
        }
        
        .whatsapp-link {
            color: #25D366;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
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
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-right: 5px;
        }
        
        .badge-info {
            background-color: #bee3f8;
            color: #2c5282;
        }
        
        .badge-secondary {
            background-color: #e2e8f0;
            color: #4a5568;
        }
        
        .text-success {
            color: #48bb78;
            font-size: 0.8rem;
            display: block;
            margin-top: 2px;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-success {
            background-color: #48bb78;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #38a169;
        }
        
        .btn-danger {
            background-color: #f56565;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #e53e3e;
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
        
        .text-muted {
            color: #a0aec0;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .header-actions {
                flex-direction: column;
                width: 100%;
                margin-top: 10px;
            }
            
            .header-actions a {
                width: 100%;
                text-align: center;
            }
            
            .filter-row {
                flex-direction: column;
            }
            
            .filter-item {
                width: 100%;
            }
            
            .clients-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</body>
</html>