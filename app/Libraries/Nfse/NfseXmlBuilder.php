<?php

namespace App\Libraries\Nfse;

class NfseXmlBuilder
{
    public function gerar(array $dados): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<GerarNfseEnvio xmlns="http://www.abrasf.org.br/nfse">';
        $xml .= '<Rps>';
        $xml .= '<IdentificacaoRps>';
        $xml .= '<Numero>' . ($dados['numero'] ?? '1') . '</Numero>';
        $xml .= '<Serie>' . ($dados['serie'] ?? '1') . '</Serie>';
        $xml .= '<Tipo>' . $this->getRpsTipo($dados['rps_tipo'] ?? 'RPS') . '</Tipo>';
        $xml .= '</IdentificacaoRps>';
        $xml .= '<DataEmissao>' . $this->formatDate($dados['data_emissao'] ?? date('Y-m-d')) . '</DataEmissao>';
        $xml .= '<NaturezaOperacao>' . ($dados['natureza_operacao'] ?? '1') . '</NaturezaOperacao>';
        $xml .= '<RegimeEspecialTributacao>' . $this->getRegimeEspecial($dados['regime_especial_tributacao'] ?? 'nenhum') . '</RegimeEspecialTributacao>';
        $xml .= '<OptanteSimplesNacional>' . (!empty($dados['optante_simples_nacional']) ? '1' : '2') . '</OptanteSimplesNacional>';
        $xml .= '<IncentivoFiscal>' . (!empty($dados['incentivo_fiscal']) ? '1' : '2') . '</IncentivoFiscal>';

        $xml .= '<Servico>';
        $xml .= '<Valores>';
        $xml .= '<ValorServicos>' . $this->formatMoney($dados['servico_valor_total'] ?? $dados['valor_total'] ?? 0) . '</ValorServicos>';
        $xml .= '<ValorDeducoes>0.00</ValorDeducoes>';
        $xml .= '<ValorPis>' . $this->formatMoney($dados['servico_valor_pis'] ?? 0) . '</ValorPis>';
        $xml .= '<ValorCofins>' . $this->formatMoney($dados['servico_valor_cofins'] ?? 0) . '</ValorCofins>';
        $xml .= '<ValorInss>' . $this->formatMoney($dados['servico_valor_inss'] ?? 0) . '</ValorInss>';
        $xml .= '<ValorIr>' . $this->formatMoney($dados['servico_valor_ir'] ?? 0) . '</ValorIr>';
        $xml .= '<ValorCsll>' . $this->formatMoney($dados['servico_valor_csll'] ?? 0) . '</ValorCsll>';
        $xml .= '<IssRetido>' . ($dados['iss_retido'] ?? '2') . '</IssRetido>';
        $xml .= '<ValorIss>' . $this->formatMoney($dados['servico_valor_iss'] ?? 0) . '</ValorIss>';
        $xml .= '<BaseCalculo>' . $this->formatMoney($dados['servico_base_calculo'] ?? $dados['servico_valor_total'] ?? 0) . '</BaseCalculo>';
        $xml .= '<Aliquota>' . $this->formatAliquota($dados['servico_aliquota_iss'] ?? 0) . '</Aliquota>';
        $xml .= '<ValorLiquidoNfse>' . $this->formatMoney($dados['servico_valor_liquido'] ?? $dados['valor_total'] ?? 0) . '</ValorLiquidoNfse>';
        $xml .= '<DescontoIncondicionado>' . $this->formatMoney($dados['servico_desconto_incondicionado'] ?? 0) . '</DescontoIncondicionado>';
        $xml .= '<DescontoCondicionado>' . $this->formatMoney($dados['servico_desconto_condicionado'] ?? 0) . '</DescontoCondicionado>';
        $xml .= '<OutrasRetencoes>' . $this->formatMoney($dados['servico_outras_retencoes'] ?? 0) . '</OutrasRetencoes>';
        $xml .= '</Valores>';

        if (!empty($dados['servico_codigo_tributacao'])) {
            $xml .= '<ItemListaServico>' . $this->sanitize($dados['servico_codigo_tributacao']) . '</ItemListaServico>';
        }
        if (!empty($dados['servico_codigo_cnae'])) {
            $xml .= '<CodigoCnae>' . $this->sanitize($dados['servico_codigo_cnae']) . '</CodigoCnae>';
        }
        $xml .= '<Discriminacao>' . $this->sanitize($dados['servico_descricao'] ?? 'Serviço prestado') . '</Discriminacao>';
        $xml .= '<CodigoMunicipio>' . $this->sanitize($dados['cliente_codigo_municipio'] ?? '3550308') . '</CodigoMunicipio>';
        $xml .= '</Servico>';

        $xml .= '<Prestador>';
        $xml .= '<CpfCnpj>';
        $xml .= '<Cnpj>' . $this->sanitize($dados['emitente_cnpj'] ?? '') . '</Cnpj>';
        $xml .= '</CpfCnpj>';
        $xml .= '<InscricaoMunicipal>' . $this->sanitize($dados['emitente_inscricao_municipal'] ?? '') . '</InscricaoMunicipal>';
        $xml .= '</Prestador>';

        $xml .= '<Tomador>';
        $xml .= '<IdentificacaoTomador>';
        $xml .= '<CpfCnpj>';
        $cpfCnpj = preg_replace('/\D/', '', $dados['cliente_cpf_cnpj'] ?? '');
        if (strlen($cpfCnpj) <= 11) {
            $xml .= '<Cpf>' . $cpfCnpj . '</Cpf>';
        } else {
            $xml .= '<Cnpj>' . $cpfCnpj . '</Cnpj>';
        }
        $xml .= '</CpfCnpj>';
        if (!empty($dados['cliente_email'])) {
            $xml .= '<Email>' . $this->sanitize($dados['cliente_email']) . '</Email>';
        }
        $xml .= '</IdentificacaoTomador>';
        $xml .= '<RazaoSocial>' . $this->sanitize($dados['cliente_nome'] ?? 'Tomador') . '</RazaoSocial>';
        $xml .= '<Endereco>';
        $xml .= '<Endereco>' . $this->sanitize($dados['cliente_endereco'] ?? '') . '</Endereco>';
        $xml .= '<Numero>' . $this->sanitize($dados['cliente_numero'] ?? '') . '</Numero>';
        $xml .= '<Complemento>' . $this->sanitize($dados['cliente_complemento'] ?? '') . '</Complemento>';
        $xml .= '<Bairro>' . $this->sanitize($dados['cliente_bairro'] ?? '') . '</Bairro>';
        $xml .= '<CodigoMunicipio>' . $this->sanitize($dados['cliente_codigo_municipio'] ?? '') . '</CodigoMunicipio>';
        $xml .= '<Uf>' . $this->sanitize($dados['cliente_uf'] ?? '') . '</Uf>';
        $xml .= '<Cep>' . preg_replace('/\D/', '', $dados['cliente_cep'] ?? '') . '</Cep>';
        $xml .= '</Endereco>';
        $xml .= '</Tomador>';

        $xml .= '</Rps>';
        $xml .= '</GerarNfseEnvio>';

        return $xml;
    }

    public function gerarCancelamento(string $numero, string $chave, string $justificativa): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<CancelarNfseEnvio xmlns="http://www.abrasf.org.br/nfse">';
        $xml .= '<Pedido>';
        $xml .= '<InfPedidoCancelamento>';
        $xml .= '<IdentificacaoNfse>';
        $xml .= '<Numero>' . $numero . '</Numero>';
        $xml .= '<Cnpj>' . preg_replace('/\D/', '', $chave) . '</Cnpj>';
        $xml .= '<InscricaoMunicipal></InscricaoMunicipal>';
        $xml .= '<CodigoMunicipio>3550308</CodigoMunicipio>';
        $xml .= '</IdentificacaoNfse>';
        $xml .= '<CodigoCancelamento>1</CodigoCancelamento>';
        $xml .= '</InfPedidoCancelamento>';
        $xml .= '</Pedido>';
        $xml .= '</CancelarNfseEnvio>';
        return $xml;
    }

    private function formatDate(string $date): string
    {
        return date('Y-m-d\TH:i:s', strtotime($date));
    }

    private function formatMoney(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function formatAliquota(float $value): string
    {
        return number_format($value, 4, '.', '');
    }

    private function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_XML1, 'UTF-8');
    }

    private function getRpsTipo(string $tipo): string
    {
        $map = ['RPS' => '1', 'RPS-Mista' => '2', 'RPS-Cancelamento' => '3'];
        return $map[$tipo] ?? '1';
    }

    private function getRegimeEspecial(string $regime): string
    {
        $map = [
            'nenhum' => '1', 'microempresa_municipal' => '2', 'estimativa' => '3',
            'sociedade_profissionais' => '4', 'cooperativa' => '5',
            'mei' => '6', 'mei_iss_fixo' => '7',
        ];
        return $map[$regime] ?? '1';
    }
}
