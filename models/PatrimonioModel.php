<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class PatrimonioModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca dados resumidos de bens e ativos.
     */
    public function getAssetsSummary()
    {
        try {
            $totalAtivos = $this->db->query("SELECT COUNT(*) FROM patrimonio_bens WHERE status = 'Ativo'")->fetchColumn();
            $bensBaixadosAno = $this->db->query("SELECT COUNT(*) FROM patrimonio_movimentacoes WHERE tipo_movimentacao = 'Baixa' AND YEAR(data_movimentacao) = YEAR(CURDATE())")->fetchColumn();

            // Reutiliza a função de cálculo de depreciação para obter o valor contábil total
            $bensComDepreciacao = $this->getBensComDepreciacao();
            $valorContabilTotal = array_sum(array_column($bensComDepreciacao, 'valor_contabil'));
            $totalDepreciaveis = count($bensComDepreciacao);

            return [
                'totalAtivos' => (int) $totalAtivos,
                'valorContabilTotal' => (float) $valorContabilTotal,
                'bensBaixadosAno' => (int) $bensBaixadosAno,
                'totalDepreciaveis' => (int) $totalDepreciaveis,
            ];
        } catch (\PDOException $e) {
            error_log("Erro ao buscar resumo de patrimônio: " . $e->getMessage());
            // Retorna valores zerados em caso de erro
            return [
                'totalAtivos' => 0,
                'valorContabilTotal' => 0.00,
                'bensBaixadosAno' => 0,
                'totalDepreciaveis' => 0,
            ];
        }
    }

    /**
     * Busca uma lista dos últimos ativos adicionados.
     */
    public function getRecentementeAdicionados(int $limit = 3, int $offset = 0): array
    {
        try {
            $sql = "SELECT id, nome, classificacao, localizacao, data_aquisicao FROM patrimonio_bens ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar bens recém-adicionados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o número total de bens cadastrados.
     * @return int
     */
    public function getBensCount(): int
    {
        try {
            return (int) $this->db->query("SELECT COUNT(*) FROM patrimonio_bens")->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar bens: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Busca um bem específico pelo seu ID.
     * @param int $id O ID do bem.
     * @return array|null
     */
    public function getBemById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM patrimonio_bens WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar bem por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Exclui um bem do banco de dados.
     * @param int $id O ID do bem a ser excluído.
     * @return bool
     */
    public function excluirBem(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM patrimonio_bens WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir bem: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Salva um novo bem ou atualiza um existente no banco de dados.
     *
     * @param array $dados Os dados do bem vindos do formulário.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function salvarBem(array $dados): bool
    {
        // Sanitiza e prepara os dados
        $id = !empty($dados['id']) ? (int)$dados['id'] : null;
        $nome = trim($dados['nome'] ?? '');
        $numero_patrimonio = trim($dados['numero_patrimonio'] ?? '');
        $classificacao = trim($dados['classificacao'] ?? '');
        $localizacao = trim($dados['localizacao'] ?? '');
        $responsavel = trim($dados['responsavel'] ?? '');
        $observacoes = trim($dados['observacoes'] ?? '');
        // Novos campos
        $data_aquisicao = !empty($dados['data_aquisicao']) ? $dados['data_aquisicao'] : null;

        // CORREÇÃO: Trata o valor monetário corretamente, convertendo vírgula para ponto
        // e removendo pontos de milhar, antes de converter para float.
        $valor_aquisicao_str = $dados['valor_aquisicao'] ?? '0';
        // Remove pontos de milhar
        $valor_aquisicao_str = str_replace('.', '', $valor_aquisicao_str);
        // Substitui a vírgula decimal por ponto
        $valor_aquisicao_str = str_replace(',', '.', $valor_aquisicao_str);
        $valor_aquisicao = !empty($valor_aquisicao_str) ? (float)$valor_aquisicao_str : null;

        $vida_util_meses = !empty($dados['vida_util_meses']) ? (int)$dados['vida_util_meses'] : null;
        $centro_custo = trim($dados['centro_custo'] ?? '');

        try {
            if ($id) {
                // UPDATE: Atualiza um bem existente
                $sql = "UPDATE patrimonio_bens 
                        SET nome = :nome, numero_patrimonio = :numero_patrimonio, classificacao = :classificacao, 
                            localizacao = :localizacao, responsavel = :responsavel, observacoes = :observacoes,
                            data_aquisicao = :data_aquisicao, valor_aquisicao = :valor_aquisicao, 
                            vida_util_meses = :vida_util_meses, centro_custo = :centro_custo
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            } else {
                // INSERT: Cria um novo bem
                $sql = "INSERT INTO patrimonio_bens (nome, numero_patrimonio, classificacao, localizacao, responsavel, observacoes, data_aquisicao, valor_aquisicao, vida_util_meses, centro_custo) 
                        VALUES (:nome, :numero_patrimonio, :classificacao, :localizacao, :responsavel, :observacoes, :data_aquisicao, :valor_aquisicao, :vida_util_meses, :centro_custo)";
                $stmt = $this->db->prepare($sql);
            }

            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':numero_patrimonio', $numero_patrimonio);
            $stmt->bindParam(':classificacao', $classificacao);
            $stmt->bindParam(':localizacao', $localizacao);
            $stmt->bindParam(':responsavel', $responsavel);
            $stmt->bindParam(':observacoes', $observacoes);
            // Bind dos novos campos
            $stmt->bindValue(':data_aquisicao', $data_aquisicao);
            $stmt->bindValue(':valor_aquisicao', $valor_aquisicao);
            $stmt->bindValue(':vida_util_meses', $vida_util_meses, PDO::PARAM_INT);
            $stmt->bindValue(':centro_custo', $centro_custo);

            return $stmt->execute();
        } catch (\PDOException $e) {
            throw $e; // Lança a exceção para o Controller capturar e exibir uma mensagem.
        }
    }

    /**
     * Busca todos os bens cadastrados para usar em selects.
     * @return array
     */
    public function getAllBens(): array
    {
        try {
            // Filtra para mostrar apenas bens que não foram baixados
            $stmt = $this->db->query("SELECT id, nome, numero_patrimonio FROM patrimonio_bens WHERE status != 'Baixado' ORDER BY nome ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar todos os bens: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca o histórico de movimentações de bens.
     * @return array
     */
    public function getMovimentacoes(): array
    {
        try {
            $sql = "SELECT 
                        m.id,
                        m.data_movimentacao,
                        m.tipo_movimentacao,
                        m.destino,
                        m.responsavel_retirada,
                        b.nome as nome_bem,
                        b.numero_patrimonio
                    FROM patrimonio_movimentacoes m
                    JOIN patrimonio_bens b ON m.bem_id = b.id
                    ORDER BY m.data_movimentacao DESC, m.id DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar movimentações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva uma nova movimentação e atualiza o status/local do bem.
     * Usa uma transação para garantir a consistência dos dados.
     *
     * @param array $dados
     * @return bool
     */
    public function salvarMovimentacao(array $dados): bool
    {
        $this->db->beginTransaction();

        try {
            // 1. Inserir o registro na tabela de movimentações
            $sqlMov = "INSERT INTO patrimonio_movimentacoes 
                        (bem_id, tipo_movimentacao, data_movimentacao, destino, responsavel_retirada, data_devolucao, motivo_baixa, observacoes) 
                       VALUES 
                        (:bem_id, :tipo_movimentacao, :data_movimentacao, :destino, :responsavel_retirada, :data_devolucao, :motivo_baixa, :observacoes)";

            $stmtMov = $this->db->prepare($sqlMov);
            $stmtMov->bindValue(':bem_id', $dados['bem_id'], PDO::PARAM_INT);
            $stmtMov->bindValue(':tipo_movimentacao', $dados['tipo_movimentacao']);
            $stmtMov->bindValue(':data_movimentacao', $dados['data_movimentacao']);
            $stmtMov->bindValue(':destino', $dados['destino'] ?? null);
            $stmtMov->bindValue(':responsavel_retirada', $dados['responsavel_retirada'] ?? null);
            $stmtMov->bindValue(':data_devolucao', !empty($dados['data_devolucao']) ? $dados['data_devolucao'] : null);
            $stmtMov->bindValue(':motivo_baixa', $dados['motivo_baixa'] ?? null);
            $stmtMov->bindValue(':observacoes', $dados['observacoes'] ?? null);
            $stmtMov->execute();

            // 2. Atualizar a tabela de bens conforme o tipo de movimentação
            $bem_id = $dados['bem_id'];
            $tipo_movimentacao = $dados['tipo_movimentacao'];

            if ($tipo_movimentacao === 'Transferência') {
                $sqlBem = "UPDATE patrimonio_bens SET localizacao = :localizacao, responsavel = :responsavel WHERE id = :id";
                $stmtBem = $this->db->prepare($sqlBem);
                $stmtBem->bindValue(':localizacao', $dados['destino']);
                $stmtBem->bindValue(':responsavel', $dados['responsavel_retirada']);
                $stmtBem->bindValue(':id', $bem_id, PDO::PARAM_INT);
                $stmtBem->execute();
            } elseif ($tipo_movimentacao === 'Baixa') {
                $sqlBem = "UPDATE patrimonio_bens SET status = 'Baixado' WHERE id = :id";
                $stmtBem = $this->db->prepare($sqlBem);
                $stmtBem->bindValue(':id', $bem_id, PDO::PARAM_INT);
                $stmtBem->execute();
            }
            // Para 'Empréstimo', não alteramos o status principal do bem, apenas registramos a movimentação.

            // Se tudo correu bem, confirma a transação
            return $this->db->commit();
        } catch (\Exception $e) {
            // Se algo deu errado, desfaz tudo
            $this->db->rollBack();
            error_log("Erro na transação de movimentação de bem: " . $e->getMessage());
            throw $e; // Lança a exceção para o controller
        }
    }

    /**
     * Busca todos os bens e calcula a depreciação para cada um.
     * @return array
     */
    public function getBensComDepreciacao(int $limit = 3, int $offset = 0): array
    {
        try {
            // Busca todos os bens que são depreciáveis (ex: não baixados e com valor)
            // CORREÇÃO: Cláusulas ORDER BY, LIMIT e OFFSET movidas para o final da query.
            // CORREÇÃO: Uso de prepare() e bindValue() para segurança com os parâmetros de paginação.
            $sql = "SELECT id, nome, numero_patrimonio, data_aquisicao, valor_aquisicao, vida_util_meses, valor_residual 
                    FROM patrimonio_bens 
                    WHERE status != 'Baixado' AND valor_aquisicao > 0 AND vida_util_meses > 0
                    ORDER BY nome
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $bens = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calcula a depreciação para cada bem
            foreach ($bens as &$bem) {
                $dataAquisicao = new \DateTime($bem['data_aquisicao']);
                $hoje = new \DateTime();
                $intervalo = $hoje->diff($dataAquisicao);
                $mesesDesdeAquisicao = ($intervalo->y * 12) + $intervalo->m;

                // Garante que não vamos depreciar além da vida útil
                $mesesParaDepreciar = min($mesesDesdeAquisicao, $bem['vida_util_meses']);

                $baseCalculo = $bem['valor_aquisicao'] - $bem['valor_residual'];
                $depreciacaoMensal = $baseCalculo / $bem['vida_util_meses'];

                $depreciacaoAcumulada = $depreciacaoMensal * $mesesParaDepreciar;
                $valorContabil = $bem['valor_aquisicao'] - $depreciacaoAcumulada;

                $bem['depreciacao_mensal'] = $depreciacaoMensal;
                $bem['depreciacao_acumulada'] = $depreciacaoAcumulada;
                $bem['valor_contabil'] = $valorContabil;
            }

            return $bens;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar e calcular depreciação: " . $e->getMessage());
            // Retorna um mock simples em caso de erro de coluna inexistente
            return [
                [
                    'id' => 1,
                    'nome' => 'Exemplo: Servidor Dell (Erro de BD)',
                    'numero_patrimonio' => '001',
                    'data_aquisicao' => '2022-01-01',
                    'valor_aquisicao' => 10000,
                    'vida_util_meses' => 60,
                    'valor_residual' => 1000,
                    'depreciacao_mensal' => 150,
                    'depreciacao_acumulada' => 5400,
                    'valor_contabil' => 4600
                ]
            ];
        }
    }

    /**
     * Salva uma nova reavaliação de valor de mercado para um bem.
     * @param array $dados
     * @return bool
     */
    public function salvarReavaliacao(array $dados): bool
    {
        $this->db->beginTransaction();
        try {
            // 1. Insere o registro na tabela de reavaliações
            $sqlReav = "INSERT INTO patrimonio_reavaliacoes (bem_id, data_reavaliacao, valor_mercado, observacoes) 
                        VALUES (:bem_id, :data_reavaliacao, :valor_mercado, :observacoes)";
            $stmtReav = $this->db->prepare($sqlReav);

            $valorLimpo = (float) str_replace(['.', ','], ['', '.'], $dados['novo_valor']);

            $stmtReav->bindValue(':bem_id', $dados['bem_id'], PDO::PARAM_INT);
            $stmtReav->bindValue(':data_reavaliacao', $dados['data_reavaliacao']);
            $stmtReav->bindValue(':valor_mercado', $valorLimpo);
            $stmtReav->bindValue(':observacoes', $dados['observacoes'] ?? null);
            $stmtReav->execute();

            // 2. Atualiza o valor contábil do bem para o novo valor de mercado (ajuste contábil)
            // Nota: Em um sistema contábil real, isso geraria lançamentos complexos.
            // Aqui, estamos simplificando e ajustando o valor de aquisição para refletir a reavaliação.
            $sqlBem = "UPDATE patrimonio_bens SET valor_aquisicao = :valor_aquisicao, data_aquisicao = :data_aquisicao WHERE id = :id";
            $stmtBem = $this->db->prepare($sqlBem);
            $stmtBem->bindValue(':valor_aquisicao', $valorLimpo);
            $stmtBem->bindValue(':data_aquisicao', $dados['data_reavaliacao']); // A depreciação recomeça a partir desta data
            $stmtBem->bindValue(':id', $dados['bem_id'], PDO::PARAM_INT);
            $stmtBem->execute();

            return $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro na transação de reavaliação: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca todos os bens ativos para a lista de checagem do inventário.
     * @return array
     */
    public function getBensParaInventario(): array
    {
        try {
            $sql = "SELECT id, nome, numero_patrimonio, localizacao, responsavel, status 
                    FROM patrimonio_bens 
                    WHERE status != 'Baixado'
                    ORDER BY localizacao, nome";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar bens para inventário: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Processa os dados do inventário físico e gera um relatório de divergências.
     * @param array $dadosInventario Dados do formulário de inventário.
     * @return array Relatório de divergências.
     */
    public function conciliarInventario(array $dadosInventario): array
    {
        $bensDoSistema = $this->getBensParaInventario();
        $bensChecadosIds = array_keys($dadosInventario);

        $relatorio = [
            'nao_localizados' => [],
            'localizacao_divergente' => [],
            'nao_checados' => [],
        ];

        $this->db->beginTransaction();
        try {
            foreach ($bensDoSistema as $bem) {
                $id = $bem['id'];

                if (in_array($id, $bensChecadosIds)) {
                    $checagem = $dadosInventario[$id];

                    if ($checagem['status_checagem'] === 'nao_localizado') {
                        $relatorio['nao_localizados'][] = $bem;
                    } elseif ($checagem['status_checagem'] === 'localizado_outro_setor') {
                        $novoLocal = trim($checagem['novo_local']);
                        if (!empty($novoLocal) && $novoLocal !== $bem['localizacao']) {
                            $bem['local_novo'] = $novoLocal;
                            $relatorio['localizacao_divergente'][] = $bem;

                            // Atualiza o bem no banco de dados
                            $stmt = $this->db->prepare("UPDATE patrimonio_bens SET localizacao = :novo_local, data_inventario = NOW() WHERE id = :id");
                            $stmt->execute([':novo_local' => $novoLocal, ':id' => $id]);
                        }
                    } else { // Localizado
                        // Apenas atualiza a data do inventário
                        $stmt = $this->db->prepare("UPDATE patrimonio_bens SET data_inventario = NOW() WHERE id = :id");
                        $stmt->execute([':id' => $id]);
                    }
                } else {
                    // O bem estava na lista mas não foi checado (não veio no POST)
                    $relatorio['nao_checados'][] = $bem;
                }
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro na conciliação do inventário: " . $e->getMessage());
            throw $e;
        }

        return $relatorio;
    }

    /**
     * Agrupa os bens por centro de custo, somando seus valores.
     * @return array
     */
    public function getBensPorCentroDeCusto(): array
    {
        try {
            // Assumindo que a tabela 'patrimonio_bens' tem uma coluna 'centro_custo'
            $sql = "SELECT 
                        COALESCE(centro_custo, 'Não Especificado') as centro_custo,
                        COUNT(id) as quantidade_bens,
                        SUM(valor_aquisicao) as valor_total
                    FROM patrimonio_bens
                    WHERE status != 'Baixado'
                    GROUP BY centro_custo
                    ORDER BY valor_total DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar bens por centro de custo: " . $e->getMessage());
            // Retorna mock se a coluna 'centro_custo' não existir
            return [
                ['centro_custo' => 'TI (Exemplo)', 'quantidade_bens' => 10, 'valor_total' => 50000],
                ['centro_custo' => 'Administrativo (Exemplo)', 'quantidade_bens' => 25, 'valor_total' => 35000],
            ];
        }
    }

    /**
     * Calcula o demonstrativo de depreciação acumulada geral.
     * @return array
     */
    public function getDepreciacaoGeral(): array
    {
        $bens = $this->getBensComDepreciacao(); // Reutiliza a função já existente

        if (empty($bens)) {
            return ['total_aquisicao' => 0, 'total_depreciacao_acumulada' => 0, 'total_valor_contabil' => 0];
        }

        $totalAquisicao = array_sum(array_column($bens, 'valor_aquisicao'));
        $totalDepreciacao = array_sum(array_column($bens, 'depreciacao_acumulada'));
        $totalContabil = array_sum(array_column($bens, 'valor_contabil'));

        return [
            'total_aquisicao' => $totalAquisicao,
            'total_depreciacao_acumulada' => $totalDepreciacao,
            'total_valor_contabil' => $totalContabil,
        ];
    }

    /**
     * Calcula indicadores de renovação de ativos.
     * @return array
     */
    public function getIndicadoresRenovacao(): array
    {
        try {
            $sql = "SELECT data_aquisicao, valor_aquisicao, vida_util_meses 
                    FROM patrimonio_bens 
                    WHERE status != 'Baixado' AND data_aquisicao IS NOT NULL AND vida_util_meses > 0";
            $stmt = $this->db->query($sql);
            $bens = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($bens)) {
                return ['idade_media_anos' => 0, 'percentual_depreciado' => 0];
            }

            $totalBens = count($bens);
            $somaIdadeMeses = 0;
            $bensTotalmenteDepreciados = 0;
            $hoje = new \DateTime();

            foreach ($bens as $bem) {
                $dataAquisicao = new \DateTime($bem['data_aquisicao']);
                $intervalo = $hoje->diff($dataAquisicao);
                $idadeMeses = ($intervalo->y * 12) + $intervalo->m;
                $somaIdadeMeses += $idadeMeses;

                if ($idadeMeses >= (int)$bem['vida_util_meses']) {
                    $bensTotalmenteDepreciados++;
                }
            }

            $idadeMediaMeses = $somaIdadeMeses / $totalBens;
            $idadeMediaAnos = $idadeMediaMeses / 12;
            $percentualDepreciado = ($bensTotalmenteDepreciados / $totalBens) * 100;

            return [
                'idade_media_anos' => round($idadeMediaAnos, 1),
                'percentual_depreciado' => round($percentualDepreciado, 2),
            ];
        } catch (\PDOException $e) {
            error_log("Erro ao calcular indicadores de renovação: " . $e->getMessage());
            return ['idade_media_anos' => 0, 'percentual_depreciado' => 0];
        }
    }
}
