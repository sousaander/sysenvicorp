<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class PropostaModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getPropostas(int $limit = 50, int $offset = 0): array
    {
        try {
            $stmt = $this->db->prepare("SELECT p.*, p.nome_proposta as titulo FROM orcamento_proposta p ORDER BY p.id DESC LIMIT :limit OFFSET :offset");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erro ao buscar propostas: ' . $e->getMessage());
            return [];
        }
    }

    public function getPropostaById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM orcamento_proposta WHERE id = ?");
            $stmt->execute([$id]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (\PDOException $e) {
            error_log('Erro ao buscar proposta por id: ' . $e->getMessage());
            return null;
        }
    }

    public function salvarProposta(array $dados): bool
    {
        try {
            $this->db->beginTransaction();

            $id = !empty($dados['id']) ? (int)$dados['id'] : null;

            if ($id) {
                // Antes de atualizar, salva o estado atual no histórico
                $propostaAtual = $this->getPropostaById($id);
                if ($propostaAtual) {
                    $this->salvarHistorico($propostaAtual);
                }

                $sql = "UPDATE orcamento_proposta SET 
                            tipo_criacao = :tipo_criacao, projeto_id = :projeto_id, cliente_id = :cliente_id, nome_proposta = :nome_proposta, 
                            descricao = :descricao, data_proposta = :data_proposta, validade = :validade, responsavel_interno = :responsavel_interno,
                            total_servicos = :total_servicos, total_materiais = :total_materiais, total_final = :total_final, 
                            status = :status, anexos = :anexos
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            } else {
                $sql = "INSERT INTO orcamento_proposta (
                            tipo_criacao, projeto_id, cliente_id, nome_proposta, descricao, data_proposta, validade, 
                            responsavel_interno, total_servicos, total_materiais, total_final, status, anexos
                        ) VALUES (
                            :tipo_criacao, :projeto_id, :cliente_id, :nome_proposta, :descricao, :data_proposta, :validade, 
                            :responsavel_interno, :total_servicos, :total_materiais, :total_final, :status, :anexos
                        )";
                $stmt = $this->db->prepare($sql);
            }

            $stmt->bindValue(':tipo_criacao', $dados['creation_type'] === 'from_project' ? 'vinculado_projeto' : 'zero');
            $stmt->bindValue(':projeto_id', !empty($dados['projeto_id']) ? (int)$dados['projeto_id'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':cliente_id', !empty($dados['cliente_id_scratch']) ? (int)$dados['cliente_id_scratch'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':nome_proposta', $dados['titulo'] ?? '');
            $stmt->bindValue(':descricao', $dados['descricao_tecnica'] ?? null);
            $stmt->bindValue(':data_proposta', !empty($dados['data_proposta']) ? $dados['data_proposta'] : null);
            $stmt->bindValue(':validade', $dados['validade_proposta'] ?? null);
            $stmt->bindValue(':responsavel_interno', !empty($dados['responsavel_interno_id']) ? (int)$dados['responsavel_interno_id'] : null, PDO::PARAM_INT);

            // Valores calculados pelo JS
            $stmt->bindValue(':total_servicos', isset($dados['total_servicos']) ? (float)str_replace(['.', ','], ['', '.'], $dados['total_servicos']) : null);
            $stmt->bindValue(':total_materiais', isset($dados['total_materiais']) ? (float)str_replace(['.', ','], ['', '.'], $dados['total_materiais']) : null);

            // Converte o valor total formatado para float
            $totalFinal = isset($dados['valor_total']) ? (float)str_replace(['.', ','], ['', '.'], $dados['valor_total']) : null;
            $stmt->bindValue(':total_final', $totalFinal);

            $stmt->bindValue(':status', $dados['status'] ?? 'Rascunho');
            $stmt->bindValue(':anexos', null); // Placeholder para futura implementação de anexos

            $success = $stmt->execute();

            if ($success) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao salvar proposta: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Salva um snapshot da proposta na tabela de histórico.
     * @param array $proposta Os dados da proposta a serem versionados.
     * @return bool
     */
    private function salvarHistorico(array $proposta): bool
    {
        $stmtVer = $this->db->prepare("SELECT MAX(versao) FROM orcamento_proposta_historico WHERE proposta_id = ?");
        $stmtVer->execute([$proposta['id']]);
        $ultimaVersao = (int)$stmtVer->fetchColumn();
        $novaVersao = $ultimaVersao + 1;

        $sql = "INSERT INTO orcamento_proposta_historico (proposta_id, versao, usuario_id, dados_proposta_json) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        // Por enquanto, o usuario_id será nulo. No futuro, pegaremos da sessão.
        $usuario_id = $_SESSION['user_id'] ?? null;
        $dadosJson = json_encode($proposta);

        return $stmt->execute([$proposta['id'], $novaVersao, $usuario_id, $dadosJson]);
    }

    /**
     * Busca o histórico de versões de uma proposta.
     * @param int $proposta_id
     * @return array
     */
    public function getHistoricoByPropostaId(int $proposta_id): array
    {
        $stmt = $this->db->prepare("SELECT h.*, u.nome as usuario_nome FROM orcamento_proposta_historico h LEFT JOIN usuarios u ON h.usuario_id = u.id WHERE h.proposta_id = ? ORDER BY h.versao DESC");
        $stmt->execute([$proposta_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um registro de histórico específico pelo seu ID.
     * @param int $historico_id
     * @return array|null
     */
    public function getHistoricoById(int $historico_id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orcamento_proposta_historico WHERE id = ?");
        $stmt->execute([$historico_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca um registro de histórico por ID da proposta e número da versão.
     * @param int $proposta_id
     * @param int $versao
     * @return array|null
     */
    public function getHistoricoByPropostaIdEVersao(int $proposta_id, int $versao): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orcamento_proposta_historico WHERE proposta_id = ? AND versao = ?");
        $stmt->execute([$proposta_id, $versao]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function excluirProposta(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM orcamento_proposta WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log('Erro ao excluir proposta: ' . $e->getMessage());
            return false;
        }
    }
}
