<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class LicencasOperacaoModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
        $this->ensureOcorrenciasTableExists();
    }

    private function ensureTableExists()
    {
        $columns = [
            'nome' => 'VARCHAR(255)',
            'numero_licenca' => 'VARCHAR(100)',
            'tipo_licenca' => 'VARCHAR(100)',
            'categoria' => 'VARCHAR(100)',
            'orgao_emissor' => 'VARCHAR(255)',
            'produto_servico' => 'VARCHAR(255)',
            'departamento' => 'VARCHAR(100)',
            'numero_serie' => 'VARCHAR(100)',
            'gestor_responsavel' => 'VARCHAR(255)',
            'email_responsavel' => 'VARCHAR(255)',
            'usuario_principal' => 'VARCHAR(255)',
            'observacoes' => 'TEXT',
            'data_emissao' => 'DATE',
            'data_inicio_vigencia' => 'DATE',
            'data_vencimento' => 'DATE',
            'periodo_renovacao' => 'VARCHAR(50)',
            'data_ultima_renovacao' => 'DATE',
            'modelo_licenca' => 'VARCHAR(100)',
            'quantidade_licencas' => 'INT',
            'licencas_em_uso' => 'INT',
            'abrangencia' => 'VARCHAR(100)',
            'status' => "VARCHAR(50) DEFAULT 'Vigente'",
            'valor_licenca' => 'DECIMAL(15,2)',
            'moeda' => 'VARCHAR(20)',
            'frequencia_pagamento' => 'VARCHAR(50)',
            'centro_custo' => 'VARCHAR(100)',
            'forma_pagamento' => 'VARCHAR(100)',
            'numero_contrato' => 'VARCHAR(100)',
            'data_assinatura_contrato' => 'DATE',
            'data_vigencia_contrato' => 'DATE',
            'cnpj_fornecedor' => 'VARCHAR(20)',
            'contato_comercial' => 'VARCHAR(255)',
            'link_contrato' => 'TEXT',
            'tags' => 'TEXT',
            'alerta_90_dias' => 'TINYINT(1) DEFAULT 1',
            'alerta_30_dias' => 'TINYINT(1) DEFAULT 1',
            'alerta_7_dias' => 'TINYINT(1) DEFAULT 1',
            'alerta_no_dia' => 'TINYINT(1) DEFAULT 0',
            'emails_notificacao' => 'TEXT',
            'auditoria_ativa' => 'TINYINT(1) DEFAULT 1',
            'requer_aprovacao' => 'TINYINT(1) DEFAULT 0',
            'licenca_regulatoria' => 'TINYINT(1) DEFAULT 0',
            'inclui_sla' => 'TINYINT(1) DEFAULT 1',
            'orgao_regulador' => 'VARCHAR(255)',
            'norma_aplicavel' => 'VARCHAR(255)',
            'notas_conformidade' => 'TEXT',
            'documento_path' => 'VARCHAR(255)',
            'projeto_id' => 'INT'
        ];

        foreach ($columns as $col => $def) {
            try {
                $this->db->query("SELECT $col FROM licencas_operacao LIMIT 1");
            } catch (\PDOException $e) {
                $this->db->exec("ALTER TABLE licencas_operacao ADD COLUMN $col $def");
            }
        }
    }

    private function ensureOcorrenciasTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS licencas_ocorrencias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            licenca_id INT NOT NULL,
            data_ocorrencia DATE NOT NULL,
            tipo VARCHAR(50) NOT NULL, -- 'Não Conformidade', 'Observação', 'Melhoria'
            descricao TEXT NOT NULL,
            plano_acao TEXT NULL,
            responsavel VARCHAR(100) NULL,
            status ENUM('Aberta', 'Em Tratativa', 'Concluída') DEFAULT 'Aberta',
            prioridade ENUM('Baixa', 'Média', 'Alta') DEFAULT 'Média',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (licenca_id) REFERENCES licencas_operacao(licenca_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);

        // Garante que a coluna exista em instalações já criadas
        try {
            $this->db->query("SELECT prioridade FROM licencas_ocorrencias LIMIT 1");
        } catch (\PDOException $e) {
            $this->db->exec("ALTER TABLE licencas_ocorrencias ADD COLUMN prioridade ENUM('Baixa', 'Média', 'Alta') DEFAULT 'Média' AFTER status");
        }
    }

    /**
     * Busca dados resumidos sobre as licenças.
     */
    public function getLicensesSummary()
    {
        try {
            return [
                'totalLicencas'    => (int) $this->db->query("SELECT COUNT(*) FROM licencas_operacao WHERE status = 'Vigente'")->fetchColumn(),
                'vencimento30Dias' => (int) $this->db->query("SELECT COUNT(*) FROM licencas_operacao WHERE data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND status = 'Vigente'")->fetchColumn(),
                'vencidas'         => (int) $this->db->query("SELECT COUNT(*) FROM licencas_operacao WHERE data_vencimento < CURDATE() OR status = 'Vencida'")->fetchColumn(),
                'emRenovacao'      => (int) $this->db->query("SELECT COUNT(*) FROM licencas_operacao WHERE status IN ('Pendente Renovação', 'Em Análise')")->fetchColumn(),
            ];
        } catch (\PDOException $e) {
            error_log("Erro ao buscar resumo de licenças: " . $e->getMessage());
            return [
                'totalLicencas' => 0,
                'vencimento30Dias' => 0,
                'vencidas' => 0,
                'emRenovacao' => 0,
            ];
        }
    }

    /**
     * Busca uma lista de licenças críticas (vencimento próximo ou status especial).
     */
    public function getCriticalLicensesList()
    {
        try {
            // Busca licenças que vencem em até 90 dias ou que possuem status crítico
            $sql = "SELECT 
                        licenca_id as id, 
                        nome, 
                        orgao_emissor as orgao, 
                        data_vencimento as vencimento, 
                        status 
                    FROM licencas_operacao 
                    WHERE data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) OR status != 'Vigente'
                    ORDER BY data_vencimento ASC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar lista crítica de licenças: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca uma licença específica pelo ID.
     */
    public function getLicenseById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT *, licenca_id as id FROM licencas_operacao WHERE licenca_id = ?");
            $stmt->execute([$id]);
            $licenca = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($licenca) {
                return $licenca;
            }
        } catch (\PDOException $e) {
            // Tabela pode não existir, segue para o fallback
        }

        // Fallback: Se não encontrou no banco, busca na lista de teste (Mock)
        foreach ($this->getCriticalLicensesList() as $mockItem) {
            if ($mockItem['id'] == $id) {
                return $mockItem;
            }
        }

        return null;
    }

    /**
     * Busca todas as licenças cadastradas.
     */
    public function getAllLicencas()
    {
        try {
            return $this->db->query("SELECT 
                        *, 
                        licenca_id as id, 
                        orgao_emissor as orgao, 
                        data_vencimento as vencimento 
                    FROM licencas_operacao 
                    ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Salva ou atualiza uma licença.
     */
    public function salvarLicenca(array $dados)
    {
        $id = $dados['id'] ?? null;
        unset($dados['id']); // Remove ID do array de dados para o loop

        if ($id) {
            $fields = implode(' = ?, ', array_keys($dados)) . ' = ?';
            $sql = "UPDATE licencas_operacao SET $fields WHERE licenca_id = ?";
            $params = array_values($dados);
            $params[] = $id;
        } else {
            $fields = implode(', ', array_keys($dados));
            $placeholders = implode(', ', array_fill(0, count($dados), '?'));
            $sql = "INSERT INTO licencas_operacao ($fields) VALUES ($placeholders)";
            $params = array_values($dados);
        }

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            error_log("Erro ao salvar licença: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui uma licença.
     */
    public function excluirLicenca($id)
    {
        $stmt = $this->db->prepare("DELETE FROM licencas_operacao WHERE licenca_id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Atualiza o status das licenças que já venceram para 'Vencida'.
     * Ignora as que já estão como 'Vencida'.
     * @return int O número de licenças atualizadas.
     */
    public function updateExpiredLicensesStatus(): int
    {
        try {
            $sql = "UPDATE licencas_operacao SET status = 'Vencida' WHERE data_vencimento < CURDATE() AND status != 'Vencida'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log("Erro ao atualizar licenças vencidas automaticamente: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Remove o documento de uma licença, apagando o arquivo físico e limpando o campo no banco.
     *
     * @param int $licencaId O ID da licença.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function removerDocumento(int $licencaId): bool
    {
        // Busca o caminho do documento para poder excluí-lo.
        $stmt = $this->db->prepare("SELECT documento_path FROM licencas_operacao WHERE licenca_id = :id");
        $stmt->execute(['id' => $licencaId]);
        $licenca = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($licenca && !empty($licenca['documento_path'])) {
            // ROOT_PATH deve ser definido no bootstrap do aplicativo (ex: public/index.php)
            // Assumindo que os documentos estão em /storage/licencas/
            $filePath = ROOT_PATH . '/storage/licencas/' . $licenca['documento_path'];

            // Apaga o arquivo físico se ele existir.
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        // Atualiza o banco de dados, definindo o caminho do documento como NULL.
        $stmtUpdate = $this->db->prepare("UPDATE licencas_operacao SET documento_path = NULL WHERE licenca_id = :id");
        return $stmtUpdate->execute(['id' => $licencaId]);
    }

    /**
     * Busca todas as não conformidades/ocorrências registradas para o relatório geral.
     */
    public function getRelatorioNaoConformidades()
    {
        try {
            $sql = "SELECT 
                        o.*, 
                        l.nome as licenca_nome, 
                        l.orgao_emissor,
                        l.status as licenca_status
                    FROM licencas_ocorrencias o
                    JOIN licencas_operacao l ON o.licenca_id = l.licenca_id
                    ORDER BY o.data_ocorrencia DESC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Salva uma nova ocorrência de não conformidade.
     */
    public function salvarOcorrencia(array $dados)
    {
        $sql = "INSERT INTO licencas_ocorrencias (licenca_id, data_ocorrencia, tipo, prioridade, descricao, plano_acao, responsavel, status) 
                VALUES (:licenca_id, :data_ocorrencia, :tipo, :prioridade, :descricao, :plano_acao, :responsavel, :status)";
        return $this->db->prepare($sql)->execute($dados);
    }

    /**
     * Busca as ocorrências vinculadas a uma licença específica.
     * @param int $licencaId O ID da licença.
     * @param int $limit O limite de registros.
     * @return array
     */
    public function getOcorrenciasByLicencaId(int $licencaId, int $limit = 5): array
    {
        try {
            $sql = "SELECT * FROM licencas_ocorrencias 
                    WHERE licenca_id = :licenca_id 
                    ORDER BY data_ocorrencia DESC, id DESC 
                    LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':licenca_id', $licencaId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar ocorrências da licença: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Atualiza uma ocorrência registrada (Plano de Ação, Status, etc).
     */
    public function atualizarOcorrencia(array $dados)
    {
        $id = $dados['id'] ?? null;
        if (!$id) return false;

        $sql = "UPDATE licencas_ocorrencias SET 
                    status = :status,
                    prioridade = :prioridade,
                    plano_acao = :plano_acao,
                    responsavel = :responsavel
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => (int)$id,
            'status' => $dados['status'],
            'prioridade' => $dados['prioridade'],
            'plano_acao' => $dados['plano_acao'],
            'responsavel' => $dados['responsavel']
        ]);
    }
}
