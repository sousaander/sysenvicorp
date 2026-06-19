<?php
try {
    // Conectar ao banco (ajuste as credenciais se necessário)
    $pdo = new PDO('mysql:host=localhost;dbname=sysenvicorp_db;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== DIAGNÓSTICO DO BANCO DE DADOS ===\n\n";

    // Verificar se as tabelas existem
    $tables = ['orcamento_proposta', 'orcamento_proposta_historico'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabela $table existe\n";
        } else {
            echo "❌ Tabela $table NÃO existe\n";
        }
    }

    echo "\n--- Estrutura orcamento_proposta ---\n";
    $stmt = $pdo->query("DESCRIBE orcamento_proposta");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Key'] . "\n";
    }

    echo "\n--- Estrutura orcamento_proposta_historico ---\n";
    $stmt = $pdo->query("DESCRIBE orcamento_proposta_historico");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Key'] . "\n";
    }

    // Testar uma query simples de UPDATE
    echo "\n--- Teste de UPDATE ---\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orcamento_proposta WHERE status = 'Rascunho' LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de propostas em rascunho: " . $result['total'] . "\n";

} catch (Exception $e) {
    echo '❌ ERRO: ' . $e->getMessage() . "\n";
}
?>