<?php

// Define o caminho raiz do sistema
// Ajuste o dirname conforme a localização deste arquivo. 
// Se estiver em /cron/backup.php, o dirname(__DIR__) aponta para a raiz do projeto.
define('ROOT_PATH', dirname(__DIR__));

// Carrega o autoloader do Composer e as configurações do sistema
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/app/config/settings.php';

use App\Core\Connection;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configurações
$backupDir = ROOT_PATH . '/storage/backups/';
$retentionDays = 7; // Manter backups por quantos dias?

$baseFilename = 'backup_auto_' . date('Y-m-d_H-i-s');
$sqlFilename = $baseFilename . '.sql';
$zipFilename = $baseFilename . '.sql.zip';
$sqlFilepath = $backupDir . $sqlFilename;
$zipFilepath = $backupDir . $zipFilename;

// Garante que o diretório de backups existe
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

echo "[" . date('Y-m-d H:i:s') . "] Iniciando backup...\n";

try {
    // Obtém a conexão com o banco (mesma lógica do sistema)
    $db = Connection::getInstance();

    $handle = fopen($sqlFilepath, 'w');
    if (!$handle) {
        throw new Exception("Não foi possível criar o arquivo: $sqlFilepath");
    }

    // Cabeçalho do Arquivo SQL
    fwrite($handle, "-- Backup Automático SysEnviCorp\n");
    fwrite($handle, "-- Data: " . date('Y-m-d H:i:s') . "\n");
    fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

    // Obtém lista de tabelas
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // Estrutura da tabela
        $createTable = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        fwrite($handle, "-- Estrutura da tabela: `$table`\n");
        fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
        fwrite($handle, $createTable['Create Table'] . ";\n\n");

        // Dados da tabela
        $rows = $db->query("SELECT * FROM `$table`");
        $rowCount = 0;
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $keys = array_keys($row);
            $values = array_map(function ($value) use ($db) {
                if ($value === null) return 'NULL';
                return $db->quote($value);
            }, array_values($row));

            $sql = "INSERT INTO `$table` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $values) . ");\n";
            fwrite($handle, $sql);
            $rowCount++;
        }
        fwrite($handle, "\n");
        echo "Tabela $table processada ($rowCount registros).\n";
    }

    fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
    fclose($handle);

    echo "Arquivo SQL gerado: $sqlFilename\n";

    // --- Compactação para .zip ---
    echo "Compactando o arquivo SQL...\n";
    $zip = new ZipArchive();
    if ($zip->open($zipFilepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        throw new Exception("Não foi possível criar o arquivo zip: $zipFilepath");
    }
    $zip->addFile($sqlFilepath, $sqlFilename);
    $zip->close();

    // Remove o arquivo .sql original para economizar espaço
    unlink($sqlFilepath);

    echo "Arquivo compactado com sucesso: $zipFilename\n";

    // --- Envio de E-mail com o Backup ---
    if (defined('MAIL_ADMIN_RECIPIENT') && !empty(MAIL_ADMIN_RECIPIENT) && filter_var(MAIL_ADMIN_RECIPIENT, FILTER_VALIDATE_EMAIL)) {
        echo "Tentando enviar e-mail de notificação...\n";
        $mail = new PHPMailer(true);
        try {
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';

            // Remetente e Destinatário
            $mail->setFrom(MAIL_FROM_ADDRESS, 'Backup Automático - SysEnviCorp');
            $mail->addAddress(MAIL_ADMIN_RECIPIENT);

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = 'Backup Automático Compactado do Sistema - ' . date('d/m/Y');
            $mail->Body    = "Olá,<br><br>O backup automático compactado (.zip) do banco de dados foi gerado com sucesso em " . date('d/m/Y H:i:s') . ".<br>O arquivo está em anexo.<br><br>Atenciosamente,<br>Sistema SysEnviCorp";
            $mail->AltBody = "O backup automático compactado (.zip) do banco de dados foi gerado com sucesso em " . date('d/m/Y H:i:s') . ". O arquivo está em anexo.";

            // Anexa o arquivo de backup, com um limite de tamanho (ex: 25MB)
            if (filesize($zipFilepath) < (25 * 1024 * 1024)) {
                $mail->addAttachment($zipFilepath);
            } else {
                $mail->Body = "Olá,<br><br>O backup automático do banco de dados foi gerado com sucesso em " . date('d/m/Y H:i:s') . ".<br><strong>O arquivo de backup compactado é muito grande para ser enviado por e-mail.</strong><br>Ele está disponível no servidor em: <code>" . $zipFilepath . "</code><br><br>Atenciosamente,<br>Sistema SysEnviCorp";
            }

            $mail->send();
            echo "E-mail de notificação enviado com sucesso para " . MAIL_ADMIN_RECIPIENT . "\n";
        } catch (Exception $e) {
            echo "ERRO AO ENVIAR E-MAIL: {$mail->ErrorInfo}\n";
            // Não interrompe o script, apenas loga o erro.
        }
    }

    // --- Limpeza de Backups Antigos ---
    echo "Verificando arquivos antigos para limpeza...\n";
    $files = glob($backupDir . 'backup_auto_*.sql.zip');
    $now = time();
    $deleted = 0;

    foreach ($files as $file) {
        if (is_file($file)) {
            // Se o arquivo for mais antigo que X dias
            if ($now - filemtime($file) >= 60 * 60 * 24 * $retentionDays) {
                unlink($file);
                echo "Removido antigo: " . basename($file) . "\n";
                $deleted++;
            }
        }
    }
    echo "Limpeza concluída. $deleted arquivos removidos.\n";
} catch (Exception $e) {
    echo "ERRO CRÍTICO: " . $e->getMessage() . "\n";
    exit(1); // Código de erro para o sistema operacional
}
