<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class TreinamentosModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
    }

    /**
     * Garante que a tabela pivô de participantes exista no banco de dados.
     */
    private function ensureTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS treinamento_participantes (
            treinamento_id INT NOT NULL,
            colaborador_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (treinamento_id, colaborador_id),
            FOREIGN KEY (treinamento_id) REFERENCES treinamentos(id) ON DELETE CASCADE,
            FOREIGN KEY (colaborador_id) REFERENCES colaboradores(colaborador_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela treinamento_participantes: " . $e->getMessage());
        }
    }

    /**
     * Busca todos os treinamentos com paginação.
     */
    public function getAllTreinamentos(int $limit, int $offset, array $filtros = []): array
    {
        try {
            $sql = "SELECT t.* FROM treinamentos t WHERE 1=1";
            $params = [];

            if (!empty($filtros['status'])) {
                $sql .= " AND t.status = :status";
                $params[':status'] = $filtros['status'];
            }

            if (!empty($filtros['search'])) {
                $sql .= " AND (t.nome_treinamento LIKE :search OR t.instrutor LIKE :search)";
                $params[':search'] = '%' . $filtros['search'] . '%';
            }

            $sql .= " ORDER BY t.data_prevista DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $treinamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($treinamentos as &$t) {
                $t['participantes'] = $this->getParticipantesByTreinamentoId($t['id']);
            }

            return $treinamentos;
        } catch (PDOException $e) {
            error_log("Erro ao buscar treinamentos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca a lista de participantes de um treinamento.
     */
    public function getParticipantesByTreinamentoId(int $treinamentoId): array
    {
        try {
            $sql = "SELECT c.colaborador_id as id, c.nome, JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.setor')) as departamento 
                    FROM treinamento_participantes tp
                    JOIN colaboradores c ON tp.colaborador_id = c.colaborador_id
                    WHERE tp.treinamento_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$treinamentoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Conta o total de treinamentos.
     */
    public function getTreinamentosCount(array $filtros = []): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM treinamentos t WHERE 1=1";
            $params = [];

            if (!empty($filtros['status'])) {
                $sql .= " AND t.status = :status";
                $params[':status'] = $filtros['status'];
            }

            if (!empty($filtros['search'])) {
                $sql .= " AND (t.nome_treinamento LIKE :search OR t.instrutor LIKE :search)";
                $params[':search'] = '%' . $filtros['search'] . '%';
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar treinamentos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna estatísticas por status.
     */
    public function getStats(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'Agendado' THEN 1 ELSE 0 END) as Agendado,
                        SUM(CASE WHEN status = 'Realizado' THEN 1 ELSE 0 END) as Realizado,
                        SUM(CASE WHEN status = 'Cancelado' THEN 1 ELSE 0 END) as Cancelado
                    FROM treinamentos";
            return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'Agendado' => 0, 'Realizado' => 0, 'Cancelado' => 0];
        } catch (PDOException $e) {
            return ['total' => 0, 'Agendado' => 0, 'Realizado' => 0, 'Cancelado' => 0];
        }
    }

    /**
     * Busca um treinamento pelo ID.
     */
    public function getTreinamentoById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM treinamentos WHERE id = ?");
            $stmt->execute([$id]);
            $treinamento = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($treinamento) {
                $treinamento['participantes'] = $this->getParticipantesByTreinamentoId($id);
            }
            return $treinamento ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar treinamento por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva o treinamento e sincroniza a lista de participantes.
     */
    public function salvar(array $dados, array $participantes = []): bool
    {
        try {
            $this->db->beginTransaction();

            if (!empty($dados['id'])) {
                $sql = "UPDATE treinamentos SET 
                        nome_treinamento = :nome, data_prevista = :data, status = :status, 
                        instrutor = :instrutor, local = :local, descricao = :desc 
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            } else {
                $sql = "INSERT INTO treinamentos (nome_treinamento, data_prevista, status, instrutor, local, descricao) 
                        VALUES (:nome, :data, :status, :instrutor, :local, :desc)";
                $stmt = $this->db->prepare($sql);
            }

            $stmt->bindValue(':nome', $dados['nome_treinamento']);
            $stmt->bindValue(':data', $dados['data_prevista']);
            $stmt->bindValue(':status', $dados['status']);
            $stmt->bindValue(':instrutor', $dados['instrutor']);
            $stmt->bindValue(':local', $dados['local']);
            $stmt->bindValue(':desc', $dados['descricao']);
            $stmt->execute();

            $treinamentoId = $dados['id'] ?: $this->db->lastInsertId();

            // Sincroniza participantes (tabela pivô)
            $this->db->prepare("DELETE FROM treinamento_participantes WHERE treinamento_id = ?")
                     ->execute([$treinamentoId]);

            if (!empty($participantes)) {
                $stmtPart = $this->db->prepare("INSERT INTO treinamento_participantes (treinamento_id, colaborador_id) VALUES (?, ?)");
                foreach ($participantes as $colabId) {
                    $stmtPart->execute([$treinamentoId, (int)$colabId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Erro TreinamentosModel::salvar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Alias para salvar (mantido por compatibilidade).
     */
    public function salvarTreinamento(array $dados): bool
    {
        return $this->salvar($dados);
    }

    /**
     * Exclui um treinamento e seus vínculos.
     */
    public function excluirTreinamento(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM treinamentos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao excluir treinamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o próximo treinamento agendado.
     */
    public function getProximoTreinamento(): ?array
    {
        try {
            $sql = "SELECT id, nome_treinamento as nome, data_prevista 
                    FROM treinamentos 
                    WHERE status = 'Agendado' AND data_prevista >= CURDATE()
                    ORDER BY data_prevista ASC 
                    LIMIT 1";
            $stmt = $this->db->query($sql);
            $treinamento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($treinamento) {
                $participantes = $this->getParticipantesByTreinamentoId($treinamento['id']);
                $treinamento['participantes'] = implode(', ', array_column($participantes, 'nome'));
            }
            
            return $treinamento ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar próximo treinamento: " . $e->getMessage());
            return null;
        }
    }
}
