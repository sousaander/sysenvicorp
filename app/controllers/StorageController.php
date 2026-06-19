<?php

namespace App\Controllers;

class StorageController extends BaseController
{
    public function __construct()
    {
        // O parent::__construct() aciona o AuthMiddleware, garantindo
        // que apenas usuários logados acessem os arquivos.
        parent::__construct();
    }

    /**
     * Método mágico para capturar qualquer nome de pasta como ação.
     * Ex: /storage/comprovantes_prestacao/arquivo.pdf
     * $name será 'comprovantes_prestacao'
     * $arguments será ['arquivo.pdf']
     */
    public function __call($name, $arguments)
    {
        $this->serveFile($name, $arguments);
    }

    public function index()
    {
        http_response_code(404);
        echo "Arquivo não especificado.";
    }

    private function serveFile($folder, $args)
    {
        if (empty($args)) {
            http_response_code(404);
            echo "Caminho de arquivo inválido.";
            exit;
        }

        // Decodifica URL encoding (ex: %20 para espaço) para garantir que o nome bata com o arquivo em disco
        $args = array_map('urldecode', $args);

        // Reconstrói o caminho relativo (ex: comprovantes_prestacao/arquivo.pdf)
        $relativePath = $folder . '/' . implode('/', $args);
        
        // Define o caminho absoluto da pasta storage
        $storageRoot = realpath(ROOT_PATH . '/storage');
        
        if (!$storageRoot) {
            http_response_code(500);
            echo "Erro: Pasta storage não encontrada no servidor.";
            exit;
        }
        
        // Constrói o caminho alvo normalizando as barras para o SO (Windows/Linux)
        $targetPath = $storageRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        
        // Tenta resolver o caminho real (retorna false se arquivo não existir)
        $filePath = realpath($targetPath);

        // VERIFICAÇÕES DE SEGURANÇA:
        // 1. $filePath deve existir
        // 2. $filePath deve estar dentro de $storageRoot (evita Directory Traversal ../../)
        // 3. Deve ser um arquivo, não um diretório
        if ($filePath && stripos($filePath, $storageRoot) === 0 && file_exists($filePath) && is_file($filePath)) {
            
            // Tenta detectar o tipo MIME
            $mimeType = mime_content_type($filePath);
            
            // Fallback se a detecção automática falhar
            if (!$mimeType) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'pdf' => 'application/pdf',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    'txt' => 'text/plain',
                    'csv' => 'text/csv',
                    'zip' => 'application/zip',
                    'rar' => 'application/x-rar-compressed',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'xls' => 'application/vnd.ms-excel',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ];
                $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
            }

            // Define os headers para entrega do arquivo
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: private, max-age=86400'); // Cache por 1 dia no navegador

            // Exibe no navegador (inline) se for imagem ou PDF, senão força download (attachment)
            $inlineTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $disposition = in_array($mimeType, $inlineTypes) ? 'inline' : 'attachment';
            
            header('Content-Disposition: ' . $disposition . '; filename="' . basename($filePath) . '"');

            // Limpa qualquer buffer de saída anterior para não corromper o arquivo
            if (ob_get_level()) ob_end_clean();
            
            readfile($filePath);
            exit;
        }

        // Se falhar as verificações
        http_response_code(404);
        echo "Arquivo não encontrado ou acesso negado.";
        
        // --- DEBUG (Descomente se precisar ver onde o sistema está procurando) ---
        // if (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
        //     echo "<br><hr><strong>Debug Info:</strong><br>";
        //     echo "Procurado em: " . htmlspecialchars($targetPath) . "<br>";
        //     echo "Pasta Storage: " . htmlspecialchars($storageRoot) . "<br>";
        // }
        
        exit;
    }
}
