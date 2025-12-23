<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class EmpresaModel extends Model
{
    private $configFile;
    public function __construct()
    {
        parent::__construct();
        // Usaremos um arquivo JSON para armazenar os dados da empresa para simplificar.
        // Em um projeto real, isso seria uma tabela 'config' ou 'empresa'.
        $this->configFile = ROOT_PATH . '/storage/config/empresa.json';
    }

    /**
     * Busca os dados da empresa do arquivo de configuração.
     */
    public function getDadosEmpresa(): array
    {
        if (file_exists($this->configFile)) {
            $json = file_get_contents($this->configFile);
            return json_decode($json, true) ?: [];
        }
        return [];
    }

    /**
     * Salva os dados da empresa no arquivo de configuração.
     */
    public function salvarDadosEmpresa(array $dados, ?string $caminhoCertificado): bool
    {
        $configAtual = $this->getDadosEmpresa();

        // Mescla os dados novos com os existentes
        $novaConfig = array_merge($configAtual, $dados);

        // Adiciona o caminho do certificado se um novo foi enviado
        if ($caminhoCertificado) {
            $novaConfig['caminho_certificado'] = $caminhoCertificado;
        }

        // Remove a senha do certificado do arquivo de configuração por segurança
        unset($novaConfig['senha_certificado']);

        $configDir = dirname($this->configFile);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0775, true);
        }

        return file_put_contents($this->configFile, json_encode($novaConfig, JSON_PRETTY_PRINT)) !== false;
    }
}
