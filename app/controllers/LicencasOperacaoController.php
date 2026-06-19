<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\LicencasOperacaoModel;

class LicencasOperacaoController extends BaseController
{
    private $model;

    /**
     * Mapeia ações para as permissões necessárias.
     * O BaseController usará este mapa para verificar o acesso.
     * @var array
     */
    protected $requiredPermissions = [
        'index' => 'licencas_operacao_view',
        'detalheLicenca' => 'licencas_operacao_view',
        'novo' => 'licencas_operacao_manage',
        'salvar' => 'licencas_operacao_manage',
        'editar' => 'licencas_operacao_manage',
        'excluir' => 'licencas_operacao_manage',
        'uploadDocumento' => 'licencas_operacao_manage',
        'relatorioNaoConformidade' => 'licencas_operacao_nc_view',
        'salvarOcorrencia' => 'licencas_operacao_nc_manage',
        'atualizarOcorrencia' => 'licencas_operacao_nc_manage',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->model = new LicencasOperacaoModel();
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getLicensesSummary();
        $criticalList = $this->model->getCriticalLicensesList();
        // Busca todas as licenças para o modal de upload (assumindo que o método existe no model, padrão do sistema)
        $todasLicencas = method_exists($this->model, 'getAllLicencas') ? $this->model->getAllLicencas() : $criticalList;

        $data = array_merge([
            'pageTitle' => 'Licenças de Operação - Conformidade',
            'criticalList' => $criticalList,
            'todasLicencas' => $todasLicencas,
        ], $summary);

        $this->renderView('licencasOperacao/index', $data);
    }

    /**
     * Exibe os detalhes de uma licença.
     * @param mixed $id
     */
    public function detalheLicenca($id = null)
    {
        // Tenta pegar o ID do parâmetro da rota ou via GET (fallback)
        $id = $id ?: filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $id = (int)$id;

        $licenca = $id ? $this->model->getLicenseById($id) : null;
        
        // Busca as últimas 5 ocorrências registradas para esta licença
        $ocorrencias = $id ? $this->model->getOcorrenciasByLicencaId($id, 5) : [];

        // Busca o nome do projeto se houver vínculo
        if ($licenca && !empty($licenca['projeto_id'])) {
            $projetosModel = new \App\Models\ProjetosModel();
            $projeto = $projetosModel->getProjetoById((int)$licenca['projeto_id']);
            $licenca['projeto_nome'] = $projeto['nome'] ?? 'Projeto não encontrado';
        }

        if (!$licenca) {
            $this->setFlashMessage('error', 'Licença não encontrada.');
            header('Location: ' . BASE_URL . '/licencasOperacao');
            exit();
        }
        
        // --- DEBUG: Verifique se os dados estão sendo passados corretamente para a view ---
        // var_dump($licenca, $ocorrencias); exit; 

        $data = [
            'pageTitle' => 'Licenças - Detalhes da Licença',
            'licenca' => $licenca,
            'ocorrencias' => $ocorrencias
        ];
        $this->renderView('licencasOperacao/detalhe', $data);
    }

    /**
     * Exibe o formulário para cadastrar uma nova licença.
     */
    public function novo()
    {
        $data = ['pageTitle' => 'Nova Licença de Operação', 'licenca' => null];
        $this->renderView('licencasOperacao/form', $data);
    }

    /**
     * Exibe o formulário para editar uma licença existente.
     * @param mixed $id
     */
    public function editar($id)
    {
        $id = (int)$id;
        // Alterado de getLicencaById para getLicenseById para manter consistência 
        // com getLicensesSummary() e getCriticalLicensesList() usados no index.
        $licenca = $this->model->getLicenseById($id);
        if (!$licenca) {
            $this->setFlashMessage('error', 'Licença não encontrada.');
            header('Location: ' . BASE_URL . '/licencasOperacao');
            exit();
        }
        $data = ['pageTitle' => 'Editar Licença', 'licenca' => $licenca];
        $this->renderView('licencasOperacao/form', $data);
    }

    /**
     * Processa o salvamento (criação ou atualização) da licença.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/licencasOperacao');
            exit();
        }

        // Validação de segurança CSRF
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/licencasOperacao');
            exit();
        }

        // Coleta todos os campos do formulário
        $dados = $_POST;
        
        // Tratamento de tipos específicos
        if (isset($dados['id'])) $dados['id'] = (int)$dados['id'] ?: null;
        if (isset($dados['projeto_id'])) $dados['projeto_id'] = (int)$dados['projeto_id'] ?: null;
        if (isset($dados['quantidade_licencas'])) $dados['quantidade_licencas'] = (int)$dados['quantidade_licencas'];
        if (isset($dados['licencas_em_uso'])) $dados['licencas_em_uso'] = (int)$dados['licencas_em_uso'];
        
        // Tratamento de valores monetários
        if (isset($dados['valor_licenca'])) {
            $dados['valor_licenca'] = (float)str_replace(['.', ','], ['', '.'], $dados['valor_licenca']);
        }

        // Tratamento de Checkboxes (booleanos)
        $checkboxes = [
            'alerta_90_dias', 'alerta_30_dias', 'alerta_7_dias', 'alerta_no_dia',
            'auditoria_ativa', 'requer_aprovacao', 'licenca_regulatoria', 'inclui_sla'
        ];
        foreach ($checkboxes as $cb) {
            $dados[$cb] = isset($_POST[$cb]) ? 1 : 0;
        }

        // Remove campos que não existem na tabela do banco de dados
        unset($dados['draft'], $dados['csrf_token']); 

        if ($this->model->salvarLicenca($dados)) {
            $this->setFlashMessage('success', 'Licença salva com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar licença.');
        }

        header('Location: ' . BASE_URL . '/licencasOperacao');
        exit();
    }

    public function excluir($id)
    {
        $id = (int)$id;
        if ($this->model->excluirLicenca($id)) {
            $this->setFlashMessage('success', 'Licença excluída com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir licença.');
        }
        header('Location: ' . BASE_URL . '/licencasOperacao');
        exit();
    }

    /**
     * Processa o upload de documentos para uma licença.
     */
    public function uploadDocumento()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/licencasOperacao');
            exit();
        }

        $id = filter_input(INPUT_POST, 'licenca_id', FILTER_VALIDATE_INT);

        if (!$id) {
            $this->setFlashMessage('error', 'Licença não selecionada.');
            header('Location: ' . BASE_URL . '/licencasOperacao');
            exit();
        }

        if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/storage/licencas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $ext = strtolower(pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION));
            $newFilename = 'licenca_' . $id . '_' . time() . '.' . $ext;

            if (move_uploaded_file($_FILES['documento']['tmp_name'], $uploadDir . $newFilename)) {
                // Aqui chamaria o model para atualizar o registro no banco
                // $this->model->atualizarDocumento($id, $newFilename);
                $this->setFlashMessage('success', 'Documento enviado com sucesso!');
            } else {
                $this->setFlashMessage('error', 'Falha ao mover o arquivo.');
            }
        } else {
            $this->setFlashMessage('error', 'Nenhum arquivo enviado ou erro no upload.');
        }

        header('Location: ' . BASE_URL . '/licencasOperacao');
        exit();
    }

    /**
     * Exibe o relatório de não conformidades/ocorrências das licenças.
     */
    public function relatorioNaoConformidade()
    {
        $ocorrencias = $this->model->getRelatorioNaoConformidades();
        $data = [
            'pageTitle' => 'Relatório de Não Conformidades',
            'ocorrencias' => $ocorrencias
        ];
        $this->renderView('licencasOperacao/relatorio_nao_conformidade', $data);
    }

    /**
     * Salva uma nova ocorrência (Não Conformidade, Observação ou Melhoria).
     */
    public function salvarOcorrencia()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->salvarOcorrencia($_POST)) {
                $this->setFlashMessage('success', 'Ocorrência registrada com sucesso!');
            } else {
                $this->setFlashMessage('error', 'Falha ao registrar a ocorrência.');
            }
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/licencasOperacao'));
        exit();
    }

    /**
     * Atualiza uma ocorrência existente (Atividades e Status).
     */
    public function atualizarOcorrencia()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->atualizarOcorrencia($_POST)) {
                $this->setFlashMessage('success', 'Tratativa de ocorrência atualizada!');
            } else {
                $this->setFlashMessage('error', 'Falha ao atualizar a tratativa.');
            }
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/licencasOperacao/relatorioNaoConformidade'));
        exit();
    }
}
