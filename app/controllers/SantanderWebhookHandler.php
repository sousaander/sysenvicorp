<?php

namespace App\Controllers\Bancos;

use PDO;
use Exception;

class SantanderWebhookHandler
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Processa o payload específico do banco Santander
     * @param array $data Corpo do JSON recebido
     * @param array $headers Headers da requisição
     * @return bool
     */
    public function processar(array $data, array $headers): bool
    {
        // Verifica se é uma notificação de Pix/Transação válida do Santander
        if (isset($data['transactionId']) || isset($data['endToEndId'])) {
            return $this->processarTransacao($data);
        }

        // Se for um webhook de teste ou configuração, retornamos true para confirmar o recebimento
        return true;
    }

    private function processarTransacao(array $data): bool
    {
        // Identificador único para evitar duplicidade
        $webhookId = $data['endToEndId'] ?? $data['transactionId'] ?? null;
        
        // O Santander costuma enviar o valor já em decimal, mas garantimos o cast para float
        $valor = (float)($data['amount'] ?? $data['valor'] ?? 0);
        
        // Mapeamos o tipo: Geralmente webhooks de entrada são créditos (R)
        $tipo = ($data['type'] ?? '') === 'DEBIT' ? 'P' : 'R';
        
        $descricao = "Santander: " . ($data['description'] ?? 'Movimentação Pix');
        if (!empty($data['payerName'])) {
            $descricao .= " - " . $data['payerName'];
        }

        $dataRef = date('Y-m-d', strtotime($data['timestamp'] ?? $data['date'] ?? 'now'));

        try {
            // Tenta localizar o banco Santander no sistema para vincular o ID da conta
            $stmtBanco = $this->conn->prepare("SELECT id FROM bancos WHERE nome LIKE '%Santander%' LIMIT 1");
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
            error_log("SantanderWebhookHandler Error: " . $e->getMessage());
            return false;
        }
    }
}