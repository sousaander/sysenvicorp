<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\OrganogramaModel;

class OrganogramaController extends BaseController
{
    private $organogramaModel;

    public function __construct()
    {
        parent::__construct(); // Garante que o SessionManager seja inicializado.
        $this->organogramaModel = new OrganogramaModel();
    }

    public function index()
    {
        // Coleta a estrutura e atividades do modelo
        $estruturaPlana = $this->organogramaModel->getEstruturaEAtividades();

        // Organiza a estrutura de forma hierárquica para a View
        $nodes = [];
        foreach ($estruturaPlana as $item) {
            $id = $item['id'];
            if (!isset($nodes[$id])) {
                $nodes[$id] = [
                    'id' => $id,
                    'cargo' => $item['cargo'],
                    'responsavel' => $item['responsavel'],
                    'parent_id' => $item['parent_id'],
                    'atividades' => [],
                    'progresso_geral' => 0,
                    'children' => []
                ];
            }
            if ($item['atividade_id']) {
                $nodes[$id]['atividades'][] = [
                    'id' => $item['atividade_id'],
                    'nome' => $item['atividade_nome'],
                    'meta' => $item['atividade_meta'],
                    'progresso' => $item['atividade_progresso'],
                ];
            }
        }

        // Calcula o progresso geral para cada nó
        foreach ($nodes as &$node) {
            if (!empty($node['atividades'])) {
                $node['progresso_geral'] = round(array_sum(array_column($node['atividades'], 'progresso')) / count($node['atividades']));
            }
        }
        unset($node);

        // Constrói a árvore hierárquica de forma padrão e segura.
        // A complexidade do "nó virtual" foi removida para garantir estabilidade.
        $nodesById = $nodes;
        $tree = []; // A árvore final

        // Usar referências (&) aqui é crucial para construir a árvore no mesmo array.
        foreach ($nodesById as $id => &$node) {
            if (isset($node['parent_id']) && isset($nodesById[$node['parent_id']])) {
                $nodesById[$node['parent_id']]['children'][] = &$node;
            }
        }
        unset($node); // Desfaz a última referência para segurança.

        // Coleta apenas os nós raiz (aqueles sem pai ou cujo pai não existe) para a árvore final.
        foreach ($nodesById as $node) {
            if (!isset($node['parent_id']) || !isset($nodesById[$node['parent_id']])) {
                $tree[] = $node;
            }
        }

        $data = [
            'pageTitle' => 'Organograma e KPIs',
            'hierarquia' => $tree,
            'estrutura' => array_values($nodes) // Envia os dados agrupados para o JS
        ];

        $this->renderView('organograma/index', $data);
    }

    /**
     * Processa a adição de um novo cargo/departamento.
     */
    public function adicionarCargo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/organograma');
            exit();
        }

        $dados = [
            'cargo' => filter_input(INPUT_POST, 'cargo', FILTER_SANITIZE_SPECIAL_CHARS),
            'responsavel' => filter_input(INPUT_POST, 'responsavel', FILTER_SANITIZE_SPECIAL_CHARS),
            'parent_id' => filter_input(INPUT_POST, 'parent_id', FILTER_SANITIZE_SPECIAL_CHARS), // Pode ser '0' ou ''
        ];

        if (empty($dados['cargo']) || empty($dados['responsavel'])) {
            $this->setFlashMessage('error', 'O nome do Cargo e do Responsável são obrigatórios.');
            header('Location: ' . BASE_URL . '/organograma');
            exit();
        }

        if ($this->organogramaModel->adicionarCargo($dados)) {
            $this->setFlashMessage('success', 'Novo cargo adicionado ao organograma com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Ocorreu um erro ao adicionar o novo cargo.');
        }

        header('Location: ' . BASE_URL . '/organograma');
        exit();
    }

    /**
     * Processa a atualização de um cargo/departamento.
     */
    public function atualizarCargo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/organograma');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT),
            'cargo' => filter_input(INPUT_POST, 'cargo', FILTER_SANITIZE_SPECIAL_CHARS),
            'responsavel' => filter_input(INPUT_POST, 'responsavel', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        if (empty($dados['id']) || empty($dados['cargo']) || empty($dados['responsavel'])) {
            $this->setFlashMessage('error', 'Dados inválidos para atualização.');
        } else {
            if ($this->organogramaModel->atualizarCargo($dados)) {
                $this->setFlashMessage('success', 'Cargo atualizado com sucesso!');
            } else {
                $this->setFlashMessage('error', 'Ocorreu um erro ao atualizar o cargo.');
            }
        }

        header('Location: ' . BASE_URL . '/organograma');
        exit();
    }

    /**
     * Processa a exclusão de um cargo/departamento.
     * @param int $id O ID do cargo a ser excluído.
     */
    public function excluirCargo(int $id)
    {
        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID de cargo inválido.');
        } else {
            if ($this->organogramaModel->excluirCargo($id)) {
                $this->setFlashMessage('success', 'Cargo excluído com sucesso!');
            } else {
                $this->setFlashMessage('error', 'Ocorreu um erro ao excluir o cargo.');
            }
        }

        header('Location: ' . BASE_URL . '/organograma');
        exit();
    }

    /**
     * Processa a adição de uma nova atividade via AJAX.
     */
    public function adicionarAtividade()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit();
        }

        $dados = [
            'estrutura_id' => filter_input(INPUT_POST, 'estrutura_id', FILTER_VALIDATE_INT),
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'meta' => filter_input(INPUT_POST, 'meta', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        if (empty($dados['estrutura_id']) || empty($dados['nome'])) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
            exit();
        }

        $newId = $this->organogramaModel->adicionarAtividade($dados);

        if ($newId) {
            echo json_encode([
                'success' => true,
                'message' => 'Atividade adicionada com sucesso!',
                'data' => ['id' => $newId, 'nome' => $dados['nome'], 'meta' => $dados['meta'], 'progresso' => 0]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar atividade.']);
        }
        exit();
    }

    /**
     * Processa a atualização de uma atividade via AJAX.
     */
    public function atualizarAtividade()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT),
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'meta' => filter_input(INPUT_POST, 'meta', FILTER_SANITIZE_SPECIAL_CHARS),
            'progresso' => filter_input(INPUT_POST, 'progresso', FILTER_VALIDATE_INT),
        ];

        if (empty($dados['id']) || empty($dados['nome']) || $dados['progresso'] === false) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos para atualização.']);
            exit();
        }

        if ($this->organogramaModel->atualizarAtividade($dados)) {
            echo json_encode(['success' => true, 'message' => 'Atividade atualizada com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar a atividade.']);
        }
        exit();
    }

    /**
     * Processa a exclusão de uma atividade via AJAX.
     * @param int $id O ID da atividade a ser excluída.
     */
    public function excluirAtividade(int $id)
    {
        header('Content-Type: application/json');

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de atividade inválido.']);
            exit();
        }

        if ($this->organogramaModel->excluirAtividade($id)) {
            echo json_encode([
                'success' => true,
                'message' => 'Atividade excluída com sucesso!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao excluir a atividade.'
            ]);
        }
        exit();
    }
}
