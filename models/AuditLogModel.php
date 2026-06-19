<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class AuditLogModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            action VARCHAR(50) NOT NULL,
            description TEXT,
            module VARCHAR(50) NULL,
            resource_id INT NULL,
            ip_address VARCHAR(45) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela de audit_logs: " . $e->getMessage());
        }
    }

    /**
     * Registra um log de auditoria.
     */
    public function log(?int $userId, string $action, string $description, ?string $module = null, ?int $resourceId = null, ?string $ip = null): bool
    {
        try {
            $sql = "INSERT INTO audit_logs (user_id, action, description, module, resource_id, ip_address) 
                    VALUES (:user_id, :action, :description, :module, :resource_id, :ip)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':description' => $description,
                ':module' => $module,
                ':resource_id' => $resourceId,
                ':ip' => $ip
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao registrar log de auditoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca logs com filtros opcionais.
     */
    public function getLogs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        try {
            $sql = "SELECT l.*, u.nome as user_name 
                    FROM audit_logs l
                    LEFT JOIN usuarios u ON l.user_id = u.id
                    WHERE 1=1";

            $params = [];

            if (!empty($filters['user_id'])) {
                $sql .= " AND l.user_id = :user_id";
                $params[':user_id'] = $filters['user_id'];
            }
            if (!empty($filters['module'])) {
                $sql .= " AND l.module = :module";
                $params[':module'] = $filters['module'];
            }
            if (!empty($filters['action'])) {
                $sql .= " AND l.action LIKE :action";
                $params[':action'] = '%' . $filters['action'] . '%';
            }

            $sql .= " ORDER BY l.created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar logs de auditoria: " . $e->getMessage());
            return [];
        }
    }
}
