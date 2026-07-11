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

    /**
     * Busca o aviso de sistema ativo no momento.
     * @return array|null Retorna o aviso se encontrado, ou null.
     */
    public function getAvisoAtivo(): ?array
    {
        $sql = "SELECT * FROM avisos_sistema 
                WHERE ativo = 1 AND NOW() BETWEEN data_inicio AND data_fim 
                ORDER BY criado_em DESC LIMIT 1";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getRegimeTributario(): string
    {
        $dados = $this->getDadosEmpresa();
        return $dados['regime_tributario'] ?? 'Lucro Presumido';
    }

    public function getNfeAmbiente(): string
    {
        $dados = $this->getDadosEmpresa();
        return $dados['nfe_ambiente'] ?? 'homologacao';
    }

    public function salvarDadosFiscais(array $dados): bool
    {
        $configAtual = $this->getDadosEmpresa();
        $camposFiscais = ['regime_tributario', 'nfe_ambiente', 'caminho_certificado', 'senha_certificado', 'codigo_municipio', 'ie', 'cnae'];
        foreach ($camposFiscais as $c) {
            if (isset($dados[$c])) {
                $configAtual[$c] = $dados[$c];
            }
        }
        unset($configAtual['senha_certificado']);
        $configDir = dirname($this->configFile);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0775, true);
        }
        return file_put_contents($this->configFile, json_encode($configAtual, JSON_PRETTY_PRINT)) !== false;
    }
}
