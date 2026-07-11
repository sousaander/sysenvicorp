<?php

namespace App\Libraries\Cte;

class CteService
{
    private CteXmlBuilder $xmlBuilder;
    private CteSoapClient $soap;

    public function __construct(string $ambiente = 'homologacao', string $uf = 'AM')
    {
        $this->xmlBuilder = new CteXmlBuilder();
        $this->soap = new CteSoapClient($ambiente, $uf);
    }

    public function emitir(array $dados): array
    {
        $xml = $this->xmlBuilder->gerar($dados);
        $chave = $dados['chave_acesso'] ?? ('cte_' . ($dados['numero'] ?? 'temp'));
        $xmlPath = $this->salvarXml($xml, $chave);

        $resultado = $this->soap->autorizar($xml);

        if (!$resultado['success']) {
            return [
                'success' => false,
                'message' => $resultado['error'] ?? 'Falha na comunicação com SEFAZ',
                'xml_file' => $xmlPath,
            ];
        }

        return $this->processarResposta($resultado['response'], $xmlPath);
    }

    public function cancelar(array $dados, string $justificativa): array
    {
        if (empty($dados['chave_acesso']) || empty($dados['protocolo'])) {
            return ['success' => false, 'message' => 'Chave de acesso e protocolo necessários'];
        }

        $xml = $this->xmlBuilder->gerarCancelamento($dados['chave_acesso'], $justificativa, $dados['protocolo']);
        $resultado = $this->soap->cancelar($xml);

        if (!$resultado['success']) {
            return ['success' => false, 'message' => $resultado['error'] ?? 'Falha no cancelamento'];
        }

        return ['success' => true, 'message' => 'CT-e cancelado com sucesso'];
    }

    private function salvarXml(string $xml, string $filename): string
    {
        $dir = ROOT_PATH . '/storage/cte/xml/';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $path = $dir . $filename . '.xml';
        file_put_contents($path, $xml);
        return 'cte/xml/' . $filename . '.xml';
    }

    private function processarResposta(string $response, string $xmlPath): array
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadXML($response);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('cte', 'http://www.portalfiscal.inf.br/cte');

        $protocolo = $xpath->evaluate('string(//cte:protCTe/cte:infProt/cte:nProt)');
        $chave = $xpath->evaluate('string(//cte:protCTe/cte:infProt/cte:chCTe)');
        $status = $xpath->evaluate('string(//cte:retEnvioCTe/cte:cStat)');
        $motivo = $xpath->evaluate('string(//cte:retEnvioCTe/cte:xMotivo)');

        return [
            'success' => $status === '100' || $status === '104',
            'protocolo' => $protocolo,
            'chave_acesso' => $chave,
            'status' => $status,
            'motivo' => $motivo,
            'xml_file' => $xmlPath,
        ];
    }
}
