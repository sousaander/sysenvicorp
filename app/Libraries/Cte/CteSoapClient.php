<?php

namespace App\Libraries\Cte;

class CteSoapClient
{
    private string $ambiente;
    private string $uf;
    private array $urls = [
        'homologacao' => [
            'AM' => 'https://hom1.sefaz.am.gov.br/cte/CTeAutorizacao/CTeAutorizacao.asmx',
            'SP' => 'https://homologacao.cte.fazenda.sp.gov.br/ws/CTeAutorizacao.asmx',
        ],
        'producao' => [
            'AM' => 'https://cte.sefaz.am.gov.br/cte/CTeAutorizacao/CTeAutorizacao.asmx',
            'SP' => 'https://cte.fazenda.sp.gov.br/ws/CTeAutorizacao.asmx',
        ],
    ];

    public function __construct(string $ambiente = 'homologacao', string $uf = 'AM')
    {
        $this->ambiente = $ambiente;
        $this->uf = $uf;
    }

    public function autorizar(string $xml): array
    {
        return $this->enviarSoap('CTeAutorizacao', 'cteDadosMsg', $xml);
    }

    public function cancelar(string $xml): array
    {
        return $this->enviarSoap('CTeCancelamento', 'cteDadosMsg', $xml);
    }

    private function enviarSoap(string $action, string $tag, string $xml): array
    {
        $soap = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">';
        $soap .= '<soap:Body>';
        $soap .= '<' . $action . ' xmlns="http://www.portalfiscal.inf.br/cte/wsdl/' . $action . '">';
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

        if ($error) return ['success' => false, 'error' => $error, 'http_code' => $httpCode];
        return ['success' => true, 'response' => $response, 'http_code' => $httpCode];
    }

    private function getUrl(): string
    {
        return $this->urls[$this->ambiente][$this->uf] ?? throw new \RuntimeException("URL CT-e não configurada para {$this->ambiente}/{$this->uf}");
    }
}
