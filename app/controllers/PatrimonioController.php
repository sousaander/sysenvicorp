<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\PatrimonioModel;
use App\Models\EmpresaModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class PatrimonioController extends BaseController
{
    private $model;

    /**
     * Mapeia ações para as permissões necessárias.
     * @var array
     */
    protected $requiredPermissions = [
        'index' => 'patrimonio_view',
        'cadastro' => 'patrimonio_create',
        'editar' => 'patrimonio_edit',
        'salvar' => 'patrimonio_create',
        'getBemJson' => 'patrimonio_view',
        'excluir' => 'patrimonio_delete',
        'movimentacoes' => 'patrimonio_movimentacoes_manage',
        'salvarMovimentacao' => 'patrimonio_movimentacoes_manage',
        'depreciacao' => 'patrimonio_view',
        'salvarReavaliacao' => 'patrimonio_edit',
        'inventario' => 'patrimonio_inventario_run',
        'conciliarInventario' => 'patrimonio_inventario_run',
        'relatorios' => 'patrimonio_view',
        'etiqueta' => 'patrimonio_view',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->model = new PatrimonioModel();
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getAssetsSummary();

        // Filtros
        $filtros = [
            'busca' => filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS),
            'tipo'  => filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS)
        ];

        // Lógica de Paginação para bens recentes
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $recentes = $this->model->getBens($filtros, $itensPorPagina, $offset);
        $totalBens = $this->model->getBensCount($filtros);
        $totalPaginas = ceil($totalBens / $itensPorPagina);

        $data = array_merge([
            'pageTitle' => 'Patrimônio - Dashboard',
            'bensRecentes' => $recentes,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtros' => $filtros
        ], $summary);

        $this->renderView('patrimonio/index', $data);
    }

    /**
     * Exibe o formulário para cadastro de um novo bem.
     */
    public function cadastro()
    {
        // No futuro, podemos buscar dados como 'tipos', 'locais', 'responsaveis' do model
        $data = [
            'pageTitle' => 'Cadastro de Bem Patrimonial',
            'bem' => null, // Para um formulário de criação, não há dados de um bem existente
        ];
        $this->renderView('patrimonio/form', $data);
    }

    /**
     * Exibe o formulário para edição de um bem existente.
     * @param int $id O ID do bem.
     */
    public function editar(int $id)
    {
        $bem = $this->model->getBemById($id);
        if (!$bem) {
            $this->session->setFlash('Bem patrimonial não encontrado.', 'error');
            header('Location: ' . BASE_URL . '/patrimonio');
            exit();
        }

        $data = [
            'pageTitle' => 'Editar Bem Patrimonial',
            'bem' => $bem,
        ];
        $this->renderView('patrimonio/form', $data);
    }

    /**
     * Salva um bem novo ou atualiza um existente.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validação de CSRF
            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                $this->session->setFlash('Erro de validação de segurança (CSRF). Por favor, tente novamente.', 'error');
                header('Location: ' . BASE_URL . '/patrimonio');
                exit();
            }

            $dados = $_POST;

            try {
                $success = $this->model->salvarBem($dados);
                if ($success) {
                    $this->session->setFlash('Bem salvo com sucesso!', 'success');
                } else {
                    $errorMessage = $this->model->getLastError();
                    $this->session->setFlash('Erro ao salvar o bem: ' . ($errorMessage ?: 'Verifique os dados e tente novamente.'), 'error');
                }
            } catch (\Exception $e) {
                $this->session->setFlash('Ocorreu um erro inesperado: ' . $e->getMessage(), 'error');
            }
            header('Location: ' . BASE_URL . '/patrimonio'); // Redireciona para o dashboard de patrimônio
            exit();
        }
    }

    /**
     * Busca os dados de um bem em formato JSON para edição no modal.
     * @param int $id O ID do bem.
     */
    public function getBemJson(int $id)
    {
        header('Content-Type: application/json');
        $bem = $this->model->getBemById($id);
        if ($bem) {
            echo json_encode(['success' => true, 'data' => $bem]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Bem não encontrado.']);
        }
        exit();
    }

    /**
     * Exclui um bem do banco de dados.
     */
    public function excluir()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // DEBUG: Registra todos os dados POST recebidos
            error_log("PatrimonioController::excluir - Dados POST: " . print_r($_POST, true));

            // Validação de CSRF
            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                $this->session->setFlash('Erro de validação de segurança (CSRF). Por favor, tente novamente.', 'error');
                header('Location: ' . BASE_URL . '/patrimonio');
                exit();
            }

            // Obtém o ID e valida como inteiro. filter_var é mais confiável que filter_input em certos ambientes.
            $idRaw = $_POST['id'] ?? null;
            $id = filter_var($idRaw, FILTER_VALIDATE_INT);

            // DEBUG: Registra o valor do ID após a filtragem
            error_log("PatrimonioController::excluir - ID processado: " . var_export($id, true));

            if ($id !== false && $id !== null) {
                if ($this->model->excluirBem($id)) {
                    $this->session->setFlash('Bem excluído com sucesso!', 'success');
                } else {
                    $this->session->setFlash('Erro ao excluir o bem no banco de dados. ID: ' . $id, 'error');
                }
            } else {
                $receivedIdRaw = $_POST['id'] ?? 'vazio';
                $this->session->setFlash('ID inválido para exclusão. Valor recebido: ' . htmlspecialchars((string)$receivedIdRaw), 'error');
            }
        }
        header('Location: ' . BASE_URL . '/patrimonio');
        exit();
    }

    /**
     * Exibe a página de controle de movimentações de bens.
     */
    public function movimentacoes()
    {
        // Busca a lista de bens para o formulário e o histórico de movimentações
        $bens = $this->model->getAllBens();
        $movimentacoes = $this->model->getMovimentacoes();

        $data = [
            'pageTitle' => 'Controle de Movimentações de Bens',
            'bens' => $bens,
            'movimentacoes' => $movimentacoes,
        ];
        $this->renderView('patrimonio/movimentacoes', $data);
    }

    /**
     * Salva uma nova movimentação de bem.
     */
    public function salvarMovimentacao()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validação de CSRF
            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                $this->session->setFlash('Erro de validação de segurança (CSRF). Por favor, tente novamente.', 'error');
                header('Location: ' . BASE_URL . '/patrimonio/movimentacoes');
                exit();
            }

            $dados = $_POST;

            // Validação básica
            if (empty($dados['bem_id']) || empty($dados['tipo_movimentacao']) || empty($dados['data_movimentacao'])) {
                $this->session->setFlash('Dados inválidos. Preencha todos os campos obrigatórios.', 'error');
                header('Location: ' . BASE_URL . '/patrimonio/movimentacoes');
                exit();
            }

            try {
                $success = $this->model->salvarMovimentacao($dados);
                if ($success) {
                    $this->session->setFlash('Movimentação registrada com sucesso!', 'success');
                } else {
                    $this->session->setFlash('Erro ao registrar a movimentação.', 'error');
                }
            } catch (\Exception $e) {
                $this->session->setFlash('Ocorreu um erro inesperado: ' . $e->getMessage(), 'error');
            }

            header('Location: ' . BASE_URL . '/patrimonio/movimentacoes');
            exit();
        }
    }

    /**
     * Exibe a página de Depreciação e Reavaliação de bens.
     */
    public function depreciacao()
    {
        // Busca a lista de bens depreciáveis com seus cálculos
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 3;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $bensDepreciaveis = $this->model->getBensComDepreciacao($itensPorPagina, $offset);
        $totalBens = $this->model->getBensCount();
        $totalPaginas = ceil($totalBens / $itensPorPagina);

        $data = [
            'pageTitle' => 'Depreciação e Reavaliação de Ativos',
            'totalPaginas' => $totalPaginas,
            'bens' => $bensDepreciaveis,
        ];
        $this->renderView('patrimonio/depreciacao', $data);
    }

    /**
     * Salva uma nova reavaliação de valor de mercado para um bem.
     */
    public function salvarReavaliacao()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validação de CSRF
            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                $this->session->setFlash('Erro de validação de segurança (CSRF). Por favor, tente novamente.', 'error');
                header('Location: ' . BASE_URL . '/patrimonio/depreciacao');
                exit();
            }

            $dados = $_POST;

            // Validação básica
            if (empty($dados['bem_id']) || empty($dados['novo_valor']) || empty($dados['data_reavaliacao'])) {
                $this->session->setFlash('Dados inválidos. Preencha todos os campos obrigatórios.', 'error');
                header('Location: ' . BASE_URL . '/patrimonio/depreciacao');
                exit();
            }

            try {
                $success = $this->model->salvarReavaliacao($dados);
                if ($success) {
                    $this->session->setFlash('Reavaliação registrada com sucesso!', 'success');
                } else {
                    $this->session->setFlash('Erro ao registrar a reavaliação.', 'error');
                }
            } catch (\Exception $e) {
                $this->session->setFlash('Ocorreu um erro inesperado: ' . $e->getMessage(), 'error');
            }

            header('Location: ' . BASE_URL . '/patrimonio/depreciacao');
            exit();
        }
    }

    /**
     * Exibe a página de Inventário Patrimonial.
     */
    public function inventario()
    {
        // Busca todos os bens ativos para a lista de checagem
        $bensParaInventario = $this->model->getBensParaInventario();
        // Busca o resultado do último inventário (se houver na sessão)
        $relatorioDivergencias = $this->session->get('relatorio_divergencias');
        $this->session->set('relatorio_divergencias', null); // Limpa após exibir

        $data = [
            'pageTitle' => 'Inventário Patrimonial',
            'bens' => $bensParaInventario,
            'relatorioDivergencias' => $relatorioDivergencias,
        ];
        $this->renderView('patrimonio/inventario', $data);
    }

    /**
     * Processa a conciliação do inventário físico.
     */
    public function conciliarInventario()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validação de CSRF
            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                $this->session->setFlash('Erro de validação de segurança (CSRF). Por favor, tente novamente.', 'error');
                header('Location: ' . BASE_URL . '/patrimonio/inventario');
                exit();
            }

            $dadosInventario = $_POST['inventario'] ?? [];

            try {
                $relatorio = $this->model->conciliarInventario($dadosInventario);

                // Salva o relatório na sessão para ser exibido na página de inventário
                $this->session->set('relatorio_divergencias', $relatorio);
                $this->session->setFlash('Inventário conciliado com sucesso! Verifique o relatório de divergências.', 'success');
            } catch (\Exception $e) {
                $this->session->setFlash('Ocorreu um erro inesperado ao conciliar o inventário: ' . $e->getMessage(), 'error');
            }

            header('Location: ' . BASE_URL . '/patrimonio/inventario');
            exit();
        }
    }

    /**
     * Exibe a página de Relatórios e Indicadores de Patrimônio.
     */
    public function relatorios()
    {
        // Busca os dados para os relatórios
        $bensPorCentroCusto = $this->model->getBensPorCentroDeCusto();
        $depreciacaoGeral = $this->model->getDepreciacaoGeral();
        $indicadoresRenovacao = $this->model->getIndicadoresRenovacao();

        $data = [
            'pageTitle' => 'Relatórios e Indicadores de Patrimônio',
            'bensPorCentroCusto' => $bensPorCentroCusto,
            'depreciacaoGeral' => $depreciacaoGeral,
            'indicadoresRenovacao' => $indicadoresRenovacao,
        ];

        $this->renderView('patrimonio/relatorios', $data);
    }

    /**
     * Gera uma etiqueta de patrimônio com QR Code em PDF.
     * @param int $id O ID do bem.
     */
    public function etiqueta(int $id)
    {
        $bem = $this->model->getBemById($id);
        if (!$bem) {
            $this->session->setFlash('Bem patrimonial não encontrado.', 'error');
            header('Location: ' . BASE_URL . '/patrimonio');
            exit();
        }

        $empresaModel = new EmpresaModel();
        $empresa = $empresaModel->getDadosEmpresa();

        $data = [
            'bem' => $bem,
            'empresa' => $empresa,
        ];

        ob_start();
        $this->renderPartial('patrimonio/etiqueta_pdf', $data);
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper([0, 0, 226.77, 113.39], 'portrait'); // Dimensão aproximada de 80mm x 40mm
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("etiqueta_" . ($bem['numero_patrimonio'] ?: $bem['id']) . ".pdf", ["Attachment" => false]);
        exit();
    }
}
