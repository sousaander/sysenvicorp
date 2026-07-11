<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/core/Connection.php';

use App\Core\Connection;

try {
    $pdo = Connection::getInstance();
    $stmt = $pdo->query("DESCRIBE bancos");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "COLUMNS:\n";
    foreach ($cols as $c) {
        echo $c['Field'] . "\t" . $c['Type'] . "\t" . ($c['Null']) . "\t" . ($c['Key']) . "\t" . ($c['Default']) . "\t" . ($c['Extra']) . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
