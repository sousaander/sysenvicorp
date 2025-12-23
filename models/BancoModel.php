<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class BancoModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca todos os bancos cadastrados.
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, nome, tipo, saldo_inicial FROM bancos ORDER BY nome ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar bancos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um banco específico pelo ID.
     */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nome, tipo, saldo_inicial FROM bancos WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar banco por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva (insere ou atualiza) um banco.
     */
    public function salvar(array $dados): bool
    {
        $sql = $dados['id']
            ? "UPDATE bancos SET nome = :nome, tipo = :tipo, saldo_inicial = :saldo_inicial WHERE id = :id"
            : "INSERT INTO bancos (nome, tipo, saldo_inicial) VALUES (:nome, :tipo, :saldo_inicial)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':tipo', $dados['tipo'] ?: 'Conta Corrente');
            $stmt->bindValue(':saldo_inicial', $dados['saldo_inicial']);
            if ($dados['id']) {
                $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao salvar banco: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um banco.
     */
    public function excluir(int $id): bool
    {
        // Adicionamos uma verificação para não excluir bancos com transações associadas.
        try {
            $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM transacoes WHERE banco_id = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                error_log("Tentativa de excluir banco (ID: $id) com transações associadas.");
                return false; // Impede a exclusão
            }

            $stmt = $this->db->prepare("DELETE FROM bancos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            // A constraint da FK também pode impedir a exclusão, gerando uma exceção.
            error_log("Erro ao excluir banco: " . $e->getMessage());
            return false;
        }
    }
}
