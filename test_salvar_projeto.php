<?php
define('ROOT_PATH', __DIR__);
require_once 'app/config/env.php';
require_once 'vendor/autoload.php';
require_once 'app/config/settings.php';

// Mock the environment/session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;

try {
    $projetosModel = new App\Models\ProjetosModel();

    $dadosProjeto = [
        'numero_projeto' => $projetosModel->getNextProjectNumber(),
        'nome'           => 'Projeto de Teste Gerado de Proposta',
        'cliente_id'     => 1, // Supposing cliente ID 1 exists
        'data_inicial'   => date('Y-m-d'),
        'orcamento'      => 15000.00,
        'orcamento_id'   => '0001-26-ENV',
        'responsavel'    => 'Não Atribuído',
        'tipo_servico'   => 'Comercial',
        'status'         => 'Planejado',
        'latitude'       => -23.5505,
        'longitude'      => -46.6333,
        'observacoes'    => 'Criado via script de teste'
    ];

    echo "Tentando salvar o projeto...\n";
    $result = $projetosModel->salvarProjeto($dadosProjeto);
    echo "Resultado do retorno de salvarProjeto(): " . var_export($result, true) . "\n";
    
    // Query last insert id
    $pdo = new PDO('mysql:host=localhost;dbname=sysenvicorp_db;charset=utf8', 'root', '');
    $stmt = $pdo->query("SELECT * FROM projetos ORDER BY id DESC LIMIT 2");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Exception pega: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
