<?php

namespace App\Libraries\NFe;

class NFeService
{
    private NFeXmlBuilder $xmlBuilder;
    private NFeSoapClient $soap;

    public function __construct(string $ambiente = 'homologacao', string $uf = 'AM')
    {
        $this->xmlBuilder = new NFeXmlBuilder();
        $this->soap = new NFeSoapClient($ambiente, $uf);
    }

    public function emitir(array $dados): array
    {
        $xml = $this->xmlBuilder->gerar($dados);

        $xmlPath = $this->salvarXml($xml, $dados['chave_acesso'] ?? 'temp');
        $dados['xml_file'] = $xmlPath;

        $resultado = $this->soap->autorizar($xml);

        if (!$resultado['success']) {
            return [
                'success' => false,
                'error' => $resultado['error'] ?? 'Falha na comunicação com SEFAZ',
                'xml_file' => $xmlPath,
            ];
        }

        return $this->processarResposta($resultado['response'], $xmlPath);
    }

    public function cancelar(string $chave, string $justificativa, string $protocolo): array
    {
        $xml = $this->xmlBuilder->gerarCancelamento($chave, $justificativa, $protocolo);
        $resultado = $this->soap->cancelar($xml);

        if (!$resultado['success']) {
            return ['success' => false, 'error' => $resultado['error'] ?? 'Falha no cancelamento'];
        }
        return ['success' => true, 'response' => $resultado['response']];
    }

    public function inutilizar(string $cnpj, string $modelo, string $serie, int $nNFIni, int $nNFFin, string $justificativa): array
    {
        $xml = $this->xmlBuilder->gerarInutilizacao($cnpj, $modelo, $serie, $nNFIni, $nNFFin, $justificativa);
        $resultado = $this->soap->inutilizar($xml);

        if (!$resultado['success']) {
            return ['success' => false, 'error' => $resultado['error'] ?? 'Falha na inutilização'];
        }
        return ['success' => true, 'response' => $resultado['response']];
    }

    private function salvarXml(string $xml, string $chave): string
    {
        $dir = ROOT_PATH . '/storage/nfe/xml/';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = $dir . $chave . '.xml';
        file_put_contents($path, $xml);
        return 'nfe/xml/' . $chave . '.xml';
    }

    private function processarResposta(string $response, string $xmlPath): array
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadXML($response);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');

        $protocolo = $xpath->evaluate('string(//nfe:protNFe/nfe:infProt/nfe:nProt)');
        $chave = $xpath->evaluate('string(//nfe:protNFe/nfe:infProt/nfe:chNFe)');
        $status = $xpath->evaluate('string(//nfe:retEnviNFe/nfe:cStat)');
        $motivo = $xpath->evaluate('string(//nfe:retEnviNFe/nfe:xMotivo)');

        return [
            'success' => $status === '104',
            'protocolo' => $protocolo,
            'chave_acesso' => $chave,
            'status' => $status,
            'motivo' => $motivo,
            'xml_file' => $xmlPath,
            'xml_response' => $response,
        ];
    }
}
