<?php

namespace App\Models;

use App\Core\Model;
use PDOException;

use PDO;

class UsuarioModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca a lista completa de usuários do sistema, incluindo seus perfis de acesso.
     */
    public function getListaUsuarios(): array
    {
        try {
            // CORREÇÃO: Fazer JOIN com as tabelas 'cargos' e 'perfis' para buscar os nomes corretos.
            $sql = "SELECT u.id, u.nome, c.nome_cargo as cargo, p.nome_perfil as perfil, u.status 
                    FROM usuarios u
                    LEFT JOIN cargos c ON u.cargo_id = c.cargo_id
                    LEFT JOIN perfis_acesso p ON u.perfil_id = p.perfil_id
                    ORDER BY u.nome ASC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar lista de usuários: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca os perfis de acesso e permissões disponíveis.
     */
    public function getPerfisDeAcesso(): array
    {
        try {
            // Busca os perfis reais da tabela 'perfis_acesso'
            $stmt = $this->db->query("SELECT perfil_id, nome_perfil, descricao FROM perfis_acesso ORDER BY nome_perfil ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar perfis de acesso: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um usuário específico pelo ID.
     */
    public function getUsuario(int $id): ?array
    {
        try {
            // Seleciona os IDs para preencher corretamente os formulários de edição
            $sql = "SELECT id, nome, email, status, cargo_id, perfil_id 
                    FROM usuarios 
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                // Verifica se há uma foto salva no disco para este usuário
                $possibleExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                foreach ($possibleExt as $ext) {
                    $filename = "users/user_{$id}." . $ext;
                    $filePath = ROOT_PATH . '/storage/' . $filename;
                    if (file_exists($filePath)) {
                        $result['foto_url'] = rtrim(BASE_URL, '/') . '/storage/' . $filename;
                        break;
                    }
                }
                if (empty($result['foto_url'])) {
                    $result['foto_url'] = null;
                }
            }

            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um usuário pelo seu e-mail.
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        try {
            $sql = "SELECT id, nome, email, senha_hash, status 
                    FROM usuarios 
                    WHERE email = ? AND status = 'Ativo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário por e-mail: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cria um novo usuário a partir do registro público.
     * @param string $nome
     * @param string $email
     * @param string $senha
     * @return bool
     */
    public function createUser(string $nome, string $email, string $senha): bool
    {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        // Define um perfil e cargo padrão para novos usuários.
        // Em um sistema real, você pode querer buscar os IDs de 'Usuário' e 'Não Definido'.
        $perfilIdPadrao = 2; // Supondo que '2' seja o ID para 'Usuário'
        $cargoIdPadrao = 1; // Supondo que '1' seja o ID para 'Não Definido' ou 'Colaborador'

        $sql = "INSERT INTO usuarios (nome, email, senha_hash, perfil_id, cargo_id, status) 
                VALUES (:nome, :email, :senha_hash, :perfil_id, :cargo_id, 'Ativo')";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':senha_hash', $senhaHash);
            $stmt->bindValue(':perfil_id', $perfilIdPadrao, PDO::PARAM_INT);
            $stmt->bindValue(':cargo_id', $cargoIdPadrao, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao criar novo usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Salva um novo usuário.
     */
    public function salvarUsuario(array $dados): bool
    {
        $sql = "INSERT INTO usuarios (nome, cargo, perfil, status) VALUES (:nome, :cargo, :perfil, :status)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':cargo', $dados['cargo']);
            $stmt->bindValue(':perfil', $dados['perfil']);
            $stmt->bindValue(':status', $dados['status']);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao salvar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza um usuário existente.
     */
    public function atualizarUsuario(int $id, array $dados): bool
    {
        // Não atualizamos a senha aqui, isso deve ser uma funcionalidade separada.
        $sql = "UPDATE usuarios SET nome = :nome, email = :email, cargo_id = :cargo_id, perfil_id = :perfil_id, status = :status WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':email', $dados['email']);
            $stmt->bindValue(':cargo_id', $dados['cargo_id'], PDO::PARAM_INT);
            $stmt->bindValue(':perfil_id', $dados['perfil_id'], PDO::PARAM_INT);
            $stmt->bindValue(':status', $dados['status']);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o status de um usuário.
     */
    public function atualizarStatus(int $id, string $novoStatus): bool
    {
        $sql = "UPDATE usuarios SET status = :status WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':status', $novoStatus);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status do usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza os dados do perfil de um usuário (nome, e-mail e senha opcional).
     * @param array $dados
     * @return bool
     */
    public function atualizarPerfil(array $dados): bool
    {
        // A query é montada dinamicamente para atualizar a senha apenas se ela for fornecida
        $sql = "UPDATE usuarios SET nome = :nome, email = :email";
        $params = [
            ':nome' => $dados['nome'],
            ':email' => $dados['email'],
            ':id' => $dados['id']
        ];

        // Se uma nova senha foi enviada, faz o hash e adiciona à query
        if (!empty($dados['senha'])) {
            $sql .= ", senha_hash = :senha_hash";
            $params[':senha_hash'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            // Verifica se o erro é de e-mail duplicado (código de erro 1062 para MySQL)
            if ($e->errorInfo[1] == 1062) {
                error_log("Tentativa de atualizar para e-mail duplicado: " . $dados['email']);
            } else {
                error_log("Erro ao atualizar perfil: " . $e->getMessage());
            }
            return false;
        }
    }
}
