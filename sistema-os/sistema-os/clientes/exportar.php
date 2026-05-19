<?php
session_start();
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$tipo = $_GET['tipo'] ?? 'csv';
$busca = $_GET['busca'] ?? '';

// Construir query
$query = "SELECT * FROM clientes";
if (!empty($busca)) {
    $query .= " WHERE (nome_completo LIKE :busca OR cpf_rg LIKE :busca OR telefone LIKE :busca)";
}

$query .= " ORDER BY nome_completo ASC";

$stmt = $db->prepare($query);
if (!empty($busca)) {
    $stmt->bindValue(':busca', "%{$busca}%");
}
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gerar CSV
if ($tipo === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=clientes_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Cabeçalho
    fputcsv($output, [
        'ID',
        'Nome Completo',
        'CPF/RG',
        'Telefone',
        'E-mail',
        'Endereço',
        'Data Cadastro'
    ], ';');
    
    // Dados
    foreach ($clientes as $cliente) {
        fputcsv($output, [
            $cliente['id'],
            $cliente['nome_completo'],
            $cliente['cpf_rg'],
            $cliente['telefone'],
            $cliente['email'],
            $cliente['endereco'],
            date('d/m/Y H:i', strtotime($cliente['data_cadastro']))
        ], ';');
    }
    
    fclose($output);
    exit;
}

// Se tipo não reconhecido, redirecionar
$_SESSION['message'] = [
    'type' => 'error',
    'text' => 'Tipo de exportação não suportado.'
];
header('Location: lista.php');
?>