<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sysenvicorp_db;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== PROPOSTAS E PROJETOS VINCULADOS ===\n";
    $stmt = $pdo->query("SELECT id, numero_proposta, nome_proposta, status, projeto_id FROM orcamento_proposta");
    $propostas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($propostas as $p) {
        echo "Proposta ID: {$p['id']} | Num: {$p['numero_proposta']} | Nome: {$p['nome_proposta']} | Status: {$p['status']} | Projeto ID Vinculado: " . ($p['projeto_id'] ?? 'NULL') . "\n";
    }

    echo "\n=== PROJETOS EXISTENTES NO BANCO ===\n";
    $stmtProj = $pdo->query("SELECT id, numero_projeto, nome, status, orcamento_id FROM projetos");
    $projetos = $stmtProj->fetchAll(PDO::FETCH_ASSOC);
    foreach ($projetos as $pr) {
        echo "Projeto ID: {$pr['id']} | Num: {$pr['numero_projeto']} | Nome: {$pr['nome']} | Status: {$pr['status']} | Orcamento/Proposta Ref: {$pr['orcamento_id']}\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
