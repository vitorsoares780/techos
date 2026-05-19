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

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar dados
        $required = ['nome_completo', 'cpf_rg', 'telefone'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("O campo {$field} é obrigatório.");
            }
        }
        
        // Verificar se CPF/RG já existe (exceto para o próprio cliente)
        $query_check = "SELECT id FROM clientes WHERE cpf_rg = :cpf_rg AND id != :id";
        $stmt_check = $db->prepare($query_check);
        $stmt_check->bindParam(':cpf_rg', $_POST['cpf_rg']);
        $stmt_check->bindParam(':id', $id);
        $stmt_check->execute();
        
        if ($stmt_check->rowCount() > 0) {
            throw new Exception("Este CPF/RG já está cadastrado para outro cliente.");
        }
        
        // Atualizar cliente
        $query_update = "UPDATE clientes SET 
                        nome_completo = :nome_completo,
                        cpf_rg = :cpf_rg,
                        telefone = :telefone,
                        email = :email,
                        endereco = :endereco
                        WHERE id = :id";
        
        $stmt_update = $db->prepare($query_update);
        $stmt_update->bindParam(':nome_completo', $_POST['nome_completo']);
        $stmt_update->bindParam(':cpf_rg', $_POST['cpf_rg']);
        $stmt_update->bindParam(':telefone', $_POST['telefone']);
        $stmt_update->bindParam(':email', $_POST['email']);
        $stmt_update->bindParam(':endereco', $_POST['endereco']);
        $stmt_update->bindParam(':id', $id);
        
        if ($stmt_update->execute()) {
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Cliente atualizado com sucesso!'
            ];
            header('Location: visualizar.php?id=' . $id);
            exit;
        } else {
            throw new Exception('Erro ao atualizar cliente.');
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
    <title>Editar Cliente: <?php echo htmlspecialchars($cliente['nome_completo']); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="form-container">
            <h1><i class="fas fa-edit"></i> Editar Cliente</h1>
            
            <div class="client-header-info">
                <p><strong>ID:</strong> #<?php echo str_pad($cliente['id'], 4, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Cadastrado em:</strong> <?php echo date('d/m/Y H:i', strtotime($cliente['data_cadastro'])); ?></p>
            </div>
            
            <form id="form_cliente" method="POST">
                <div class="form-section">
                    <h2>Dados Pessoais</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome_completo">Nome Completo *</label>
                            <input type="text" id="nome_completo" name="nome_completo" 
                                   value="<?php echo htmlspecialchars($cliente['nome_completo']); ?>" 
                                   required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="cpf_rg">CPF ou RG *</label>
                            <input type="text" id="cpf_rg" name="cpf_rg" 
                                   value="<?php echo htmlspecialchars($cliente['cpf_rg']); ?>" 
                                   required class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Contato</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefone">Telefone/WhatsApp *</label>
                            <input type="text" id="telefone" name="telefone" 
                                   value="<?php echo htmlspecialchars($cliente['telefone']); ?>" 
                                   required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-mail (opcional)</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($cliente['email']); ?>" 
                                   class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Endereço</h2>
                    <div class="form-group">
                        <label for="endereco">Endereço Completo (opcional)</label>
                        <textarea id="endereco" name="endereco" rows="3" 
                                  class="form-control"><?php echo htmlspecialchars($cliente['endereco']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Atualizar Cliente
                    </button>
                    <a href="visualizar.php?id=<?php echo $id; ?>" class="btn">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    
                    <!-- Verificar se cliente tem ordens antes de permitir exclusão -->
                    <?php
                    $query_check_ordens = "SELECT COUNT(*) as total FROM ordens_servico WHERE cliente_id = :cliente_id";
                    $stmt_check_ordens = $db->prepare($query_check_ordens);
                    $stmt_check_ordens->bindParam(':cliente_id', $id);
                    $stmt_check_ordens->execute();
                    $ordens_cliente = $stmt_check_ordens->fetch(PDO::FETCH_ASSOC);
                    
                    if ($ordens_cliente['total'] == 0):
                    ?>
                        <button type="button" class="btn btn-danger" onclick="confirmarExclusao()">
                            <i class="fas fa-trash"></i> Excluir Cliente
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-danger" disabled title="Cliente possui ordens de serviço">
                            <i class="fas fa-trash"></i> Excluir Cliente
                        </button>
                        <small class="text-muted">Não é possível excluir cliente com ordens de serviço</small>
                    <?php endif; ?>
                </div>
            </form>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
    <script>
        // Aplicar máscaras
        document.addEventListener('DOMContentLoaded', function() {
            // Máscara para CPF/RG
            const cpfInput = document.getElementById('cpf_rg');
            cpfInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length <= 11) {
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                } else {
                    value = value.substring(0, 14); // Limitar para RG
                }
                
                e.target.value = value;
            });
            
            // Máscara para telefone
            const telInput = document.getElementById('telefone');
            telInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length <= 10) {
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                }
                
                e.target.value = value;
            });
        });
        
        function confirmarExclusao() {
            if (confirm('ATENÇÃO: Tem certeza que deseja excluir este cliente?\nEsta ação não pode ser desfeita!')) {
                window.location.href = 'processa_cliente.php?action=excluir&id=<?php echo $id; ?>';
            }
        }
        
        // Validação do formulário
        document.getElementById('form_cliente').addEventListener('submit', function(e) {
            if (!validarFormulario('form_cliente')) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
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
        }
        
        .client-header-info p {
            margin: 0;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .text-muted {
            color: #a0aec0;
            font-size: 0.9rem;
            margin-left: 10px;
        }
    </style>
</body>
</html>