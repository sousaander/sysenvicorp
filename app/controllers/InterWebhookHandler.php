<?php

namespace App\Controllers\Bancos;

use PDO;
use Exception;

class InterWebhookHandler
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Processa o payload específico do banco Inter
     * @param array $data Corpo do JSON recebido
     * @param array $headers Headers da requisição
     * @return bool
     */
    public function processar(array $data, array $headers): bool
    {
        // O Banco Inter geralmente envia webhooks de Pix em uma lista (array 'pix')
        if (isset($data['pix']) && is_array($data['pix'])) {
            $success = true;
            foreach ($data['pix'] as $pix) {
                if (!$this->processarPix($pix)) {
                    $success = false;
                }
            }
            return $success;
        }

        // Se for um webhook de tipo informativo ou confirmação de registro do Inter
        if (isset($data['webhookType']) && $data['webhookType'] === 'CODE_VERIFICATION') {
            return true;
        }

        return true; 
    }

    private function processarPix(array $pix): bool
    {
        $webhookId = $pix['endToEndId'] ?? null;
        $valor = (float)($pix['valor'] ?? 0);
        
        // No Banco Inter, Pix recebido é sempre Receita (R)
        $tipo = 'R'; 
        
        $descricao = "Pix Inter: " . ($pix['txid'] ?? 'Recebimento');
        if (!empty($pix['infoPagador'])) {
            $descricao .= " - " . $pix['infoPagador'];
        }

        $dataRef = date('Y-m-d', strtotime($pix['horario'] ?? 'now'));

        try {
            // Tenta localizar o banco Inter no sistema para vincular o ID da conta
            $stmtBanco = $this->conn->prepare("SELECT id FROM bancos WHERE nome LIKE '%Inter%' LIMIT 1");
            $stmtBanco->execute();
            $bancoId = $stmtBanco->fetchColumn() ?: null;

            $sql = "INSERT INTO transacoes (banco_id, descricao, valor, tipo, status, vencimento, data_pagamento, webhook_id, forma_pagamento, created_at) 
                    VALUES (:banco_id, :descricao, :valor, :tipo, 'Pago', :data, :data, :webhook_id, 'PIX', NOW())";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':banco_id' => $bancoId,
                ':descricao' => $descricao,
                ':valor' => $valor,
                ':tipo' => $tipo,
                ':data' => $dataRef,
                ':webhook_id' => $webhookId
            ]);
        } catch (Exception $e) {
            error_log("InterWebhookHandler Error: " . $e->getMessage());
            return false;
        }
    }
}