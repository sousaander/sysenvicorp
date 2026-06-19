<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class PerfilModel extends Model
{
    private $lastError;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retorna a última mensagem de erro.
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Busca todos os perfis de acesso.
     *
     * CORREÇÃO: A query foi ajustada para selecionar explicitamente 'perfil_id' e também
     * criar um alias 'id'. Isso garante que a chave do ID esteja sempre disponível
     * de forma consistente, resolvendo o problema de links de edição quebrados.
     */
    public function getAll(): array
    {
        try {
            $sql = "SELECT
                        perfil_id, 
                        perfil_id as id, 
                        nome_perfil, 
                        descricao, 
                        permissoes 
                    FROM perfis_acesso                    
                    ORDER BY nome_perfil ASC"; // A cláusula WHERE foi removida para listar todos os perfis.
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar todos os perfis: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um perfil pelo seu ID.
     */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT perfil_id, perfil_id as id, nome_perfil, descricao, permissoes FROM perfis_acesso WHERE perfil_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar perfil por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva (insere ou atualiza) um perfil.
     */
    public function salvar(array $dados): bool
    {
        $permissoesJson = !empty($dados['permissoes']) ? json_encode($dados['permissoes']) : null;

        $sql = !empty($dados['id'])
            ? "UPDATE perfis_acesso SET nome_perfil = :nome_perfil, descricao = :descricao, permissoes = :permissoes WHERE perfil_id = :id"
            : "INSERT INTO perfis_acesso (nome_perfil, descricao, permissoes) VALUES (:nome_perfil, :descricao, :permissoes)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome_perfil', $dados['nome_perfil']);
            $stmt->bindValue(':descricao', $dados['descricao']);
            $stmt->bindValue(':permissoes', $permissoesJson);
            if (!empty($dados['id'])) {
                $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Erro ao salvar perfil: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um perfil.
     */
    public function excluir(int $id): bool
    {
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE perfil_id = ?");
        $stmtCheck->execute([$id]);
        if ($stmtCheck->fetchColumn() > 0) {
            $this->lastError = "Não é possível excluir o perfil, pois ele está associado a um ou mais usuários.";
            return false;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM perfis_acesso WHERE perfil_id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Erro ao excluir perfil: " . $e->getMessage());
            return false;
        }
    }
}