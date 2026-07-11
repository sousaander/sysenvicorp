<?php

namespace App\Libraries\NFe;

class NFeXmlBuilder
{
    private \DOMDocument $dom;

    public function __construct()
    {
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;
    }

    public function gerar(array $dados): string
    {
        $nfe = $this->dom->createElementNS('http://www.portalfiscal.inf.br/nfe', 'NFe');
        $nfe->setAttribute('xmlns', 'http://www.portalfiscal.inf.br/nfe');
        $infNFe = $this->dom->createElement('infNFe');
        $infNFe->setAttribute('versao', '4.00');
        $infNFe->setAttribute('Id', 'NFe' . $dados['chave_acesso']);

        $this->addIde($infNFe, $dados);
        $this->addEmitente($infNFe, $dados);
        $this->addDestinatario($infNFe, $dados);
        $this->addItens($infNFe, $dados);
        $this->addTotais($infNFe, $dados);
        $this->addInformacoesAdicionais($infNFe, $dados);

        $nfe->appendChild($infNFe);
        $this->dom->appendChild($nfe);

        return $this->dom->saveXML();
    }

    public function gerarCancelamento(string $chave, string $justificativa, string $protocolo): string
    {
        $cancel = $this->dom->createElement('cancelamento');
        $cancel->appendChild($this->dom->createElement('chave', $chave));
        $cancel->appendChild($this->dom->createElement('protocolo', $protocolo));
        $cancel->appendChild($this->dom->createElement('justificativa', $justificativa));
        $this->dom->appendChild($cancel);
        return $this->dom->saveXML();
    }

    public function gerarInutilizacao(string $cnpj, string $modelo, string $serie, int $nNFIni, int $nNFFin, string $justificativa): string
    {
        $inut = $this->dom->createElement('inutilizacao');
        $inut->appendChild($this->dom->createElement('cnpj', $cnpj));
        $inut->appendChild($this->dom->createElement('modelo', $modelo));
        $inut->appendChild($this->dom->createElement('serie', $serie));
        $inut->appendChild($this->dom->createElement('nNFIni', (string)$nNFIni));
        $inut->appendChild($this->dom->createElement('nNFFin', (string)$nNFFin));
        $inut->appendChild($this->dom->createElement('justificativa', $justificativa));
        $this->dom->appendChild($inut);
        return $this->dom->saveXML();
    }

    private function addIde(\DOMElement $parent, array $d): void
    {
        $ide = $this->dom->createElement('ide');
        $ide->appendChild($this->dom->createElement('cUF', $d['cUF'] ?? '13'));
        $ide->appendChild($this->dom->createElement('cNF', substr($d['chave_acesso'] ?? '', 35)));
        $ide->appendChild($this->dom->createElement('natOp', $this->sanitize($d['natureza_operacao'] ?? '')));
        $ide->appendChild($this->dom->createElement('mod', '55'));
        $ide->appendChild($this->dom->createElement('serie', $d['serie'] ?? '1'));
        $ide->appendChild($this->dom->createElement('nNF', (string)($d['numero'] ?? '')));
        $ide->appendChild($this->dom->createElement('dhEmi', $this->formatDateTime($d['emissao'] ?? '')));
        $ide->appendChild($this->dom->createElement('tpNF', $d['tipo'] === 'Entrada' ? '0' : '1'));
        $ide->appendChild($this->dom->createElement('idDest', $d['idDest'] ?? '1'));
        $ide->appendChild($this->dom->createElement('cMunFG', $d['cMunFG'] ?? '1302603'));
        $ide->appendChild($this->dom->createElement('tpImp', '1'));
        $ide->appendChild($this->dom->createElement('tpEmis', '1'));
        $ide->appendChild($this->dom->createElement('cDV', substr($d['chave_acesso'] ?? '', -1)));
        $ide->appendChild($this->dom->createElement('tpAmb', $d['ambiente'] === 'producao' ? '1' : '2'));
        $ide->appendChild($this->dom->createElement('finNFe', $d['finalidade'] ?? '1'));
        $ide->appendChild($this->dom->createElement('indFinal', '1'));
        $ide->appendChild($this->dom->createElement('indPres', '0'));
        $ide->appendChild($this->dom->createElement('procEmi', '0'));
        $ide->appendChild($this->dom->createElement('verProc', '1.0'));
        $parent->appendChild($ide);
    }

    private function addEmitente(\DOMElement $parent, array $d): void
    {
        $emit = $this->dom->createElement('emit');
        $emit->appendChild($this->dom->createElement('CNPJ', $this->onlyDigits($d['empresa_cnpj'] ?? '')));
        $emit->appendChild($this->dom->createElement('xNome', $this->sanitize($d['empresa_razao'] ?? '')));
        $emit->appendChild($this->dom->createElement('xFant', $this->sanitize($d['empresa_fantasia'] ?? '')));
        $enderEmit = $this->dom->createElement('enderEmit');
        $enderEmit->appendChild($this->dom->createElement('xLgr', $this->sanitize($d['empresa_logradouro'] ?? '')));
        $enderEmit->appendChild($this->dom->createElement('nro', $d['empresa_numero'] ?? 'S/N'));
        $enderEmit->appendChild($this->dom->createElement('xBairro', $this->sanitize($d['empresa_bairro'] ?? '')));
        $enderEmit->appendChild($this->dom->createElement('cMun', $d['empresa_cMun'] ?? ''));
        $enderEmit->appendChild($this->dom->createElement('xMun', $this->sanitize($d['empresa_cidade'] ?? '')));
        $enderEmit->appendChild($this->dom->createElement('UF', $d['empresa_uf'] ?? ''));
        $enderEmit->appendChild($this->dom->createElement('CEP', $this->onlyDigits($d['empresa_cep'] ?? '')));
        $enderEmit->appendChild($this->dom->createElement('fone', $this->onlyDigits($d['empresa_telefone'] ?? '')));
        $emit->appendChild($enderEmit);
        $emit->appendChild($this->dom->createElement('IE', $d['empresa_ie'] ?? ''));
        $emit->appendChild($this->dom->createElement('CRT', $this->getCrt($d['empresa_regime'] ?? 'Lucro Presumido')));
        $parent->appendChild($emit);
    }

    private function addDestinatario(\DOMElement $parent, array $d): void
    {
        $dest = $this->dom->createElement('dest');
        if (strlen($this->onlyDigits($d['cliente_cnpj_cpf'] ?? '')) === 14) {
            $dest->appendChild($this->dom->createElement('CNPJ', $this->onlyDigits($d['cliente_cnpj_cpf'] ?? '')));
        } else {
            $dest->appendChild($this->dom->createElement('CPF', $this->onlyDigits($d['cliente_cnpj_cpf'] ?? '')));
        }
        $dest->appendChild($this->dom->createElement('xNome', $this->sanitize($d['cliente_nome'] ?? '')));
        $enderDest = $this->dom->createElement('enderDest');
        $enderDest->appendChild($this->dom->createElement('xLgr', $this->sanitize($d['cliente_endereco'] ?? '')));
        $enderDest->appendChild($this->dom->createElement('nro', $d['cliente_numero'] ?? 'S/N'));
        $enderDest->appendChild($this->dom->createElement('xBairro', $this->sanitize($d['cliente_bairro'] ?? '')));
        $enderDest->appendChild($this->dom->createElement('cMun', $d['cliente_cMun'] ?? ''));
        $enderDest->appendChild($this->dom->createElement('xMun', $this->sanitize($d['cliente_cidade'] ?? '')));
        $enderDest->appendChild($this->dom->createElement('UF', $d['cliente_uf'] ?? ''));
        $enderDest->appendChild($this->dom->createElement('CEP', $this->onlyDigits($d['cliente_cep'] ?? '')));
        $dest->appendChild($enderDest);
        $dest->appendChild($this->dom->createElement('indIEDest', $d['cliente_ie'] ? '1' : '9'));
        if (!empty($d['cliente_ie'])) {
            $dest->appendChild($this->dom->createElement('IE', $d['cliente_ie']));
        }
        $dest->appendChild($this->dom->createElement('email', $d['cliente_email'] ?? ''));
        $parent->appendChild($dest);
    }

    private function addItens(\DOMElement $parent, array $d): void
    {
        $itens = json_decode($d['itens_json'] ?? '[]', true);
        $nItem = 1;
        foreach ($itens as $item) {
            $det = $this->dom->createElement('det');
            $det->setAttribute('nItem', (string)$nItem);
            $prod = $this->dom->createElement('prod');
            $prod->appendChild($this->dom->createElement('cProd', $item['codigo'] ?? (string)$nItem));
            $prod->appendChild($this->dom->createElement('cEAN', $item['ean'] ?? 'SEM GTIN'));
            $prod->appendChild($this->dom->createElement('xProd', $this->sanitize($item['descricao'] ?? '')));
            $prod->appendChild($this->dom->createElement('NCM', $item['ncm'] ?? '00000000'));
            $prod->appendChild($this->dom->createElement('CFOP', $item['cfop'] ?? $d['cfop'] ?? '0000'));
            $prod->appendChild($this->dom->createElement('uCom', $item['unidade'] ?? 'UN'));
            $prod->appendChild($this->dom->createElement('qCom', number_format((float)($item['quantidade'] ?? 1), 4, '.', '')));
            $prod->appendChild($this->dom->createElement('vUnCom', number_format((float)($item['valor_unitario'] ?? 0), 4, '.', '')));
            $prod->appendChild($this->dom->createElement('vProd', number_format((float)($item['valor_total'] ?? 0), 2, '.', '')));
            $prod->appendChild($this->dom->createElement('cEANTrib', $item['ean'] ?? 'SEM GTIN'));
            $prod->appendChild($this->dom->createElement('uTrib', $item['unidade'] ?? 'UN'));
            $prod->appendChild($this->dom->createElement('qTrib', number_format((float)($item['quantidade'] ?? 1), 4, '.', '')));
            $prod->appendChild($this->dom->createElement('vUnTrib', number_format((float)($item['valor_unitario'] ?? 0), 4, '.', '')));
            $prod->appendChild($this->dom->createElement('indTot', '1'));
            $det->appendChild($prod);

            $imposto = $this->dom->createElement('imposto');
            $this->addIcms($imposto, $item);
            $this->addPis($imposto, $item);
            $this->addCofins($imposto, $item);
            $det->appendChild($imposto);

            $det->appendChild($this->dom->createElement('infAdProd', $item['info_adicional'] ?? ''));
            $parent->appendChild($det);
            $nItem++;
        }
    }

    private function addIcms(\DOMElement $parent, array $item): void
    {
        $icms = $this->dom->createElement('ICMS');
        $icms00 = $this->dom->createElement('ICMS00');
        $icms00->appendChild($this->dom->createElement('orig', '0'));
        $icms00->appendChild($this->dom->createElement('CST', $item['cst_icms'] ?? '00'));
        $icms00->appendChild($this->dom->createElement('modBC', '3'));
        $icms00->appendChild($this->dom->createElement('vBC', number_format((float)($item['base_icms'] ?? 0), 2, '.', '')));
        $icms00->appendChild($this->dom->createElement('pICMS', number_format((float)($item['aliquota_icms'] ?? 0), 2, '.', '')));
        $icms00->appendChild($this->dom->createElement('vICMS', number_format((float)($item['valor_icms'] ?? 0), 2, '.', '')));
        $icms->appendChild($icms00);
        $parent->appendChild($icms);
    }

    private function addPis(\DOMElement $parent, array $item): void
    {
        $pis = $this->dom->createElement('PIS');
        $pisAliq = $this->dom->createElement('PISAliq');
        $pisAliq->appendChild($this->dom->createElement('CST', $item['cst_pis'] ?? '01'));
        $pisAliq->appendChild($this->dom->createElement('vBC', number_format((float)($item['base_pis'] ?? 0), 2, '.', '')));
        $pisAliq->appendChild($this->dom->createElement('pPIS', number_format((float)($item['aliquota_pis'] ?? 0), 2, '.', '')));
        $pisAliq->appendChild($this->dom->createElement('vPIS', number_format((float)($item['valor_pis'] ?? 0), 2, '.', '')));
        $pis->appendChild($pisAliq);
        $parent->appendChild($pis);
    }

    private function addCofins(\DOMElement $parent, array $item): void
    {
        $cofins = $this->dom->createElement('COFINS');
        $cofinsAliq = $this->dom->createElement('COFINSAliq');
        $cofinsAliq->appendChild($this->dom->createElement('CST', $item['cst_cofins'] ?? '01'));
        $cofinsAliq->appendChild($this->dom->createElement('vBC', number_format((float)($item['base_cofins'] ?? 0), 2, '.', '')));
        $cofinsAliq->appendChild($this->dom->createElement('pCOFINS', number_format((float)($item['aliquota_cofins'] ?? 0), 2, '.', '')));
        $cofinsAliq->appendChild($this->dom->createElement('vCOFINS', number_format((float)($item['valor_cofins'] ?? 0), 2, '.', '')));
        $cofins->appendChild($cofinsAliq);
        $parent->appendChild($cofins);
    }

    private function addTotais(\DOMElement $parent, array $d): void
    {
        $total = $this->dom->createElement('total');
        $icmsTot = $this->dom->createElement('ICMSTot');
        $itens = json_decode($d['itens_json'] ?? '[]', true);
        $vBC = (float)($d['base_calculo_icms'] ?? 0);
        $vICMS = (float)($d['valor_icms'] ?? 0);
        $vProd = array_sum(array_column($itens, 'valor_total'));
        $vNF = $vProd + (float)($d['valor_iss'] ?? 0);

        $icmsTot->appendChild($this->dom->createElement('vBC', number_format($vBC, 2, '.', '')));
        $icmsTot->appendChild($this->dom->createElement('vICMS', number_format($vICMS, 2, '.', '')));
        $icmsTot->appendChild($this->dom->createElement('vICMSDeson', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vFCP', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vBCST', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vST', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vFCPST', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vFCPSTRet', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vProd', number_format($vProd, 2, '.', '')));
        $icmsTot->appendChild($this->dom->createElement('vFrete', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vSeg', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vDesc', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vII', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vIPI', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vIPIDevol', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vPIS', number_format((float)($d['valor_pis'] ?? 0), 2, '.', '')));
        $icmsTot->appendChild($this->dom->createElement('vCOFINS', number_format((float)($d['valor_cofins'] ?? 0), 2, '.', '')));
        $icmsTot->appendChild($this->dom->createElement('vOutro', '0.00'));
        $icmsTot->appendChild($this->dom->createElement('vNF', number_format($vNF, 2, '.', '')));
        $icmsTot->appendChild($this->dom->createElement('vTotTrib', number_format($vICMS + (float)($d['valor_pis'] ?? 0) + (float)($d['valor_cofins'] ?? 0), 2, '.', '')));
        $total->appendChild($icmsTot);
        $parent->appendChild($total);
    }

    private function addInformacoesAdicionais(\DOMElement $parent, array $d): void
    {
        $infAdic = $this->dom->createElement('infAdic');
        $infAdic->appendChild($this->dom->createElement('infCpl', $this->sanitize($d['observacoes'] ?? '')));
        $parent->appendChild($infAdic);
    }

    private function sanitize(string $value): string
    {
        return trim(preg_replace('/[^\x20-\x7E\xC0-\xFF\x{201C}\x{201D}\x{2019}\x{2018}]/u', '', $value));
    }

    private function formatDateTime(string $date): string
    {
        if (empty($date)) return date('Y-m-d\TH:i:sP');
        return date('Y-m-d\TH:i:sP', strtotime($date));
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    private function getCrt(string $regime): string
    {
        return match ($regime) {
            'Simples Nacional' => '1',
            'Simples Nacional (excesso)' => '2',
            'Lucro Real' => '3',
            default => '3',
        };
    }
}
