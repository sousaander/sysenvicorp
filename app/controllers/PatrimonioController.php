<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\PatrimonioModel;

class PatrimonioController extends BaseController
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PatrimonioModel();
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getAssetsSummary();

        // Lógica de Paginação para bens recentes
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 3; // 3 itens por página, conforme solicitado
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $recentes = $this->model->getRecentementeAdicionados($itensPorPagina, $offset);
        $totalBens = $this->model->getBensCount(); // Total de bens para calcular as páginas
        $totalPaginas = ceil($totalBens / $itensPorPagina);

        $data = array_merge([
            'pageTitle' => 'Patrimônio - Dashboard',
            'bensRecentes' => $recentes,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
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
     * Salva um bem novo ou atualiza um existente.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = $_POST;

            try {
                $success = $this->model->salvarBem($dados);
                if ($success) {
                    $this->session->setFlash('Bem salvo com sucesso!', 'success');
                } else {
                    $this->session->setFlash('Erro ao salvar o bem. Verifique os dados e tente novamente.', 'error');
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
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

            if ($id) {
                if ($this->model->excluirBem($id)) {
                    $this->session->setFlash('Bem excluído com sucesso!', 'success');
                } else {
                    $this->session->setFlash('Erro ao excluir o bem.', 'error');
                }
            } else {
                $this->session->setFlash('ID inválido para exclusão.', 'error');
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
}
