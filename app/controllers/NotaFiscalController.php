<?php

namespace App\Controllers;

use App\Models\NotaFiscalModel;
use App\Models\RetencaoModel;
use App\Models\ClientesModel;
use App\Libraries\NFe\NFeService;

class NotaFiscalController extends BaseController
{
    protected $requiredPermissions = [
        'index' => 'fiscal_notas_view',
        'form' => 'fiscal_notas_manage',
        'salvar' => 'fiscal_notas_manage',
        'detalhe' => 'fiscal_notas_view',
        'excluir' => 'fiscal_notas_manage',
        'emitirNfe' => 'fiscal_notas_manage',
        'cancelarNfe' => 'fiscal_notas_manage',
        'danfe' => 'fiscal_notas_view',
    ];

    private NotaFiscalModel $notaFiscalModel;
    private RetencaoModel $retencaoModel;

    public function __construct()
    {
        parent::__construct();
        $this->notaFiscalModel = new NotaFiscalModel();
        $this->retencaoModel = new RetencaoModel();
    }

    public function index()
    {
        $filtros = [];
        if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
        if (!empty($_GET['tipo'])) $filtros['tipo'] = $_GET['tipo'];
        if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];

        $notas = $this->notaFiscalModel->getAll($filtros);

        $this->renderView('fiscal/notas', [
            'pageTitle' => 'Notas Fiscais',
            'notas' => $notas,
            'filtros' => $filtros,
        ]);
    }

    public function form(int $id = null)
    {
        $nota = null;
        if ($id) {
            $nota = $this->notaFiscalModel->getById($id);
            if (!$nota) {
                $this->setFlashMessage('error', 'Nota Fiscal não encontrada.');
                header('Location: ' . BASE_URL . '/fiscal/notas');
                exit();
            }
        }

        $clientesModel = new \App\Models\ClientesModel();
        $clientes = $clientesModel->getClientesSummary() ?? [];

        $empresaModel = new \App\Models\EmpresaModel();
        $empresa = $empresaModel->getDadosEmpresa();

        $proximoNumero = $this->notaFiscalModel->getProximoNumero();

        $this->renderView('fiscal/nota_form', [
            'pageTitle' => $id ? 'Editar Nota Fiscal' : 'Emitir Nova Nota Fiscal',
            'nota' => $nota,
            'clientes' => $clientes,
            'empresa' => $empresa,
            'proximoNumero' => $proximoNumero,
        ]);
    }

    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/fiscal/notas');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'numero' => $_POST['numero'] ?? '',
            'serie' => $_POST['serie'] ?? '1',
            'chave_acesso' => $_POST['chave_acesso'] ?? '',
            'cfop' => $_POST['cfop'] ?? '',
            'natureza_operacao' => $_POST['natureza_operacao'] ?? '',
            'finalidade' => $_POST['finalidade'] ?? '1',
            'tipo' => $_POST['tipo'] ?? 'Saida',
            'cliente_id' => filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT) ?: null,
            'cliente_fornecedor' => $_POST['cliente_fornecedor'] ?? '',
            'cnpj_cpf' => $_POST['cnpj_cpf'] ?? '',
            'cliente_ie' => $_POST['cliente_ie'] ?? '',
            'cliente_endereco' => $_POST['cliente_endereco'] ?? '',
            'cliente_municipio_ibge' => $_POST['cliente_municipio_ibge'] ?? '',
            'cliente_uf' => $_POST['cliente_uf'] ?? '',
            'emissao' => $_POST['emissao'] ?? date('Y-m-d'),
            'valor' => str_replace(',', '.', $_POST['valor'] ?? '0'),
            'itens_json' => $_POST['itens_json'] ?? '[]',
            'status' => $_POST['status'] ?? 'Pendente',
            'cfop' => $_POST['cfop'] ?? '',
            'natureza_operacao' => $_POST['natureza_operacao'] ?? '',
            'finalidade' => $_POST['finalidade'] ?? '1',
            'tipo' => $_POST['tipo'] ?? 'Saida',
            'base_calculo_icms' => str_replace(',', '.', $_POST['base_calculo_icms'] ?? '0'),
            'valor_icms' => str_replace(',', '.', $_POST['valor_icms'] ?? '0'),
            'base_calculo_pis' => str_replace(',', '.', $_POST['base_calculo_pis'] ?? '0'),
            'valor_pis' => str_replace(',', '.', $_POST['valor_pis'] ?? '0'),
            'base_calculo_cofins' => str_replace(',', '.', $_POST['base_calculo_cofins'] ?? '0'),
            'valor_cofins' => str_replace(',', '.', $_POST['valor_cofins'] ?? '0'),
            'valor_iss' => str_replace(',', '.', $_POST['valor_iss'] ?? '0'),
            'valor_irrf' => str_replace(',', '.', $_POST['valor_irrf'] ?? '0'),
            'valor_inss' => str_replace(',', '.', $_POST['valor_inss'] ?? '0'),
            'valor_csll' => str_replace(',', '.', $_POST['valor_csll'] ?? '0'),
            'retencao_pis' => str_replace(',', '.', $_POST['retencao_pis'] ?? '0'),
            'retencao_cofins' => str_replace(',', '.', $_POST['retencao_cofins'] ?? '0'),
            'retencao_csll' => str_replace(',', '.', $_POST['retencao_csll'] ?? '0'),
            'retencao_iss' => str_replace(',', '.', $_POST['retencao_iss'] ?? '0'),
            'retencao_inss' => str_replace(',', '.', $_POST['retencao_inss'] ?? '0'),
            'retencao_irrf' => str_replace(',', '.', $_POST['retencao_irrf'] ?? '0'),
            'observacoes' => $_POST['observacoes'] ?? '',
            'usuario_emissao' => $this->session->get('user_id'),
        ];

        $id = $this->notaFiscalModel->salvar($dados);
        if ($id) {
            $this->setFlashMessage('success', 'Nota fiscal salva com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar nota fiscal.');
        }
        header('Location: ' . BASE_URL . '/fiscal/notas');
        exit();
    }

    public function detalhe(int $id)
    {
        $nota = $this->notaFiscalModel->getById($id);
        if (!$nota) {
            $this->setFlashMessage('error', 'Nota fiscal não encontrada.');
            header('Location: ' . BASE_URL . '/fiscal/notas');
            exit();
        }

        $retencoes = $this->retencaoModel->getByNotaFiscal($id);

        $this->renderView('fiscal/nota_detalhe', [
            'pageTitle' => 'Nota Fiscal #' . $nota['numero'],
            'nota' => $nota,
            'retencoes' => $retencoes,
        ]);
    }

    public function excluir(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/fiscal/notas');
            exit();
        }

        if ($this->notaFiscalModel->excluir($id)) {
            $this->setFlashMessage('success', 'Nota fiscal excluída.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir nota fiscal.');
        }
        header('Location: ' . BASE_URL . '/fiscal/notas');
        exit();
    }

    public function emitirNfe(int $id)
    {
        $nota = $this->notaFiscalModel->getById($id);
        if (!$nota) {
            $this->setFlashMessage('error', 'Nota fiscal não encontrada.');
            header('Location: ' . BASE_URL . '/fiscal/notas');
            exit();
        }

        $empresaModel = new \App\Models\EmpresaModel();
        $empresa = $empresaModel->getDadosEmpresa();

        $ambiente = $empresa['nfe_ambiente'] ?? 'homologacao';
        $uf = $empresa['uf'] ?? 'AM';

        try {
            $nfeService = new \App\Libraries\NFe\NFeService($ambiente, $uf);
            $resultado = $nfeService->emitir([
                'chave_acesso' => $nota['chave_acesso'],
                'numero' => $nota['numero'],
                'serie' => $nota['serie'] ?? '1',
                'natureza_operacao' => $nota['natureza_operacao'] ?? 'Venda',
                'finalidade' => $nota['finalidade'] ?? '1',
                'tipo' => $nota['tipo'],
                'emissao' => $nota['emissao'],
                'cfop' => $nota['cfop'],
                'ambiente' => $ambiente,
                'empresa_cnpj' => $empresa['cnpj'] ?? '',
                'empresa_razao' => $empresa['razao_social'] ?? '',
                'empresa_fantasia' => $empresa['nome_fantasia'] ?? '',
                'empresa_logradouro' => $empresa['logradouro'] ?? '',
                'empresa_numero' => $empresa['numero'] ?? '',
                'empresa_bairro' => $empresa['bairro'] ?? '',
                'empresa_cMun' => $empresa['codigo_municipio'] ?? '',
                'empresa_cidade' => $empresa['cidade'] ?? '',
                'empresa_uf' => $empresa['uf'] ?? '',
                'empresa_cep' => $empresa['cep'] ?? '',
                'empresa_telefone' => $empresa['telefone'] ?? '',
                'empresa_ie' => $empresa['ie'] ?? '',
                'empresa_regime' => $empresa['regime_tributario'] ?? 'Lucro Presumido',
                'cliente_cnpj_cpf' => $nota['cnpj_cpf'],
                'cliente_nome' => $nota['cliente_fornecedor'],
                'cliente_endereco' => $nota['cliente_endereco'] ?? '',
                'cliente_uf' => $nota['cliente_uf'] ?? '',
                'cliente_ie' => $nota['cliente_ie'] ?? '',
                'itens_json' => $nota['itens_json'] ?? '[]',
                'base_calculo_icms' => $nota['base_calculo_icms'] ?? 0,
                'valor_icms' => $nota['valor_icms'] ?? 0,
                'base_calculo_pis' => $nota['base_calculo_pis'] ?? 0,
                'valor_pis' => $nota['valor_pis'] ?? 0,
                'base_calculo_cofins' => $nota['base_calculo_cofins'] ?? 0,
                'valor_cofins' => $nota['valor_cofins'] ?? 0,
                'valor_iss' => $nota['valor_iss'] ?? 0,
                'observacoes' => $nota['observacoes'] ?? '',
            ]);

            if ($resultado['success']) {
                $this->notaFiscalModel->atualizarStatus($id, 'Autorizada', $resultado['protocolo']);
                $this->notaFiscalModel->salvar([
                    'id' => $id,
                    'xml_file' => $resultado['xml_file'],
                    'protocolo' => $resultado['protocolo'],
                ]);
                $this->setFlashMessage('success', 'NF-e emitida com sucesso! Protocolo: ' . $resultado['protocolo']);
            } else {
                $this->notaFiscalModel->atualizarStatus($id, 'Rejeitada');
                $this->setFlashMessage('error', 'NF-e rejeitada: ' . ($resultado['motivo'] ?? $resultado['error']));
            }
        } catch (\Exception $e) {
            $this->notaFiscalModel->atualizarStatus($id, 'Erro');
            $this->setFlashMessage('error', 'Erro na emissão: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/notaFiscal/detalhe/' . $id);
        exit();
    }

    public function cancelarNfe(int $id)
    {
        $nota = $this->notaFiscalModel->getById($id);
        if (!$nota || $nota['status'] !== 'Autorizada') {
            $this->setFlashMessage('error', 'Nota não pode ser cancelada.');
            header('Location: ' . BASE_URL . '/fiscal/notas');
            exit();
        }

        $justificativa = $_POST['justificativa'] ?? '';
        if (strlen($justificativa) < 15) {
            $this->setFlashMessage('error', 'Justificativa deve ter no mínimo 15 caracteres.');
            header('Location: ' . BASE_URL . '/notaFiscal/detalhe/' . $id);
            exit();
        }

        $empresaModel = new \App\Models\EmpresaModel();
        $empresa = $empresaModel->getDadosEmpresa();
        $ambiente = $empresa['nfe_ambiente'] ?? 'homologacao';
        $uf = $empresa['uf'] ?? 'AM';

        try {
            $nfeService = new \App\Libraries\NFe\NFeService($ambiente, $uf);
            $resultado = $nfeService->cancelar($nota['chave_acesso'], $justificativa, $nota['protocolo']);

            if ($resultado['success']) {
                $this->notaFiscalModel->atualizarStatus($id, 'Cancelada');
                $this->notaFiscalModel->salvar(['id' => $id, 'justificativa_cancelamento' => $justificativa]);
                $this->setFlashMessage('success', 'NF-e cancelada com sucesso!');
            } else {
                $this->setFlashMessage('error', 'Erro no cancelamento: ' . ($resultado['error'] ?? 'Falha na SEFAZ'));
            }
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erro no cancelamento: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/notaFiscal/detalhe/' . $id);
        exit();
    }

    public function danfe(int $id)
    {
        $nota = $this->notaFiscalModel->getById($id);
        if (!$nota || empty($nota['xml_file'])) {
            $this->setFlashMessage('error', 'DANFE não disponível.');
            header('Location: ' . BASE_URL . '/fiscal/notas');
            exit();
        }

        $xmlPath = ROOT_PATH . '/storage/' . $nota['xml_file'];
        if (!file_exists($xmlPath)) {
            $this->setFlashMessage('error', 'Arquivo XML não encontrado.');
            header('Location: ' . BASE_URL . '/fiscal/notas');
            exit();
        }

        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="nfe_' . $nota['chave_acesso'] . '.xml"');
        readfile($xmlPath);
        exit();
    }
}