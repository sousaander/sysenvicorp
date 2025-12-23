<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class TreinamentosModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca todos os treinamentos com paginação.
     */
    public function getAllTreinamentos(int $limit, int $offset): array
    {
        try {
            $sql = "SELECT * FROM treinamentos ORDER BY data_prevista DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar treinamentos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o total de treinamentos.
     */
    public function getTreinamentosCount(): int
    {
        try {
            return (int) $this->db->query("SELECT COUNT(*) FROM treinamentos")->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar treinamentos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Busca um treinamento pelo ID.
     */
    public function getTreinamentoById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM treinamentos WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar treinamento por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva (insere ou atualiza) um treinamento.
     */
    public function salvarTreinamento(array $dados): bool
    {
        $sql = $dados['id']
            ? "UPDATE treinamentos SET nome_treinamento = :nome, descricao = :desc, data_prevista = :data, instrutor = :instrutor, local = :local, status = :status WHERE id = :id"
            : "INSERT INTO treinamentos (nome_treinamento, descricao, data_prevista, instrutor, local, status) VALUES (:nome, :desc, :data, :instrutor, :local, :status)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $dados['nome_treinamento']);
            $stmt->bindValue(':desc', $dados['descricao']);
            $stmt->bindValue(':data', $dados['data_prevista']);
            $stmt->bindValue(':instrutor', $dados['instrutor']);
            $stmt->bindValue(':local', $dados['local']);
            $stmt->bindValue(':status', $dados['status']);
            if ($dados['id']) {
                $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao salvar treinamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um treinamento.
     */
    public function excluirTreinamento(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM treinamentos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao excluir treinamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o próximo treinamento agendado.
     */
    public function getProximoTreinamento(): ?array
    {
        try {
            $sql = "SELECT nome_treinamento, data_prevista 
                    FROM treinamentos 
                    WHERE status = 'Agendado' AND data_prevista >= CURDATE()
                    ORDER BY data_prevista ASC 
                    LIMIT 1";
            $stmt = $this->db->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar próximo treinamento: " . $e->getMessage());
            return null;
        }
    }
}
