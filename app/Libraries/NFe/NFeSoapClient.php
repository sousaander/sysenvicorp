<?php

namespace App\Libraries\NFe;

class NFeSoapClient
{
    private string $ambiente;
    private string $uf;
    private array $urls = [
        'homologacao' => [
            'AM' => 'https://hom1.sefaz.am.gov.br/nfe/NFeAutorizacao/NFeAutorizacao.asmx',
        ],
        'producao' => [
            'AM' => 'https://nfe.sefaz.am.gov.br/nfe/NFeAutorizacao/NFeAutorizacao.asmx',
        ],
    ];

    public function __construct(string $ambiente = 'homologacao', string $uf = 'AM')
    {
        $this->ambiente = $ambiente;
        $this->uf = $uf;
    }

    public function autorizar(string $xml): array
    {
        return $this->enviarSoap('NFeAutorizacao', 'nfeDadosMsg', $xml);
    }

    public function cancelar(string $xml): array
    {
        return $this->enviarSoap('NFeCancelamento', 'cancelamento', $xml);
    }

    public function inutilizar(string $xml): array
    {
        return $this->enviarSoap('NFeInutilizacao', 'nfeDadosMsg', $xml);
    }

    public function consultarRecibo(string $recibo): array
    {
        $xml = '<consReciNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00"><tpAmb>' .
            ($this->ambiente === 'producao' ? '1' : '2') . '</tpAmb><nRec>' . $recibo . '</nRec></consReciNFe>';

        $ch = curl_init($this->getUrl());
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_HTTPHEADER => ['Content-Type: application/xml; charset=utf-8'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 60,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }
        return ['success' => true, 'response' => $response];
    }

    private function enviarSoap(string $action, string $tag, string $xml): array
    {
        $soap = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">';
        $soap .= '<soap:Body>';
        $soap .= '<' . $action . ' xmlns="http://www.portalfiscal.inf.br/nfe/wsdl/' . $action . '">';
        $soap .= '<' . $tag . '>' . $xml . '</' . $tag . '>';
        $soap .= '</' . $action . '>';
        $soap .= '</soap:Body></soap:Envelope>';

        $ch = curl_init($this->getUrl());
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $soap,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/soap+xml; charset=utf-8',
                'SOAPAction: ' . $action,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 120,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error, 'http_code' => $httpCode];
        }
        return ['success' => true, 'response' => $response, 'http_code' => $httpCode];
    }

    private function getUrl(): string
    {
        return $this->urls[$this->ambiente][$this->uf]
            ?? throw new \RuntimeException("URL não configurada para {$this->ambiente}/{$this->uf}");
    }
}
