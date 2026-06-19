<?php
// models/CaptacaoIAModel.php

namespace App\Models;

use App\Core\Model;
use PDO;

class CaptacaoIAModel extends Model {
    private $table = 'captacoes_ia';

    public function getAll($filtros = [], $limit = 50, $offset = 0) {
        $where = [];
        $params = [];
        
        if (!empty($filtros['nao_ignoradas'])) {
            $where[] = "ignorado = 0";
        }
        
        if (!empty($filtros['apenas_favoritas'])) {
            $where[] = "favorito = 1";
        }
        
        if (!empty($filtros['portal'])) {
            $where[] = "portal_origem = :portal";
            $params[':portal'] = $filtros['portal'];
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "SELECT * FROM {$this->table} 
                  {$whereClause}
                  ORDER BY captado_em DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCount($filtros = []) {
        $where = [];
        $params = [];
        
        if (!empty($filtros['nao_ignoradas'])) {
            $where[] = "ignorado = 0";
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function save($data) {
        // Verifica duplicata pelo hash
        if (!empty($data['raw_html_hash'])) {
            $checkQuery = "SELECT id FROM {$this->table} WHERE raw_html_hash = :hash LIMIT 1";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute([':hash' => $data['raw_html_hash']]);
            if ($checkStmt->fetch()) {
                return false; // Já existe
            }
        }
        
        $query = "INSERT INTO {$this->table} 
                  (portal_origem, orgao_externo, objeto, numero_edital, valor_estimado, 
                   data_publicacao, data_sessao, link_edital, raw_html_hash, captado_em, entidades, keywords_match)
                  VALUES 
                  (:portal_origem, :orgao_externo, :objeto, :numero_edital, :valor_estimado,
                   :data_publicacao, :data_sessao, :link_edital, :raw_html_hash, NOW(), :entidades, :keywords_match)";
        
        $stmt = $this->db->prepare($query);
        
        $entidades = isset($data['entidades']) ? json_encode($data['entidades']) : null;
        $keywords_match = isset($data['keywords_match']) ? json_encode($data['keywords_match']) : null;
        
        return $stmt->execute([
            ':portal_origem' => $data['portal_origem'],
            ':orgao_externo' => $data['orgao_externo'],
            ':objeto' => $data['objeto'],
            ':numero_edital' => $data['numero_edital'] ?? '',
            ':valor_estimado' => $data['valor_estimado'] ?? 0,
            ':data_publicacao' => $data['data_publicacao'] ?? null,
            ':data_sessao' => $data['data_sessao'] ?? null,
            ':link_edital' => $data['link_edital'] ?? '',
            ':raw_html_hash' => $data['raw_html_hash'],
            ':entidades' => $entidades,
            ':keywords_match' => $keywords_match
        ]);
    }

    public function favoritar($id, $favorito = true) {
        $query = "UPDATE {$this->table} SET favorito = :favorito WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':favorito' => $favorito ? 1 : 0, ':id' => $id]);
    }

    public function ignorar($id, $ignorado = true) {
        $query = "UPDATE {$this->table} SET ignorado = :ignorado WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':ignorado' => $ignorado ? 1 : 0, ':id' => $id]);
    }

    public function marcarConvertido($id, $licitacaoId) {
        $query = "UPDATE {$this->table} SET convertido_para_licitacao_id = :licitacao_id WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':licitacao_id' => $licitacaoId, ':id' => $id]);
    }

    public function getUltimas($limit = 5) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE ignorado = 0 
                  ORDER BY captado_em DESC 
                  LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContagemNaoLidas() {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE ignorado = 0 AND lida = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Marca todas as captações pendentes como lidas.
     * @return int|false Quantidade de registros afetados
     */
    public function marcarTodasComoLidas() {
        $query = "UPDATE {$this->table} SET lida = 1 WHERE lida = 0";
        return $this->db->exec($query);
    }
}