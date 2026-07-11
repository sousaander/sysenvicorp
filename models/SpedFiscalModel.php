<?php

namespace App\Models;

use App\Core\Model;

class SpedFiscalModel extends Model
{
    public function gerarSpedFiscal(string $dataInicio, string $dataFim): array
    {
        $blocos = [];

        $blocos['B000'] = $this->bloco0($dataInicio, $dataFim);
        $blocos['C000'] = $this->blocoC($dataInicio, $dataFim);
        $blocos['D000'] = $this->blocoD($dataInicio, $dataFim);
        $blocos['E000'] = $this->blocoE($dataInicio, $dataFim);
        $blocos['H000'] = $this->blocoH($dataInicio, $dataFim);

        return $blocos;
    }

    public function gerarSpedContabil(string $dataInicio, string $dataFim): array
    {
        $blocos = [];

        $blocos['I000'] = $this->blocoI($dataInicio, $dataFim);
        $blocos['J000'] = $this->blocoJ($dataInicio, $dataFim);

        return $blocos;
    }

    public function exportarTxt(array $blocos): string
    {
        $linhas = [];
        $linhas[] = '|REGISTRO|CAMPO1|CAMPO2|CAMPO3|';

        foreach ($blocos as $registro => $dados) {
            if (isset($dados['linhas'])) {
                foreach ($dados['linhas'] as $linha) {
                    $linhas[] = '|' . implode('|', $linha) . '|';
                }
            }
        }

        return implode("\r\n", $linhas);
    }

    private function bloco0(string $di, string $df): array
    {
        $empresa = $this->getDadosEmpresa();
        return [
            'linhas' => [
                ['0000', '000', $empresa['cnpj'] ?? '', $empresa['razao_social'] ?? '', $di, $df],
                ['0001', '0'],
                ['0002', '0'],
            ]
        ];
    }

    private function blocoC(string $di, string $df): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT n.numero, n.emissao, n.cnpj_cpf, n.cliente_fornecedor,
                       n.valor, n.cfop, n.itens_json, n.base_calculo_icms, n.valor_icms
                FROM notas_fiscais n
                WHERE n.emissao BETWEEN :di AND :df AND n.status = 'Autorizada'
                ORDER BY n.emissao
            ");
            $stmt->execute([':di' => $di, ':df' => $df]);
            $notas = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $linhas = [];
            foreach ($notas as $n) {
                $linhas[] = ['C100', $n['numero'], $n['emissao'], $n['cliente_fornecedor'], $n['cnpj_cpf'], number_format($n['valor'], 2, '.', '')];
                $linhas[] = ['C190', $n['cfop'] ?? '0000', number_format($n['base_calculo_icms'] ?? 0, 2, '.', ''), number_format($n['valor_icms'] ?? 0, 2, '.', '')];
            }
            return ['linhas' => $linhas];
        } catch (\PDOException $e) {
            return ['linhas' => []];
        }
    }

    private function blocoD(string $di, string $df): array
    {
        return ['linhas' => [['D000', '0']]];
    }

    private function blocoE(string $di, string $df): array
    {
        return ['linhas' => [['E000', '0']]];
    }

    private function blocoH(string $di, string $df): array
    {
        return ['linhas' => [['H000', '0']]];
    }

    private function blocoI(string $di, string $df): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT l.id, l.descricao, l.valor, l.tipo, l.data_lancamento, l.categoria
                FROM lancamentos_contabeis l
                WHERE l.data_lancamento BETWEEN :di AND :df
                ORDER BY l.data_lancamento
            ");
            $stmt->execute([':di' => $di, ':df' => $df]);
            $lancamentos = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $linhas = [];
            foreach ($lancamentos as $l) {
                $linhas[] = ['I100', $l['id'], $l['descricao'], $l['categoria'], $l['tipo'], number_format($l['valor'], 2, '.', '')];
            }
            return ['linhas' => $linhas];
        } catch (\PDOException $e) {
            return ['linhas' => []];
        }
    }

    private function blocoJ(string $di, string $df): array
    {
        return ['linhas' => [['J000', '0']]];
    }

    private function getDadosEmpresa(): array
    {
        $file = ROOT_PATH . '/storage/config/empresa.json';
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true) ?: [];
        }
        return [];
    }
}
