<?php
// Verificar se as funções já foram carregadas
if (!function_exists('exibirData')) {
    
    // Incluir config apenas se necessário
    if (!defined('SISTEMA_NOME')) {
        require_once __DIR__ . '/../config/config.php';
    }
    
    /**
     * Exibe data formatada para visualização
     * @param string $data Data do banco
     * @param string $formato Formato de saída
     * @return string Data formatada
     */
    function exibirData($data, $formato = FORMATO_DATA_HORA) {
        return formatarData($data, FORMATO_DATA_HORA_BANCO, $formato);
    }
    
    /**
     * Exibe apenas a data (sem hora)
     * @param string $data Data do banco
     * @return string Data formatada
     */
    function exibirDataSimples($data) {
        return formatarData($data, FORMATO_DATA_HORA_BANCO, FORMATO_DATA);
    }
    
    /**
     * Exibe apenas a hora
     * @param string $data Data do banco
     * @return string Hora formatada
     */
    function exibirHora($data) {
        return formatarData($data, FORMATO_DATA_HORA_BANCO, FORMATO_HORA);
    }
    
    /**
     * Retorna a data atual formatada para input date
     * @return string Data no formato Y-m-d
     */
    function dataAtualInputDate() {
        return date(FORMATO_INPUT_DATE);
    }
    
    /**
     * Retorna a data/hora atual formatada para input datetime-local
     * @return string Data no formato Y-m-d\TH:i
     */
    function dataAtualInputDateTime() {
        return date(FORMATO_INPUT_DATETIME);
    }
    
    /**
     * Formata valor para exibição
     * @param mixed $valor Valor numérico
     * @return string Valor formatado em Reais
     */
    function exibirValor($valor) {
        if (empty($valor) || !is_numeric($valor)) {
            return 'R$ 0,00';
        }
        return formatarMoeda(floatval($valor));
    }
    
    /**
     * Retorna classe CSS baseada no status
     * @param string $status Status da ordem
     * @return string Classe CSS
     */
    function classeStatus($status) {
        $classes = [
            STATUS_EM_ORCAMENTO => 'status-em-orcamento',
            STATUS_AGUARDANDO => 'status-aguardando',
            STATUS_APROVADO => 'status-aprovado',
            STATUS_ANDAMENTO => 'status-andamento',
            STATUS_CONCLUIDA => 'status-concluida',
            STATUS_ENTREGUE => 'status-entregue'
        ];
        
        return $classes[$status] ?? 'status-default';
    }
    
    /**
     * Retorna ícone baseado no status
     * @param string $status Status da ordem
     * @return string HTML do ícone
     */
    function iconeStatus($status) {
        $icones = [
            STATUS_EM_ORCAMENTO => 'fas fa-file-invoice-dollar',
            STATUS_AGUARDANDO => 'fas fa-clock',
            STATUS_APROVADO => 'fas fa-check-circle',
            STATUS_ANDAMENTO => 'fas fa-tools',
            STATUS_CONCLUIDA => 'fas fa-check-double',
            STATUS_ENTREGUE => 'fas fa-box'
        ];
        
        return $icones[$status] ?? 'fas fa-question-circle';
    }
    
    /**
     * Formata CPF/CNPJ para exibição
     * @param string $documento Documento sem máscara
     * @return string Documento formatado
     */
    function formatarDocumento($documento) {
        $documento = removerMascara($documento);
        
        if (strlen($documento) === 11) {
            // CPF: 000.000.000-00
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $documento);
        } elseif (strlen($documento) === 14) {
            // CNPJ: 00.000.000/0000-00
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $documento);
        }
        
        return $documento;
    }
    
    /**
     * Formata telefone para exibição
     * @param string $telefone Número de telefone
     * @return string Telefone formatado
     */
    function formatarTelefone($telefone) {
        $telefone = removerMascara($telefone);
        
        if (strlen($telefone) === 11) {
            // (11) 99999-9999
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
        } elseif (strlen($telefone) === 10) {
            // (11) 9999-9999
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
        }
        
        return $telefone;
    }
    
    /**
     * Retorna link do WhatsApp
     * @param string $telefone Número de telefone
     * @return string Link do WhatsApp
     */
    function linkWhatsApp($telefone) {
        $telefone = removerMascara($telefone);
        return "https://wa.me/55{$telefone}";
    }
    
    /**
     * Retorna link de e-mail
     * @param string $email Endereço de e-mail
     * @return string Link mailto:
     */
    function linkEmail($email) {
        return "mailto:{$email}";
    }
    
    /**
     * Sanitiza entrada de dados
     * @param mixed $dados Dados a serem sanitizados
     * @return mixed Dados sanitizados
     */
    function sanitizar($dados) {
        if (is_array($dados)) {
            return array_map('sanitizar', $dados);
        }
        
        if (is_string($dados)) {
            // Remove espaços no início e fim
            $dados = trim($dados);
            // Remove barras invertidas
            $dados = stripslashes($dados);
            // Converte caracteres especiais
            $dados = htmlspecialchars($dados, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return $dados;
    }
    
    /**
     * Redireciona com mensagem flash
     * @param string $url URL para redirecionar
     * @param string $tipo Tipo da mensagem (success, error, warning, info)
     * @param string $mensagem Mensagem a ser exibida
     */
    function redirecionar($url, $tipo = null, $mensagem = null) {
        if ($tipo && $mensagem) {
            $_SESSION['message'] = [
                'type' => $tipo,
                'text' => $mensagem
            ];
        }
        
        header("Location: $url");
        exit;
    }
    
    /**
     * Converte valor monetário do formato brasileiro para float
     * @param string $valor Valor no formato "R$ 1.234,56"
     * @return float Valor numérico
     */
    function valorParaFloat($valor) {
        if (empty($valor)) {
            return 0.0;
        }
        
        // Remove "R$", pontos e substitui vírgula por ponto
        $valor = str_replace('R$', '', $valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        
        return floatval(trim($valor));
    }
    
    /**
     * Retorna cor baseada no status para gráficos
     * @param string $status Status da ordem
     * @return string Cor em hexadecimal
     */
    function corStatus($status) {
        $cores = [
            STATUS_EM_ORCAMENTO => '#ed8936',
            STATUS_AGUARDANDO => '#a0aec0',
            STATUS_APROVADO => '#48bb78',
            STATUS_ANDAMENTO => '#4299e1',
            STATUS_CONCLUIDA => '#9f7aea',
            STATUS_ENTREGUE => '#38a169'
        ];
        
        return $cores[$status] ?? '#718096';
    }
    
    /**
     * Gera texto resumido para exibição em cards
     * @param string $texto Texto completo
     * @param int $limite Número máximo de caracteres
     * @return string Texto resumido
     */
    function resumirTexto($texto, $limite = 100) {
        if (strlen($texto) <= $limite) {
            return $texto;
        }
        
        $texto = substr($texto, 0, $limite);
        $ultimo_espaco = strrpos($texto, ' ');
        
        if ($ultimo_espaco !== false) {
            $texto = substr($texto, 0, $ultimo_espaco);
        }
        
        return $texto . '...';
    }
    
    /**
     * Valida se um e-mail é válido
     * @param string $email E-mail a ser validado
     * @return bool True se e-mail for válido
     */
    function validarEmail($email) {
        if (empty($email)) {
            return true; // E-mail opcional
        }
        
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Gera senha aleatória
     * @param int $tamanho Tamanho da senha
     * @return string Senha gerada
     */
    function gerarSenha($tamanho = 8) {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $senha = '';
        
        for ($i = 0; $i < $tamanho; $i++) {
            $senha .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        
        return $senha;
    }
    
    /**
     * Calcula a idade a partir da data de nascimento
     * @param string $data_nascimento Data de nascimento (Y-m-d)
     * @return int Idade em anos
     */
    function calcularIdade($data_nascimento) {
        if (empty($data_nascimento)) {
            return null;
        }
        
        try {
            $nascimento = new DateTime($data_nascimento);
            $hoje = new DateTime();
            $idade = $hoje->diff($nascimento);
            
            return $idade->y;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Gera um token único
     * @param int $tamanho Tamanho do token
     * @return string Token gerado
     */
    function gerarToken($tamanho = 32) {
        return bin2hex(random_bytes($tamanho));
    }
    
    /**
     * Retorna o cliente IP
     * @return string IP do cliente
     */
    function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
    
    /**
     * Formata número para máscara de CPF/CNPJ automaticamente
     * @param string $numero Número sem máscara
     * @return string Número com máscara
     */
    function formatarCpfCnpj($numero) {
        $numero = preg_replace('/[^0-9]/', '', $numero);
        
        if (strlen($numero) === 11) {
            return formatarDocumento($numero);
        } elseif (strlen($numero) === 14) {
            return formatarDocumento($numero);
        }
        
        return $numero;
    }
    
    /**
     * Retorna o tempo decorrido em formato humano (ex: "há 2 horas")
     * @param string $data Data no passado
     * @return string Tempo decorrido formatado
     */
    function tempoDecorrido($data) {
        if (empty($data)) {
            return '';
        }
        
        try {
            $data_passado = new DateTime($data);
            $agora = new DateTime();
            $diferenca = $agora->diff($data_passado);
            
            if ($diferenca->y > 0) {
                return $diferenca->y . ' ano' . ($diferenca->y > 1 ? 's' : '') . ' atrás';
            } elseif ($diferenca->m > 0) {
                return $diferenca->m . ' mês' . ($diferenca->m > 1 ? 'es' : '') . ' atrás';
            } elseif ($diferenca->d > 0) {
                return $diferenca->d . ' dia' . ($diferenca->d > 1 ? 's' : '') . ' atrás';
            } elseif ($diferenca->h > 0) {
                return $diferenca->h . ' hora' . ($diferenca->h > 1 ? 's' : '') . ' atrás';
            } elseif ($diferenca->i > 0) {
                return $diferenca->i . ' minuto' . ($diferenca->i > 1 ? 's' : '') . ' atrás';
            } else {
                return 'agora mesmo';
            }
        } catch (Exception $e) {
            return '';
        }
    }
    
    /**
     * Cria um slug a partir de um texto
     * @param string $texto Texto para criar slug
     * @return string Slug gerado
     */
    function criarSlug($texto) {
        $texto = strtolower($texto);
        $texto = preg_replace('/[^a-z0-9-]/', '-', $texto);
        $texto = preg_replace('/-+/', '-', $texto);
        $texto = trim($texto, '-');
        
        return $texto;
    }
    
    /**
     * Verifica se uma string contém apenas números
     * @param string $string String a ser verificada
     * @return bool True se contiver apenas números
     */
    function apenasNumeros($string) {
        return preg_match('/^[0-9]+$/', $string) === 1;
    }
    
    /**
     * Retorna o primeiro nome de um nome completo
     * @param string $nome_completo Nome completo
     * @return string Primeiro nome
     */
    function primeiroNome($nome_completo) {
        $partes = explode(' ', $nome_completo);
        return $partes[0] ?? $nome_completo;
    }
    
    /**
     * Retorna iniciais de um nome
     * @param string $nome Nome completo
     * @return string Iniciais (ex: "JS" para "João Silva")
     */
    function iniciaisNome($nome) {
        $partes = explode(' ', $nome);
        $iniciais = '';
        
        foreach ($partes as $parte) {
            if (!empty($parte)) {
                $iniciais .= strtoupper(substr($parte, 0, 1));
            }
        }
        
        return substr($iniciais, 0, 2);
    }
    
    /**
     * Verifica se uma data é válida
     * @param string $data Data a ser validada
     * @param string $formato Formato da data (padrão: Y-m-d)
     * @return bool True se data for válida
     */
    function validarData($data, $formato = 'Y-m-d') {
        $dateTime = DateTime::createFromFormat($formato, $data);
        return $dateTime && $dateTime->format($formato) === $data;
    }
}
?>