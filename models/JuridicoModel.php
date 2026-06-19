<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class JuridicoModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
    }

    /**
     * Garante a existência da tabela de processos judiciais.
     */
    private function ensureTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS juridico_processos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero_cnj VARCHAR(50) NOT NULL UNIQUE,
            tipo VARCHAR(100) NOT NULL,
            status VARCHAR(50) DEFAULT 'Ativo',
            fase VARCHAR(100) DEFAULT 'Inicial',
            cliente_id INT NULL,
            responsavel_id INT NULL,
            parte_contraria VARCHAR(255),
            tribunal VARCHAR(255),
            vara_camara VARCHAR(255),
            objeto TEXT,
            valor_causa DECIMAL(15,2) DEFAULT 0.00,
            data_distribuicao DATE,
            observacoes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
            FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);

        // Migração: Garante que as colunas esperadas pelo Dashboard existam
        $cols = [
            'fase' => "VARCHAR(100) DEFAULT 'Inicial' AFTER status",
            'numero_cnj' => "VARCHAR(50) NOT NULL UNIQUE AFTER id",
            'responsavel_id' => "INT NULL AFTER cliente_id"
        ];
        foreach ($cols as $col => $def) {
            $check = $this->db->query("SHOW COLUMNS FROM juridico_processos LIKE '$col'");
            if ($check->rowCount() == 0) $this->db->exec("ALTER TABLE juridico_processos ADD COLUMN $col $def");
        }
    }

    /**
     * Retorna o total de processos ativos.
     * Por enquanto, simula um valor.
     * @return int
     */
    public function getTotalProcessosAtivos(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM juridico_processos WHERE status = 'Ativo'");
            $count = (int) $stmt->fetchColumn();
            return $count > 0 ? $count : 15; // Retorna fallback para dados simulados se a base estiver vazia
        } catch (PDOException $e) {
            return 15; // Fallback para dados simulados em caso de erro
        }
    }

    /**
     * Retorna indicadores para o Dashboard.
     */
    public function getDashboardKpis(): array
    {
        return [
            'processos_ativos'      => $this->getTotalProcessosAtivos(),
            'processos_novos_mes'   => 3,
            'audiencias_mes'        => 8,
            'audiencias_semana'     => 2,
            'encerrados_ano'        => 12,
            'taxa_exito'            => 85,
            'diligencias_abertas'   => 5,
            'diligencias_atrasadas' => 1
        ];
    }

    /**
     * Retorna a distribuição de processos por tipo para os gráficos.
     */
    public function getDistribuicaoPorTipo(): array
    {
        try {
            $sql = "SELECT tipo, COUNT(*) as total FROM juridico_processos GROUP BY tipo";
            $res = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            return !empty($res) ? $res : [
                ['tipo' => 'Cível', 'total' => 10],
                ['tipo' => 'Trabalhista', 'total' => 5]
            ];
        } catch (PDOException $e) {
            return [['tipo' => 'Cível', 'total' => 10], ['tipo' => 'Trabalhista', 'total' => 5]];
        }
    }

    /**
     * Retorna uma lista de prazos próximos (ex: nos próximos N dias).
     * Por enquanto, simula alguns prazos.
     * @param int $dias Quantidade de dias para considerar como "próximo".
     * @return array
     */
    public function getPrazosProximos(int $dias = 7): array
    {
        // Em um cenário real, faria uma consulta ao banco de dados:
        // $stmt = $this->db->prepare("SELECT * FROM juridico_prazos WHERE data_prazo BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY) ORDER BY data_prazo ASC");
        // $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        // $stmt->execute();
        // return $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [ // Dados simulados
            ['id' => 1, 'descricao' => 'Audiência Processo 123/2024', 'data_prazo' => date('Y-m-d', strtotime('+2 days')), 'processo_id' => 101, 'numero_processo' => '123/2024', 'vara' => '3ª Vara Cível'],
            ['id' => 2, 'descricao' => 'Entrega de Documentos Processo 456/2024', 'data_prazo' => date('Y-m-d', strtotime('+5 days')), 'processo_id' => 102, 'numero_processo' => '456/2024', 'vara' => '2ª Vara do Trabalho'],
        ];
    }

    public function getAndamentosRecentes(int $limit = 5): array
    {
        return [
            ['descricao' => 'Petição Protocolada', 'complemento' => 'Manifestação sobre laudo pericial', 'tipo_andamento' => 'peticao', 'criado_em' => date('Y-m-d H:i')],
            ['descricao' => 'Audiência Realizada', 'complemento' => 'Conciliação infrutífera', 'tipo_andamento' => 'audiencia', 'criado_em' => date('Y-m-d H:i', strtotime('-1 day'))],
        ];
    }

    public function getAudienciasProximas(int $limit = 5): array
    {
        return [
            ['data_audiencia' => date('Y-m-d 14:00', strtotime('+3 days')), 'tipo_audiencia' => 'Conciliação', 'vara' => '3ª Vara Cível', 'responsavel_nome' => 'Dr. Ricardo'],
            ['data_audiencia' => date('Y-m-d 10:30', strtotime('+1 day')), 'tipo_audiencia' => 'Instrução', 'vara' => '2ª Vara Trabalho', 'responsavel_nome' => 'Dra. Beatriz'],
        ];
    }

    public function getResponsaveisCarga(): array
    {
        return [
            ['nome' => 'Dr. Ricardo Oliveira', 'cargo' => 'Sócio', 'total_processos' => 25],
            ['nome' => 'Dra. Beatriz Santos', 'cargo' => 'Advogada Pleno', 'total_processos' => 18],
            ['nome' => 'Dr. Marcos Lima', 'cargo' => 'Advogado Júnior', 'total_processos' => 12],
        ];
    }

    /* Busca a lista de processos com filtros e paginação.
     * @param array $filtros
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getProcessos(array $filtros = [], int $limit = 10, int $offset = 0): array
    {
        // Dados simulados com as chaves corretas para a tabela de Processos Ativos
        return [
            ['id' => 1, 'numero_cnj' => '0000123-45.2024.8.26.0100', 'tipo' => 'Cível', 'status' => 'Ativo', 'parte_adversa' => 'Empresa X', 'tribunal' => 'TJSP', 'proximo_prazo' => '2024-11-20', 'fase' => 'Conhecimento', 'vara' => '1ª Vara Cível', 'responsavel_nome' => 'Dr. Ricardo'],
            ['id' => 2, 'numero_cnj' => '0000456-78.2024.5.02.0001', 'tipo' => 'Trabalhista', 'status' => 'Suspenso', 'parte_adversa' => 'Ex-colaborador Y', 'tribunal' => 'TRT2', 'proximo_prazo' => null, 'fase' => 'Instrução', 'vara' => '2ª Vara do Trabalho', 'responsavel_nome' => 'Dra. Beatriz'],
            ['id' => 3, 'numero_cnj' => '0000789-12.2024.4.01.3400', 'tipo' => 'Administrativo', 'status' => 'Concluído', 'parte_adversa' => 'IBAMA', 'tribunal' => 'JFDF', 'proximo_prazo' => null, 'fase' => 'Encerrado', 'vara' => 'Justiça Federal', 'responsavel_nome' => 'Dr. Marcos'],
        ];
    }

    /**
     * Conta o total de processos para controle de paginação.
     * @param array $filtros
     * @return int
     */
    public function getProcessosCount(array $filtros = []): int
    {
        // Em produção: SELECT COUNT(*) FROM juridico_processos
        return 15; // Valor simulado
    }

    /**
     * Salva (insere ou atualiza) um processo no banco de dados.
     */
    public function salvarProcesso(array $dados): bool
    {
        try {
            $id = !empty($dados['id']) ? (int)$dados['id'] : null;

            if ($id) {
                $sql = "UPDATE juridico_processos SET 
                            numero = :numero, tipo = :tipo, status = :status, cliente_id = :cliente_id, 
                            parte_contraria = :parte_contraria, tribunal = :tribunal, vara_camara = :vara_camara, 
                            objeto = :objeto, valor_causa = :valor_causa, data_distribuicao = :data_distribuicao, 
                            observacoes = :observacoes 
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            } else {
                $sql = "INSERT INTO juridico_processos (numero, tipo, status, cliente_id, parte_contraria, tribunal, vara_camara, objeto, valor_causa, data_distribuicao, observacoes) 
                        VALUES (:numero, :tipo, :status, :cliente_id, :parte_contraria, :tribunal, :vara_camara, :objeto, :valor_causa, :data_distribuicao, :observacoes)";
                $stmt = $this->db->prepare($sql);
            }

            $stmt->bindValue(':numero', $dados['numero']);
            $stmt->bindValue(':tipo', $dados['tipo']);
            $stmt->bindValue(':status', $dados['status']);
            $stmt->bindValue(':cliente_id', $dados['cliente_id'], $dados['cliente_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':parte_contraria', $dados['parte_contraria']);
            $stmt->bindValue(':tribunal', $dados['tribunal']);
            $stmt->bindValue(':vara_camara', $dados['vara_camara']);
            $stmt->bindValue(':objeto', $dados['objeto']);
            $stmt->bindValue(':valor_causa', $dados['valor_causa']);
            $stmt->bindValue(':data_distribuicao', $dados['data_distribuicao'] ?: null);
            $stmt->bindValue(':observacoes', $dados['observacoes']);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao salvar processo jurídico: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca um processo jurídico específico pelo ID.
     * @param int $id O ID do processo.
     * @return array|null
     */
    public function getProcessoById(int $id): ?array
    {
        // Em um cenário real, você faria uma consulta SQL como esta:
        // $stmt = $this->db->prepare("SELECT * FROM juridico_processos WHERE id = ?");
        // $stmt->execute([$id]);
        // return $stmt->fetch(PDO::FETCH_ASSOC);

        // Por enquanto, retorna um dado simulado para o ID 1, e null para outros IDs.
        if ($id === 1) {
            return [
                'id' => 1,
                'numero' => '0000123-45.2024.8.26.0100',
                'tipo' => 'Cível',
                'status' => 'Ativo',
                'cliente_id' => 1, // Supondo que o cliente ID 1 exista
                'parte_contraria' => 'Empresa X',
                'tribunal' => 'TJSP',
                'vara_camara' => '2ª Vara Cível',
                'objeto' => 'Ação de cobrança de serviços prestados.',
                'valor_causa' => 15000.00,
                'data_distribuicao' => '2024-03-15',
                'observacoes' => 'Processo em fase inicial de instrução. Aguardando manifestação da parte contrária.',
                'created_at' => '2024-03-10 10:00:00',
                'updated_at' => '2024-03-10 10:00:00',
            ];
        }
        return null;
    }

    /**
     * Exclui um processo jurídico permanentemente do banco de dados.
     * @param int $id O ID do processo.
     * @return bool
     */
    public function excluirProcesso(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM juridico_processos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao excluir processo jurídico: " . $e->getMessage());
            return false;
        }
    }
}