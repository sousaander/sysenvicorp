<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sysenvicorp_db;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== VERIFICAÇÃO DE DUPLICATAS ===\n\n";

    // Verificar se há propostas com token_aprovacao duplicado
    $stmt = $pdo->query("SELECT token_aprovacao, COUNT(*) as count FROM orcamento_proposta WHERE token_aprovacao IS NOT NULL AND token_aprovacao != '' GROUP BY token_aprovacao HAVING count > 1");
    $duplicados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($duplicados) > 0) {
        echo "❌ TOKENS DUPLICADOS ENCONTRADOS:\n";
        foreach ($duplicados as $dup) {
            echo "Token: {$dup['token_aprovacao']} - Count: {$dup['count']}\n";
        }
    } else {
        echo "✅ Nenhum token duplicado encontrado\n";
    }

    // Verificar se há propostas com numero_proposta duplicado
    $stmt2 = $pdo->query("SELECT numero_proposta, COUNT(*) as count FROM orcamento_proposta WHERE numero_proposta IS NOT NULL AND numero_proposta != '' GROUP BY numero_proposta HAVING count > 1");
    $duplicados2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    if (count($duplicados2) > 0) {
        echo "❌ NÚMEROS DE PROPOSTA DUPLICADOS:\n";
        foreach ($duplicados2 as $dup) {
            echo "Número: {$dup['numero_proposta']} - Count: {$dup['count']}\n";
        }
    } else {
        echo "✅ Nenhum número de proposta duplicado encontrado\n";
    }

    // Verificar uma proposta específica para debug
    $stmt3 = $pdo->query("SELECT id, numero_proposta, status, token_aprovacao FROM orcamento_proposta WHERE status = 'Rascunho' LIMIT 1");
    $proposta = $stmt3->fetch(PDO::FETCH_ASSOC);

    if ($proposta) {
        echo "\n--- PROPOSTA DE TESTE ---\n";
        echo "ID: {$proposta['id']}\n";
        echo "Número: {$proposta['numero_proposta']}\n";
        echo "Status: {$proposta['status']}\n";
        echo "Token: {$proposta['token_aprovacao']}\n";
    }

} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage() . "\n";
}
?>