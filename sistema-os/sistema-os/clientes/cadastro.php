<?php
session_start();
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar dados
        $required = ['nome_completo', 'cpf_rg', 'telefone'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("O campo {$field} é obrigatório.");
            }
        }
        
        // Verificar se CPF/RG já existe
        $query_check = "SELECT id FROM clientes WHERE cpf_rg = :cpf_rg";
        $stmt_check = $db->prepare($query_check);
        $stmt_check->bindParam(':cpf_rg', $_POST['cpf_rg']);
        $stmt_check->execute();
        
        if ($stmt_check->rowCount() > 0) {
            throw new Exception("Este CPF/RG já está cadastrado no sistema.");
        }
        
        // Inserir cliente
        $query = "INSERT INTO clientes (nome_completo, cpf_rg, telefone, email, endereco) 
                  VALUES (:nome_completo, :cpf_rg, :telefone, :email, :endereco)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nome_completo', $_POST['nome_completo']);
        $stmt->bindParam(':cpf_rg', $_POST['cpf_rg']);
        $stmt->bindParam(':telefone', $_POST['telefone']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':endereco', $_POST['endereco']);
        
        if ($stmt->execute()) {
            $cliente_id = $db->lastInsertId();
            
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Cliente cadastrado com sucesso!'
            ];
            
            // Redirecionar para nova ordem ou lista
            if (isset($_GET['redirect']) && $_GET['redirect'] === 'ordem') {
                header('Location: ../ordens/cadastro.php?cliente_id=' . $cliente_id);
            } else {
                header('Location: visualizar.php?id=' . $cliente_id);
            }
            exit;
        } else {
            throw new Exception('Erro ao cadastrar cliente.');
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
    <title>Cadastrar Cliente</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="form-container">
            <h1><i class="fas fa-user-plus"></i> Cadastrar Cliente</h1>
            
            <form id="form_cliente" method="POST">
                <div class="form-section">
                    <h2>Dados Pessoais</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome_completo">Nome Completo *</label>
                            <input type="text" id="nome_completo" name="nome_completo" 
                                   placeholder="Digite o nome completo" required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="cpf_rg">CPF ou RG *</label>
                            <input type="text" id="cpf_rg" name="cpf_rg" 
                                   placeholder="Digite CPF ou RG" required class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Contato</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefone">Telefone/WhatsApp *</label>
                            <input type="text" id="telefone" name="telefone" 
                                   placeholder="(11) 99999-9999" required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-mail (opcional)</label>
                            <input type="email" id="email" name="email" 
                                   placeholder="cliente@email.com" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Endereço</h2>
                    <div class="form-group">
                        <label for="endereco">Endereço Completo (opcional)</label>
                        <textarea id="endereco" name="endereco" rows="3" 
                                  placeholder="Rua, Número, Bairro, Cidade - Estado" 
                                  class="form-control"></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cadastrar Cliente
                    </button>
                    <?php if (isset($_GET['redirect']) && $_GET['redirect'] === 'ordem'): ?>
                        <a href="../ordens/cadastro.php" class="btn">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <small class="form-hint">
                            <i class="fas fa-info-circle"></i> Após cadastrar, você será redirecionado para criar uma nova ordem para este cliente.
                        </small>
                    <?php else: ?>
                        <a href="lista.php" class="btn">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
            
            <!-- Cliente rápido (para criação durante nova ordem) -->
            <?php if (isset($_GET['quick']) && $_GET['quick'] === 'true'): ?>
            <div class="quick-info">
                <h3><i class="fas fa-bolt"></i> Cadastro Rápido</h3>
                <p>Preencha apenas os campos essenciais para criar uma ordem rapidamente.</p>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.getElementById('nome_completo').focus();
                    });
                </script>
            </div>
            <?php endif; ?>
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
        
        // Validação do formulário
        document.getElementById('form_cliente').addEventListener('submit', function(e) {
            if (!validarFormulario('form_cliente')) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
        });
    </script>
</body>
</html>