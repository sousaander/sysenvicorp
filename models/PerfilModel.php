<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class PerfilModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca todos os perfis cadastrados.
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("SELECT perfil_id, nome_perfil, descricao FROM perfis_acesso ORDER BY nome_perfil ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar perfis: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um perfil específico pelo ID.
     */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT perfil_id, nome_perfil, descricao FROM perfis_acesso WHERE perfil_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
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
        $sql = $dados['id']
            ? "UPDATE perfis_acesso SET nome_perfil = :nome_perfil, descricao = :descricao WHERE perfil_id = :id"
            : "INSERT INTO perfis_acesso (nome_perfil, descricao) VALUES (:nome_perfil, :descricao)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome_perfil', $dados['nome_perfil']);
            $stmt->bindValue(':descricao', $dados['descricao'] ?: null);
            if ($dados['id']) {
                $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao salvar perfil: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um perfil.
     */
    public function excluir(int $id): bool
    {
        // A constraint da FK na tabela 'usuarios' (ON DELETE SET NULL) garantirá
        // que os usuários associados não sejam perdidos, apenas desvinculados do perfil.
        try {
            $stmt = $this->db->prepare("DELETE FROM perfis_acesso WHERE perfil_id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            // A constraint da FK também pode impedir a exclusão, gerando uma exceção.
            error_log("Erro ao excluir perfil: " . $e->getMessage());
            return false;
        }
    }
}
