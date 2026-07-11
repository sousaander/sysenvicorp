<?php

namespace App\Libraries\Nfse;

class NfseService
{
    const AMBIENTE_HOMOLOGACAO = 2;
    const AMBIENTE_PRODUCAO = 1;

    private string $ambiente;
    private string $municipioIbge;
    private string $certPath;
    private string $certPassword;

    public function __construct(string $ambiente = 'homologacao', string $municipioIbge = '3550308')
    {
        $this->ambiente = $ambiente;
        $this->municipioIbge = $municipioIbge;
        $this->certPath = ROOT_PATH . '/storage/certs/cert.pfx';
        $this->certPassword = '';
    }

    public function emitir(array $dados): array
    {
        $xmlBuilder = new NfseXmlBuilder();
        $xml = $xmlBuilder->gerar($dados);
        $xmlPath = $this->salvarXml($xml, $dados['numero'] ?? 'temp');

        $resultado = $this->enviarSoap('GerarNfse', $xml);

        if (!$resultado['success']) {
            $this->salvarXml($resultado['response'] ?? '', 'erro_' . ($dados['numero'] ?? 'temp'));
            return [
                'success' => false,
                'message' => $resultado['error'] ?? 'Falha na comunicação com a prefeitura',
                'xml_file' => $xmlPath,
            ];
        }

        return $this->processarResposta($resultado['response'], $xmlPath);
    }

    public function cancelar(array $dados, string $justificativa): array
    {
        $xmlBuilder = new NfseXmlBuilder();
        $xml = $xmlBuilder->gerarCancelamento($dados['numero'] ?? '', $dados['chave_acesso'] ?? '', $justificativa);

        $resultado = $this->enviarSoap('CancelarNfse', $xml);

        if (!$resultado['success']) {
            return ['success' => false, 'message' => $resultado['error'] ?? 'Falha no cancelamento'];
        }

        return ['success' => true, 'message' => 'NFS-e cancelada com sucesso'];
    }

    private function enviarSoap(string $acao, string $xml): array
    {
        $urls = [
            'homologacao' => [
                '3550308' => 'https://homologacao.nfse.sao-paulo.gov.br/ws/nfse.asmx',
            ],
            'producao' => [
                '3550308' => 'https://nfse.sao-paulo.gov.br/ws/nfse.asmx',
            ],
        ];

        $url = $urls[$this->ambiente][$this->municipioIbge] ?? '';

        if (empty($url)) {
            return ['success' => false, 'error' => "URL não configurada para município {$this->municipioIbge}"];
        }

        $soap = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:nfse="http://www.abrasf.org.br/nfse">';
        $soap .= '<soap:Body><nfse:' . $acao . '><nfse:input>' . $xml . '</nfse:input></nfse:' . $acao . '></soap:Body></soap:Envelope>';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $soap,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "' . $url . '/' . $acao . '"',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 120,
        ]);

        if (!empty($this->certPath) && file_exists($this->certPath)) {
            curl_setopt($ch, CURLOPT_SSLCERT, $this->certPath);
            if (!empty($this->certPassword)) {
                curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->certPassword);
            }
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }
        return ['success' => true, 'response' => $response];
    }

    private function salvarXml(string $xml, string $filename): string
    {
        $dir = ROOT_PATH . '/storage/nfse/xml/';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $path = $dir . $filename . '.xml';
        file_put_contents($path, $xml);
        return 'nfse/xml/' . $filename . '.xml';
    }

    private function processarResposta(string $response, string $xmlPath): array
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadXML($response);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('nfse', 'http://www.abrasf.org.br/nfse');

        $numero = $xpath->evaluate('string(//nfse:NumeroNfse)');
        $codigoVerificacao = $xpath->evaluate('string(//nfse:CodigoVerificacao)');
        $dataEmissao = $xpath->evaluate('string(//nfse:DataEmissao)');
        $linkPdf = $xpath->evaluate('string(//nfse:LinkDownloadPdf)');
        $linkXml = $xpath->evaluate('string(//nfse:LinkDownloadXml)');

        return [
            'success' => !empty($numero),
            'numero_nfse' => $numero,
            'codigo_verificacao' => $codigoVerificacao,
            'data_emissao' => $dataEmissao,
            'link_pdf' => $linkPdf,
            'link_xml' => $linkXml,
            'xml_file' => $xmlPath,
            'protocolo' => $numero,
        ];
    }
}
