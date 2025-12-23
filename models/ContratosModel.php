<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ContratosModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca dados resumidos sobre a carteira de contratos.
     */
    public function getContratosSummary()
    {
        try {
            $totalVigentes = $this->db->query("SELECT COUNT(*) FROM contratos WHERE status = 'Em Vigência'")->fetchColumn();
            $totalVencidos = $this->db->query("SELECT COUNT(*) FROM contratos WHERE status = 'Em Vigência' AND vencimento < CURDATE()")->fetchColumn();
            $vencendo30dias = $this->db->query("SELECT COUNT(*) FROM contratos WHERE status = 'Em Vigência' AND vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
            $comPendenciaDocs = $this->db->query("SELECT COUNT(*) FROM contratos WHERE status = 'Pendência Assinatura'")->fetchColumn();
            $valorTotalAnual = $this->db->query("SELECT SUM(c.valor) FROM contratos c WHERE c.status = 'Em Vigência'")->fetchColumn();

            return [
                'totalVigentes' => $totalVigentes,
                'totalVencidos' => $totalVencidos,
                'vencendo30dias' => $vencendo30dias,
                'comPendenciaDocs' => $comPendenciaDocs,
                'valorTotalAnual' => 'R$ ' . number_format($valorTotalAnual ?? 0, 2, ',', '.'),
            ];
        } catch (\PDOException $e) {
            error_log("Erro ao buscar resumo de contratos: " . $e->getMessage());
            return ['totalVigentes' => 0, 'totalVencidos' => 0, 'vencendo30dias' => 0, 'comPendenciaDocs' => 0, 'valorTotalAnual' => 'R$ 0,00'];
        }
    }

    /**
     * Busca uma lista de contratos, com suporte para filtros e paginação.
     * @param array $filtros Filtros de busca (para uso futuro)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getContratos(array $filtros = [], int $limit = 5, int $offset = 0): array
    {
        try {
            $sql = "SELECT 
                        c.id, 
                        c.tipo, 
                        COALESCE(cli.nome, forn.razao_social) as parteContratada, 
                        c.valor, 
                        c.vencimento,
                        c.documento_path, 
                        c.status
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                    -- WHERE clause para filtros futuros
                    ORDER BY c.id DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar lista de contratos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o número total de contratos que correspondem a um filtro.
     * @param array $filtros
     * @return int
     */
    public function getContratosCount(array $filtros = []): int
    {
        $sql = "SELECT COUNT(*) FROM contratos c";
        $params = [];

        // Lógica de filtro (para uso futuro)

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca uma lista de contratos associados a uma pessoa (fornecedor).
     * @param int $pessoa_id
     * @return array
     */
    public function getContratosByPessoaId(int $pessoa_id): array
    {
        try {
            $sql = "SELECT 
                        id, 
                        objeto, 
                        valor, 
                        vencimento,
                        documento_path, 
                        status
                    FROM contratos
                    WHERE pessoa_id = :pessoa_id
                    ORDER BY vencimento DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':pessoa_id', $pessoa_id, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contratos por pessoa_id: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um contrato específico pelo ID.
     * @param int $id O ID do contrato.
     * @return array|null
     */
    public function getContratoById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM contratos WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contrato por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um contrato específico pelo ID com todos os dados detalhados.
     * @param int $id O ID do contrato.
     * @return array|null
     */
    public function getContratoDetalhadoById(int $id): ?array
    {
        try {
            $sql = "SELECT c.*, COALESCE(cli.nome, forn.razao_social) as parteContratada
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                    WHERE c.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contrato detalhado por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca todos os aditivos de um contrato específico.
     * @param int $contrato_id
     * @return array
     */
    public function getAditivosByContratoId(int $contrato_id): array
    {
        try {
            $sql = "SELECT * FROM contratos_aditivos WHERE contrato_id = ? ORDER BY data_aditivo DESC, id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$contrato_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar aditivos do contrato: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um aditivo específico pelo ID.
     * @param int $id
     * @return array|null
     */
    public function getAditivoById(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM contratos_aditivos WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar aditivo por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva um novo aditivo no banco de dados.
     * @param array $dados
     * @return bool
     */
    public function salvarAditivo(array $dados): bool
    {
        $this->db->beginTransaction();

        try {
            // 1. Inserir o aditivo
            $sqlAditivo = "INSERT INTO contratos_aditivos 
                    (contrato_id, tipo_aditivo, data_aditivo, descricao, valor_alteracao, novo_vencimento, documento_path) 
                VALUES 
                    (:contrato_id, :tipo_aditivo, :data_aditivo, :descricao, :valor_alteracao, :novo_vencimento, :documento_path)";

            $stmtAditivo = $this->db->prepare($sqlAditivo);
            $stmtAditivo->bindValue(':contrato_id', $dados['contrato_id'], \PDO::PARAM_INT);
            $stmtAditivo->bindValue(':tipo_aditivo', $dados['tipo_aditivo']);
            $stmtAditivo->bindValue(':data_aditivo', $dados['data_aditivo']);
            $stmtAditivo->bindValue(':descricao', $dados['descricao']);
            $stmtAditivo->bindValue(':valor_alteracao', $dados['valor_alteracao'], $dados['valor_alteracao'] !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $stmtAditivo->bindValue(':novo_vencimento', $dados['novo_vencimento'], $dados['novo_vencimento'] !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $stmtAditivo->bindValue(':documento_path', $dados['documento_path'] ?? null, \PDO::PARAM_STR);

            if (!$stmtAditivo->execute()) {
                $this->db->rollBack();
                return false;
            }

            // 2. Atualizar o contrato principal, se necessário
            $updates = [];
            $params = [':contrato_id' => $dados['contrato_id']];

            if (!empty($dados['novo_vencimento'])) {
                $updates[] = "vencimento = :novo_vencimento";
                $params[':novo_vencimento'] = $dados['novo_vencimento'];
            }
            if (!empty($dados['valor_alteracao'])) {
                // Adiciona o valor do aditivo ao valor existente do contrato
                $updates[] = "valor = valor + :valor_alteracao";
                $params[':valor_alteracao'] = $dados['valor_alteracao'];
            }

            if (!empty($updates)) {
                $sqlContrato = "UPDATE contratos SET " . implode(', ', $updates) . " WHERE id = :contrato_id";
                $stmtContrato = $this->db->prepare($sqlContrato);
                $stmtContrato->execute($params);
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao salvar aditivo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Salva um novo contrato ou atualiza um existente no banco de dados.
     * @param array $dados Os dados do contrato a serem salvos.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function salvarContrato(array $dados): bool // A assinatura permanece, mas o comportamento muda
    {
        // Sanitiza e prepara os dados
        $id = !empty($dados['id']) ? (int)$dados['id'] : null;
        // CORREÇÃO: Separamos cliente_id de pessoa_id (fornecedor)
        $cliente_id = (!empty($dados['cliente_id']) && $dados['tipo'] === 'Venda') ? (int)$dados['cliente_id'] : null;
        $pessoa_id = (!empty($dados['pessoa_id']) && $dados['tipo'] === 'Compra') ? (int)$dados['pessoa_id'] : null;

        $projeto_id = !empty($dados['projeto_id']) ? (int)$dados['projeto_id'] : null;
        $objeto = trim($dados['objeto'] ?? ''); // 'titulo' na sua tabela antiga
        $tipo = $dados['tipo'] ?? null;
        $status = $dados['status'] ?? 'Em Vigência';
        $data_inicio = !empty($dados['data_inicio']) ? $dados['data_inicio'] : null;
        $vencimento = !empty($dados['vencimento']) ? $dados['vencimento'] : null;
        $valor = !empty($dados['valor']) ? (float)$dados['valor'] : 0.0;
        $documento_path = $dados['documento_path'] ?? null;

        try {
            if ($id) {
                // UPDATE: Atualiza um contrato existente
                // Adiciona a atualização do documento apenas se um novo foi enviado
                $documentoSql = $documento_path ? ", documento_path = :documento_path" : "";

                $sql = "UPDATE contratos SET 
                            cliente_id = :cliente_id, pessoa_id = :pessoa_id, projeto_id = :projeto_id, objeto = :objeto, tipo = :tipo, status = :status, 
                            data_inicio = :data_inicio, vencimento = :vencimento, valor = :valor {$documentoSql}
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            } else {
                // INSERT: Cria um novo contrato
                $sql = "INSERT INTO contratos (cliente_id, pessoa_id, projeto_id, objeto, tipo, status, data_inicio, vencimento, valor, documento_path, dataCriacao) 
                        VALUES (:cliente_id, :pessoa_id, :projeto_id, :objeto, :tipo, :status, :data_inicio, :vencimento, :valor, :documento_path, NOW())";
                $stmt = $this->db->prepare($sql);
            }

            $stmt->bindValue(':cliente_id', $cliente_id, $cliente_id ? \PDO::PARAM_INT : \PDO::PARAM_NULL);
            $stmt->bindValue(':pessoa_id', $pessoa_id, $pessoa_id ? \PDO::PARAM_INT : \PDO::PARAM_NULL);
            $stmt->bindValue(':projeto_id', $projeto_id, $projeto_id ? \PDO::PARAM_INT : \PDO::PARAM_NULL);
            $stmt->bindParam(':objeto', $objeto);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':status', $status);
            $stmt->bindValue(':data_inicio', $data_inicio, $data_inicio ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $stmt->bindValue(':vencimento', $vencimento, $vencimento ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $stmt->bindValue(':valor', $valor, \PDO::PARAM_STR); // Usar STR para decimais

            // Faz o bind do documento para INSERT ou para UPDATE (se houver um novo)
            if ($id && $documento_path) { // Apenas para UPDATE se houver novo doc
                $stmt->bindValue(':documento_path', $documento_path, \PDO::PARAM_STR);
            } elseif (!$id) { // Para INSERT, faz o bind (pode ser nulo)
                $stmt->bindValue(':documento_path', $documento_path, $documento_path ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            }

            return $stmt->execute();
        } catch (\PDOException $e) {
            // Lança a exceção para que o Controller possa capturá-la
            // e exibir uma mensagem de erro mais detalhada.
            throw $e;
        }
    }

    /**
     * Exclui um aditivo e reverte suas alterações no contrato principal.
     * @param int $aditivoId
     * @return bool
     */
    public function excluirAditivo(int $aditivoId): bool
    {
        $aditivo = $this->getAditivoById($aditivoId);
        if (!$aditivo) {
            return false; // Aditivo não existe
        }

        $this->db->beginTransaction();
        try {
            // 1. Reverter as alterações no contrato principal
            $updates = [];
            $params = [':contrato_id' => $aditivo['contrato_id']];

            if (!empty($aditivo['valor_alteracao'])) {
                $updates[] = "valor = valor - :valor_alteracao";
                $params[':valor_alteracao'] = $aditivo['valor_alteracao'];
            }

            // Se o vencimento foi alterado por este aditivo, precisamos encontrar o vencimento anterior
            $vencimentoFinalContrato = $this->getUltimoVencimentoContrato($aditivo['contrato_id'], $aditivoId);
            $updates[] = "vencimento = :vencimento_final";
            $params[':vencimento_final'] = $vencimentoFinalContrato;

            if (!empty($updates)) {
                $sqlContrato = "UPDATE contratos SET " . implode(', ', $updates) . " WHERE id = :contrato_id";
                $this->db->prepare($sqlContrato)->execute($params);
            }

            // 2. Excluir o registro do aditivo
            $stmtDelete = $this->db->prepare("DELETE FROM contratos_aditivos WHERE id = :id");
            $stmtDelete->execute([':id' => $aditivoId]);

            // Opcional: Excluir o arquivo físico do aditivo
            if (!empty($aditivo['documento_path'])) {
                $filePath = ROOT_PATH . '/storage/contratos/aditivos/' . $aditivo['documento_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Erro na transação ao excluir aditivo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o último vencimento válido de um contrato, com base nos aditivos.
     * @param int $contratoId
     * @param int|null $excludeAditivoId ID de um aditivo a ser ignorado na busca (útil para exclusão).
     * @return string
     */
    private function getUltimoVencimentoContrato(int $contratoId, ?int $excludeAditivoId = null): string
    {
        $excludeSql = $excludeAditivoId ? "AND id != " . (int)$excludeAditivoId : "";
        $stmt = $this->db->query("SELECT novo_vencimento FROM contratos_aditivos WHERE contrato_id = {$contratoId} AND novo_vencimento IS NOT NULL {$excludeSql} ORDER BY data_aditivo DESC, id DESC LIMIT 1");
        $ultimoVencimentoAditivo = $stmt->fetchColumn();

        if ($ultimoVencimentoAditivo) {
            return $ultimoVencimentoAditivo;
        }

        // Se nenhum aditivo define o vencimento, busca o do contrato original
        $stmtContrato = $this->db->query("SELECT vencimento FROM contratos WHERE id = {$contratoId}");
        return $stmtContrato->fetchColumn();
    }

    /**
     * Busca contratos com base no seu status de vigência.
     * @param string $categoria Categoria de vigência ('vencidos', 'vencendo_30', etc.)
     * @return array
     */
    public function getContratosPorVigencia(string $categoria): array
    {
        $whereClause = "";
        switch ($categoria) {
            case 'vencidos':
                $whereClause = "c.vencimento < CURDATE() AND c.status = 'Em Vigência'";
                break;
            case 'vencendo_30':
                $whereClause = "c.vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND c.status = 'Em Vigência'";
                break;
            case 'vencendo_60':
                $whereClause = "c.vencimento BETWEEN DATE_ADD(CURDATE(), INTERVAL 31 DAY) AND DATE_ADD(CURDATE(), INTERVAL 60 DAY) AND c.status = 'Em Vigência'";
                break;
            case 'vencendo_90':
                $whereClause = "c.vencimento BETWEEN DATE_ADD(CURDATE(), INTERVAL 61 DAY) AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND c.status = 'Em Vigência'";
                break;
            case 'vigencia_longa':
                $whereClause = "c.vencimento > DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND c.status = 'Em Vigência'";
                break;
            default:
                return []; // Categoria inválida
        }

        try {
            $sql = "SELECT 
                        c.id, 
                        c.tipo, 
                        COALESCE(cli.nome, forn.razao_social) as parteContratada, 
                        c.valor, 
                        c.data_inicio,
                        c.vencimento,
                        DATEDIFF(c.vencimento, CURDATE()) as dias_para_vencer,
                        c.status
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                    WHERE {$whereClause}
                    ORDER BY c.vencimento ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contratos por vigência ({$categoria}): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca todas as obrigações de um contrato.
     * @param int $contrato_id
     * @return array
     */
    public function getObrigacoesByContratoId(int $contrato_id): array
    {
        try {
            $sql = "SELECT * FROM contrato_obrigacoes WHERE contrato_id = ? ORDER BY data_prevista ASC, id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$contrato_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar obrigações do contrato: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva uma nova obrigação contratual.
     * @param array $dados
     * @return bool
     */
    public function salvarObrigacao(array $dados): bool
    {
        $sql = "INSERT INTO contrato_obrigacoes (contrato_id, descricao, tipo_clausula, responsavel, data_prevista, status) 
                VALUES (:contrato_id, :descricao, :tipo_clausula, :responsavel, :data_prevista, 'Pendente')";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':contrato_id', $dados['contrato_id'], \PDO::PARAM_INT);
            $stmt->bindValue(':descricao', $dados['descricao']);
            $stmt->bindValue(':tipo_clausula', $dados['tipo_clausula']);
            $stmt->bindValue(':responsavel', $dados['responsavel'] ?: null);
            $stmt->bindValue(':data_prevista', $dados['data_prevista'] ?: null);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro ao salvar obrigação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o status de uma obrigação.
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatusObrigacao(int $id, string $status): bool
    {
        $sql = "UPDATE contrato_obrigacoes SET status = :status WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro ao atualizar status da obrigação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui uma obrigação.
     * @param int $id
     * @return bool
     */
    public function excluirObrigacao(int $id): bool
    {
        $sql = "DELETE FROM contrato_obrigacoes WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro ao excluir obrigação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca uma lista de contratos ativos para a tela de gestão de obrigações.
     * @return array
     */
    public function getContratosAtivosParaObrigacoes(): array
    {
        try {
            $sql = "SELECT 
                        c.id, 
                        c.objeto,
                        c.tipo, 
                        COALESCE(cli.nome, forn.razao_social) as parteContratada, 
                        c.vencimento,
                        (SELECT COUNT(*) FROM contrato_obrigacoes co WHERE co.contrato_id = c.id) as total_obrigacoes,
                        (SELECT COUNT(*) FROM contrato_obrigacoes co WHERE co.contrato_id = c.id AND co.status = 'Concluída') as obrigacoes_concluidas
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                    WHERE c.status = 'Em Vigência'
                    ORDER BY c.vencimento ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contratos ativos para obrigações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca uma lista de contratos ativos para a tela de gestão financeira.
     * @return array
     */
    public function getContratosAtivosParaFinanceiro(): array
    {
        try {
            $sql = "SELECT 
                        c.id, 
                        c.objeto,
                        c.tipo, 
                        COALESCE(cli.nome, forn.razao_social) as parteContratada, 
                        c.vencimento,
                        (SELECT SUM(cp.valor) FROM contrato_parcelas cp WHERE cp.contrato_id = c.id) as valor_previsto
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                    WHERE c.status = 'Em Vigência'
                    ORDER BY c.vencimento ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contratos ativos para financeiro: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca todas as parcelas de um contrato.
     * @param int $contrato_id
     * @return array
     */
    public function getParcelasByContratoId(int $contrato_id): array
    {
        try {
            $sql = "SELECT * FROM contrato_parcelas WHERE contrato_id = ? ORDER BY data_vencimento ASC, id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$contrato_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar parcelas do contrato: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca uma parcela específica pelo ID.
     * @param int $id
     * @return array|null
     */
    public function getParcelaById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM contrato_parcelas WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar parcela por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva uma nova parcela de contrato.
     * @param array $dados
     * @return bool
     */
    public function salvarParcela(array $dados): bool
    {
        $this->db->beginTransaction();
        try {
            // 1. Inserir a nova parcela
            $sqlParcela = "INSERT INTO contrato_parcelas (contrato_id, descricao, valor, data_vencimento, status) 
                           VALUES (:contrato_id, :descricao, :valor, :data_vencimento, 'Pendente')";
            $stmt = $this->db->prepare($sqlParcela);
            $stmt->bindValue(':contrato_id', $dados['contrato_id'], \PDO::PARAM_INT);
            $stmt->bindValue(':descricao', $dados['descricao']);
            $stmt->bindValue(':valor', $dados['valor']);
            $stmt->bindValue(':data_vencimento', $dados['data_vencimento']);

            if (!$stmt->execute()) {
                $this->db->rollBack();
                return false;
            }

            // 2. Atualizar o valor total do contrato principal
            $sqlSoma = "SELECT SUM(valor) FROM contrato_parcelas WHERE contrato_id = :contrato_id";
            $stmtSoma = $this->db->prepare($sqlSoma);
            $stmtSoma->execute([':contrato_id' => $dados['contrato_id']]);
            $novoValorTotal = $stmtSoma->fetchColumn();

            $sqlContrato = "UPDATE contratos SET valor = :valor WHERE id = :contrato_id";
            $this->db->prepare($sqlContrato)->execute([':valor' => $novoValorTotal, ':contrato_id' => $dados['contrato_id']]);

            return $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao salvar parcela: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vincula uma transação financeira a uma parcela de contrato.
     * @param int $parcelaId
     * @param int $transacaoId
     * @return bool
     */
    public function vincularTransacao(int $parcelaId, int $transacaoId): bool
    {
        $sql = "UPDATE contrato_parcelas SET transacao_id = :transacao_id, status = 'Lançada' WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':transacao_id', $transacaoId, \PDO::PARAM_INT);
            $stmt->bindValue(':id', $parcelaId, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro ao vincular transação à parcela: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca os contratos ativos para a tela de Compliance, incluindo os campos jurídicos.
     *
     * @return array
     */
    public function getContratosParaCompliance(): array
    {
        // Esta query é similar a outras que buscam contratos, mas seleciona os novos campos.
        // Ela junta com clientes e fornecedores para obter o nome da 'parteContratada'.
        $sql = "SELECT 
                    c.id, 
                    c.objeto, 
                    c.tipo,
                    c.clausula_lgpd,
                    c.risco_contratual,
                    COALESCE(cli.nome, forn.razao_social) as parteContratada
                FROM contratos c
                LEFT JOIN clientes cli ON c.cliente_id = cli.id
                LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                WHERE c.status = 'Em Vigência'
                ORDER BY c.data_inicio DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contratos para compliance: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva os dados de compliance (cláusula LGPD, risco, parecer) de um contrato.
     *
     * @param array $dados
     * @return bool
     */
    public function salvarDadosCompliance(array $dados): bool
    {
        $sql = "UPDATE contratos SET
                    clausula_lgpd = :clausula_lgpd,
                    risco_contratual = :risco_contratual,
                    parecer_juridico = :parecer_juridico
                WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($dados);
        } catch (\PDOException $e) {
            error_log("Erro ao salvar dados de compliance: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Conta o número de contratos agrupados por status.
     * @return array
     */
    public function getContratosCountByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as total 
                FROM contratos 
                GROUP BY status 
                ORDER BY total DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao contar contratos por status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o número de contratos agrupados por tipo.
     * @return array
     */
    public function getContratosCountByType(): array
    {
        $sql = "SELECT tipo, COUNT(*) as total 
                FROM contratos 
                GROUP BY tipo 
                ORDER BY total DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao contar contratos por tipo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Soma os valores dos contratos, agrupados por tipo.
     * @return array
     */
    public function getContratosSumValorByType(): array
    {
        $sql = "SELECT tipo, SUM(valor) as total_valor 
                FROM contratos 
                WHERE status = 'Em Vigência'
                GROUP BY tipo 
                ORDER BY total_valor DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao somar valores de contratos por tipo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna um resumo do status de todas as obrigações contratuais.
     * @return array
     */
    public function getObrigacoesSummary(): array
    {
        $sql = "SELECT status, COUNT(*) as total FROM contrato_obrigacoes GROUP BY status";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar resumo de obrigações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca todos os contratos vigentes com os dados necessários para relatórios.
     * @return array
     */
    public function getTodosContratosParaRelatorio(): array
    {
        try {
            $sql = "SELECT 
                        c.id, 
                        c.objeto,
                        c.tipo, 
                        COALESCE(cli.nome, forn.razao_social) as parteContratada, 
                        c.valor,
                        c.data_inicio,
                        c.vencimento,
                        c.status
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                    WHERE c.status = 'Em Vigência'
                    ORDER BY c.vencimento ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar todos os contratos para relatório: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Exclui um contrato do banco de dados.
     *
     * @param int $id O ID do contrato a ser excluído.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function excluirContrato(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM contratos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir contrato: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o caminho do documento de um contrato específico.
     *
     * @param int $contratoId O ID do contrato a ser atualizado.
     * @param string $documentoPath O novo nome do arquivo do documento.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function updateDocumentoPath(int $contratoId, string $documentoPath): bool
    {
        // Primeiro, busca o caminho do documento antigo para poder excluí-lo depois.
        $stmt = $this->db->prepare("SELECT documento_path FROM contratos WHERE id = :id");
        $stmt->execute(['id' => $contratoId]);
        $contratoAntigo = $stmt->fetch();

        // Atualiza o banco de dados com o novo caminho.
        $stmtUpdate = $this->db->prepare("UPDATE contratos SET documento_path = :documento_path WHERE id = :id");
        $success = $stmtUpdate->execute([
            'documento_path' => $documentoPath,
            'id' => $contratoId
        ]);

        // Se a atualização foi bem-sucedida e existia um arquivo antigo, remove-o do servidor.
        if ($success && $contratoAntigo && !empty($contratoAntigo['documento_path'])) {
            $oldFilePath = ROOT_PATH . '/storage/contratos/' . $contratoAntigo['documento_path'];
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        return $success;
    }

    /**
     * Remove o documento de um contrato, apagando o arquivo físico e limpando o campo no banco.
     *
     * @param int $contratoId O ID do contrato.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function removerDocumento(int $contratoId): bool
    {
        // Busca o caminho do documento para poder excluí-lo.
        $stmt = $this->db->prepare("SELECT documento_path FROM contratos WHERE id = :id");
        $stmt->execute(['id' => $contratoId]);
        $contrato = $stmt->fetch();

        if ($contrato && !empty($contrato['documento_path'])) {
            $filePath = ROOT_PATH . '/storage/contratos/' . $contrato['documento_path'];

            // Apaga o arquivo físico se ele existir.
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Atualiza o banco de dados, definindo o caminho do documento como NULL.
        $stmtUpdate = $this->db->prepare("UPDATE contratos SET documento_path = NULL WHERE id = :id");
        return $stmtUpdate->execute(['id' => $contratoId]);
    }

    /**
     * Busca contratos com base em filtros de compliance específicos.
     *
     * @param array $filtros Ex: ['clausula_lgpd' => 'Não', 'risco_contratual' => 'Alto']
     * @return array
     */
    public function getContratosPorFiltroCompliance(array $filtros): array
    {
        $sql = "SELECT 
                    c.id, 
                    c.objeto, 
                    c.tipo, 
                    COALESCE(cli.nome, forn.razao_social) as parteContratada,
                    c.clausula_lgpd,
                    c.risco_contratual
                FROM contratos c
                LEFT JOIN clientes cli ON c.cliente_id = cli.id
                LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                WHERE c.status = 'Em Vigência'";

        if (!empty($filtros['clausula_lgpd'])) {
            $sql .= " AND c.clausula_lgpd = :clausula_lgpd";
        }
        if (!empty($filtros['risco_contratual'])) {
            $sql .= " AND c.risco_contratual = :risco_contratual";
        }
        // Adicionar mais filtros aqui se necessário

        $sql .= " ORDER BY c.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($filtros);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
