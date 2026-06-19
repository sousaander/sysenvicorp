<?php

namespace App\Controllers\Bancos;

use PDO;
use Exception;

class CoraWebhookHandler
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Processa o payload específico do banco Cora
     * @param array $data Corpo do JSON recebido
     * @param array $headers Headers da requisição
     * @return bool
     */
    public function processar(array $data, array $headers): bool
    {
        $event = $data['event'] ?? '';
        $details = $data['data'] ?? [];

        if (empty($event) || empty($details)) {
            return false;
        }

        // Mapeamento de eventos da Cora
        switch ($event) {
            case 'transaction.created':
                return $this->processarTransacao($details);
            
            case 'invoice.paid':
                // Lógica para boletos da Cora pagos
                return true; 

            default:
                // Retornamos true para não forçar a Cora a repetir o envio de eventos irrelevantes
                return true;
        }
    }

    private function processarTransacao(array $details): bool
    {
        $webhookId = $details['id'] ?? null;
        
        // A Cora envia o valor em centavos (inteiro). Convertemos para decimal (float).
        $valor = (float)($details['amount'] ?? 0) / 100;
        
        // Mapeamento: CREDIT = Receita (R), DEBIT = Despesa (P)
        $tipo = ($details['type'] === 'CREDIT') ? 'R' : 'P';
        
        $descricao = $details['description'] ?? 'Movimentação Cora';
        $contraparte = $details['counterparty']['name'] ?? '';
        if ($contraparte) {
            $descricao .= ' - ' . $contraparte;
        }

        $dataRef = date('Y-m-d', strtotime($details['created_at'] ?? 'now'));

        try {
            // Tenta localizar o banco Cora no sistema para vincular a transação automática
            $stmtBanco = $this->conn->prepare("SELECT id FROM bancos WHERE nome LIKE '%Cora%' LIMIT 1");
            $stmtBanco->execute();
            $bancoId = $stmtBanco->fetchColumn() ?: null;

            $sql = "INSERT INTO transacoes (banco_id, descricao, valor, tipo, status, vencimento, data_pagamento, webhook_id, forma_pagamento, created_at) 
                    VALUES (:banco_id, :descricao, :valor, :tipo, 'Pago', :data, :data, :webhook_id, :forma, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':banco_id' => $bancoId,
                ':descricao' => 'Cora: ' . $descricao,
                ':valor' => $valor,
                ':tipo' => $tipo,
                ':data' => $dataRef,
                ':webhook_id' => $webhookId,
                ':forma' => $details['entry_type'] ?? 'Cora'
            ]);
        } catch (Exception $e) {
            error_log("CoraWebhookHandler Error: " . $e->getMessage());
            return false;
        }
    }
}