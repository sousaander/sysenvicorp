<?php

namespace App\Controllers;

use App\Models\NfseModel;
use App\Libraries\Nfse\NfseService;

class NfseController extends BaseController
{
    protected $requiredPermissions = [
        'index' => 'fiscal_notas_view',
        'form' => 'fiscal_notas_manage',
        'salvar' => 'fiscal_notas_manage',
        'detalhe' => 'fiscal_notas_view',
        'excluir' => 'fiscal_notas_manage',
        'emitir' => 'fiscal_notas_manage',
        'cancelar' => 'fiscal_notas_manage',
        'downloadXml' => 'fiscal_notas_view',
        'downloadPdf' => 'fiscal_notas_view',
    ];

    private NfseModel $nfseModel;

    public function __construct()
    {
        parent::__construct();
        $this->nfseModel = new NfseModel();
    }

    public function index()
    {
        $filtros = [];
        if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
        if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];

        $notas = $this->nfseModel->getAll($filtros);

        $this->renderView('nfse/list', [
            'pageTitle' => 'NFS-e - Notas Fiscais de Serviço',
            'notas' => $notas,
            'filtros' => $filtros,
        ]);
    }

    public function form(int $id = null)
    {
        $nota = null;
        if ($id) {
            $nota = $this->nfseModel->getById($id);
            if (!$nota) {
                $this->setFlashMessage('error', 'NFS-e não encontrada.');
                header('Location: ' . BASE_URL . '/nfse');
                exit();
            }
        }

        $clientesModel = new \App\Models\ClientesModel();
        $clientes = $clientesModel->getClientesSummary() ?? [];

        $empresaModel = new \App\Models\EmpresaModel();
        $empresa = $empresaModel->getDadosEmpresa();

        $proximoNumero = $this->nfseModel->getProximoNumero();

        $this->renderView('nfse/form', [
            'pageTitle' => $id ? 'Editar NFS-e' : 'Emitir Nova NFS-e',
            'nota' => $nota,
            'clientes' => $clientes,
            'empresa' => $empresa,
            'proximoNumero' => $proximoNumero,
        ]);
    }

    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/nfse');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'numero' => $_POST['numero'] ?? '',
            'serie' => $_POST['serie'] ?? '1',
            'rps_numero' => $_POST['rps_numero'] ?? $_POST['numero'] ?? '',
            'rps_serie' => $_POST['rps_serie'] ?? '1',
            'rps_tipo' => $_POST['rps_tipo'] ?? 'RPS',
            'tipo_documento' => $_POST['tipo_documento'] ?? 'NFS-e',
            'natureza_operacao' => $_POST['natureza_operacao'] ?? '',
            'regime_especial_tributacao' => $_POST['regime_especial_tributacao'] ?? 'nenhum',
            'optante_simples_nacional' => !empty($_POST['optante_simples_nacional']) ? 1 : 0,
            'incentivo_fiscal' => !empty($_POST['incentivo_fiscal']) ? 1 : 0,
            'servico_descricao' => $_POST['servico_descricao'] ?? '',
            'servico_codigo_tributacao' => $_POST['servico_codigo_tributacao'] ?? '',
            'servico_codigo_cnae' => $_POST['servico_codigo_cnae'] ?? '',
            'servico_aliquota_iss' => str_replace(',', '.', $_POST['servico_aliquota_iss'] ?? '0'),
            'servico_valor_iss' => str_replace(',', '.', $_POST['servico_valor_iss'] ?? '0'),
            'servico_base_calculo' => str_replace(',', '.', $_POST['servico_base_calculo'] ?? '0'),
            'servico_valor_liquido' => str_replace(',', '.', $_POST['servico_valor_liquido'] ?? '0'),
            'servico_valor_pis' => str_replace(',', '.', $_POST['servico_valor_pis'] ?? '0'),
            'servico_valor_cofins' => str_replace(',', '.', $_POST['servico_valor_cofins'] ?? '0'),
            'servico_valor_inss' => str_replace(',', '.', $_POST['servico_valor_inss'] ?? '0'),
            'servico_valor_ir' => str_replace(',', '.', $_POST['servico_valor_ir'] ?? '0'),
            'servico_valor_csll' => str_replace(',', '.', $_POST['servico_valor_csll'] ?? '0'),
            'servico_outras_retencoes' => str_replace(',', '.', $_POST['servico_outras_retencoes'] ?? '0'),
            'servico_desconto_condicionado' => str_replace(',', '.', $_POST['servico_desconto_condicionado'] ?? '0'),
            'servico_desconto_incondicionado' => str_replace(',', '.', $_POST['servico_desconto_incondicionado'] ?? '0'),
            'servico_valor_total' => str_replace(',', '.', $_POST['servico_valor_total'] ?? '0'),
            'valor_total' => str_replace(',', '.', $_POST['valor_total'] ?? '0'),
            'cliente_id' => filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT) ?: null,
            'cliente_nome' => $_POST['cliente_nome'] ?? '',
            'cliente_cpf_cnpj' => $_POST['cliente_cpf_cnpj'] ?? '',
            'cliente_ie' => $_POST['cliente_ie'] ?? '',
            'cliente_email' => $_POST['cliente_email'] ?? '',
            'cliente_endereco' => $_POST['cliente_endereco'] ?? '',
            'cliente_numero' => $_POST['cliente_numero'] ?? '',
            'cliente_complemento' => $_POST['cliente_complemento'] ?? '',
            'cliente_bairro' => $_POST['cliente_bairro'] ?? '',
            'cliente_codigo_municipio' => $_POST['cliente_codigo_municipio'] ?? '',
            'cliente_municipio' => $_POST['cliente_municipio'] ?? '',
            'cliente_uf' => $_POST['cliente_uf'] ?? '',
            'cliente_cep' => $_POST['cliente_cep'] ?? '',
            'cliente_telefone' => $_POST['cliente_telefone'] ?? '',
            'data_emissao' => $_POST['data_emissao'] ?? date('Y-m-d'),
            'data_competencia' => $_POST['data_competencia'] ?? null,
            'data_vencimento' => $_POST['data_vencimento'] ?? null,
            'status' => $_POST['status'] ?? 'Pendente',
            'observacoes' => $_POST['observacoes'] ?? '',
            'usuario_emissao' => $this->session->get('user_id'),
        ];

        $id = $this->nfseModel->salvar($dados);
        if ($id) {
            $this->setFlashMessage('success', 'NFS-e salva com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar NFS-e.');
        }
        header('Location: ' . BASE_URL . '/nfse');
        exit();
    }

    public function detalhe(int $id)
    {
        $nota = $this->nfseModel->getById($id);
        if (!$nota) {
            $this->setFlashMessage('error', 'NFS-e não encontrada.');
            header('Location: ' . BASE_URL . '/nfse');
            exit();
        }

        $this->renderView('nfse/detail', [
            'pageTitle' => 'NFS-e #' . $nota['numero'],
            'nota' => $nota,
        ]);
    }

    public function excluir(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/nfse');
            exit();
        }

        if ($this->nfseModel->excluir($id)) {
            $this->setFlashMessage('success', 'NFS-e excluída.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir NFS-e.');
        }
        header('Location: ' . BASE_URL . '/nfse');
        exit();
    }

    public function emitir(int $id)
    {
        $nota = $this->nfseModel->getById($id);
        if (!$nota) {
            $this->setFlashMessage('error', 'NFS-e não encontrada.');
            header('Location: ' . BASE_URL . '/nfse');
            exit();
        }

        $empresaModel = new \App\Models\EmpresaModel();
        $empresa = $empresaModel->getDadosEmpresa();

        $ambiente = $empresa['nfe_ambiente'] ?? 'homologacao';

        try {
            $nfseService = new \App\Libraries\Nfse\NfseService($ambiente, $empresa['codigo_municipio'] ?? '3550308');
            $resultado = $nfseService->emitir([
                'numero' => $nota['numero'],
                'serie' => $nota['serie'] ?? '1',
                'rps_numero' => $nota['rps_numero'] ?? $nota['numero'],
                'rps_tipo' => $nota['rps_tipo'] ?? 'RPS',
                'data_emissao' => $nota['data_emissao'],
                'natureza_operacao' => $nota['natureza_operacao'] ?? '1',
                'regime_especial_tributacao' => $nota['regime_especial_tributacao'] ?? 'nenhum',
                'optante_simples_nacional' => $nota['optante_simples_nacional'] ?? 1,
                'incentivo_fiscal' => $nota['incentivo_fiscal'] ?? 0,
                'iss_retido' => $nota['iss_retido'] ?? 0,
                'emitente_cnpj' => $empresa['cnpj'] ?? '',
                'emitente_razao' => $empresa['razao_social'] ?? '',
                'emitente_inscricao_municipal' => $empresa['inscricao_municipal'] ?? '',
                'servico_descricao' => $nota['servico_descricao'] ?? '',
                'servico_codigo_tributacao' => $nota['servico_codigo_tributacao'] ?? '',
                'servico_codigo_cnae' => $nota['servico_codigo_cnae'] ?? '',
                'servico_valor_total' => $nota['servico_valor_total'] ?? $nota['valor_total'] ?? 0,
                'servico_base_calculo' => $nota['servico_base_calculo'] ?? $nota['servico_valor_total'] ?? 0,
                'servico_aliquota_iss' => $nota['servico_aliquota_iss'] ?? 0,
                'servico_valor_iss' => $nota['servico_valor_iss'] ?? 0,
                'servico_valor_liquido' => $nota['servico_valor_liquido'] ?? $nota['valor_total'] ?? 0,
                'servico_valor_pis' => $nota['servico_valor_pis'] ?? 0,
                'servico_valor_cofins' => $nota['servico_valor_cofins'] ?? 0,
                'servico_valor_inss' => $nota['servico_valor_inss'] ?? 0,
                'servico_valor_ir' => $nota['servico_valor_ir'] ?? 0,
                'servico_valor_csll' => $nota['servico_valor_csll'] ?? 0,
                'servico_desconto_condicionado' => $nota['servico_desconto_condicionado'] ?? 0,
                'servico_desconto_incondicionado' => $nota['servico_desconto_incondicionado'] ?? 0,
                'cliente_nome' => $nota['cliente_nome'],
                'cliente_cpf_cnpj' => $nota['cliente_cpf_cnpj'],
                'cliente_endereco' => $nota['cliente_endereco'] ?? '',
                'cliente_numero' => $nota['cliente_numero'] ?? '',
                'cliente_complemento' => $nota['cliente_complemento'] ?? '',
                'cliente_bairro' => $nota['cliente_bairro'] ?? '',
                'cliente_codigo_municipio' => $nota['cliente_codigo_municipio'] ?? '',
                'cliente_uf' => $nota['cliente_uf'] ?? '',
                'cliente_cep' => $nota['cliente_cep'] ?? '',
                'cliente_email' => $nota['cliente_email'] ?? '',
            ]);

            if ($resultado['success']) {
                $this->nfseModel->atualizarStatus($id, 'Autorizada', $resultado['protocolo']);
                $this->nfseModel->salvar([
                    'id' => $id,
                    'xml_file' => $resultado['xml_file'],
                    'protocolo' => $resultado['protocolo'],
                    'codigo_verificacao' => $resultado['codigo_verificacao'] ?? null,
                    'link_download_pdf' => $resultado['link_pdf'] ?? null,
                    'link_download_xml' => $resultado['link_xml'] ?? null,
                    'numero_nfse' => $resultado['numero_nfse'] ?? null,
                ]);
                $this->setFlashMessage('success', 'NFS-e emitida com sucesso! Protocolo: ' . $resultado['protocolo']);
            } else {
                $this->nfseModel->atualizarStatus($id, 'Rejeitada');
                $this->setFlashMessage('error', 'NFS-e rejeitada: ' . ($resultado['message'] ?? 'Erro desconhecido'));
            }
        } catch (\Exception $e) {
            $this->nfseModel->atualizarStatus($id, 'Erro');
            $this->setFlashMessage('error', 'Erro na emissão: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/nfse/detalhe/' . $id);
        exit();
    }

    public function cancelar(int $id)
    {
        $nota = $this->nfseModel->getById($id);
        if (!$nota || $nota['status'] !== 'Autorizada') {
            $this->setFlashMessage('error', 'NFS-e não pode ser cancelada.');
            header('Location: ' . BASE_URL . '/nfse');
            exit();
        }

        $justificativa = $_POST['justificativa'] ?? '';
        if (strlen($justificativa) < 15) {
            $this->setFlashMessage('error', 'Justificativa deve ter no mínimo 15 caracteres.');
            header('Location: ' . BASE_URL . '/nfse/detalhe/' . $id);
            exit();
        }

        $empresaModel = new \App\Models\EmpresaModel();
        $empresa = $empresaModel->getDadosEmpresa();
        $ambiente = $empresa['nfe_ambiente'] ?? 'homologacao';

        try {
            $nfseService = new \App\Libraries\Nfse\NfseService($ambiente, $empresa['codigo_municipio'] ?? '3550308');
            $resultado = $nfseService->cancelar($nota, $justificativa);

            if ($resultado['success']) {
                $this->nfseModel->atualizarStatus($id, 'Cancelada');
                $this->nfseModel->salvar(['id' => $id, 'justificativa_cancelamento' => $justificativa]);
                $this->setFlashMessage('success', 'NFS-e cancelada com sucesso!');
            } else {
                $this->setFlashMessage('error', 'Erro no cancelamento: ' . ($resultado['message'] ?? 'Falha na prefeitura'));
            }
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erro no cancelamento: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/nfse/detalhe/' . $id);
        exit();
    }

    public function downloadXml(int $id)
    {
        $nota = $this->nfseModel->getById($id);
        if (!$nota || empty($nota['xml_file'])) {
            $this->setFlashMessage('error', 'XML não disponível.');
            header('Location: ' . BASE_URL . '/nfse');
            exit();
        }

        $xmlPath = ROOT_PATH . '/storage/' . $nota['xml_file'];
        if (!file_exists($xmlPath)) {
            $xmlPath = ROOT_PATH . '/storage/nfse/xml/' . $nota['xml_file'];
        }
        if (!file_exists($xmlPath)) {
            $this->setFlashMessage('error', 'Arquivo XML não encontrado.');
            header('Location: ' . BASE_URL . '/nfse');
            exit();
        }

        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="nfse_' . $nota['numero'] . '.xml"');
        readfile($xmlPath);
        exit();
    }

    public function downloadPdf(int $id)
    {
        $nota = $this->nfseModel->getById($id);
        if (!$nota || empty($nota['pdf_file'])) {
            $this->setFlashMessage('error', 'PDF não disponível.');
            header('Location: ' . BASE_URL . '/nfse');
            exit();
        }

        $pdfPath = ROOT_PATH . '/storage/' . $nota['pdf_file'];
        if (!file_exists($pdfPath)) {
            $this->setFlashMessage('error', 'Arquivo PDF não encontrado.');
            header('Location: ' . BASE_URL . '/nfse');
            exit();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="nfse_' . $nota['numero'] . '.pdf"');
        readfile($pdfPath);
        exit();
    }
}
