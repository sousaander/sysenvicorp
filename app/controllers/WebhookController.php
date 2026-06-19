<?php
// controllers/WebhookController.php
require_once __DIR__ . '/../config/database.php';

class WebhookController {
    private $conn;
    
    public function __construct() {
        $this->conn = getDatabaseConnection();
    }
    
    /**
     * Endpoint principal para receber webhooks
     * URL: https://seusite.com/webhook/receber
     */
    public function receberWebhook() {
        header('Content-Type: application/json');
        
        // 1. VALIDAÇÃO DE SEGURANÇA
        // Verifica API Key (configure no .env)
        $headers = getallheaders();
        $apiKeyRecebida = $headers['X-API-Key'] ?? '';
        $apiKeyCorreta = $_ENV['WEBHOOK_SECRET_KEY'] ?? 'sua_chave_secreta_aqui';
        
        if ($apiKeyRecebida !== $apiKeyCorreta) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        // 2. LÊ O CORPO DA REQUISIÇÃO
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }
        
        // 3. PREVENÇÃO CONTRA DUPLICIDADE
        $transactionId = $data['id'] ?? $data['txid'] ?? $data['payment_id'] ?? null;
        
        if ($transactionId) {
            $stmt = $this->conn->prepare("SELECT id FROM transacoes WHERE webhook_id = ?");
            $stmt->execute([$transactionId]);
            if ($stmt->fetch()) {
                http_response_code(200);
                echo json_encode(['status' => 'already_processed']);
                return;
            }
        }
        
        // 4. IDENTIFICA O TIPO DE MOVIMENTAÇÃO
        $tipoMovimentacao = $this->identificarTipoMovimentacao($data);
        
        // 5. PROCESSA CONFORME O TIPO
        $resultado = false;
        switch ($tipoMovimentacao) {
            case 'pix_recebido':
                $resultado = $this->processarPixRecebido($data);
                break;
            case 'pix_enviado':
                $resultado = $this->processarPixEnviado($data);
                break;
            case 'cartao_credito':
                $resultado = $this->processarCartaoCredito($data);
                break;
            case 'cartao_debito':
                $resultado = $this->processarCartaoDebito($data);
                break;
            case 'transferencia_recebida':
                $resultado = $this->processarTransferenciaRecebida($data);
                break;
            case 'transferencia_enviada':
                $resultado = $this->processarTransferenciaEnviada($data);
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Tipo de movimentação não reconhecido']);
                return;
        }
        
        if ($resultado) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Movimentação processada']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao processar movimentação']);
        }
    }
    
    /**
     * Processa PIX recebido
     */
    private function processarPixRecebido($data) {
        $valor = $data['amount']['value'] ?? $data['valor'] ?? 0;
        $descricao = $data['description'] ?? $data['txid'] ?? 'PIX Recebido';
        $contaId = $this->identificarContaDestino($data);
        $dataMovimentacao = $data['date'] ?? date('Y-m-d H:i:s');
        $webhookId = $data['id'] ?? $data['txid'] ?? null;
        
        // Insere como RECEITA (tipo 'R')
        return $this->inserirMovimentacao([
            'banco_id' => $contaId,
            'descricao' => 'PIX: ' . $descricao,
            'valor' => $valor,
            'tipo' => 'R', // Receita
            'vencimento' => date('Y-m-d', strtotime($dataMovimentacao)),
            'status' => 'Pago',
            'webhook_id' => $webhookId,
            'forma_pagamento' => 'PIX'
        ]);
    }
    
    /**
     * Processa PIX enviado
     */
    private function processarPixEnviado($data) {
        $valor = $data['amount']['value'] ?? $data['valor'] ?? 0;
        $descricao = $data['description'] ?? $data['txid'] ?? 'PIX Enviado';
        $contaId = $this->identificarContaOrigem($data);
        
        return $this->inserirMovimentacao([
            'banco_id' => $contaId,
            'descricao' => 'PIX: ' . $descricao,
            'valor' => $valor,
            'tipo' => 'P', // Despesa
            'vencimento' => date('Y-m-d'),
            'status' => 'Pago',
            'webhook_id' => $data['id'] ?? null,
            'forma_pagamento' => 'PIX'
        ]);
    }
    
    /**
     * Processa pagamento com cartão de crédito
     */
    private function processarCartaoCredito($data) {
        $valor = $data['amount'] ?? $data['valor'] ?? 0;
        $descricao = $data['description'] ?? $data['statement_descriptor'] ?? 'Compra Cartão Crédito';
        $contaId = $this->identificarContaDestino($data);
        
        return $this->inserirMovimentacao([
            'banco_id' => $contaId,
            'descricao' => 'Cartão Crédito: ' . $descricao,
            'valor' => $valor,
            'tipo' => 'R',
            'vencimento' => date('Y-m-d'),
            'status' => 'Pago',
            'webhook_id' => $data['id'] ?? null,
            'forma_pagamento' => 'Cartão Crédito'
        ]);
    }
    
    /**
     * Processa pagamento com cartão de débito
     */
    private function processarCartaoDebito($data) {
        $valor = $data['amount'] ?? $data['valor'] ?? 0;
        $descricao = $data['description'] ?? 'Compra Débito';
        $contaId = $this->identificarContaDestino($data);
        
        return $this->inserirMovimentacao([
            'banco_id' => $contaId,
            'descricao' => 'Cartão Débito: ' . $descricao,
            'valor' => $valor,
            'tipo' => 'R',
            'vencimento' => date('Y-m-d'),
            'status' => 'Pago',
            'webhook_id' => $data['id'] ?? null,
            'forma_pagamento' => 'Cartão Débito'
        ]);
    }
    
    /**
     * Processa transferência recebida (TED/DOC entre contas)
     */
    private function processarTransferenciaRecebida($data) {
        $valor = $data['amount'] ?? $data['valor'] ?? 0;
        $descricao = $data['description'] ?? $data['origin_bank'] ?? 'Transferência Recebida';
        $contaId = $this->identificarContaDestino($data);
        
        return $this->inserirMovimentacao([
            'banco_id' => $contaId,
            'descricao' => 'Transferência: ' . $descricao,
            'valor' => $valor,
            'tipo' => 'R',
            'vencimento' => date('Y-m-d'),
            'status' => 'Pago',
            'webhook_id' => $data['id'] ?? null,
            'forma_pagamento' => 'Transferência'
        ]);
    }
    
    /**
     * Processa transferência enviada
     */
    private function processarTransferenciaEnviada($data) {
        $valor = $data['amount'] ?? $data['valor'] ?? 0;
        $descricao = $data['description'] ?? $data['destination_bank'] ?? 'Transferência Enviada';
        $contaId = $this->identificarContaOrigem($data);
        
        return $this->inserirMovimentacao([
            'banco_id' => $contaId,
            'descricao' => 'Transferência: ' . $descricao,
            'valor' => $valor,
            'tipo' => 'P',
            'vencimento' => date('Y-m-d'),
            'status' => 'Pago',
            'webhook_id' => $data['id'] ?? null,
            'forma_pagamento' => 'Transferência'
        ]);
    }
    
    /**
     * Identifica automaticamente a conta de destino baseado nos dados do webhook
     */
    private function identificarContaDestino($data) {
        // Tenta identificar pela chave PIX, conta bancária, etc.
        $chavePix = $data['destination']['pix_key'] ?? $data['chave_pix'] ?? null;
        $agencia = $data['destination']['agency'] ?? $data['agencia'] ?? null;
        $conta = $data['destination']['account'] ?? $data['conta'] ?? null;
        
        if ($chavePix) {
            $stmt = $this->conn->prepare("SELECT id FROM bancos WHERE (JSON_UNQUOTE(JSON_EXTRACT(configuracoes_json, '$.chave_pix')) = ? OR nome LIKE ?)");
            $stmt->execute([$chavePix]);
            if ($row = $stmt->fetch()) return $row['id'];
        }
        
        // Se não encontrou, retorna a primeira conta ativa
        $stmt = $this->conn->query("SELECT id FROM bancos LIMIT 1");
        if ($row = $stmt->fetch()) return $row['id'];
        
        // Se não tem nenhuma conta, cria uma padrão
        return $this->criarContaPadrao();
    }
    
    private function identificarContaOrigem($data) {
        // Similar ao identificarContaDestino, mas para débitos
        return $this->identificarContaDestino($data);
    }
    
    private function identificarTipoMovimentacao($data) {
        // Lógica para identificar baseado nos campos recebidos
        if (isset($data['pix'])) return 'pix_recebido';
        if (isset($data['transaction_type']) && $data['transaction_type'] === 'pix') return 'pix_recebido';
        if (isset($data['payment_method']) && $data['payment_method'] === 'credit_card') return 'cartao_credito';
        if (isset($data['payment_method']) && $data['payment_method'] === 'debit_card') return 'cartao_debito';
        if (isset($data['ted']) || isset($data['doc'])) return 'transferencia_recebida';
        
        return 'desconhecido';
    }
    
    private function inserirMovimentacao($dados) {
        try {
            $sql = "INSERT INTO transacoes (banco_id, descricao, valor, tipo, vencimento, data_pagamento, status, webhook_id, forma_pagamento, created_at) 
                    VALUES (:banco_id, :descricao, :valor, :tipo, :vencimento, :vencimento, :status, :webhook_id, :forma_pagamento, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':banco_id' => $dados['banco_id'],
                ':descricao' => $dados['descricao'],
                ':valor' => $dados['valor'],
                ':tipo' => $dados['tipo'],
                ':vencimento' => $dados['vencimento'],
                ':status' => $dados['status'],
                ':webhook_id' => $dados['webhook_id'],
                ':forma_pagamento' => $dados['forma_pagamento']
            ]);
            
            // Atualiza o saldo da conta
            $this->atualizarSaldoConta($dados['banco_id'], $dados['valor'], $dados['tipo']);
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao inserir movimentação: " . $e->getMessage());
            return false;
        }
    }
    
    private function atualizarSaldoConta($contaId, $valor, $tipo) {
        $operador = ($tipo === 'R') ? '+' : '-';
        $sql = "UPDATE bancos SET saldo_atual = saldo_atual {$operador} :valor, updated_at = NOW() WHERE id = :conta_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':valor' => $valor, ':conta_id' => $contaId]);
    }
    
    private function criarContaPadrao() {
        $sql = "INSERT INTO bancos (nome, tipo, saldo_atual, status, created_at) VALUES ('Conta Principal (Auto)', 'Corrente', 0, 'Ativo', NOW())";
        $this->conn->exec($sql);
        return $this->conn->lastInsertId();
    }
}
?>