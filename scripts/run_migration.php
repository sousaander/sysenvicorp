<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/core/Connection.php';

use App\Core\Connection;

$sqlFile = __DIR__ . '/migrate_bancos_add_columns.sql';
if (!file_exists($sqlFile)) {
    echo "Arquivo de migração não encontrado: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
try {
    $pdo = Connection::getInstance();
    // Desabilita emulação de multi-statement se necessário; executa por partes
    $stmts = array_filter(array_map('trim', explode(";\n", $sql)));
    foreach ($stmts as $s) {
        if (empty($s)) continue;
        $pdo->exec($s);
    }
    echo "Migração aplicada com sucesso.\n";
} catch (Exception $e) {
    echo "Erro ao aplicar migração: " . $e->getMessage() . "\n";
    exit(1);
}
