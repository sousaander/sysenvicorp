<?php

namespace App\Controllers;

use App\Core\Connection;
use PDO;

class ConfiguracoesController extends BaseController
{
    private $db;

    public function __construct()
    {
        parent::__construct();

        // Proteção de Acesso: Apenas administradores podem acessar este controller.
        // CORREÇÃO: Aceita 'admin' ou 'administrador'
        $perfilAtual = strtolower(trim($this->session->get('usuario_perfil') ?? ''));
        $permissoesAtuais = $this->session->get('usuario_permissoes') ?? [];

        if ($perfilAtual !== 'admin' && $perfilAtual !== 'administrador' && !in_array('*', $permissoesAtuais)) {
            $this->setFlashMessage('error', 'Acesso negado. Você não tem permissão para acessar as configurações do sistema.');
            header('Location: ' . BASE_URL . '/');
            exit();
        }

        // Acesso direto ao PDO para operações de backup
        $this->db = Connection::getInstance();
    }


    public function index()
    {
        // Definição dos menus com ícones Boxicons (compatíveis com seu main_template)
        $menus = [
            [
                'titulo' => 'Dados da Empresa',
                'descricao' => 'Configure o CNPJ, endereço, certificado digital e dados de contato da sua organização.',
                'url' => BASE_URL . '/empresa',
                'icone' => 'bx bx-building-house',
                'cor' => 'text-amber-600',
                'bg' => 'bg-amber-100'
            ],
            [
                'titulo' => 'Itens de Propostas',
                'descricao' => 'Gerencie as categorias de serviços e unidades de medida utilizadas nos orçamentos.',
                'url' => BASE_URL . '/orcamento/gerenciarItens',
                'icone' => 'bx bx-list-check',
                'cor' => 'text-sky-600',
                'bg' => 'bg-sky-100'
            ],
            [
                'titulo' => 'Cadastro Geral',
                'descricao' => 'Bancos, Categorias Financeiras, Centros de Custo e Segmentos de Clientes.',
                'url' => BASE_URL . '/configuracoes/cadastro',
                'icone' => 'bx bx-edit',
                'cor' => 'text-blue-600',
                'bg' => 'bg-blue-100'
            ],
            [
                'titulo' => 'Backup e Restauração',
                'descricao' => 'Exporte dados em SQL/CSV ou restaure backups de segurança.',
                'url' => BASE_URL . '/configuracoes/backup',
                'icone' => 'bx bx-database',
                'cor' => 'text-green-600',
                'bg' => 'bg-green-100'
            ],
            [
                'titulo' => 'Usuários e Permissões',
                'descricao' => 'Controle quem acessa o sistema e quais módulos estão visíveis.',
                'url' => BASE_URL . '/usuario',
                'icone' => 'bx bx-user-pin',
                'cor' => 'text-purple-600',
                'bg' => 'bg-purple-100'
            ],
            [
                'titulo' => 'Perfis de Acesso',
                'descricao' => 'Configure permissões detalhadas para cada cargo ou função.',
                'url' => BASE_URL . '/perfil',
                'icone' => 'bx bx-shield-quarter',
                'cor' => 'text-red-600',
                'bg' => 'bg-red-100'
            ],
            [
                'titulo' => 'Logs de Auditoria',
                'descricao' => 'Veja o histórico de todas as alterações feitas no sistema.',
                'url' => BASE_URL . '/auditlog',
                'icone' => 'bx bx-history',
                'cor' => 'text-gray-600',
                'bg' => 'bg-gray-200'
            ],
            [
                'titulo' => 'Limpeza de Notificações',
                'descricao' => 'Remove avisos lidos com mais de 30 dias para otimizar o banco de dados.',
                'url' => BASE_URL . '/notificacoes/limpar?token=' . (defined('CRON_TOKEN') ? CRON_TOKEN : ''),
                'icone' => 'bx bx-trash',
                'cor' => 'text-orange-600',
                'bg' => 'bg-orange-50'
            ],
            [
                'titulo' => 'Avisos do Sistema',
                'descricao' => 'Crie alertas de manutenção ou atualizações para serem exibidos no Dashboard.',
                'url' => BASE_URL . '/configuracoes/avisos',
                'icone' => 'bx bx-notification',
                'cor' => 'text-indigo-600',
                'bg' => 'bg-indigo-100'
            ],
        ];

        $data = [
            'pageTitle' => 'Configurações',
            'menus' => $menus
        ];
        $this->renderView('configuracoes/index', $data);
    }

    /**
     * Exibe a página de cadastro.
     */
    public function cadastro()
    {
        $data = [
            'pageTitle' => 'Cadastro',
        ];
        $this->renderView('configuracoes/cadastro', $data);
    }

    /**
     * Exibe a página de backup e restauração.
     */
    public function backup()
    {
        $data = [
            'pageTitle' => 'Backup e Restauração',
        ];
        $this->renderView('configuracoes/backup', $data);
    }

    /**
     * Lida com a exportação de dados (completa ou por módulo).
     */
    public function exportar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/configuracoes/backup');
            exit();
        }

        $tipo = $_POST['tipo_exportacao'] ?? 'completo';

        if ($tipo === 'completo') {
            $this->exportarCompleto();
        } else {
            $this->exportarModulo($tipo);
        }
    }

    /**
     * Exporta um backup completo do banco de dados em formato SQL.
     */
    private function exportarCompleto()
    {
        try {
            $output = "-- Backup do Banco de Dados - SysEnviCorp\n";
            $output .= "-- Gerado em: " . date('Y-m-d H:i:s') . "\n\n";

            $stmt = $this->db->query('SHOW TABLES');
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $output .= "--\n-- Estrutura da tabela: `{$table}`\n--\n";
                $stmtCreate = $this->db->query("SHOW CREATE TABLE `{$table}`");
                $createResult = $stmtCreate->fetch(PDO::FETCH_ASSOC);
                $output .= $createResult['Create Table'] . ";\n\n";

                $output .= "--\n-- Dados da tabela: `{$table}`\n--\n";
                $stmtData = $this->db->query("SELECT * FROM `{$table}`");
                while ($row = $stmtData->fetch(PDO::FETCH_ASSOC)) {
                    $keys = array_keys($row);
                    $values = array_map(function ($value) {
                        if ($value === null) return 'NULL';
                        return $this->db->quote($value);
                    }, array_values($row));

                    $output .= "INSERT INTO `{$table}` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $values) . ");\n";
                }
                $output .= "\n";
            }

            $filename = 'backup_completo_sysenvicorp_' . date('Y-m-d_H-i-s') . '.sql';
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $output;
            exit();
        } catch (\PDOException $e) {
            $this->setFlashMessage('error', 'Erro ao gerar backup completo: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/configuracoes/backup');
            exit();
        }
    }

    /**
     * Exporta os dados de um módulo específico para um arquivo CSV.
     */
    private function exportarModulo(string $modulo)
    {
        $filename = "backup_{$modulo}_" . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // BOM para UTF-8 no Excel

        try {
            switch ($modulo) {
                case 'financeiro':
                    $this->exportarTabela('transacoes', $output);
                    $this->exportarTabela('transacao_classificacoes', $output);
                    $this->exportarTabela('centros_custo', $output);
                    $this->exportarTabela('bancos', $output);
                    break;

                case 'rh':
                    $this->exportarTabela('usuarios', $output);
                    $this->exportarTabela('colaboradores', $output);
                    $this->exportarTabela('cargos', $output);
                    break;

                case 'projetos':
                    $this->exportarTabela('projetos', $output);
                    $this->exportarTabela('tarefas', $output);
                    break;

                case 'contratos':
                    $this->exportarTabela('contratos', $output);
                    break;

                default:
                    fclose($output);
                    $this->setFlashMessage('error', 'Módulo de exportação desconhecido.');
                    header('Location: ' . BASE_URL . '/configuracoes/backup');
                    exit();
            }
        } catch (\PDOException $e) {
            fclose($output);
            $this->setFlashMessage('error', 'Erro ao exportar módulo: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/configuracoes/backup');
            exit();
        }

        fclose($output);
        exit();
    }

    /**
     * Função auxiliar para exportar uma tabela para o stream CSV.
     */
    private function exportarTabela(string $tableName, $outputStream): void
    {
        // Verifica se a tabela existe antes de tentar consultar
        try {
            $check = $this->db->query("SHOW TABLES LIKE '{$tableName}'");
            if ($check->rowCount() == 0) return;
        } catch (\Exception $e) {
            return;
        }

        fputcsv($outputStream, ["--- TABLE:{$tableName} ---"], ';');
        $stmt = $this->db->query("SELECT * FROM `{$tableName}`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($rows)) {
            fputcsv($outputStream, array_keys($rows[0]), ';');
            foreach ($rows as $row) {
                fputcsv($outputStream, $row, ';');
            }
        }
        fputcsv($outputStream, [], ';');
    }

    /**
     * Lida com a importação de dados de um módulo a partir de um arquivo CSV.
     */
    public function importar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['arquivo_csv']['tmp_name']) || $_FILES['arquivo_csv']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlashMessage('error', 'Nenhum arquivo enviado.');
            header('Location: ' . BASE_URL . '/configuracoes/backup');
            exit();
        }

        $file = $_FILES['arquivo_csv']['tmp_name'];
        // Captura o modo de importação do formulário. 'upsert' é o padrão mais seguro.
        $importMode = $_POST['import_mode'] ?? 'upsert'; // 'replace' ou 'upsert'

        try {
            $handle = fopen($file, "r");
            if ($handle === false) {
                throw new \Exception("Não foi possível abrir o arquivo CSV.");
            }

            // Pula o BOM (Byte Order Mark) se existir
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $this->db->beginTransaction();

            // Desativa verificação de chave estrangeira para evitar erros de integridade durante a importação
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');

            $currentTable = null;
            $headers = [];
            $dbColumns = [];
            $sucessos = 0;
            $erros = 0;
            $stmtInsert = null; // Variável para armazenar o statement preparado

            while (($row = fgetcsv($handle, 0, ";")) !== false) {
                // Linha vazia, pula
                if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                    continue;
                }

                // Verifica se é uma linha de marcador de tabela
                if (preg_match('/^--- TABLE:(.+) ---$/', $row[0], $matches)) {
                    $currentTable = trim($matches[1]);
                    $headers = []; // Reseta os cabeçalhos para a nova tabela
                    $stmtInsert = null; // Reseta o statement para a nova tabela

                    // Validação Sênior: Verifica se a tabela está acessível no motor do banco de dados (Engine)
                    // Isso evita o erro 1932 de corrupção do InnoDB ao tentar DELETE ou DESCRIBE
                    try {
                        $this->db->query("SELECT 1 FROM `{$currentTable}` LIMIT 0");
                    } catch (\Exception $e) {
                        throw new \Exception("A tabela '{$currentTable}' está corrompida ou ausente no motor do banco de dados (Erro 1932). É necessário recriá-la manualmente no MySQL.");
                    }

                    // Se o modo for 'replace', limpa a tabela antes de importar os novos dados.
                    if ($importMode === 'replace') {
                        try {
                            $this->db->exec("DELETE FROM `{$currentTable}`");
                        } catch (\Exception $e) {
                            throw new \Exception("Falha ao limpar a tabela '{$currentTable}' antes da importação no modo 'Substituir': " . $e->getMessage());
                        }
                    }

                    // Busca as colunas reais da tabela no banco para validação
                    try {
                        $stmtCols = $this->db->query("SHOW COLUMNS FROM `{$currentTable}`");
                        $dbColumns = $stmtCols->fetchAll(PDO::FETCH_COLUMN);
                    } catch (\Exception $e) {
                        throw new \Exception("Tabela '{$currentTable}' não encontrada no banco de dados.");
                    }
                    continue;
                }

                // Se não temos uma tabela, não podemos processar
                if (!$currentTable) continue;

                // A primeira linha após o marcador é o cabeçalho
                if (empty($headers)) {
                    $headers = array_map('trim', $row); // Remove espaços dos cabeçalhos

                    // Valida se as colunas do CSV existem na tabela do banco
                    $colunasInvalidas = array_diff($headers, $dbColumns);
                    if (!empty($colunasInvalidas)) {
                        throw new \Exception("Colunas inválidas no CSV para a tabela '{$currentTable}': " . implode(', ', $colunasInvalidas));
                    }

                    // OTIMIZAÇÃO: Prepara a query uma única vez por tabela
                    $placeholders = array_fill(0, count($headers), '?');
                    $updatePairs = array_map(fn($c) => "`$c` = VALUES(`$c`)", $headers);
                    $sql = "INSERT INTO `{$currentTable}` (`" . implode('`, `', $headers) . "`) ";
                    $sql .= "VALUES (" . implode(', ', $placeholders) . ") ";
                    $sql .= "ON DUPLICATE KEY UPDATE " . implode(', ', $updatePairs);
                    $stmtInsert = $this->db->prepare($sql);

                    continue;
                }

                // Combina cabeçalhos com os dados da linha
                if (count($headers) !== count($row)) {
                    $erros++;
                    continue; // Pula linha mal formatada
                }
                $data = array_combine($headers, $row);

                // Processa os dados para corrigir formatos (ex: datas) e tratar nulos
                foreach ($data as $key => $value) {
                    $value = trim($value);
                    $data[$key] = $value; // Atualiza o valor limpo no array final

                    // Proteção para colunas de ID (evita conversão de data e corrige inteiros do Excel)
                    // Verifica se a coluna é 'id' ou termina com '_id' (ex: banco_id)
                    if ($key === 'id' || substr($key, -3) === '_id') {
                        if ($value === '') {
                            $data[$key] = null;
                        } elseif (is_numeric($value)) {
                            $data[$key] = (int)$value; // Remove decimais (.0) que o Excel pode adicionar
                        }
                        continue; // Pula verificação de data para IDs
                    }

                    // Conversão para colunas de VALOR (monetário), tratando formatos como "1.234,56"
                    if (in_array($key, ['valor', 'saldo_inicial', 'juros', 'desconto', 'valor_alteracao'])) {
                        if ($value === '') {
                            $data[$key] = null;
                        } else {
                            $cleanValue = $value;
                            // Se tem ponto E vírgula, assume formato pt-BR (ex: 1.234,56)
                            if (strpos($cleanValue, '.') !== false && strpos($cleanValue, ',') !== false) {
                                $cleanValue = str_replace('.', '', $cleanValue); // Remove separador de milhar
                                $cleanValue = str_replace(',', '.', $cleanValue); // Troca vírgula por ponto
                            } elseif (strpos($cleanValue, ',') !== false) { // Se tem apenas vírgula (ex: 1234,56)
                                $cleanValue = str_replace(',', '.', $cleanValue); // Troca vírgula por ponto
                            }
                            // Se tiver apenas ponto (ex: 1234.56), já está no formato correto para o banco.
                            $data[$key] = is_numeric($cleanValue) ? (float)$cleanValue : 0.0;
                        }
                        continue; // Pula as verificações de data
                    }

                    // Converte data de DD/MM/YYYY para YYYY-MM-DD
                    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
                        $data[$key] = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
                    }
                    // Converte datahora de DD/MM/YYYY HH:MM:SS para YYYY-MM-DD HH:MM:SS
                    elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}):(\d{1,2}):(\d{1,2})$/', $value, $matches)) {
                        $data[$key] = "{$matches[3]}-{$matches[2]}-{$matches[1]} {$matches[4]}:{$matches[5]}:{$matches[6]}";
                    } elseif ($value === '') {
                        $data[$key] = null;
                    }
                }

                // Lógica para evitar duplicidade em tabelas auxiliares se o ID for nulo (busca por nome)
                if (empty($data['id']) && in_array($currentTable, ['bancos', 'centros_custo', 'transacao_classificacoes'])) {
                    $nameCol = 'nome'; // Padrão para estas tabelas
                    if (!empty($data[$nameCol])) {
                        try {
                            $stmtCheck = $this->db->prepare("SELECT id FROM `{$currentTable}` WHERE `{$nameCol}` = ? LIMIT 1");
                            $stmtCheck->execute([$data[$nameCol]]);
                            $existingId = $stmtCheck->fetchColumn();
                            if ($existingId) {
                                $data['id'] = $existingId;
                            }
                        } catch (\Exception $e) {
                            // Ignora erro e segue para insert padrão
                        }
                    }
                }

                // Executa o statement já preparado com os valores da linha atual
                $stmtInsert->execute(array_values($data));
                $sucessos++;
            }

            fclose($handle);

            // Reativa verificação de chave estrangeira
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
            $this->db->commit();
            $this->setFlashMessage('success', "Importação concluída! Registros processados: {$sucessos}. Falhas: {$erros}.");
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            // Garante que a verificação de chaves estrangeiras seja reativada mesmo em caso de erro
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
            $this->setFlashMessage('error', 'Erro durante a importação: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/configuracoes/backup');
        exit();
    }

    /**
     * Restaura o banco de dados a partir de um arquivo SQL enviado.
     */
    public function restaurar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/configuracoes/backup');
            exit();
        }

        if (empty($_FILES['arquivo_sql']['tmp_name']) || $_FILES['arquivo_sql']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlashMessage('error', 'Nenhum arquivo enviado ou erro no upload.');
            header('Location: ' . BASE_URL . '/configuracoes/backup');
            exit();
        }

        $file = $_FILES['arquivo_sql']['tmp_name'];
        $ext = pathinfo($_FILES['arquivo_sql']['name'], PATHINFO_EXTENSION);
        $tempSqlFile = null; // Para guardar o caminho do arquivo SQL extraído do ZIP

        // Validação de extensão
        if (!in_array(strtolower($ext), ['sql', 'zip'])) {
            $this->setFlashMessage('error', 'Formato de arquivo inválido. Envie um arquivo .sql ou .zip.');
            header('Location: ' . BASE_URL . '/configuracoes/backup');
            exit();
        }

        // Lógica para descompactar ZIP
        if (strtolower($ext) === 'zip') {
            $zip = new \ZipArchive;
            if ($zip->open($file) === TRUE) {
                $sqlFileInZip = null;
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'sql') {
                        $sqlFileInZip = $filename;
                        break;
                    }
                }

                if ($sqlFileInZip) {
                    $tempSqlFile = tempnam(sys_get_temp_dir(), 'backup_sql_');
                    file_put_contents($tempSqlFile, $zip->getFromName($sqlFileInZip));
                    $file = $tempSqlFile; // O arquivo a ser processado agora é o extraído
                } else {
                    $zip->close();
                    $this->setFlashMessage('error', 'Nenhum arquivo .sql encontrado dentro do ZIP.');
                    header('Location: ' . BASE_URL . '/configuracoes/backup');
                    exit();
                }
                $zip->close();
            } else {
                $this->setFlashMessage('error', 'Não foi possível abrir o arquivo ZIP.');
                header('Location: ' . BASE_URL . '/configuracoes/backup');
                exit();
            }
        }

        try {
            // 1. Desativa verificação de chave estrangeira
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 0;');

            // 2. Drop todas as tabelas existentes para evitar conflitos
            $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                $this->db->exec("DROP TABLE IF EXISTS `$table`;");
            }

            // 3. Executa o SQL do backup, comando por comando, para evitar 'max_allowed_packet'
            $query = '';
            $handle = fopen($file, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    // Pula comentários e linhas vazias
                    if (substr(trim($line), 0, 2) == '--' || trim($line) == '') {
                        continue;
                    }

                    $query .= $line;

                    // Se a linha termina com ';', é o fim de um comando
                    if (substr(trim($line), -1, 1) == ';') {
                        try {
                            $this->db->exec($query);
                        } catch (\PDOException $e) {
                            // Ignora erros comuns de dump, mas lança outros
                            if (strpos($e->getMessage(), 'already exists') === false && strpos($e->getMessage(), 'Unknown collation') === false) {
                                throw new \Exception("Erro ao executar comando SQL: " . $e->getMessage() . "\nComando: " . substr($query, 0, 250) . "...");
                            }
                        }
                        $query = ''; // Reseta para o próximo comando
                    }
                }
                fclose($handle);
            } else {
                throw new \Exception("Não foi possível ler o arquivo de backup.");
            }

            // 4. Reativa verificação de chave estrangeira
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 1;');

            // Limpa o arquivo temporário se foi extraído de um zip
            if ($tempSqlFile) {
                @unlink($tempSqlFile);
            }

            // Destrói a sessão atual para forçar novo login, garantindo consistência
            session_destroy();
            header('Location: ' . BASE_URL . '/auth/login?msg=restauracao_sucesso');
            exit();
        } catch (\Exception $e) {
            // Limpa o arquivo temporário se foi extraído de um zip
            if ($tempSqlFile) {
                @unlink($tempSqlFile);
            }
            $this->setFlashMessage('error', 'Erro ao restaurar backup: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/configuracoes/backup');
            exit();
        }
    }

    /**
     * Realiza o Reset de Fábrica do sistema.
     * Apaga todos os dados e recria o usuário admin padrão.
     */
    public function resetarSistema()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/configuracoes/backup');
            exit();
        }

        try {
            // 1. Desativa verificação de chave estrangeira para permitir limpar as tabelas em qualquer ordem
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 0;');

            // 2. Obtém todas as tabelas e as limpa (TRUNCATE reseta os IDs auto_increment)
            $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                $this->db->exec("TRUNCATE TABLE `$table`;");
            }

            // 3. Recria os dados essenciais (Seed)

            // Perfis Padrão
            // Define permissões básicas para o Colaborador
            $permissoesColaborador = json_encode(['dashboard_view', 'clientes_view', 'projetos_view', 'financeiro_dashboard_view', 'licencas_operacao_view']);
            // Admin não precisa de permissões explícitas pois tem acesso total via código, mas Colaborador precisa.
            $this->db->exec("INSERT INTO perfis_acesso (nome_perfil, descricao, permissoes) VALUES ('Admin', 'Acesso total ao sistema', NULL), ('Colaborador', 'Acesso restrito', '$permissoesColaborador');");
            $adminPerfilId = $this->db->lastInsertId(); // Assume que Admin é o primeiro

            // Cargo Padrão
            $this->db->exec("INSERT INTO cargos (nome_cargo) VALUES ('Administrador');");
            $adminCargoId = $this->db->lastInsertId();
            // Cria também o cargo padrão para novos usuários
            $this->db->exec("INSERT INTO cargos (nome_cargo) VALUES ('Não Definido');");

            // Usuário Admin Padrão
            $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
            $sqlUser = "INSERT INTO usuarios (nome, email, senha_hash, perfil_id, cargo_id, status) VALUES ('Administrador', 'admin@sysenvicorp.com', '$senhaHash', $adminPerfilId, $adminCargoId, 'Ativo')";
            $this->db->exec($sqlUser . ';');

            $this->db->exec('SET FOREIGN_KEY_CHECKS = 1;');

            // Destrói a sessão atual e redireciona para o login
            session_destroy();
            header('Location: ' . BASE_URL . '/auth/login?msg=reset_concluido');
            exit();
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erro crítico ao resetar o sistema: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/configuracoes/backup');
            exit();
        }
    }

    /**
     * Exibe a lista de avisos do sistema para gerenciamento.
     */
    public function avisos()
    {
        $stmt = $this->db->query("SELECT * FROM avisos_sistema ORDER BY criado_em DESC");
        $avisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'pageTitle' => 'Gerenciar Avisos do Sistema',
            'avisos' => $avisos
        ];
        $this->renderView('configuracoes/avisos', $data);
    }

    /**
     * Salva ou atualiza um aviso no banco de dados.
     */
    public function salvarAviso()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/configuracoes/avisos');
            exit();
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
        $mensagem = $_POST['mensagem'] ?? '';
        $tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS);
        $data_inicio = filter_input(INPUT_POST, 'data_inicio');
        $data_fim = filter_input(INPUT_POST, 'data_fim');
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if ($id) {
            $sql = "UPDATE avisos_sistema SET titulo = ?, mensagem = ?, tipo = ?, data_inicio = ?, data_fim = ?, ativo = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([$titulo, $mensagem, $tipo, $data_inicio, $data_fim, $ativo, $id]);
        } else {
            $sql = "INSERT INTO avisos_sistema (titulo, mensagem, tipo, data_inicio, data_fim, ativo) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([$titulo, $mensagem, $tipo, $data_inicio, $data_fim, $ativo]);
        }

        if ($success) {
            $this->setFlashMessage('success', 'Aviso salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Ocorreu um erro ao salvar o aviso.');
        }

        header('Location: ' . BASE_URL . '/configuracoes/avisos');
        exit();
    }

    /**
     * Remove um aviso permanentemente.
     */
    public function excluirAviso($id)
    {
        $id = (int)$id;
        $stmt = $this->db->prepare("DELETE FROM avisos_sistema WHERE id = ?");
        if ($stmt->execute([$id])) {
            $this->setFlashMessage('success', 'Aviso removido com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao remover o aviso.');
        }
        header('Location: ' . BASE_URL . '/configuracoes/avisos');
        exit();
    }
}
