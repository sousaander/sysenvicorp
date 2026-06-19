<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\TreinamentosModel;
use App\Models\EmpresaModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class TreinamentosController extends BaseController
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new TreinamentosModel();
    }

    /**
     * Exibe a lista de treinamentos.
     */
    public function index()
    {
        $filtros = [
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'search' => filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $treinamentos = $this->model->getAllTreinamentos($itensPorPagina, $offset, $filtros);
        $totalRegistros = $this->model->getTreinamentosCount($filtros);
        $totalPaginas = ceil($totalRegistros / $itensPorPagina);

        $stats = $this->model->getStats();

        $data = [
            'pageTitle' => 'Gestão de Treinamentos',
            'treinamentos' => $treinamentos,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'totalTreinamentos' => $stats['total'] ?? 0,
            'totalAgendados' => $stats['Agendado'] ?? 0,
            'totalRealizados' => $stats['Realizado'] ?? 0,
            'totalCancelados' => $stats['Cancelado'] ?? 0,
        ];

        $this->renderView('treinamentos/index', $data);
    }

    /**
     * Exibe o formulário para um novo treinamento.
     */
    public function novo()
    {
        $rhModel = new \App\Models\RhModel();
        $colaboradores = $rhModel->getFuncionarios(['status' => 'Ativo'], 999, 0);

        $data = [
            'pageTitle' => 'Novo Treinamento',
            'treinamento' => null,
            'colaboradores' => $colaboradores
        ];
        $this->renderView('treinamentos/form', $data);
    }

    /**
     * Exibe o formulário para editar um treinamento.
     * @param int $id
     */
    public function editar(int $id)
    {
        $treinamento = $this->model->getTreinamentoById($id);
        if (!$treinamento) {
            $this->setFlashMessage('error', 'Treinamento não encontrado.');
            header('Location: ' . BASE_URL . '/treinamentos');
            exit();
        }

        $rhModel = new \App\Models\RhModel();
        $colaboradores = $rhModel->getFuncionarios(['status' => 'Ativo'], 999, 0);

        $data = [
            'pageTitle' => 'Editar Treinamento',
            'treinamento' => $treinamento,
            'colaboradores' => $colaboradores
        ];
        $this->renderView('treinamentos/form', $data);
    }

    /**
     * Salva um treinamento (novo ou existente).
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/treinamentos');
            exit();
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;

        $dados = [
            'id' => $id,
            'nome_treinamento' => filter_input(INPUT_POST, 'nome_treinamento', FILTER_SANITIZE_SPECIAL_CHARS),
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_prevista' => filter_input(INPUT_POST, 'data_prevista'),
            'instrutor' => filter_input(INPUT_POST, 'instrutor', FILTER_SANITIZE_SPECIAL_CHARS),
            'local' => filter_input(INPUT_POST, 'local', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        $participantes = $_POST['participantes'] ?? [];

        if (empty($dados['nome_treinamento']) || empty($dados['data_prevista'])) {
            $this->setFlashMessage('error', 'Nome e Data Prevista são obrigatórios.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/treinamentos'));
            exit();
        }

        try {
            if ($this->model->salvar($dados, $participantes)) {
                $msg = $id ? 'Treinamento atualizado com sucesso!' : 'Treinamento agendado com sucesso!';
                $this->setFlashMessage('success', $msg);
            } else {
                $this->setFlashMessage('error', 'Erro ao salvar o treinamento.');
            }
        } catch (\Exception $e) {
            error_log("Erro ao salvar treinamento: " . $e->getMessage());
            $this->setFlashMessage('error', 'Erro interno do servidor.');
        }

        header('Location: ' . BASE_URL . '/treinamentos');
        exit();
    }

    /**
     * Exclui um treinamento.
     * @param int $id
     */
    public function excluir(int $id)
    {
        if ($id <= 0) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        }

        if ($this->model->excluirTreinamento($id)) {
            $this->setFlashMessage('success', 'Treinamento excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o treinamento.');
        }

        header('Location: ' . BASE_URL . '/treinamentos');
        exit();
    }

    /**
     * Gera a lista de presença em PDF para um treinamento específico.
     * @param int $id
     */
    public function listaPresenca(int $id)
    {
        $treinamento = $this->model->getTreinamentoById($id);
        if (!$treinamento) {
            $this->setFlashMessage('error', 'Treinamento não encontrado.');
            header('Location: ' . BASE_URL . '/treinamentos');
            exit();
        }

        $empresaModel = new EmpresaModel();
        $empresa = $empresaModel->getDadosEmpresa();

        $data = [
            'treinamento' => $treinamento,
            'empresa' => $empresa,
            'dataGeracao' => date('d/m/Y H:i')
        ];

        ob_start();
        $this->renderPartial('treinamentos/lista_presenca_pdf', $data);
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = "Lista_Presenca_Treinamento_" . $id . ".pdf";
        $dompdf->stream($filename, ["Attachment" => false]);
        exit();
    }
}