<?php

namespace App\Models;

use App\Core\Model;
use PDOException;

use PDO;

class UsuarioModel extends Model
{
    /**
     * Lista de avatares disponíveis na pasta public/assets/avatars/
     */
    public const AVATARS_PADRAO = [
        'avatar1.png', 'avatar2.png', 'avatar3.png', 'avatar4.png', 'avatar5.png',
        'avatar6.png', 'avatar7.png', 'avatar8.png', 'avatar9.png', 'avatar10.png'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->ensureColumnsExist();
    }

    /**
     * Garante que as colunas necessárias existam na tabela usuários.
     */
    private function ensureColumnsExist()
    {
        try {
            // Verifica se a coluna avatar_filename existe, caso contrário, cria-a.
            $stmt = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'avatar_filename'");
            if (!$stmt->fetch()) {
                $this->db->exec("ALTER TABLE usuarios ADD COLUMN avatar_filename VARCHAR(255) NULL AFTER status");
            }
        } catch (PDOException $e) {
            error_log("Erro ao sincronizar schema da tabela usuarios: " . $e->getMessage());
        }
    }

    /**
     * Busca a lista completa de usuários do sistema, incluindo seus perfis de acesso.
     */
    public function getListaUsuarios(?string $status = null): array
    {
        try {
            // CORREÇÃO: Adicionado email, cargo_id e perfil_id para popular o formulário de edição.
            // ALTERAÇÃO: Adicionada cláusula WHERE com subquery para filtrar apenas o ID mais recente de cada e-mail.
            // Isso resolve o problema visual de exibir usuários duplicados na lista.
            $params = [];
            $sql = "SELECT u.id, u.nome, u.email, c.nome_cargo as cargo, p.nome_perfil as perfil, u.status, u.cargo_id, u.perfil_id, u.perfil_id as perfil_id_val
                    FROM usuarios u
                    LEFT JOIN cargos c ON u.cargo_id = c.cargo_id
                    LEFT JOIN perfis_acesso p ON u.perfil_id = p.perfil_id
                    WHERE u.id IN (
                        SELECT MAX(id) FROM usuarios 
                        GROUP BY LOWER(TRIM(email))
                    )";

            if ($status) {
                $sql .= " AND u.status = :status";
                $params[':status'] = $status;
            }

            $sql .= " GROUP BY u.id
                    ORDER BY u.nome ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
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
            // Adicionado alias 'id' para compatibilidade com views que esperam 'id' no select
            $stmt = $this->db->query("SELECT perfil_id, perfil_id as id, nome_perfil, descricao FROM perfis_acesso ORDER BY nome_perfil ASC");
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
            // Seleciona os IDs e avatar_filename para preencher corretamente os formulários de edição
            $sql = "SELECT id, nome, email, status, cargo_id, perfil_id, avatar_filename
                    FROM usuarios 
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $result['foto_url'] = null; // Inicializa a variável

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

                // Se não há foto de upload, verifica se há um avatar selecionado na galeria
                if (empty($result['foto_url']) && !empty($result['avatar_filename'])) {
                    $result['foto_url'] = rtrim(BASE_URL, '/') . '/public/assets/avatars/' . $result['avatar_filename'];
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
            // OTIMIZAÇÃO: Removido LOWER/TRIM da coluna para permitir uso de INDICE.
            $sql = "SELECT u.id, u.nome, u.email, u.senha_hash, u.status, u.avatar_filename, p.nome_perfil as perfil, p.permissoes as permissoes, c.nome_cargo as cargo_nome
                    FROM usuarios u
                    LEFT JOIN perfis_acesso p ON u.perfil_id = p.perfil_id
                    LEFT JOIN cargos c ON u.cargo_id = c.cargo_id
                    WHERE u.email = ? AND u.status = 'Ativo' ORDER BY u.id DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([strtolower(trim($email))]);
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
        // Verifica duplicidade antes de inserir
        if ($this->emailExists($email)) {
            return false;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Lógica de Autocorreção do Perfil Padrão
        // Busca o perfil 'Colaborador' para garantir que temos o ID correto e que ele tem permissões
        $stmt = $this->db->prepare("SELECT perfil_id, permissoes FROM perfis_acesso WHERE nome_perfil = 'Colaborador' LIMIT 1");
        $stmt->execute();
        $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

        $permissoesBasicas = json_encode(['dashboard_view', 'clientes_view', 'projetos_view', 'financeiro_dashboard_view', 'licencas_operacao_view']);

        if ($perfil) {
            $perfilIdPadrao = $perfil['perfil_id'];
            // Se o perfil existe mas não tem permissões configuradas, adiciona as básicas
            if (empty($perfil['permissoes']) || $perfil['permissoes'] === 'null') {
                $this->db->prepare("UPDATE perfis_acesso SET permissoes = ? WHERE perfil_id = ?")->execute([$permissoesBasicas, $perfilIdPadrao]);
            }
        } else {
            // Se o perfil não existe, cria um novo com permissões básicas
            $this->db->prepare("INSERT INTO perfis_acesso (nome_perfil, descricao, permissoes) VALUES ('Colaborador', 'Perfil padrão para novos usuários', ?)")->execute([$permissoesBasicas]);
            $perfilIdPadrao = $this->db->lastInsertId();
        }

        // Lógica para Cargo Padrão (Dinâmico)
        $cargoNome = 'Não Definido';
        $stmtCargo = $this->db->prepare("SELECT cargo_id FROM cargos WHERE nome_cargo = ?");
        $stmtCargo->execute([$cargoNome]);
        $cargoIdPadrao = $stmtCargo->fetchColumn();

        if (!$cargoIdPadrao) {
            $this->db->prepare("INSERT INTO cargos (nome_cargo) VALUES (?)")->execute([$cargoNome]);
            $cargoIdPadrao = $this->db->lastInsertId();
        }

        // Sorteia um avatar para o novo usuário
        $avatarAleatorio = self::AVATARS_PADRAO[array_rand(self::AVATARS_PADRAO)];

        $sql = "INSERT INTO usuarios (nome, email, senha_hash, perfil_id, cargo_id, status, avatar_filename) 
                VALUES (:nome, :email, :senha_hash, :perfil_id, :cargo_id, 'Ativo', :avatar)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':senha_hash', $senhaHash);
            $stmt->bindValue(':perfil_id', $perfilIdPadrao, PDO::PARAM_INT);
            $stmt->bindValue(':cargo_id', $cargoIdPadrao, PDO::PARAM_INT);
            $stmt->bindValue(':avatar', $avatarAleatorio);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao criar novo usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Salva um novo usuário a partir do painel de admin.
     * Este método substitui a implementação anterior que estava incorreta.
     */
    public function salvarUsuario(array $dados): bool
    {
        // Verifica duplicidade antes de inserir
        if ($this->emailExists($dados['email'])) {
            return false;
        }

        // Gera uma senha padrão para o novo usuário
        $senhaHash = password_hash('Mudar@123', PASSWORD_DEFAULT);

        // Sorteia um avatar para o novo usuário
        $avatarAleatorio = self::AVATARS_PADRAO[array_rand(self::AVATARS_PADRAO)];

        $sql = "INSERT INTO usuarios (nome, email, senha_hash, cargo_id, perfil_id, status, avatar_filename) 
                VALUES (:nome, :email, :senha_hash, :cargo_id, :perfil_id, :status, :avatar)";
        try {
            // A verificação de e-mail duplicado é tratada pela constraint UNIQUE no banco de dados,
            // que lançará uma PDOException.
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':email', $dados['email']);
            $stmt->bindValue(':senha_hash', $senhaHash);
            $stmt->bindValue(':cargo_id', $dados['cargo_id'], PDO::PARAM_INT);
            $stmt->bindValue(':perfil_id', $dados['perfil_id'], PDO::PARAM_INT);
            $stmt->bindValue(':status', $dados['status']);
            $stmt->bindValue(':avatar', $avatarAleatorio);

            return $stmt->execute();
        } catch (PDOException $e) {
            // Verifica se o erro é de e-mail duplicado (código de erro 1062 para MySQL)
            // Isso é mais confiável do que fazer um SELECT antes.
            if ($e->errorInfo[1] == 1062) {
                error_log("Tentativa de criar usuário com e-mail duplicado: " . $dados['email']);
            } else {
                error_log("Erro ao salvar usuário: " . $e->getMessage());
            }
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
     * Redefine a senha de um usuário para um valor padrão.
     * @param int $id O ID do usuário.
     * @return bool Retorna true em sucesso, false em falha.
     */
    public function resetarSenha(int $id): bool
    {
        // A senha padrão é a mesma usada na criação de novos usuários.
        $senhaPadrao = 'Mudar@123';
        $senhaHash = password_hash($senhaPadrao, PASSWORD_DEFAULT);

        $sql = "UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':senha_hash', $senhaHash);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao resetar senha do usuário ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um usuário e seu registro de colaborador correspondente de forma transacional.
     * Este método serve como uma alternativa manual à exclusão via módulo de RH.
     * @param int $id O ID do usuário a ser excluído.
     * @return bool Retorna true em sucesso, false em falha.
     */
    public function excluirUsuario(int $id): bool
    {
        // Inicia uma transação para garantir que ambas as exclusões ocorram ou nenhuma ocorra.
        $this->db->beginTransaction();

        try {
            // 1. Exclui da tabela 'colaboradores'.
            // A ordem é importante se não houver 'ON DELETE CASCADE' na FK.
            // Excluir da tabela filha (colaboradores) primeiro.
            $stmtColaborador = $this->db->prepare("DELETE FROM colaboradores WHERE colaborador_id = ?");
            $stmtColaborador->execute([$id]);

            // 2. Exclui da tabela 'usuarios'.
            $stmtUsuario = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmtUsuario->execute([$id]);

            // Se ambas as exclusões foram bem-sucedidas, confirma a transação.
            return $this->db->commit();
        } catch (PDOException $e) {
            // Se ocorrer qualquer erro, desfaz a transação.
            $this->db->rollBack();
            error_log("Erro ao excluir usuário e colaborador associado (ID {$id}): " . $e->getMessage());
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
        // A query é montada dinamicamente para atualizar a senha e o avatar apenas se eles forem fornecidos
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

        // Se avatar_filename foi explicitamente fornecido (pode ser null para limpar)
        if (array_key_exists('avatar_filename', $dados)) {
            $sql .= ", avatar_filename = :avatar_filename";
            $params[':avatar_filename'] = $dados['avatar_filename'];
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

    /**
     * Atualiza a senha de um usuário (usado na recuperação de senha).
     * @param int $id
     * @param string $novaSenha
     * @return bool
     */
    public function updatePassword(int $id, string $novaSenha): bool
    {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':senha_hash', $senhaHash);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar senha do usuário ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se um e-mail já está em uso por outro usuário.
     * @param string $email O e-mail a ser verificado.
     * @param int|null $excludeId O ID do usuário a ser ignorado na verificação (útil na edição).
     * @return bool Retorna true se o e-mail já existe, false caso contrário.
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        // Lança uma exceção personalizada em vez de retornar um booleano,
        // para que o Controller possa capturar uma mensagem de erro específica.
        // Isso centraliza a lógica de verificação de e-mail aqui.
        if (empty($email)) {
            throw new PDOException("O e-mail não pode ser vazio.");
        }

        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        if ($excludeId) {
            $sql .= " AND id != :excludeId";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        if ($excludeId) $stmt->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        // Retorna true se encontrou (existe), false se não.
        // A lógica anterior lançava exceção, vamos manter compatibilidade mas permitir uso booleano em outros lugares se necessário
        $exists = $stmt->fetchColumn() > 0;
        if ($exists && $excludeId === null) {
             // Se for verificação simples (sem excludeId), pode ser usada para validação booleana
             // Mas se o código espera exceção, mantemos.
             // Para salvarUsuario e createUser, vamos deixar a exceção propagar ou tratar lá.
             // O ideal é retornar bool e deixar o controller tratar a mensagem.
             // Mas como o código legado usa exceção:
             return true; 
        }
        return false;
    }

    /**
     * Atualiza o token de recuperação de senha e sua validade.
     */
    public function updatePasswordResetToken(int $userId, ?string $token, ?string $expiry): bool
    {
        try {
            $sql = "UPDATE usuarios SET reset_token = ?, reset_expiry = ? WHERE id = ?";
            return $this->db->prepare($sql)->execute([$token, $expiry, $userId]);
        } catch (\PDOException $e) {
            error_log("Erro ao salvar token de reset: " . $e->getMessage());
            return false;
        }
    }
}
