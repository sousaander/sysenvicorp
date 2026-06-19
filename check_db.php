<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=sysenvicorp_db', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$hoje = date('Y-m-d');
$dataLimite = $hoje;

try {
    $stmtStats = $db->prepare("
        SELECT 
            COUNT(CASE WHEN (status = 'Pago' AND data_pagamento > vencimento) OR (status IN ('Pendente', 'Atrasado') AND vencimento < :hoje) THEN 1 END) as qtd_atraso,
            COUNT(CASE WHEN data_pagamento < vencimento THEN 1 END) as qtd_adiantado,
            SUM(CASE WHEN (status = 'Pago' AND data_pagamento > vencimento) OR (status IN ('Pendente', 'Atrasado') AND vencimento < :hoje) THEN valor ELSE 0 END) as valor_atraso,
            SUM(CASE WHEN data_pagamento < vencimento THEN valor ELSE 0 END) as valor_adiantado,
            AVG(CASE 
                WHEN status = 'Pago' AND data_pagamento > vencimento THEN DATEDIFF(data_pagamento, vencimento) 
                WHEN status IN ('Pendente', 'Atrasado') AND vencimento < :hoje_v THEN DATEDIFF(:hoje_v2, vencimento)
            END) as media_dias_atraso,
            AVG(CASE WHEN data_pagamento < vencimento THEN DATEDIFF(vencimento, data_pagamento) END) as media_dias_adiantado
        FROM transacoes 
        WHERE tipo = 'R' AND status != 'Cancelado' AND cliente_id > 0 
        AND (vencimento <= :limite OR data_pagamento <= :limite2)
    ");
    $stmtStats->execute([':limite' => $dataLimite, ':limite2' => $dataLimite, ':hoje' => $hoje, ':hoje_v' => $hoje, ':hoje_v2' => $hoje]);
    echo "Success!\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
