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
    die('Ordem não encontrada.');
}

// Buscar configurações do prestador
$query_config = "SELECT * FROM configuracao LIMIT 1";
$stmt_config = $db->prepare($query_config);
$stmt_config->execute();
$config = $stmt_config->fetch(PDO::FETCH_ASSOC);

// Nome fixo para VS Tech
$nome_prestador = 'VS Tech (Vitor Soares)';
$telefone_prestador = $config['telefone_prestador'] ?? '(11) 99999-9999';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço #<?php echo $ordem['numero_ordem']; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/print.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ESTILOS COMPACTOS COM MARGENS SEGURAS */
        @page {
            size: A4;
            margin: 15mm;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: white;
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.1;
        }
        
        .print-container {
            width: 180mm; /* 210mm - 30mm de margens (15mm cada lado) */
            min-height: 267mm; /* 297mm - 30mm de margens */
            margin: 0 auto;
            padding: 0;
            background: white;
            box-sizing: border-box;
        }
        
        .print-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5mm;
            padding-bottom: 3mm;
            border-bottom: 1pt solid #000;
        }
        
        .print-logo h2 {
            font-size: 16pt;
            margin: 0 0 1mm 0;
            color: #000;
            line-height: 1;
        }
        
        .print-logo p {
            margin: 0;
            color: #666;
            font-size: 9pt;
        }
        
        .print-info {
            text-align: right;
            font-size: 8pt;
        }
        
        .print-info p {
            margin: 0.5mm 0;
        }
        
        /* Seções compactas com margens seguras */
        .print-section {
            margin-bottom: 4mm;
            page-break-inside: avoid;
        }
        
        .print-section h3 {
            background-color: #f0f0f0;
            padding: 2mm 3mm;
            margin: 0 0 2mm 0;
            font-size: 9pt;
            border-left: 1.5mm solid #000;
        }
        
        .form-row {
            display: flex;
            margin-bottom: 1.5mm;
        }
        
        .form-group {
            flex: 1;
            margin-right: 4mm;
            font-size: 8.5pt;
        }
        
        .form-group:last-child {
            margin-right: 0;
        }
        
        .form-group p {
            margin: 0.8mm 0;
        }
        
        .print-content-box {
            background-color: #f9f9f9;
            padding: 2mm;
            border: 0.5pt solid #ddd;
            border-radius: 1mm;
            margin: 1mm 0 2.5mm 0;
            font-size: 8.5pt;
            line-height: 1.2;
            min-height: 8mm;
            max-height: 15mm;
            overflow: hidden;
        }
        
        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2mm 0;
            font-size: 8.5pt;
        }
        
        .print-table td {
            padding: 1.5mm;
            border-bottom: 0.5pt solid #ddd;
        }
        
        .values-table {
            width: 50%;
            margin-left: auto;
        }
        
        .total-row td {
            font-weight: bold;
            font-size: 9.5pt;
            border-top: 1pt solid #000;
            border-bottom: none;
        }
        
        /* Termo jurídico compacto */
        .legal-term {
            font-size: 7.5pt;
            line-height: 1.2;
            margin: 3mm 0;
            padding: 3mm;
            border: 0.5pt solid #000;
            background-color: #f9f9f9;
            text-align: justify;
            max-height: 25mm;
            overflow: hidden;
        }
        
        .legal-term h3 {
            font-size: 8.5pt;
            margin: 0 0 1.5mm 0;
            padding: 0;
            background: none;
            border: none;
        }
        
        /* Assinaturas compactas */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 4mm;
            page-break-inside: avoid;
        }
        
        .signature-block {
            width: 48%;
            text-align: center;
        }
        
        .signature-block h3 {
            font-size: 8.5pt;
            margin: 0 0 2mm 0;
            background: none;
            border: none;
            padding: 0;
        }
        
        .signature-line {
            width: 100%;
            height: 0.5pt;
            border-top: 0.5pt solid #000;
            margin: 8mm auto 1mm;
            position: relative;
        }
        
        .signature-line p {
            position: absolute;
            top: -5mm;
            left: 0;
            right: 0;
            font-size: 7.5pt;
            background: white;
            display: inline-block;
            padding: 0 2mm;
        }
        
        .signature-info {
            font-size: 7.5pt;
            margin-top: 1mm;
        }
        
        .signature-info p {
            margin: 0.5mm 0;
        }
        
        /* Rodapé compacto */
        .print-footer {
            margin-top: 4mm;
            padding-top: 2mm;
            border-top: 0.5pt solid #ddd;
            text-align: center;
            font-size: 7.5pt;
            color: #666;
        }
        
        .print-small {
            font-size: 6.5pt !important;
            color: #999 !important;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
        
        /* Controles não impressos */
        .no-print {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f0f0f0;
            padding: 4mm;
            text-align: center;
            border-top: 1px solid #ddd;
            z-index: 1000;
        }
        
        .btn {
            padding: 2mm 4mm;
            margin: 0 2mm;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 1.5mm;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 9pt;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-primary {
            background: #28a745;
        }
        
        .btn-primary:hover {
            background: #1e7e34;
        }
        
        /* Impressão */
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0 !important;
                padding: 0 !important;
                font-size: 9pt !important;
            }
            
            .print-container {
                width: 180mm !important;
                min-height: 267mm !important;
                padding: 0 !important;
                margin: 0 auto !important;
            }
            
            /* Forçar uma página apenas */
            .print-section {
                page-break-inside: avoid;
                page-break-after: avoid;
            }
            
            .signature-section {
                page-break-before: avoid;
            }
            
            /* Aumentar levemente o conteúdo se couber */
            @page {
                margin: 12mm;
            }
            
            body.printing {
                font-size: 9.5pt !important;
            }
        }
        
        /* Ajuste específico para visualização */
        @media screen {
            body {
                background: #f5f5f5;
                padding: 10mm;
            }
            
            .print-container {
                background: white;
                padding: 10mm;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                border-radius: 2mm;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Cabeçalho para impressão -->
        <div class="print-header">
            <div class="print-logo">
                <h2>ORDEM DE SERVIÇO</h2>
                <p>Manutenção de Celulares</p>
            </div>
            <div class="print-info">
                <p><strong>Número:</strong> <?php echo $ordem['numero_ordem']; ?></p>
                <p><strong>Data Entrada:</strong> <?php echo exibirData($ordem['data_entrada']); ?></p>
            </div>
        </div>
        
        <!-- Identificação do Prestador -->
        <div class="print-section no-break">
            <h3><i class="fas fa-user-tie"></i> IDENTIFICAÇÃO DO PRESTADOR</h3>
            <div class="form-row">
                <div class="form-group">
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($nome_prestador); ?></p>
                </div>
                <div class="form-group">
                    <p><strong>Telefone:</strong> <?php echo $telefone_prestador; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Dados do Cliente -->
        <div class="print-section no-break">
            <h3><i class="fas fa-user"></i> DADOS DO CLIENTE</h3>
            <div class="form-row">
                <div class="form-group">
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($ordem['nome_completo']); ?></p>
                </div>
                <div class="form-group">
                    <p><strong>CPF/RG:</strong> <?php echo formatarDocumento($ordem['cpf_rg']); ?></p>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <p><strong>Telefone:</strong> <?php echo formatarTelefone($ordem['telefone']); ?></p>
                </div>
                <div class="form-group">
                    <p><strong>E-mail:</strong> <?php echo $ordem['email'] ?: 'Não informado'; ?></p>
                </div>
            </div>
            <div class="form-group">
                <p><strong>Endereço:</strong> <?php echo $ordem['endereco'] ? htmlspecialchars($ordem['endereco']) : 'Não informado'; ?></p>
            </div>
        </div>
        
        <!-- Dados do Aparelho -->
        <div class="print-section no-break">
            <h3><i class="fas fa-mobile-alt"></i> DADOS DO APARELHO</h3>
            <div class="form-row">
                <div class="form-group">
                    <p><strong>Marca:</strong> <?php echo htmlspecialchars($ordem['marca_aparelho']); ?></p>
                </div>
                <div class="form-group">
                    <p><strong>Modelo:</strong> <?php echo htmlspecialchars($ordem['modelo_aparelho']); ?></p>
                </div>
            </div>
            <div class="form-group">
                <p><strong>Estado Físico:</strong></p>
                <div class="print-content-box">
                    <?php echo nl2br(htmlspecialchars($ordem['estado_fisico'] ?: 'Não especificado')); ?>
                </div>
            </div>
        </div>
        
        <!-- Descrição do Serviço -->
        <div class="print-section no-break">
            <h3><i class="fas fa-tools"></i> DESCRIÇÃO DO SERVIÇO</h3>
            <div class="form-group">
                <p><strong>Defeito Relatado:</strong></p>
                <div class="print-content-box">
                    <?php echo nl2br(htmlspecialchars($ordem['defeito_relatado'])); ?>
                </div>
            </div>
            <div class="form-group">
                <p><strong>Serviço a Ser Realizado:</strong></p>
                <div class="print-content-box">
                    <?php echo nl2br(htmlspecialchars($ordem['servico_realizar'])); ?>
                </div>
            </div>
            <?php if ($ordem['observacoes_tecnicas']): ?>
            <div class="form-group">
                <p><strong>Observações Técnicas:</strong></p>
                <div class="print-content-box">
                    <?php echo nl2br(htmlspecialchars($ordem['observacoes_tecnicas'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Valores -->
        <div class="print-section no-break">
            <h3><i class="fas fa-dollar-sign"></i> VALORES</h3>
            <table class="print-table values-table">
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
                    <td><strong>VALOR TOTAL:</strong></td>
                    <td><strong><?php echo exibirValor($ordem['valor_total']); ?></strong></td>
                </tr>
            </table>
        </div>
        
        <!-- Termo Jurídico -->
        <div class="print-section legal-term no-break">
            <h3>TERMO DE RESPONSABILIDADE</h3>
            <p>
                Declaro estar ciente das condições do serviço. Não me responsabilizo por perda de dados, 
                informações ou aplicativos não previamente salvos. O serviço somente será executado após 
                aprovação do orçamento. A garantia refere-se exclusivamente ao serviço realizado ou peças 
                substituídas, não cobrindo mau uso, quedas, contato com líquidos ou intervenções de terceiros. 
                Aparelhos não retirados após 90 dias da conclusão poderão ser descartados.
            </p>
        </div>
        
        <!-- Assinaturas -->
        <div class="print-section signature-section no-break">
            <div class="signature-block">
                <h3>ENTREGA DO APARELHO</h3>
                <div class="signature-line">
                    <p>Assinatura do Cliente</p>
                </div>
                <div class="signature-info">
                    <p>Data: ____/____/________</p>
                    <p>Hora: _______:_______</p>
                </div>
            </div>
            
            <div class="signature-block">
                <h3>RETIRADA DO APARELHO</h3>
                <div class="signature-line">
                    <p>Assinatura do Cliente</p>
                </div>
                <div class="signature-info">
                    <p>Data: ____/____/________</p>
                    <p>Hora: _______:_______</p>
                </div>
            </div>
        </div>
        
        <!-- Rodapé com identificação da via -->
        <div class="print-footer">
            <p>VS Tech (Vitor Soares) - <?php echo $telefone_prestador; ?></p>
            <p class="print-small">Documento válido para acompanhamento e garantia do serviço</p>
        </div>
    </div>
    
    <!-- Botão de impressão (visível apenas na tela) -->
    <div class="no-print">
        <button onclick="printDocument()" class="btn btn-primary">
            <i class="fas fa-print"></i> Imprimir 2 Cópias
        </button>
        <a href="visualizar.php?id=<?php echo $id; ?>" class="btn">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <button onclick="window.close()" class="btn">
            <i class="fas fa-times"></i> Fechar
        </button>
        <p style="font-size: 8.5pt; margin-top: 3mm; color: #666;">
            <strong>Dica:</strong> Na janela de impressão, configure "Cópias: 2" e verifique as margens.
        </p>
    </div>
    
    <script>
        // Função de impressão otimizada
        function printDocument() {
            // Adicionar classe para impressão
            document.body.classList.add('printing');
            
            // Configurar timeout para garantir que o CSS seja aplicado
            setTimeout(function() {
                window.print();
                // Remover classe após impressão
                setTimeout(function() {
                    document.body.classList.remove('printing');
                }, 100);
            }, 50);
        }
        
        // Impressão automática ao carregar a página (opcional)
        window.onload = function() {
            // Descomente para impressão automática
            // setTimeout(printDocument, 500);
        }
        
        // Configurar antes de imprimir
        window.onbeforeprint = function() {
            document.querySelector('.no-print').style.display = 'none';
            document.body.classList.add('printing');
        }
        
        // Restaurar após imprimir
        window.onafterprint = function() {
            document.querySelector('.no-print').style.display = 'block';
            document.body.classList.remove('printing');
        }
    </script>
</body>
</html>