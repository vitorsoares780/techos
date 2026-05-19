<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/helpers.php';

$database = new Database();
$db = $database->getConnection();

// Gerar número da ordem
$numero_ordem = gerarNumeroOrdem($db);

// Buscar clientes para o select
$query_clientes = "SELECT id, nome_completo FROM clientes ORDER BY nome_completo";
$stmt_clientes = $db->prepare($query_clientes);
$stmt_clientes->execute();
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// Cliente específico se passado por GET
$cliente_id = $_GET['cliente_id'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Ordem de Serviço</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="form-container">
            <h1><i class="fas fa-plus-circle"></i> Nova Ordem de Serviço</h1>
            
            <form id="form_ordem" action="processa_ordem.php" method="POST">
                <input type="hidden" name="action" value="cadastrar">
                
                <div class="form-section">
                    <h2>Informações da Ordem</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="numero_ordem">Número da Ordem *</label>
                            <input type="text" id="numero_ordem" name="numero_ordem" 
                                   value="<?php echo $numero_ordem; ?>" readonly required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="data_entrada">Data de Entrada *</label>
                            <input type="datetime-local" id="data_entrada" name="data_entrada" 
                                   value="<?php echo dataAtualInputDateTime(); ?>" required class="form-control">
                            <small class="form-text">Data e hora atual do sistema: <?php echo dataAtual(FORMATO_DATA_HORA); ?></small>
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
                                <option value="<?php echo $cliente['id']; ?>" 
                                    <?php echo ($cliente['id'] == $cliente_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cliente['nome_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-actions-inline">
                            <a href="../clientes/cadastro.php?redirect=ordem" class="btn btn-sm btn-secondary">
                                <i class="fas fa-user-plus"></i> Cadastrar Novo Cliente
                            </a>
                            <button type="button" class="btn btn-sm" onclick="document.getElementById('cliente_id').value = '';">
                                <i class="fas fa-times"></i> Limpar seleção
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Dados do Aparelho</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="marca_aparelho">Marca *</label>
                            <input type="text" id="marca_aparelho" name="marca_aparelho" 
                                   placeholder="Ex: Samsung, Apple, Xiaomi..." required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="modelo_aparelho">Modelo *</label>
                            <input type="text" id="modelo_aparelho" name="modelo_aparelho" 
                                   placeholder="Ex: Galaxy S20, iPhone 12..." required class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="imei">IMEI (opcional)</label>
                            <input type="text" id="imei" name="imei" placeholder="15 dígitos" class="form-control"
                                   pattern="[0-9]{15}" title="Digite 15 números">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="estado_fisico">Estado Físico do Aparelho</label>
                        <textarea id="estado_fisico" name="estado_fisico" rows="3" 
                                  placeholder="Descreva o estado físico: riscos, tela trincada, marcas de uso, etc." 
                                  class="form-control"></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Descrição do Serviço</h2>
                    <div class="form-group">
                        <label for="defeito_relatado">Defeito Relatado pelo Cliente *</label>
                        <textarea id="defeito_relatado" name="defeito_relatado" rows="4" 
                                  placeholder="Descreva o problema relatado pelo cliente..." 
                                  required class="form-control"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="servico_realizar">Serviço a Ser Realizado *</label>
                        <textarea id="servico_realizar" name="servico_realizar" rows="4" 
                                  placeholder="Descreva o serviço que será realizado..." 
                                  required class="form-control"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="pecas_utilizadas">Peças Utilizadas (opcional)</label>
                        <textarea id="pecas_utilizadas" name="pecas_utilizadas" rows="3" 
                                  placeholder="Liste as peças que serão utilizadas..." 
                                  class="form-control"></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Valores e Prazo</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="valor_servico">Valor do Serviço (R$) *</label>
                            <input type="text" id="valor_servico" name="valor_servico" 
                                   placeholder="0,00" required class="form-control money-input">
                        </div>
                        
                        <div class="form-group">
                            <label for="valor_pecas">Valor das Peças (R$)</label>
                            <input type="text" id="valor_pecas" name="valor_pecas" 
                                   placeholder="0,00" class="form-control money-input">
                        </div>
                        
                        <div class="form-group">
                            <label for="valor_total">Valor Total (R$)</label>
                            <input type="text" id="valor_total" name="valor_total" 
                                   placeholder="0,00" readonly class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="forma_pagamento">Forma de Pagamento</label>
                            <select id="forma_pagamento" name="forma_pagamento" class="form-control">
                                <option value="">Selecione...</option>
                                <?php foreach ($FORMAS_PAGAMENTO as $forma): ?>
                                    <option value="<?php echo $forma; ?>"><?php echo $forma; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="prazo_entrega">Prazo de Entrega</label>
                            <input type="date" id="prazo_entrega" name="prazo_entrega" 
                                   min="<?php echo date('Y-m-d'); ?>" class="form-control">
                            <small class="form-text">Mínimo: <?php echo date('d/m/Y'); ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Status e Observações</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status da Ordem *</label>
                            <select id="status" name="status" required class="form-control">
                                <?php foreach ($STATUS_OPCOES as $status): ?>
                                    <option value="<?php echo $status; ?>" 
                                        <?php echo ($status === STATUS_EM_ORCAMENTO) ? 'selected' : ''; ?>>
                                        <?php echo $status; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacoes_tecnicas">Observações Técnicas</label>
                        <textarea id="observacoes_tecnicas" name="observacoes_tecnicas" rows="4" 
                                  placeholder="Observações adicionais do técnico..." 
                                  class="form-control"></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Ordem
                    </button>
                    <button type="button" class="btn btn-success" onclick="salvarEAvancar()">
                        <i class="fas fa-forward"></i> Salvar e Avançar
                    </button>
                    <a href="../index.php" class="btn">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
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
            if (input.value) {
                let value = input.value.replace(/\D/g, '');
                value = (parseInt(value) || 0) / 100;
                input.value = value.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
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
        
        // Calcular total ao carregar a página
        document.addEventListener('DOMContentLoaded', calcularTotal);
        
        // Máscara para IMEI
        document.getElementById('imei').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 15);
        });
        
        // Salvar e avançar para impressão
        function salvarEAvancar() {
            const form = document.getElementById('form_ordem');
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'avancar';
            hiddenInput.value = 'imprimir';
            form.appendChild(hiddenInput);
            form.submit();
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
        
        // Auto-focus no primeiro campo
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('cliente_id').focus();
        });
    </script>
</body>
</html>