<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class FornecedoresModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca dados resumidos sobre a base de fornecedores.
     */
    public function getFornecedoresSummary()
    {
        try {
            $totalAtivos = $this->db->query("SELECT COUNT(*) FROM pessoas WHERE tipo = 'Fornecedor' AND status = 'Ativo'")->fetchColumn();
            // As colunas para os demais resumos (contratoVencer30, pendenciaDocs, riscoAlto) não existem na tabela 'pessoas'.
            // Para que funcionem, a estrutura do banco precisaria ser estendida. Por enquanto, retornaremos 0.
            return [
                'totalAtivos' => (int) $totalAtivos,
                'contratoVencer30' => 0, // Mockado - necessita da coluna de vencimento de contrato
                'pendenciaDocs' => 0,    // Mockado - necessita da coluna de status de documentação
                'riscoAlto' => 0,        // Mockado - necessita da coluna de avaliação de risco
            ];
        } catch (\PDOException $e) {
            error_log("Erro ao buscar resumo de fornecedores: " . $e->getMessage());
            return ['totalAtivos' => 0, 'contratoVencer30' => 0, 'pendenciaDocs' => 0, 'riscoAlto' => 0];
        }
    }

    /**
     * Busca uma lista de fornecedores com filtros e paginação.
     * @param array $filtros
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFornecedores(array $filtros = [], int $limit = 10, int $offset = 0): array
    {
        try {
            $sql = "SELECT pessoa_id as id, razao_social as nome, cnpj_cpf as cnpj, email, telefone, cidade, status
                    FROM pessoas
                    WHERE tipo = 'Fornecedor'";
            $params = [];

            if (!empty($filtros['busca'])) {
                $sql .= " AND (razao_social LIKE :busca OR cnpj_cpf LIKE :busca OR cidade LIKE :busca)";
                $params[':busca'] = '%' . $filtros['busca'] . '%';
            }
            if (!empty($filtros['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filtros['status'];
            }

            $sql .= " ORDER BY razao_social ASC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar lista de fornecedores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o número total de fornecedores que correspondem a um filtro.
     * @param array $filtros
     * @return int
     */
    public function getFornecedoresCount(array $filtros = []): int
    {
        $sql = "SELECT COUNT(*) FROM pessoas WHERE tipo = 'Fornecedor'";
        $params = [];

        if (!empty($filtros['busca'])) {
            $sql .= " AND (razao_social LIKE :busca OR cnpj_cpf LIKE :busca OR cidade LIKE :busca)";
            $params[':busca'] = '%' . $filtros['busca'] . '%';
        }
        if (!empty($filtros['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filtros['status'];
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        // CORREÇÃO: Chamar execute() sem parâmetros, pois eles já foram associados
        // com bindParam no loop acima.
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca todos os fornecedores para preencher listas de seleção.
     * @return array
     */
    public function getAllFornecedores(): array
    {
        try {
            // CORREÇÃO FINAL: Usando a coluna 'tipo' em vez de 'tipo_pessoa'.
            $stmt = $this->db->query("SELECT pessoa_id as id, razao_social as nome FROM pessoas WHERE tipo = 'Fornecedor' ORDER BY razao_social ASC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar todos os fornecedores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um fornecedor específico pelo ID.
     * @param int $id O ID do fornecedor.
     * @return array|null
     */
    public function getFornecedorById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT *, pessoa_id as id, razao_social as nome, cnpj_cpf as cnpj, contato_principal as contato FROM pessoas WHERE tipo = 'Fornecedor' AND pessoa_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar fornecedor por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva um novo fornecedor ou atualiza um existente no banco de dados.
     *
     * @param array $dados Os dados do fornecedor vindos do formulário.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function salvarFornecedor(array $dados): bool
    {
        // Sanitiza e prepara os dados
        $id = !empty($dados['id']) ? (int) $dados['id'] : null;
        $razao_social = trim($dados['nome'] ?? '');
        $cnpj_cpf = preg_replace('/\D/', '', trim($dados['cnpj'] ?? ''));
        // Novos campos do formulário
        $tipo_pessoa = trim($dados['tipo_pessoa'] ?? 'Juridica');
        $nome_fantasia = trim($dados['nome_fantasia'] ?? '');
        $ie_isento = isset($dados['ie_isento']) ? 1 : 0;
        $categoria_fornecimento = trim($dados['categoria_fornecimento'] ?? '');
        $motivo_inativacao = trim($dados['motivo_inativacao'] ?? '');
        $data_inativacao = !empty($dados['data_inativacao']) ? $dados['data_inativacao'] : null;
        $status = trim($dados['status'] ?? 'Ativo');
        $inscricao_estadual = trim($dados['inscricao_estadual'] ?? '');
        $inscricao_municipal = trim($dados['inscricao_municipal'] ?? '');
        // Campos JSON
        $endereco_json = isset($dados['endereco']) ? json_encode($dados['endereco']) : null;
        $contato_json = isset($dados['contato']) ? json_encode($dados['contato']) : null;
        $dados_financeiros_json = isset($dados['dados_financeiros']) ? json_encode($dados['dados_financeiros']) : null;
        $info_comerciais_json = isset($dados['info_comerciais']) ? json_encode($dados['info_comerciais']) : null;

        // Campos legados para manter compatibilidade (se necessário)
        $contato_principal = $dados['contato']['representante_nome'] ?? '';
        $email = $dados['contato']['email_principal'] ?? '';
        $telefone = $dados['contato']['telefone_comercial'] ?? '';
        $cidade = $dados['endereco']['cidade'] ?? ''; // Pega a cidade do array de endereço

        try { // Envolve a operação em um bloco try-catch para capturar erros do PDO.
            if ($id) {
                // UPDATE: Atualiza um fornecedor existente
                $sql = "UPDATE pessoas SET tipo_pessoa = :tipo_pessoa, razao_social = :razao_social, nome_fantasia = :nome_fantasia, cnpj_cpf = :cnpj_cpf, contato_principal = :contato_principal, email = :email, telefone = :telefone, cidade = :cidade, status = :status, categoria_fornecimento = :categoria, inscricao_estadual = :ie, ie_isento = :ie_isento, inscricao_municipal = :im, motivo_inativacao = :motivo_inativacao, data_inativacao = :data_inativacao, endereco_json = :end_json, contato_json = :cont_json, dados_financeiros_json = :fin_json, info_comerciais_json = :com_json WHERE pessoa_id = :id AND tipo = 'Fornecedor'";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            } else {
                // INSERT: Cria um novo fornecedor
                $sql = "INSERT INTO pessoas (tipo_pessoa, razao_social, nome_fantasia, cnpj_cpf, contato_principal, email, telefone, tipo, cidade, status, categoria_fornecimento, inscricao_estadual, ie_isento, inscricao_municipal, motivo_inativacao, data_inativacao, endereco_json, contato_json, dados_financeiros_json, info_comerciais_json, data_criacao) VALUES (:tipo_pessoa, :razao_social, :nome_fantasia, :cnpj_cpf, :contato_principal, :email, :telefone, 'Fornecedor', :cidade, :status, :categoria, :ie, :ie_isento, :im, :motivo_inativacao, :data_inativacao, :end_json, :cont_json, :fin_json, :com_json, NOW())";
                $stmt = $this->db->prepare($sql);
            }

            $stmt->bindParam(':razao_social', $razao_social);
            $stmt->bindParam(':cnpj_cpf', $cnpj_cpf);
            $stmt->bindParam(':contato_principal', $contato_principal);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':cidade', $cidade);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':tipo_pessoa', $tipo_pessoa);
            $stmt->bindParam(':nome_fantasia', $nome_fantasia);
            $stmt->bindParam(':categoria', $categoria_fornecimento);
            $stmt->bindParam(':ie', $inscricao_estadual);
            $stmt->bindParam(':im', $inscricao_municipal);
            $stmt->bindParam(':ie_isento', $ie_isento, \PDO::PARAM_INT);
            $stmt->bindParam(':motivo_inativacao', $motivo_inativacao);
            $stmt->bindParam(':data_inativacao', $data_inativacao);
            $stmt->bindParam(':end_json', $endereco_json);
            $stmt->bindParam(':cont_json', $contato_json);
            $stmt->bindParam(':fin_json', $dados_financeiros_json);
            $stmt->bindParam(':com_json', $info_comerciais_json);

            return $stmt->execute();
        } catch (\PDOException $e) {
            // Em vez de retornar false, lança a exceção para o controller tratar.
            throw $e;
        }
    }

    /**
     * Busca o histórico de ocorrências de um fornecedor.
     * @param int $fornecedorId
     * @return array
     */
    public function getOcorrenciasByFornecedorId(int $fornecedorId): array
    {
        try {
            $sql = "SELECT id, data_ocorrencia as data, tipo, descricao, responsavel 
                    FROM fornecedor_ocorrencias 
                    WHERE fornecedor_id = :fornecedor_id 
                    ORDER BY data_ocorrencia DESC, id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':fornecedor_id' => $fornecedorId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar ocorrências do fornecedor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva uma nova ocorrência para um fornecedor.
     * @param array $dados
     * @return bool
     */
    public function salvarOcorrencia(array $dados): bool
    {
        $sql = "INSERT INTO fornecedor_ocorrencias (fornecedor_id, data_ocorrencia, tipo, descricao, responsavel) 
                VALUES (:fornecedor_id, :data_ocorrencia, :tipo, :descricao, :responsavel)";
        try {
            $stmt = $this->db->prepare($sql);
            // O controller passará os dados já sanitizados
            return $stmt->execute($dados);
        } catch (\PDOException $e) {
            error_log("Erro ao salvar ocorrência do fornecedor: " . $e->getMessage());
            return false;
        }
    }
}
