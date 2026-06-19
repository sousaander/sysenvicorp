<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class TarefasModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        // Garante que a tabela exista (útil para o primeiro uso)
        $this->createTableIfNotExists();
    }

    /**
     * Cria a tabela de tarefas no banco de dados se ela não existir.
     */
    private function createTableIfNotExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS projetos_tarefas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            projeto_id INT NOT NULL,
            titulo VARCHAR(255) NOT NULL,
            descricao TEXT,
            status ENUM('Pendente', 'Em Andamento', 'Concluída', 'Cancelada') DEFAULT 'Pendente',
            prioridade ENUM('Baixa', 'Média', 'Alta', 'Urgente') DEFAULT 'Média',
            data_inicio DATE,
            data_fim DATE,
            responsavel_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sqlChecklist = "CREATE TABLE IF NOT EXISTS projetos_tarefas_checklist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tarefa_id INT NOT NULL,
            descricao VARCHAR(255) NOT NULL,
            concluido TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tarefa_id) REFERENCES projetos_tarefas(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sqlLogs = "CREATE TABLE IF NOT EXISTS projetos_tarefas_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            projeto_id INT NOT NULL,
            tarefa_id INT NULL,
            usuario_id INT NULL,
            acao VARCHAR(50) NOT NULL,
            descricao TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
            FOREIGN KEY (tarefa_id) REFERENCES projetos_tarefas(id) ON DELETE SET NULL,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sqlDependencias = "CREATE TABLE IF NOT EXISTS projetos_tarefas_dependencias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tarefa_id INT NOT NULL,
            dependencia_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tarefa_id) REFERENCES projetos_tarefas(id) ON DELETE CASCADE,
            FOREIGN KEY (dependencia_id) REFERENCES projetos_tarefas(id) ON DELETE CASCADE,
            UNIQUE KEY unique_dependency (tarefa_id, dependencia_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sqlTags = "CREATE TABLE IF NOT EXISTS projetos_tarefas_tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            projeto_id INT NOT NULL,
            nome VARCHAR(50) NOT NULL,
            cor VARCHAR(7) DEFAULT '#6B7280',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sqlTagsVinculo = "CREATE TABLE IF NOT EXISTS projetos_tarefas_tags_vinculo (
            tarefa_id INT NOT NULL,
            tag_id INT NOT NULL,
            PRIMARY KEY (tarefa_id, tag_id),
            FOREIGN KEY (tarefa_id) REFERENCES projetos_tarefas(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES projetos_tarefas_tags(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sqlComentarios = "CREATE TABLE IF NOT EXISTS projetos_tarefas_comentarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tarefa_id INT NOT NULL,
            usuario_id INT NOT NULL,
            comentario TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tarefa_id) REFERENCES projetos_tarefas(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $this->db->exec($sql);
            $this->db->exec($sqlChecklist);
            $this->db->exec($sqlLogs);
            $this->db->exec($sqlDependencias);
            $this->db->exec($sqlTags);
            $this->db->exec($sqlTagsVinculo);
            $this->db->exec($sqlComentarios);
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela de tarefas: " . $e->getMessage());
        }
    }

    /**
     * Busca todas as tarefas de um projeto.
     */
    public function getTarefasByProjetoId(int $projetoId, array $filtros = []): array
    {
        try {
            $sql = "SELECT t.*, u.nome as responsavel_nome 
                    FROM projetos_tarefas t
                    LEFT JOIN usuarios u ON t.responsavel_id = u.id
                    WHERE t.projeto_id = :projeto_id";

            $params = ['projeto_id' => $projetoId];

            if (!empty($filtros['status'])) {
                $sql .= " AND t.status = :status";
                $params['status'] = $filtros['status'];
            }

            if (!empty($filtros['responsavel_id'])) {
                $sql .= " AND t.responsavel_id = :responsavel_id";
                $params['responsavel_id'] = $filtros['responsavel_id'];
            }

            $sql .= " ORDER BY 
                        CASE 
                            WHEN t.status = 'Pendente' THEN 1
                            WHEN t.status = 'Em Andamento' THEN 2
                            WHEN t.status = 'Concluída' THEN 3
                            ELSE 4
                        END,
                        t.data_fim ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($tarefas)) {
                $tarefaIds = array_column($tarefas, 'id');
                $inQuery = implode(',', array_fill(0, count($tarefaIds), '?'));

                $sqlTags = "SELECT tv.tarefa_id, t.id, t.nome, t.cor 
                            FROM projetos_tarefas_tags t 
                            JOIN projetos_tarefas_tags_vinculo tv ON t.id = tv.tag_id 
                            WHERE tv.tarefa_id IN ($inQuery)";
                $stmtTags = $this->db->prepare($sqlTags);
                $stmtTags->execute($tarefaIds);
                $tags = $stmtTags->fetchAll(PDO::FETCH_ASSOC);

                $tagsByTarefa = [];
                foreach ($tags as $tag) $tagsByTarefa[$tag['tarefa_id']][] = $tag;
                foreach ($tarefas as &$tarefa) $tarefa['tags'] = $tagsByTarefa[$tarefa['id']] ?? [];
            }
            return $tarefas;
        } catch (PDOException $e) {
            error_log("Erro ao buscar tarefas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta as tarefas pendentes (Pendente ou Em Andamento) de um usuário específico.
     */
    public function getCountTarefasPendentesByUsuario(int $usuarioId): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM projetos_tarefas 
                    WHERE responsavel_id = :usuario_id 
                    AND status IN ('Pendente', 'Em Andamento')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['usuario_id' => $usuarioId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar tarefas pendentes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Busca as últimas tarefas pendentes de um usuário específico.
     */
    public function getTarefasPendentesByUsuario(int $usuarioId, int $limit = 5): array
    {
        try {
            $sql = "SELECT t.id, t.titulo, t.prioridade, t.data_fim, t.projeto_id, p.nome as projeto_nome
                    FROM projetos_tarefas t
                    LEFT JOIN projetos p ON t.projeto_id = p.id
                    WHERE t.responsavel_id = :usuario_id 
                    AND t.status IN ('Pendente', 'Em Andamento')
                    ORDER BY t.data_fim ASC, t.prioridade DESC
                    LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar tarefas pendentes do usuário: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva (cria ou atualiza) uma tarefa.
     */
    public function salvar(array $dados, int $usuarioId): bool
    {
        try {
            if (empty($dados['id'])) {
                $acao = 'Criou Tarefa';
                $sql = "INSERT INTO projetos_tarefas (projeto_id, titulo, descricao, status, prioridade, data_inicio, data_fim, responsavel_id) 
                        VALUES (:projeto_id, :titulo, :descricao, :status, :prioridade, :data_inicio, :data_fim, :responsavel_id)";
                $stmt = $this->db->prepare($sql);
                $descricaoLog = "Criou a tarefa '{$dados['titulo']}'";
            } else {
                $acao = 'Editou Tarefa';
                $sql = "UPDATE projetos_tarefas SET 
                        titulo = :titulo, descricao = :descricao, status = :status, prioridade = :prioridade, 
                        data_inicio = :data_inicio, data_fim = :data_fim, responsavel_id = :responsavel_id
                        WHERE id = :id AND projeto_id = :projeto_id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
                $descricaoLog = "Atualizou a tarefa '{$dados['titulo']}'";
            }

            $stmt->bindValue(':projeto_id', $dados['projeto_id'], PDO::PARAM_INT);
            $stmt->bindValue(':titulo', $dados['titulo']);
            $stmt->bindValue(':descricao', $dados['descricao']);
            $stmt->bindValue(':status', $dados['status']);
            $stmt->bindValue(':prioridade', $dados['prioridade']);
            $stmt->bindValue(':data_inicio', !empty($dados['data_inicio']) ? $dados['data_inicio'] : null);
            $stmt->bindValue(':data_fim', !empty($dados['data_fim']) ? $dados['data_fim'] : null);
            $stmt->bindValue(':responsavel_id', !empty($dados['responsavel_id']) ? $dados['responsavel_id'] : null, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $tarefaId = !empty($dados['id']) ? $dados['id'] : $this->db->lastInsertId();

                // Processa as tags se enviadas
                if (isset($dados['tags'])) {
                    $this->vincularTags($tarefaId, is_array($dados['tags']) ? $dados['tags'] : []);
                }

                $this->registrarLog($dados['projeto_id'], $tarefaId, $usuarioId, $acao, $descricaoLog);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao salvar tarefa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o status de uma tarefa.
     */
    public function updateStatus(int $id, string $status, int $usuarioId): bool
    {
        try {
            $tarefa = $this->getTarefaById($id);
            if (!$tarefa) return false;

            $sql = "UPDATE projetos_tarefas SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $this->registrarLog($tarefa['projeto_id'], $id, $usuarioId, 'Alterou Status', "Alterou status para '$status'");
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status da tarefa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui uma tarefa.
     */
    public function excluir(int $id, int $usuarioId): bool
    {
        try {
            // Busca dados para o log antes de excluir
            $tarefa = $this->getTarefaById($id);

            $stmt = $this->db->prepare("DELETE FROM projetos_tarefas WHERE id = :id");
            if ($stmt->execute(['id' => $id]) && $tarefa) {
                $this->registrarLog($tarefa['projeto_id'], null, $usuarioId, 'Excluiu Tarefa', "Excluiu a tarefa '{$tarefa['titulo']}'");
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao excluir tarefa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca uma tarefa específica pelo ID, incluindo dados do responsável e do projeto.
     */
    public function getTarefaById(int $id): ?array
    {
        try {
            $sql = "SELECT t.*, u.nome as responsavel_nome, u.email as responsavel_email, p.nome as projeto_nome 
                    FROM projetos_tarefas t
                    LEFT JOIN usuarios u ON t.responsavel_id = u.id
                    LEFT JOIN projetos p ON t.projeto_id = p.id
                    WHERE t.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar tarefa por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca os itens do checklist de uma tarefa.
     */
    public function getChecklistByTarefaId(int $tarefaId): array
    {
        try {
            $sql = "SELECT * FROM projetos_tarefas_checklist WHERE tarefa_id = :tarefa_id ORDER BY id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['tarefa_id' => $tarefaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar checklist: " . $e->getMessage());
            return [];
        }
    }

    public function addChecklistItem(int $tarefaId, string $descricao, int $usuarioId): bool
    {
        try {
            $sql = "INSERT INTO projetos_tarefas_checklist (tarefa_id, descricao) VALUES (:tarefa_id, :descricao)";
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute(['tarefa_id' => $tarefaId, 'descricao' => $descricao])) {
                $tarefa = $this->getTarefaById($tarefaId);
                if ($tarefa) {
                    $this->registrarLog($tarefa['projeto_id'], $tarefaId, $usuarioId, 'Checklist', "Adicionou item: '$descricao'");
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao adicionar item ao checklist: " . $e->getMessage());
            return false;
        }
    }

    public function toggleChecklistItem(int $id, int $status, int $usuarioId): bool
    {
        // Busca item para log
        $stmtGet = $this->db->prepare("SELECT c.*, t.projeto_id FROM projetos_tarefas_checklist c JOIN projetos_tarefas t ON c.tarefa_id = t.id WHERE c.id = ?");
        $stmtGet->execute([$id]);
        $item = $stmtGet->fetch(PDO::FETCH_ASSOC);

        $sql = "UPDATE projetos_tarefas_checklist SET concluido = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute(['status' => $status, 'id' => $id]) && $item) {
            $acaoDesc = $status ? "Concluiu item" : "Reabriu item";
            $this->registrarLog($item['projeto_id'], $item['tarefa_id'], $usuarioId, 'Checklist', "$acaoDesc: '{$item['descricao']}'");
            return true;
        }
        return false;
    }

    public function deleteChecklistItem(int $id, int $usuarioId): bool
    {
        // Busca item para log
        $stmtGet = $this->db->prepare("SELECT c.*, t.projeto_id FROM projetos_tarefas_checklist c JOIN projetos_tarefas t ON c.tarefa_id = t.id WHERE c.id = ?");
        $stmtGet->execute([$id]);
        $item = $stmtGet->fetch(PDO::FETCH_ASSOC);

        $sql = "DELETE FROM projetos_tarefas_checklist WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute(['id' => $id]) && $item) {
            $this->registrarLog($item['projeto_id'], $item['tarefa_id'], $usuarioId, 'Checklist', "Removeu item: '{$item['descricao']}'");
            return true;
        }
        return false;
    }

    public function registrarLog(int $projetoId, ?int $tarefaId, int $usuarioId, string $acao, string $descricao)
    {
        try {
            $sql = "INSERT INTO projetos_tarefas_logs (projeto_id, tarefa_id, usuario_id, acao, descricao) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projetoId, $tarefaId, $usuarioId, $acao, $descricao]);
        } catch (PDOException $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }

    public function getLogs(int $projetoId, int $limit = 10, int $offset = 0): array
    {
        try {
            $sql = "SELECT l.*, u.nome as usuario_nome, t.titulo as tarefa_titulo
                    FROM projetos_tarefas_logs l
                    LEFT JOIN usuarios u ON l.usuario_id = u.id
                    LEFT JOIN projetos_tarefas t ON l.tarefa_id = t.id
                    WHERE l.projeto_id = :projeto_id
                    ORDER BY l.created_at DESC
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar logs: " . $e->getMessage());
            return [];
        }
    }

    public function getLogsCount(int $projetoId): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM projetos_tarefas_logs WHERE projeto_id = :projeto_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['projeto_id' => $projetoId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar logs: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Adiciona uma dependência entre tarefas.
     */
    public function addDependencia(int $tarefaId, int $dependenciaId): bool
    {
        if ($tarefaId === $dependenciaId) return false; // Não pode depender de si mesma

        try {
            $sql = "INSERT INTO projetos_tarefas_dependencias (tarefa_id, dependencia_id) VALUES (:tarefa_id, :dependencia_id)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['tarefa_id' => $tarefaId, 'dependencia_id' => $dependenciaId]);
        } catch (PDOException $e) {
            error_log("Erro ao adicionar dependência: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove uma dependência.
     */
    public function removeDependencia(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM projetos_tarefas_dependencias WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Erro ao remover dependência: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca as dependências de uma tarefa.
     */
    public function getDependencias(int $tarefaId): array
    {
        try {
            $sql = "SELECT d.id, d.dependencia_id, t.titulo, t.status 
                    FROM projetos_tarefas_dependencias d
                    JOIN projetos_tarefas t ON d.dependencia_id = t.id
                    WHERE d.tarefa_id = :tarefa_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['tarefa_id' => $tarefaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar dependências: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se todas as dependências de uma tarefa estão concluídas.
     * Retorna true se estiver livre para iniciar/concluir.
     */
    public function checkDependenciasConcluidas(int $tarefaId): bool
    {
        try {
            // Conta quantas dependências NÃO estão concluídas
            $sql = "SELECT COUNT(*) 
                    FROM projetos_tarefas_dependencias d
                    JOIN projetos_tarefas t ON d.dependencia_id = t.id
                    WHERE d.tarefa_id = :tarefa_id AND t.status != 'Concluída'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['tarefa_id' => $tarefaId]);
            return $stmt->fetchColumn() == 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar dependências: " . $e->getMessage());
            return false;
        }
    }

    // --- Métodos para Tags ---

    public function getTagsByProjetoId(int $projetoId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM projetos_tarefas_tags WHERE projeto_id = ? ORDER BY nome ASC");
        $stmt->execute([$projetoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvarTag(array $dados): bool
    {
        if (empty($dados['id'])) {
            $sql = "INSERT INTO projetos_tarefas_tags (projeto_id, nome, cor) VALUES (:projeto_id, :nome, :cor)";
            $stmt = $this->db->prepare($sql);
        } else {
            $sql = "UPDATE projetos_tarefas_tags SET nome = :nome, cor = :cor WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $dados['id']);
        }

        if (!empty($dados['projeto_id'])) $stmt->bindValue(':projeto_id', $dados['projeto_id']);
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':cor', $dados['cor']);

        return $stmt->execute();
    }

    public function excluirTag(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM projetos_tarefas_tags WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function vincularTags(int $tarefaId, array $tagIds)
    {
        $this->db->prepare("DELETE FROM projetos_tarefas_tags_vinculo WHERE tarefa_id = ?")->execute([$tarefaId]);
        if (empty($tagIds)) return;
        $values = implode(", ", array_fill(0, count($tagIds), "($tarefaId, ?)"));
        $this->db->prepare("INSERT INTO projetos_tarefas_tags_vinculo (tarefa_id, tag_id) VALUES $values")->execute($tagIds);
    }

    /**
     * Busca os comentários de uma tarefa.
     */
    public function getComentariosByTarefaId(int $tarefaId): array
    {
        try {
            $sql = "SELECT c.*, u.nome as usuario_nome 
                    FROM projetos_tarefas_comentarios c
                    JOIN usuarios u ON c.usuario_id = u.id
                    WHERE c.tarefa_id = :tarefa_id
                    ORDER BY c.created_at ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['tarefa_id' => $tarefaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar comentários: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva um novo comentário.
     */
    public function salvarComentario(int $tarefaId, int $usuarioId, string $comentario): bool
    {
        try {
            $sql = "INSERT INTO projetos_tarefas_comentarios (tarefa_id, usuario_id, comentario) VALUES (:tarefa_id, :usuario_id, :comentario)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['tarefa_id' => $tarefaId, 'usuario_id' => $usuarioId, 'comentario' => $comentario]);
        } catch (PDOException $e) {
            error_log("Erro ao salvar comentário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o texto de um comentário existente.
     * Apenas o autor do comentário pode editá-lo.
     */
    public function atualizarComentario(int $comentarioId, string $novoTexto, int $usuarioId): bool
    {
        try {
            $sql = "UPDATE projetos_tarefas_comentarios SET comentario = :comentario WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'comentario' => $novoTexto,
                'id' => $comentarioId,
                'usuario_id' => $usuarioId
            ]);
            // rowCount() > 0 significa que a atualização foi bem-sucedida e o usuário era o dono.
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar comentário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um comentário.
     * Apenas o autor do comentário pode excluí-lo.
     */
    public function excluirComentario(int $comentarioId, int $usuarioId): bool
    {
        try {
            $sql = "DELETE FROM projetos_tarefas_comentarios WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $comentarioId, 'usuario_id' => $usuarioId]);
        } catch (PDOException $e) {
            error_log("Erro ao excluir comentário: " . $e->getMessage());
            return false;
        }
    }
}
