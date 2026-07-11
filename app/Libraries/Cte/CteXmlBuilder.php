<?php

namespace App\Libraries\Cte;

class CteXmlBuilder
{
    public function gerar(array $dados): string
    {
        $cNF = $this->onlyDigits($dados['chave_acesso'] ?? str_pad((string)($dados['numero'] ?? '1'), 44, '0'));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<CTe xmlns="http://www.portalfiscal.inf.br/cte" versao="4.00">';
        $xml .= '<infCte versao="4.00" Id="CTe' . $cNF . '">';

        $xml .= '<ide>';
        $xml .= '<cUF>' . $this->getCodigoUF($dados['tomador_uf'] ?? 'AM') . '</cUF>';
        $xml .= '<cCT>' . $this->onlyDigits(substr($cNF, 0, 8)) . '</cCT>';
        $xml .= '<CFOP>' . $this->sanitize($dados['cfop'] ?? '5353') . '</CFOP>';
        $xml .= '<natOp>' . $this->sanitize($dados['natureza_operacao'] ?? 'Prestacao de servico de transporte') . '</natOp>';
        $xml .= '<mod>57</mod>';
        $xml .= '<serie>' . $this->sanitize($dados['serie'] ?? '1') . '</serie>';
        $xml .= '<nCT>' . ($dados['numero'] ?? '1') . '</nCT>';
        $xml .= '<dhEmi>' . $this->formatDateTime($dados['data_emissao'] ?? date('Y-m-d')) . '</dhEmi>';
        $xml .= '<tpImp>1</tpImp>';
        $xml .= '<tpEmis>1</tpEmis>';
        $xml .= '<cDV>' . $this->calcularDV($cNF) . '</cDV>';
        $xml .= '<tpAmb>' . $this->getAmbiente() . '</tpAmb>';
        $xml .= '<tpCTe>' . $this->getTipoCte($dados['tipo_cte'] ?? 'normal') . '</tpCTe>';
        $xml .= '<procEmi>0</procEmi>';
        $xml .= '<verProc>Sistema Envicorp 1.0</verProc>';
        $xml .= '<cMunFG>3550308</cMunFG>';
        $xml .= '<tpServ>' . $this->getTipoServico($dados['tipo_servico'] ?? 'normal') . '</tpServ>';
        $xml .= '<cMunIni>3550308</cMunIni>';
        $xml .= '<xFerrei>' . $this->sanitize($dados['tomador_municipio'] ?? '') . '</xFerrei>';
        $xml .= '</ide>';

        $xml .= '<emit>';
        $xml .= '<CNPJ>' . $this->onlyDigits($dados['emitente_cnpj'] ?? '') . '</CNPJ>';
        $xml .= '<xNome>' . $this->sanitize($dados['emitente_nome'] ?? 'Empresa Emitente') . '</xNome>';
        $xml .= '<xFant>' . $this->sanitize($dados['emitente_fantasia'] ?? '') . '</xFant>';
        $xml .= '<enderEmit>';
        $xml .= '<xLgr>' . $this->sanitize($dados['emitente_endereco'] ?? '') . '</xLgr>';
        $xml .= '<nro>' . $this->sanitize($dados['emitente_numero'] ?? '') . '</nro>';
        $xml .= '<xBairro>' . $this->sanitize($dados['emitente_bairro'] ?? '') . '</xBairro>';
        $xml .= '<cMun>3550308</cMun>';
        $xml .= '<xMun>' . $this->sanitize($dados['emitente_municipio'] ?? 'Sao Paulo') . '</xMun>';
        $xml .= '<UF>' . $this->sanitize($dados['emitente_uf'] ?? 'SP') . '</UF>';
        $xml .= '<CEP>' . $this->onlyDigits($dados['emitente_cep'] ?? '') . '</CEP>';
        $xml .= '<fone>' . $this->onlyDigits($dados['emitente_telefone'] ?? '') . '</fone>';
        $xml .= '</enderEmit>';
        $xml .= '<IE>' . $this->sanitize($dados['emitente_ie'] ?? '') . '</IE>';
        $xml .= '</emit>';

        $xml .= '<tomador>';
        $xml .= '<toma>' . ($dados['tomador_tipo'] ?? '3') . '</toma>';
        $xml .= '<CNPJ>' . $this->onlyDigits($dados['tomador_cpf_cnpj'] ?? '') . '</CNPJ>';
        $xml .= '<xNome>' . $this->sanitize($dados['tomador_nome'] ?? 'Tomador') . '</xNome>';
        $xml .= '<enderToma>';
        $xml .= '<xLgr>' . $this->sanitize($dados['tomador_endereco'] ?? '') . '</xLgr>';
        $xml .= '<nro>s/n</nro>';
        $xml .= '<xBairro>Centro</xBairro>';
        $xml .= '<cMun>3550308</cMun>';
        $xml .= '<xMun>' . $this->sanitize($dados['tomador_municipio'] ?? '') . '</xMun>';
        $xml .= '<UF>' . $this->sanitize($dados['tomador_uf'] ?? 'SP') . '</UF>';
        $xml .= '<CEP>' . $this->onlyDigits($dados['tomador_cep'] ?? '') . '</CEP>';
        $xml .= '</enderToma>';
        $xml .= '</tomador>';

        $xml .= '<vPrest>';
        $xml .= '<vTPrest>' . $this->formatMoney($dados['valor_total'] ?? $dados['valor_frete'] ?? 0) . '</vTPrest>';
        $xml .= '<vRec>' . $this->formatMoney($dados['valor_recebido'] ?? $dados['valor_frete'] ?? 0) . '</vRec>';
        $xml .= '</vPrest>';

        $xml .= '<imp>';
        $xml .= '<ICMS>';
        $xml .= '<ICMS00>';
        $xml .= '<CST>00</CST>';
        $xml .= '<vBC>' . $this->formatMoney($dados['base_calculo_icms'] ?? 0) . '</vBC>';
        $xml .= '<pICMS>' . $this->formatMoney($dados['aliquota_icms'] ?? 0) . '</pICMS>';
        $xml .= '<vICMS>' . $this->formatMoney($dados['valor_icms'] ?? 0) . '</vICMS>';
        $xml .= '<pRedBC>' . $this->formatMoney($dados['perc_red_base_calc_icms'] ?? 0) . '</pRedBC>';
        $xml .= '</ICMS00>';
        $xml .= '</ICMS>';

        $xml .= '<infCteSub>';
        $xml .= '<chCte>' . $this->sanitize($dados['chave_cte_substituto'] ?? '') . '</chCte>';
        $xml .= '</infCteSub>';

        $xml .= '</imp>';

        $xml .= '<infCTeNorm>';
        $xml .= '<infCarga>';
        $xml .= '<vCarga>' . $this->formatMoney($dados['valor_mercadorias'] ?? 0) . '</vCarga>';
        $xml .= '<proPred>' . $this->sanitize($dados['produto_predominante'] ?? 'Mercadorias diversas') . '</proPred>';
        $xml .= '</infCarga>';

        if (!empty($dados['remetente_nome'])) {
            $xml .= '<rem>';
            $xml .= '<CNPJ>' . $this->onlyDigits($dados['remetente_cpf_cnpj'] ?? '') . '</CNPJ>';
            $xml .= '<xNome>' . $this->sanitize($dados['remetente_nome']) . '</xNome>';
            $xml .= '<enderRem>';
            $xml .= '<xLgr>' . $this->sanitize($dados['remetente_endereco'] ?? '') . '</xLgr>';
            $xml .= '<nro>s/n</nro>';
            $xml .= '<xBairro>Centro</xBairro>';
            $xml .= '<cMun>3550308</cMun>';
            $xml .= '<xMun>' . $this->sanitize($dados['remetente_municipio'] ?? '') . '</xMun>';
            $xml .= '<UF>' . $this->sanitize($dados['remetente_uf'] ?? 'SP') . '</UF>';
            $xml .= '</enderRem>';
            $xml .= '</rem>';
        }

        if (!empty($dados['destinatario_nome'])) {
            $xml .= '<dest>';
            $xml .= '<CNPJ>' . $this->onlyDigits($dados['destinatario_cpf_cnpj'] ?? '') . '</CNPJ>';
            $xml .= '<xNome>' . $this->sanitize($dados['destinatario_nome']) . '</xNome>';
            $xml .= '<enderDest>';
            $xml .= '<xLgr>' . $this->sanitize($dados['destinatario_endereco'] ?? '') . '</xLgr>';
            $xml .= '<nro>s/n</nro>';
            $xml .= '<xBairro>Centro</xBairro>';
            $xml .= '<cMun>3550308</cMun>';
            $xml .= '<xMun>' . $this->sanitize($dados['destinatario_municipio'] ?? '') . '</xMun>';
            $xml .= '<UF>' . $this->sanitize($dados['destinatario_uf'] ?? 'SP') . '</UF>';
            $xml .= '</enderDest>';
            $xml .= '</dest>';
        }

        $xml .= '</infCTeNorm>';

        $xml .= '</infCte>';
        $xml .= '</CTe>';

        return $xml;
    }

    public function gerarCancelamento(string $chave, string $justificativa, string $protocolo): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<cancelamentoCTe xmlns="http://www.portalfiscal.inf.br/cte" versao="4.00">';
        $xml .= '<infCanc>';
        $xml .= '<chCTe>' . $this->sanitize($chave) . '</chCTe>';
        $xml .= '<nProt>' . $this->sanitize($protocolo) . '</nProt>';
        $xml .= '<xJust>' . $this->sanitize($justificativa) . '</xJust>';
        $xml .= '</infCanc>';
        $xml .= '</cancelamentoCTe>';
        return $xml;
    }

    private function formatDateTime(string $date): string
    {
        return date('Y-m-d\TH:i:sP', strtotime($date));
    }

    private function formatMoney(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value ?? ''), ENT_XML1, 'UTF-8');
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value ?? '');
    }

    private function getCodigoUF(string $uf): string
    {
        $map = ['RO' => '11','AC' => '12','AM' => '13','RR' => '14','PA' => '15','AP' => '16','TO' => '17',
                'MA' => '21','PI' => '22','CE' => '23','RN' => '24','PB' => '25','PE' => '26','AL' => '27',
                'SE' => '28','BA' => '29','MG' => '31','ES' => '32','RJ' => '33','SP' => '35','PR' => '41',
                'SC' => '42','RS' => '43','MS' => '50','MT' => '51','GO' => '52','DF' => '53'];
        return $map[strtoupper($uf)] ?? '35';
    }

    private function calcularDV(string $chave): string
    {
        $multiplicadores = [2,3,4,5,6,7,8,9];
        $soma = 0;
        $pos = 0;
        for ($i = strlen($chave) - 1; $i >= 0; $i--) {
            $soma += (int)$chave[$i] * $multiplicadores[$pos % 8];
            $pos++;
        }
        $resto = $soma % 11;
        return $resto < 2 ? '0' : (string)(11 - $resto);
    }

    private function getAmbiente(): string
    {
        return (defined('NFE_AMBIENTE') && NFE_AMBIENTE === 'producao') ? '1' : '2';
    }

    private function getTipoCte(string $tipo): string
    {
        $map = ['normal' => '0', 'complementar' => '1', 'anulacao' => '2', 'substituto' => '3'];
        return $map[$tipo] ?? '0';
    }

    private function getTipoServico(string $tipo): string
    {
        $map = ['normal' => '0', 'subcontratacao' => '1', 'redespacho_intermediario' => '2', 'servico_municipal' => '3'];
        return $map[$tipo] ?? '0';
    }
}
