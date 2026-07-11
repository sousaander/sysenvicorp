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
            $stmt = $this->db->query(
                "SELECT id, nome, nome_titular, tipo, saldo_inicial, logo, agencia, conta, ativo, cor, " .
                "banco_codigo, agencia_dv, conta_dv, pix_tipo, pix_chave, limite_credito, " .
                "observacoes FROM bancos ORDER BY nome ASC"
            );
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
            $stmt = $this->db->prepare(
                "SELECT id, nome, nome_titular, tipo, saldo_inicial, logo, agencia, conta, ativo, cor, " .
                "banco_codigo, agencia_dv, conta_dv, pix_tipo, pix_chave, limite_credito, " .
                "observacoes FROM bancos WHERE id = ?"
            );
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
        try {
            if ($dados['id']) {
                // UPDATE
                $sql = "UPDATE bancos SET 
                    nome = :nome,
                    nome_titular = :nome_titular,
                    tipo = :tipo,
                    banco_codigo = :banco_codigo,
                    agencia = :agencia,
                    agencia_dv = :agencia_dv,
                    conta = :conta,
                    conta_dv = :conta_dv,
                    pix_tipo = :pix_tipo,
                    pix_chave = :pix_chave,
                    saldo_inicial = :saldo_inicial,
                    limite_credito = :limite_credito,
                    cor = :cor,
                    ativo = :ativo,
                    observacoes = :observacoes" .
                    (isset($dados['logo']) ? ", logo = :logo" : "") .
                    " WHERE id = :id";
            } else {
                // INSERT
                $sql = "INSERT INTO bancos (
                    nome, nome_titular, tipo, banco_codigo, agencia, agencia_dv, conta, conta_dv,
                    pix_tipo, pix_chave, saldo_inicial, limite_credito, cor, ativo,
                    observacoes, logo
                ) VALUES (
                    :nome, :nome_titular, :tipo, :banco_codigo, :agencia, :agencia_dv, :conta, :conta_dv,
                    :pix_tipo, :pix_chave, :saldo_inicial, :limite_credito, :cor, :ativo,
                    :observacoes, :logo
                )";
            }

            $stmt = $this->db->prepare($sql);
            
            // Bind values
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':nome_titular', $dados['nome_titular'] ?? null);
            $stmt->bindValue(':tipo', $dados['tipo'] ?? 'corrente');
            $stmt->bindValue(':banco_codigo', $dados['banco_codigo'] ?? null);
            $stmt->bindValue(':agencia', $dados['agencia'] ?? null);
            $stmt->bindValue(':agencia_dv', $dados['agencia_dv'] ?? null);
            $stmt->bindValue(':conta', $dados['conta'] ?? null);
            $stmt->bindValue(':conta_dv', $dados['conta_dv'] ?? null);
            $stmt->bindValue(':pix_tipo', $dados['pix_tipo'] ?? null);
            $stmt->bindValue(':pix_chave', $dados['pix_chave'] ?? null);
            $stmt->bindValue(':saldo_inicial', $dados['saldo_inicial'] ?? 0.00);
            $stmt->bindValue(':limite_credito', $dados['limite_credito'] ?? 0.00);
            $stmt->bindValue(':cor', $dados['cor'] ?? '#10b981');
            $stmt->bindValue(':ativo', $dados['ativo'] ?? 1, PDO::PARAM_INT);
            $stmt->bindValue(':observacoes', $dados['observacoes'] ?? null);
            
            if (isset($dados['logo']) || !$dados['id']) {
                $stmt->bindValue(':logo', $dados['logo'] ?? null);
            }
            
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
