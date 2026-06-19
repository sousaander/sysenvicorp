<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sysenvicorp_db;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- Estrutura projetos ---\n";
    $stmt = $pdo->query("DESCRIBE projetos");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Key'] . ' - ' . $row['Default'] . "\n";
    }
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage() . "\n";
}
