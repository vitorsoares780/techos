<?php
// ============================================
// CONFIGURAÇÕES DO SISTEMA - ORDENS DE SERVIÇO
// ============================================

// Configurar fuso horário do Brasil (GMT-3)
date_default_timezone_set('America/Sao_Paulo');

// Informações do sistema
define('SISTEMA_NOME', 'Sistema de Ordens de Serviço');
define('SISTEMA_DESCRICAO', 'Gestão de manutenção de celulares');
define('SISTEMA_VERSAO', '1.0.0');
define('SISTEMA_ANO', date('Y'));

// Configurações de datas
define('FORMATO_DATA', 'd/m/Y');
define('FORMATO_HORA', 'H:i');
define('FORMATO_DATA_HORA', 'd/m/Y H:i');
define('FORMATO_DATA_HORA_COMPLETO', 'd/m/Y H:i:s');
define('FORMATO_DATA_BANCO', 'Y-m-d');
define('FORMATO_DATA_HORA_BANCO', 'Y-m-d H:i:s');
define('FORMATO_INPUT_DATE', 'Y-m-d');
define('FORMATO_INPUT_DATETIME', 'Y-m-d\TH:i');

// Status das ordens
define('STATUS_EM_ORCAMENTO', 'Em orçamento');
define('STATUS_AGUARDANDO', 'Aguardando resposta do cliente');
define('STATUS_APROVADO', 'Aprovado');
define('STATUS_ANDAMENTO', 'Em andamento');
define('STATUS_CONCLUIDA', 'Concluída');
define('STATUS_ENTREGUE', 'Entregue');

$STATUS_OPCOES = [
    STATUS_EM_ORCAMENTO,
    STATUS_AGUARDANDO,
    STATUS_APROVADO,
    STATUS_ANDAMENTO,
    STATUS_CONCLUIDA,
    STATUS_ENTREGUE
];

// Formas de pagamento
$FORMAS_PAGAMENTO = [
    'Dinheiro',
    'PIX',
    'Cartão Débito',
    'Cartão Crédito',
    'Transferência'
];

// ============================================
// FUNÇÕES ÚTEIS
// ============================================

/**
 * Retorna data e hora atual no formato especificado
 * @param string $formato Formato da data (padrão: Y-m-d H:i:s)
 * @return string Data formatada
 */
function dataAtual($formato = FORMATO_DATA_HORA_BANCO) {
    return date($formato);
}

/**
 * Formata uma data para exibição
 * @param string $data Data a ser formatada
 * @param string $formato_entrada Formato de entrada (padrão: Y-m-d H:i:s)
 * @param string $formato_saida Formato de saída (padrão: d/m/Y H:i)
 * @return string Data formatada ou string vazia se data inválida
 */
function formatarData($data, $formato_entrada = FORMATO_DATA_HORA_BANCO, $formato_saida = FORMATO_DATA_HORA) {
    if (empty($data) || $data === '0000-00-00 00:00:00' || $data === '0000-00-00') {
        return '';
    }
    
    try {
        $dateTime = DateTime::createFromFormat($formato_entrada, $data);
        if ($dateTime === false) {
            // Tenta formato alternativo
            $dateTime = new DateTime($data);
        }
        
        if ($dateTime) {
            return $dateTime->format($formato_saida);
        }
    } catch (Exception $e) {
        // Log do erro (em produção)
        error_log("Erro ao formatar data: " . $e->getMessage());
    }
    
    return $data; // Retorna original se não conseguir formatar
}

/**
 * Converte data do formato de exibição para banco de dados
 * @param string $data Data no formato dd/mm/yyyy
 * @return string Data no formato Y-m-d ou null se inválida
 */
function dataParaBanco($data) {
    if (empty($data)) {
        return null;
    }
    
    $dateTime = DateTime::createFromFormat('d/m/Y', $data);
    if ($dateTime) {
        return $dateTime->format('Y-m-d');
    }
    
    return null;
}

/**
 * Converte data/hora do formato de exibição para banco de dados
 * @param string $data Data no formato dd/mm/yyyy HH:ii
 * @return string Data no formato Y-m-d H:i:s ou null se inválida
 */
function dataHoraParaBanco($data) {
    if (empty($data)) {
        return null;
    }
    
    $dateTime = DateTime::createFromFormat('d/m/Y H:i', $data);
    if ($dateTime) {
        return $dateTime->format('Y-m-d H:i:s');
    }
    
    return null;
}

/**
 * Converte data/hora do input datetime-local para banco de dados
 * @param string $data Data no formato Y-m-d\TH:i
 * @return string Data no formato Y-m-d H:i:s ou null se inválida
 */
function inputParaBanco($data) {
    if (empty($data)) {
        return null;
    }
    
    $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $data);
    if ($dateTime) {
        return $dateTime->format('Y-m-d H:i:s');
    }
    
    return null;
}

/**
 * Gera número da ordem de serviço automático
 * @param PDO $db Conexão com banco de dados
 * @param string $prefixo Prefixo da ordem (padrão: OS)
 * @return string Número da ordem gerado
 */
function gerarNumeroOrdem($db, $prefixo = 'OS') {
    $ano = date('Y');
    $mes = date('m');
    
    try {
        $query = "SELECT COUNT(*) as total FROM ordens_servico 
                  WHERE YEAR(data_entrada) = :ano AND MONTH(data_entrada) = :mes";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':ano', $ano);
        $stmt->bindParam(':mes', $mes);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sequencia = str_pad(($result['total'] + 1), 4, '0', STR_PAD_LEFT);
        return $prefixo . $ano . $mes . $sequencia;
    } catch (Exception $e) {
        // Em caso de erro, gera baseado no timestamp
        return $prefixo . date('YmdHis');
    }
}

/**
 * Verifica se uma ordem está atrasada
 * @param string $prazo_entrega Data do prazo de entrega
 * @param string $status Status atual da ordem
 * @return bool True se estiver atrasada
 */
function estaAtrasado($prazo_entrega, $status) {
    if (empty($prazo_entrega) || in_array($status, [STATUS_CONCLUIDA, STATUS_ENTREGUE])) {
        return false;
    }
    
    try {
        $hoje = new DateTime();
        $prazo = new DateTime($prazo_entrega);
        
        return $hoje > $prazo;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Calcula dias de atraso
 * @param string $prazo_entrega Data do prazo de entrega
 * @return int Dias de atraso (0 se não estiver atrasado)
 */
function calcularDiasAtraso($prazo_entrega) {
    if (empty($prazo_entrega)) {
        return 0;
    }
    
    try {
        $hoje = new DateTime();
        $prazo = new DateTime($prazo_entrega);
        
        if ($hoje > $prazo) {
            $diferenca = $hoje->diff($prazo);
            return $diferenca->days;
        }
    } catch (Exception $e) {
        return 0;
    }
    
    return 0;
}

/**
 * Remove máscaras de CPF, telefone, etc.
 * @param string $valor Valor com máscara
 * @return string Valor sem máscara
 */
function removerMascara($valor) {
    return preg_replace('/[^0-9]/', '', $valor);
}

/**
 * Formata valor monetário
 * @param float $valor Valor a ser formatado
 * @return string Valor formatado em Reais
 */
function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Valida CPF
 * @param string $cpf CPF a ser validado
 * @return bool True se CPF válido
 */
function validarCPF($cpf) {
    $cpf = removerMascara($cpf);
    
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    
    return true;
}
?>