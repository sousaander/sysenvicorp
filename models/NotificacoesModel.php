<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class NotificacoesModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS notificacoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            titulo VARCHAR(255) NOT NULL,
            mensagem TEXT,
            link VARCHAR(255),
            lida TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela de notificações: " . $e->getMessage());
        }
    }

    public function criarNotificacao(int $usuarioId, string $titulo, string $mensagem, ?string $link = null): bool
    {
        try {
            $sql = "INSERT INTO notificacoes (usuario_id, titulo, mensagem, link) VALUES (:usuario_id, :titulo, :mensagem, :link)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'usuario_id' => $usuarioId,
                'titulo' => $titulo,
                'mensagem' => $mensagem,
                'link' => $link
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao criar notificação: " . $e->getMessage());
            return false;
        }
    }

    public function getNaoLidas(int $usuarioId): array
    {
        try {
            $sql = "SELECT * FROM notificacoes WHERE usuario_id = :usuario_id AND lida = 0 ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['usuario_id' => $usuarioId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar notificações: " . $e->getMessage());
            return [];
        }
    }

    public function marcarComoLida(int $id, int $usuarioId): bool
    {
        $sql = "UPDATE notificacoes SET lida = 1 WHERE id = :id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'usuario_id' => $usuarioId]);
    }

    public function marcarTodasComoLidas(int $usuarioId): bool
    {
        $sql = "UPDATE notificacoes SET lida = 1 WHERE usuario_id = :usuario_id AND lida = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['usuario_id' => $usuarioId]);
    }

    /**
     * Remove notificações lidas que são mais antigas que o número de dias especificado.
     * 
     * @param int $dias Quantidade de dias para manter no histórico.
     * @return int Quantidade de registros removidos.
     */
    public function limparNotificacoes(int $dias = 30): int
    {
        try {
            // A query remove apenas notificações que já foram lidas (lida = 1)
            // e que foram criadas antes do intervalo de dias definido.
            $sql = "DELETE FROM notificacoes WHERE lida = 1 AND created_at < DATE_SUB(NOW(), INTERVAL :dias DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Erro ao limpar notificações: " . $e->getMessage());
            return 0;
        }
    }
}
