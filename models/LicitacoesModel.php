<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class LicitacoesModel extends Model
{
    private $lastError = null;

    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
    }

    /**
     * Garante que a tabela licitacoes e suas colunas necessárias existam para evitar falhas na listagem.
     */
    private function ensureTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS licitacoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero VARCHAR(50) NOT NULL,
            modalidade VARCHAR(100) NOT NULL,
            orgao VARCHAR(255) NOT NULL,
            objeto TEXT NOT NULL,
            responsavel VARCHAR(255) NOT NULL,
            setor VARCHAR(255) NULL,
            email_contato VARCHAR(255) NULL,
            telefone VARCHAR(50) NULL,
            dt_abertura DATE NULL,
            dt_sessao DATE NOT NULL,
            dt_entrega DATE NULL,
            valor_estimado DECIMAL(15,2) DEFAULT 0.00,
            categorias TEXT NULL,
            sigilo VARCHAR(50) DEFAULT 'publico',
            status VARCHAR(50) DEFAULT 'rascunho',
            edital_path VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);

        // Validação de integridade: garante que created_at exista para não quebrar o ORDER BY
        try {
            $this->db->query("SELECT created_at FROM licitacoes LIMIT 1");
        } catch (\PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE licitacoes ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            } catch (\PDOException $innerEx) {
                error_log("Erro ao sincronizar schema de licitacoes: " . $innerEx->getMessage());
            }
        }

        // Garante que updated_at exista (coluna reportada no banco mas ausente no sync do código)
        try {
            $this->db->query("SELECT updated_at FROM licitacoes LIMIT 1");
        } catch (\PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE licitacoes ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            } catch (\PDOException $innerEx) {}
        }

        // Garante que a coluna justificativa exista (usada na view de detalhes)
        try {
            $this->db->query("SELECT justificativa FROM licitacoes LIMIT 1");
        } catch (\PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE licitacoes ADD COLUMN justificativa TEXT NULL");
            } catch (\PDOException $innerEx) {}
        }

        // Garante que a tabela licitacoes_config_ia exista
        $sqlConfigIa = "CREATE TABLE IF NOT EXISTS licitacoes_config_ia (
            id INT AUTO_INCREMENT PRIMARY KEY,
            portais JSON NULL,
            palavras_chave TEXT NULL,
            ativo TINYINT(1) DEFAULT 0,
            sound_alerts_enabled TINYINT(1) DEFAULT 1, -- New column
            daily_email_summary_enabled TINYINT(1) DEFAULT 0,
            notification_sound VARCHAR(50) DEFAULT 'ping',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        try {
            $this->db->exec($sqlConfigIa);
            // Garante que a coluna sound_alerts_enabled exista
            $this->db->query("SELECT sound_alerts_enabled FROM licitacoes_config_ia LIMIT 1");
        } catch (\PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE licitacoes_config_ia ADD COLUMN sound_alerts_enabled TINYINT(1) DEFAULT 1");
            } catch (\PDOException $innerEx) {}
        }

        // Garante que a coluna daily_email_summary_enabled exista
        try {
            $this->db->query("SELECT daily_email_summary_enabled FROM licitacoes_config_ia LIMIT 1");
        } catch (\PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE licitacoes_config_ia ADD COLUMN daily_email_summary_enabled TINYINT(1) DEFAULT 0");
            } catch (\PDOException $innerEx) {}
        }

        // Garante que a coluna notification_sound exista
        try {
            $this->db->query("SELECT notification_sound FROM licitacoes_config_ia LIMIT 1");
        } catch (\PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE licitacoes_config_ia ADD COLUMN notification_sound VARCHAR(50) DEFAULT 'ping'");
            } catch (\PDOException $innerEx) {}
        }


        // Garante que a coluna visualizado exista no Radar IA
        try {
            $this->db->query("SELECT visualizado FROM licitacoes_captadas LIMIT 1");
        } catch (\PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE licitacoes_captadas ADD COLUMN visualizado TINYINT(1) DEFAULT 0");
            } catch (\PDOException $innerEx) {}
        }
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Lista as licitações com filtros de busca e status.
     */
    public function listar(array $filtros = [], int $limit = 10, int $offset = 0)
    {
        $sql = "SELECT * FROM licitacoes WHERE 1=1";
        $params = [];

        if (!empty($filtros['busca'])) {
            $sql .= " AND (numero LIKE ? OR orgao LIKE ? OR objeto LIKE ?)";
            $term = '%' . $filtros['busca'] . '%';
            $params[] = $term; $params[] = $term; $params[] = $term;
        }

        if (!empty($filtros['status'])) {
            if ($filtros['status'] === 'em_andamento') {
                $sql .= " AND status IN ('publicada', 'aberta', 'em_analise')";
            } elseif ($filtros['status'] === 'urgente' || $filtros['status'] === 's-urgente') {
                $sql .= " AND status NOT IN ('concluida', 'cancelada', 'suspensa', 'vencida') AND dt_sessao BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
            } else {
                $sql .= " AND status = ?";
                $params[] = $filtros['status'];
            }
        }

        if (!empty($filtros['com_edital'])) {
            $sql .= " AND edital_path IS NOT NULL AND edital_path != ''";
        }

        // Ordenação robusta. Se created_at for nulo, o ID garante a ordem cronológica.
        $sql .= " ORDER BY COALESCE(created_at, '2000-01-01') DESC, id DESC"; 

        // Adiciona LIMIT e OFFSET
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        try {
            $stmt = $this->db->prepare($sql);
            
            // Vincula os parâmetros de filtro (posicionais)
            foreach ($params as $key => $val) {
                $stmt->bindValue($key + 1, $val);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Erro na listagem de licitações: " . $e->getMessage());
            return []; // Retorna vazio se a coluna 'id' ou 'created_at' não existirem
        }
    }

    /**
     * Conta o total de registros para a paginação, respeitando os mesmos filtros da listagem.
     * Essencial para performance em tabelas com muitos registros.
     */
    public function contarListagem(array $filtros = [])
    {
        $sql = "SELECT COUNT(*) FROM licitacoes WHERE 1=1";
        $params = [];

        if (!empty($filtros['busca'])) {
            $sql .= " AND (numero LIKE ? OR orgao LIKE ? OR objeto LIKE ?)";
            $term = '%' . $filtros['busca'] . '%';
            $params[] = $term; $params[] = $term; $params[] = $term;
        }

        if (!empty($filtros['status'])) {
            if ($filtros['status'] === 'em_andamento') {
                $sql .= " AND status IN ('publicada', 'aberta', 'em_analise')";
            } elseif ($filtros['status'] === 'urgente' || $filtros['status'] === 's-urgente') {
                $sql .= " AND status NOT IN ('concluida', 'cancelada', 'suspensa', 'vencida') AND dt_sessao BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
            } else {
                $sql .= " AND status = ?";
                $params[] = $filtros['status'];
            }
        }

        if (!empty($filtros['com_edital'])) {
            $sql .= " AND edital_path IS NOT NULL AND edital_path != ''";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca uma licitação específica pelo ID.
     */
    public function getById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM licitacoes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Salva ou atualiza uma licitação.
     */
    public function salvar(array $dados)
    {
        try {
            if (isset($dados['id']) && !empty($dados['id'])) {
                // Lógica de Update
                $id = $dados['id'];
                unset($dados['id']);
                
                $fields = array_map(fn($key) => "$key = :$key", array_keys($dados));
                $sql = "UPDATE licitacoes SET " . implode(', ', $fields) . " WHERE id = :id_key";
                $dados['id_key'] = $id;
                // A coluna updated_at será atualizada automaticamente pelo MySQL devido ao ON UPDATE
                
                $stmt = $this->db->prepare($sql);
                return $stmt->execute($dados);
            } else {
                // Lógica de Insert
                $fields = array_keys($dados);
                $placeholders = array_map(fn($key) => ":$key", $fields);
                $sql = "INSERT INTO licitacoes (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                
                $stmt = $this->db->prepare($sql);
                if ($stmt->execute($dados)) {
                    return $this->db->lastInsertId();
                }
                return false;
            }
        } catch (\PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Remove um registro.
     */
    public function excluir(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM licitacoes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Retorna os KPIs para o Dashboard.
     */
    public function getKpis()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(valor_estimado) as valor_total,
                    COUNT(CASE WHEN status IN ('publicada', 'aberta', 'em_analise') THEN 1 END) as em_andamento,
                    COUNT(CASE WHEN status = 'concluida' THEN 1 END) as finalizadas,
                    COUNT(CASE WHEN status = 'aberta' THEN 1 END) as abertas,
                    COUNT(CASE WHEN status = 'suspensa' THEN 1 END) as suspensas,
                    COUNT(CASE WHEN status NOT IN ('concluida', 'cancelada', 'suspensa', 'vencida') AND dt_sessao BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 1 END) as urgentes
                FROM licitacoes";
        
        $stmt = $this->db->query($sql);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total' => $res['total'] ?? 0,
            'valor_total' => $res['valor_total'] ?? 0,
            'em_andamento' => $res['em_andamento'] ?? 0,
            'finalizadas' => $res['finalizadas'] ?? 0,
            'abertas' => $res['abertas'] ?? 0,
            'suspensas' => $res['suspensas'] ?? 0,
            'urgentes' => $res['urgentes'] ?? 0
        ];
    }

    /**
     * Retorna o volume mensal de licitações para o gráfico.
     */
    public function getVolumeMensal()
    {
        $ano = date('Y');
        $sql = "SELECT MONTH(dt_sessao) as mes, COUNT(*) as qtd 
                FROM licitacoes 
                WHERE YEAR(dt_sessao) = ? 
                GROUP BY mes";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ano]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $meses = array_fill(1, 12, 0);
        foreach ($rows as $mes => $qtd) {
            $meses[(int)$mes] = (int)$qtd;
        }
        
        return array_values($meses);
    }

    // --- Métodos para Radar IA e Configurações ---

    public function getIAConfig()
    {
        return $this->db->query("SELECT * FROM licitacoes_config_ia LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }

    public function salvarIAConfig(array $dados)
    {
        $sql = "UPDATE licitacoes_config_ia SET portais = ?, palavras_chave = ?, ativo = ?, sound_alerts_enabled = ?, notification_sound = ?, daily_email_summary_enabled = ? WHERE id = 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$dados['portais'], $dados['palavras_chave'], $dados['ativo'], $dados['sound_alerts_enabled'], $dados['notification_sound'], $dados['daily_email_summary_enabled']]);
    }

    public function listarCaptacoes()
    {
        return $this->db->query("SELECT * FROM licitacoes_captadas ORDER BY captado_em DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarCaptacoesNaoLidas()
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM licitacoes_captadas WHERE visualizado = 0")->fetchColumn();
    }

    public function marcarCaptacoesComoLidas()
    {
        return $this->db->exec("UPDATE licitacoes_captadas SET visualizado = 1 WHERE visualizado = 0");
    }

    public function favoritarCaptacao(int $id)
    {
        return $this->db->prepare("UPDATE licitacoes_captadas SET favorito = NOT favorito WHERE id = ?")->execute([$id]);
    }

    public function excluirCaptacao(int $id)
    {
        return $this->db->prepare("DELETE FROM licitacoes_captadas WHERE id = ?")->execute([$id]);
    }

    /**
     * Busca todos os editais (licitações com edital anexado) para um determinado mês e ano.
     * @param int $mes
     * @param int $ano
     * @return array
     */
    public function getEditaisPorMesAno(int $mes, int $ano, ?string $categoria = null): array
    {
        $sql = "SELECT * FROM licitacoes 
                WHERE edital_path IS NOT NULL 
                AND edital_path != '' 
                AND MONTH(dt_sessao) = :mes 
                AND YEAR(dt_sessao) = :ano";
        
        $params = [':mes' => $mes, ':ano' => $ano];

        if (!empty($categoria)) {
            $sql .= " AND categorias LIKE :categoria";
            $params[':categoria'] = '%' . $categoria . '%';
        }

        $sql .= " ORDER BY dt_sessao ASC, numero ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar editais por mês/ano: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Consolida dados para um relatório mensal de editais.
     * Retorna a lista de editais e estatísticas de volume e valor.
     * 
     * @param int $mes
     * @param int $ano
     * @return array
     */
    public function getDadosRelatorioMensal(int $mes, int $ano, ?string $categoria = null): array
    {
        $editais = $this->getEditaisPorMesAno($mes, $ano, $categoria);
        
        $totalValor = 0;
        foreach ($editais as $edital) {
            $totalValor += (float)($edital['valor_estimado'] ?? 0);
        }

        return [
            'mes' => $mes,
            'ano' => $ano,
            'data_geracao' => date('Y-m-d H:i:s'),
            'total_registros' => count($editais),
            'valor_total' => $totalValor,
            'lista' => $editais
        ];
    }
}