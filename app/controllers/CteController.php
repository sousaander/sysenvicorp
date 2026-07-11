<?php

namespace App\Controllers;

use App\Models\CteModel;
use App\Libraries\Cte\CteService;

class CteController extends BaseController
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
        'downloadDacte' => 'fiscal_notas_view',
    ];

    private CteModel $cteModel;

    public function __construct()
    {
        parent::__construct();
        $this->cteModel = new CteModel();
    }

    public function index()
    {
        $filtros = [];
        if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
        if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];

        $ctes = $this->cteModel->getAll($filtros);

        $this->renderView('cte/list', [
            'pageTitle' => 'CT-e - Conhecimentos de Transporte',
            'ctes' => $ctes,
            'filtros' => $filtros,
        ]);
    }

    public function form(int $id = null)
    {
        $cte = null;
        if ($id) {
            $cte = $this->cteModel->getById($id);
            if (!$cte) {
                $this->setFlashMessage('error', 'CT-e não encontrado.');
                header('Location: ' . BASE_URL . '/cte');
                exit();
            }
        }

        $clientesModel = new \App\Models\ClientesModel();
        $clientes = $clientesModel->getClientesSummary() ?? [];

        $empresaModel = new \App\Models\EmpresaModel();
        $empresa = $empresaModel->getDadosEmpresa();

        $proximoNumero = $this->cteModel->getProximoNumero();

        $this->renderView('cte/form', [
            'pageTitle' => $id ? 'Editar CT-e' : 'Emitir Novo CT-e',
            'cte' => $cte,
            'clientes' => $clientes,
            'empresa' => $empresa,
            'proximoNumero' => $proximoNumero,
        ]);
    }

    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cte');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'numero' => $_POST['numero'] ?? '',
            'serie' => $_POST['serie'] ?? '1',
            'cfop' => $_POST['cfop'] ?? '',
            'natureza_operacao' => $_POST['natureza_operacao'] ?? '',
            'tipo_servico' => $_POST['tipo_servico'] ?? 'normal',
            'forma_pagamento' => $_POST['forma_pagamento'] ?? 'pagamento_contra entrega',
            'modal' => $_POST['modal'] ?? 'rodoviario',
            'tipo_cte' => $_POST['tipo_cte'] ?? 'normal',
            'tomador_id' => filter_input(INPUT_POST, 'tomador_id', FILTER_VALIDATE_INT) ?: null,
            'tomador_nome' => $_POST['tomador_nome'] ?? '',
            'tomador_cpf_cnpj' => $_POST['tomador_cpf_cnpj'] ?? '',
            'tomador_ie' => $_POST['tomador_ie'] ?? '',
            'tomador_email' => $_POST['tomador_email'] ?? '',
            'tomador_endereco' => $_POST['tomador_endereco'] ?? '',
            'tomador_municipio' => $_POST['tomador_municipio'] ?? '',
            'tomador_uf' => $_POST['tomador_uf'] ?? '',
            'tomador_cep' => $_POST['tomador_cep'] ?? '',
            'remetente_id' => filter_input(INPUT_POST, 'remetente_id', FILTER_VALIDATE_INT) ?: null,
            'remetente_nome' => $_POST['remetente_nome'] ?? '',
            'remetente_cpf_cnpj' => $_POST['remetente_cpf_cnpj'] ?? '',
            'remetente_endereco' => $_POST['remetente_endereco'] ?? '',
            'remetente_municipio' => $_POST['remetente_municipio'] ?? '',
            'remetente_uf' => $_POST['remetente_uf'] ?? '',
            'destinatario_id' => filter_input(INPUT_POST, 'destinatario_id', FILTER_VALIDATE_INT) ?: null,
            'destinatario_nome' => $_POST['destinatario_nome'] ?? '',
            'destinatario_cpf_cnpj' => $_POST['destinatario_cpf_cnpj'] ?? '',
            'destinatario_endereco' => $_POST['destinatario_endereco'] ?? '',
            'destinatario_municipio' => $_POST['destinatario_municipio'] ?? '',
            'destinatario_uf' => $_POST['destinatario_uf'] ?? '',
            'expedidor_nome' => $_POST['expedidor_nome'] ?? '',
            'recebedor_nome' => $_POST['recebedor_nome'] ?? '',
            'valor_mercadorias' => str_replace(',', '.', $_POST['valor_mercadorias'] ?? '0'),
            'valor_frete' => str_replace(',', '.', $_POST['valor_frete'] ?? '0'),
            'valor_recebido' => str_replace(',', '.', $_POST['valor_recebido'] ?? '0'),
            'valor_total' => str_replace(',', '.', $_POST['valor_total'] ?? '0'),
            'base_calculo_icms' => str_replace(',', '.', $_POST['base_calculo_icms'] ?? '0'),
            'valor_icms' => str_replace(',', '.', $_POST['valor_icms'] ?? '0'),
            'aliquota_icms' => str_replace(',', '.', $_POST['aliquota_icms'] ?? '0'),
            'base_calculo_pis' => str_replace(',', '.', $_POST['base_calculo_pis'] ?? '0'),
            'valor_pis' => str_replace(',', '.', $_POST['valor_pis'] ?? '0'),
            'base_calculo_cofins' => str_replace(',', '.', $_POST['base_calculo_cofins'] ?? '0'),
            'valor_cofins' => str_replace(',', '.', $_POST['valor_cofins'] ?? '0'),
            'perc_red_base_calc_icms' => str_replace(',', '.', $_POST['perc_red_base_calc_icms'] ?? '0'),
            'data_emissao' => $_POST['data_emissao'] ?? date('Y-m-d'),
            'data_prevista_entrega' => $_POST['data_prevista_entrega'] ?? null,
            'status' => $_POST['status'] ?? 'Pendente',
            'observacoes' => $_POST['observacoes'] ?? '',
            'usuario_emissao' => $this->session->get('user_id'),
        ];

        $id = $this->cteModel->salvar($dados);
        if ($id) {
            $this->setFlashMessage('success', 'CT-e salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar CT-e.');
        }
        header('Location: ' . BASE_URL . '/cte');
        exit();
    }

    public function detalhe(int $id)
    {
        $cte = $this->cteModel->getById($id);
        if (!$cte) {
            $this->setFlashMessage('error', 'CT-e não encontrado.');
            header('Location: ' . BASE_URL . '/cte');
            exit();
        }

        $notasFiscais = $this->cteModel->getNotasFiscais($id);

        $this->renderView('cte/detail', [
            'pageTitle' => 'CT-e #' . $cte['numero'],
            'cte' => $cte,
            'notasFiscais' => $notasFiscais,
        ]);
    }

    public function excluir(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cte');
            exit();
        }

        if ($this->cteModel->excluir($id)) {
            $this->setFlashMessage('success', 'CT-e excluído.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir CT-e.');
        }
        header('Location: ' . BASE_URL . '/cte');
        exit();
    }

    public function emitir(int $id)
    {
        $cte = $this->cteModel->getById($id);
        if (!$cte) {
            $this->setFlashMessage('error', 'CT-e não encontrado.');
            header('Location: ' . BASE_URL . '/cte');
            exit();
        }

        $empresaModel = new \App\Models\EmpresaModel();
        $empresa = $empresaModel->getDadosEmpresa();

        $ambiente = $empresa['nfe_ambiente'] ?? 'homologacao';
        $uf = $empresa['uf'] ?? 'AM';

        try {
            $cteService = new \App\Libraries\Cte\CteService($ambiente, $uf);
            $resultado = $cteService->emitir([
                'chave_acesso' => $cte['chave_acesso'] ?? '',
                'numero' => $cte['numero'],
                'serie' => $cte['serie'] ?? '1',
                'cfop' => $cte['cfop'] ?? '5353',
                'natureza_operacao' => $cte['natureza_operacao'] ?? 'Prestacao de servico de transporte',
                'data_emissao' => $cte['data_emissao'],
                'tipo_cte' => $cte['tipo_cte'] ?? 'normal',
                'tipo_servico' => $cte['tipo_servico'] ?? 'normal',
                'emitente_cnpj' => $empresa['cnpj'] ?? '',
                'emitente_nome' => $empresa['razao_social'] ?? '',
                'emitente_fantasia' => $empresa['nome_fantasia'] ?? '',
                'emitente_endereco' => $empresa['logradouro'] ?? '',
                'emitente_numero' => $empresa['numero'] ?? '',
                'emitente_bairro' => $empresa['bairro'] ?? '',
                'emitente_municipio' => $empresa['cidade'] ?? '',
                'emitente_uf' => $empresa['uf'] ?? '',
                'emitente_cep' => $empresa['cep'] ?? '',
                'emitente_telefone' => $empresa['telefone'] ?? '',
                'emitente_ie' => $empresa['ie'] ?? '',
                'tomador_cpf_cnpj' => $cte['tomador_cpf_cnpj'] ?? '',
                'tomador_nome' => $cte['tomador_nome'],
                'tomador_endereco' => $cte['tomador_endereco'] ?? '',
                'tomador_municipio' => $cte['tomador_municipio'] ?? '',
                'tomador_uf' => $cte['tomador_uf'] ?? '',
                'tomador_cep' => $cte['tomador_cep'] ?? '',
                'tomador_tipo' => '3',
                'remetente_cpf_cnpj' => $cte['remetente_cpf_cnpj'] ?? '',
                'remetente_nome' => $cte['remetente_nome'] ?? '',
                'remetente_endereco' => $cte['remetente_endereco'] ?? '',
                'remetente_municipio' => $cte['remetente_municipio'] ?? '',
                'remetente_uf' => $cte['remetente_uf'] ?? '',
                'destinatario_cpf_cnpj' => $cte['destinatario_cpf_cnpj'] ?? '',
                'destinatario_nome' => $cte['destinatario_nome'] ?? '',
                'destinatario_endereco' => $cte['destinatario_endereco'] ?? '',
                'destinatario_municipio' => $cte['destinatario_municipio'] ?? '',
                'destinatario_uf' => $cte['destinatario_uf'] ?? '',
                'valor_mercadorias' => $cte['valor_mercadorias'] ?? 0,
                'valor_frete' => $cte['valor_frete'] ?? 0,
                'valor_recebido' => $cte['valor_recebido'] ?? 0,
                'base_calculo_icms' => $cte['base_calculo_icms'] ?? 0,
                'aliquota_icms' => $cte['aliquota_icms'] ?? 0,
                'valor_icms' => $cte['valor_icms'] ?? 0,
                'perc_red_base_calc_icms' => $cte['perc_red_base_calc_icms'] ?? 0,
                'produto_predominante' => $cte['produto_predominante'] ?? 'Mercadorias diversas',
            ]);

            if ($resultado['success']) {
                $this->cteModel->atualizarStatus($id, 'Autorizada', $resultado['protocolo']);
                $this->cteModel->salvar([
                    'id' => $id,
                    'xml_file' => $resultado['xml_file'],
                    'protocolo' => $resultado['protocolo'],
                    'chave_acesso' => $resultado['chave_acesso'] ?? '',
                ]);
                $this->setFlashMessage('success', 'CT-e emitido com sucesso! Protocolo: ' . $resultado['protocolo']);
            } else {
                $this->cteModel->atualizarStatus($id, 'Rejeitada');
                $this->setFlashMessage('error', 'CT-e rejeitado: ' . ($resultado['motivo'] ?? $resultado['message'] ?? 'Erro desconhecido'));
            }
        } catch (\Exception $e) {
            $this->cteModel->atualizarStatus($id, 'Erro');
            $this->setFlashMessage('error', 'Erro na emissão: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/cte/detalhe/' . $id);
        exit();
    }

    public function cancelar(int $id)
    {
        $cte = $this->cteModel->getById($id);
        if (!$cte || $cte['status'] !== 'Autorizada') {
            $this->setFlashMessage('error', 'CT-e não pode ser cancelado.');
            header('Location: ' . BASE_URL . '/cte');
            exit();
        }

        $justificativa = $_POST['justificativa'] ?? '';
        if (strlen($justificativa) < 15) {
            $this->setFlashMessage('error', 'Justificativa deve ter no mínimo 15 caracteres.');
            header('Location: ' . BASE_URL . '/cte/detalhe/' . $id);
            exit();
        }

        $empresaModel = new \App\Models\EmpresaModel();
        $empresa = $empresaModel->getDadosEmpresa();
        $ambiente = $empresa['nfe_ambiente'] ?? 'homologacao';
        $uf = $empresa['uf'] ?? 'AM';

        try {
            $cteService = new \App\Libraries\Cte\CteService($ambiente, $uf);
            $resultado = $cteService->cancelar($cte, $justificativa);

            if ($resultado['success']) {
                $this->cteModel->atualizarStatus($id, 'Cancelada');
                $this->cteModel->salvar(['id' => $id, 'justificativa_cancelamento' => $justificativa]);
                $this->setFlashMessage('success', 'CT-e cancelado com sucesso!');
            } else {
                $this->setFlashMessage('error', 'Erro no cancelamento: ' . ($resultado['message'] ?? 'Falha na SEFAZ'));
            }
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erro no cancelamento: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/cte/detalhe/' . $id);
        exit();
    }

    public function downloadXml(int $id)
    {
        $cte = $this->cteModel->getById($id);
        if (!$cte || empty($cte['xml_file'])) {
            $this->setFlashMessage('error', 'XML não disponível.');
            header('Location: ' . BASE_URL . '/cte');
            exit();
        }

        $xmlPath = ROOT_PATH . '/storage/' . $cte['xml_file'];
        if (!file_exists($xmlPath)) {
            $xmlPath = ROOT_PATH . '/storage/cte/xml/' . $cte['xml_file'];
        }
        if (!file_exists($xmlPath)) {
            $this->setFlashMessage('error', 'Arquivo XML não encontrado.');
            header('Location: ' . BASE_URL . '/cte');
            exit();
        }

        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="cte_' . $cte['chave_acesso'] . '.xml"');
        readfile($xmlPath);
        exit();
    }

    public function downloadDacte(int $id)
    {
        $cte = $this->cteModel->getById($id);
        if (!$cte || empty($cte['dacte_file'])) {
            $this->setFlashMessage('error', 'DACTE não disponível.');
            header('Location: ' . BASE_URL . '/cte');
            exit();
        }

        $pdfPath = ROOT_PATH . '/storage/' . $cte['dacte_file'];
        if (!file_exists($pdfPath)) {
            $this->setFlashMessage('error', 'Arquivo DACTE não encontrado.');
            header('Location: ' . BASE_URL . '/cte');
            exit();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="dacte_' . $cte['chave_acesso'] . '.pdf"');
        readfile($pdfPath);
        exit();
    }
}
