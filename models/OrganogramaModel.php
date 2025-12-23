<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class OrganogramaModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca a estrutura hierárquica e as métricas de atividade (mock)
     */
    public function getEstruturaEAtividades()
    {
        try {
            $sql = "
                SELECT 
                    e.id, 
                    e.cargo, 
                    e.responsavel, 
                    e.parent_id,
                    a.id as atividade_id,
                    a.nome as atividade_nome,
                    a.meta as atividade_meta,
                    a.progresso as atividade_progresso
                FROM organograma_estrutura e
                LEFT JOIN organograma_atividades a ON e.id = a.estrutura_id
                ORDER BY e.id, a.id;
            ";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar estrutura do organograma: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Adiciona um novo cargo/departamento na estrutura.
     * @param array $dados Dados do novo cargo (cargo, responsavel, parent_id).
     * @return bool
     */
    public function adicionarCargo(array $dados): bool
    {
        try {
            $sql = "INSERT INTO organograma_estrutura (cargo, responsavel, parent_id) VALUES (:cargo, :responsavel, :parent_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':cargo', $dados['cargo']);
            $stmt->bindValue(':responsavel', $dados['responsavel']);

            // Trata o caso de 'parent_id' ser '0' (Diretoria) ou um ID numérico
            if ($dados['parent_id'] === '' || $dados['parent_id'] === null) {
                $stmt->bindValue(':parent_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':parent_id', (int)$dados['parent_id'], PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro ao adicionar cargo no organograma: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza os dados de um cargo/departamento.
     * @param array $dados Dados a serem atualizados (id, cargo, responsavel).
     * @return bool
     */
    public function atualizarCargo(array $dados): bool
    {
        try {
            $sql = "UPDATE organograma_estrutura SET cargo = :cargo, responsavel = :responsavel WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            $stmt->bindValue(':cargo', $dados['cargo']);
            $stmt->bindValue(':responsavel', $dados['responsavel']);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro ao atualizar cargo no organograma: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um cargo/departamento da estrutura.
     * @param int $id O ID do cargo a ser excluído.
     * @return bool
     */
    public function excluirCargo(int $id): bool
    {
        // A constraint da FK na tabela 'organograma_estrutura' (ON DELETE SET NULL) fará com que
        // os cargos filhos se tornem nós raiz. As atividades serão excluídas em cascata.
        try {
            $stmt = $this->db->prepare("DELETE FROM organograma_estrutura WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir cargo do organograma: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adiciona uma nova atividade/meta a um cargo.
     * @param array $dados Dados da atividade (estrutura_id, nome, meta).
     * @return int|false Retorna o ID da nova atividade ou false em caso de erro.
     */
    public function adicionarAtividade(array $dados)
    {
        try {
            $sql = "INSERT INTO organograma_atividades (estrutura_id, nome, meta, progresso) VALUES (:estrutura_id, :nome, :meta, 0)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':estrutura_id', $dados['estrutura_id'], PDO::PARAM_INT);
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':meta', $dados['meta']);

            $stmt->execute();
            return (int)$this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Erro ao adicionar atividade no organograma: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza os dados de uma atividade/meta.
     * @param array $dados Dados da atividade (id, nome, meta, progresso).
     * @return bool
     */
    public function atualizarAtividade(array $dados): bool
    {
        try {
            $sql = "UPDATE organograma_atividades SET nome = :nome, meta = :meta, progresso = :progresso WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':meta', $dados['meta']);
            $stmt->bindValue(':progresso', $dados['progresso'], PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro ao atualizar atividade no organograma: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui uma atividade/meta.
     * @param int $id O ID da atividade a ser excluída.
     * @return bool
     */
    public function excluirAtividade(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM organograma_atividades WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir atividade do organograma: " . $e->getMessage());
            return false;
        }
    }
}
