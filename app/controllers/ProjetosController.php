<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\ClientesModel;
use App\Models\ProjetosModel;

class ProjetosController extends BaseController
{
    private $model;
    private $clientesModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ProjetosModel(); // Já estava correto
        $this->clientesModel = new ClientesModel(); // Já estava correto
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getProjetosSummary();

        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 5; // Define quantos projetos por página
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Busca os projetos da página atual
        $projetos = $this->model->getProjetos([], $itensPorPagina, $offset);
        // Conta o total de projetos para calcular o total de páginas
        $totalProjetos = $this->model->getProjetosCount([]);
        $totalPaginas = ceil($totalProjetos / $itensPorPagina);

        // Busca os clientes para o formulário da modal
        $clientes = $this->clientesModel->getAllClientes();

        // Os dados do resumo são mesclados aqui para a página principal
        $data = array_merge($summary, [
            'pageTitle' => 'Projetos - Cronogramas e Entregas',
            'projetos' => $projetos,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtros' => [], // Para uso futuro com filtros
            'baseUrl' => '/projetos', // Define a URL base para a paginação e links
            'clientes' => $clientes, // Adiciona os clientes para a modal
        ]);

        $this->renderView('projetos/index', $data);
    }

    /**
     * Exibe os detalhes de um projeto para edição.
     * Agora funciona como um dashboard para o projeto, com submenus.
     * @param int $id O ID do projeto.
     * @param string $submenu O submenu a ser exibido (default: 'resumo').
     */
    public function detalhe(int $id, string $submenu = 'resumo')
    {
        $projeto = $this->model->getProjetoById($id);

        if (!$projeto) {
            $this->setFlashMessage('error', 'Projeto não encontrado.');
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        // Carrega os dados necessários para o submenu específico
        $submenuData = []; // Garante que a variável sempre exista
        switch ($submenu) {
            case 'dados_gerais':
                // Reutiliza o form.php para edição
                // A view 'projetos/form' espera a variável $clientes diretamente.
                // Adicionamos ao array $data principal para garantir consistência.
                $data['clientes'] = $this->clientesModel->getAllClientes();
                $submenuView = 'projetos/form';
                break;
            case 'orcamento':
                // Lógica para buscar dados do orçamento
                $submenuData['itens_orcamento'] = $this->model->getOrcamentoByProjetoId($id);
                $submenuData['summary'] = $this->model->getOrcamentoSummary($id);
                $submenuView = 'projetos/submenus/orcamento';
                break;
            case 'cronograma':
                // Lógica para buscar dados do cronograma (a ser implementada)
                $submenuView = 'projetos/submenus/cronograma';
                break;
            case 'cdt':
                $submenuData['documentos'] = $this->model->getCDTByProjetoId($id);
                $submenuView = 'projetos/submenus/cdt';
                break;
            case 'mapas':
                $submenuData['mapas'] = $this->model->getMapasByProjetoId($id);
                $submenuView = 'projetos/submenus/mapas';
                break;
            case 'arquivos':
                $submenuData['arquivos'] = $this->model->getArquivosByProjetoId($id);
                $submenuView = 'projetos/submenus/arquivos';
                break;
            case 'art':
                $submenuData['arts'] = $this->model->getArtByProjetoId($id);
                $submenuView = 'projetos/submenus/art';
                break;
            case 'resumo':
            default:
                // Busca os dados agregados para o dashboard de resumo do projeto
                $submenuData['summaryDetails'] = $this->model->getProjectDetailsSummary($id);
                $submenuData['timeline'] = $this->model->getTimelineByProjectId($id);
                $submenuView = 'projetos/submenus/resumo';
                break;
        }

        // Mescla os dados já definidos (como 'clientes') com os dados padrão.
        $data = array_merge($data ?? [], [
            'pageTitle' => 'Projeto: ' . htmlspecialchars($projeto['nome']),
            'projeto' => $projeto,
            'submenu' => $submenu,
            'submenuView' => $submenuView,
            'submenuData' => $submenuData, // Passa os dados específicos do submenu para a view
        ]);
        $this->renderView('projetos/detalhe', $data);
    }

    /**
     * Exibe o formulário para adicionar um novo projeto.
     */
    public function novo()
    {
        $clientes = $this->clientesModel->getAllClientes();
        $data = ['pageTitle' => 'Novo Projeto', 'projeto' => null, 'clientes' => $clientes];
        $this->renderView('projetos/form', $data); // Assumindo que a view se chamará 'form.php'
    }

    /**
     * Salva um item do orçamento de um projeto.
     */
    public function salvarItemOrcamento()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        // Combina POST e FILES para o model
        $dados = array_merge($_POST, ['comprovante' => $_FILES['comprovante'] ?? null]);
        $projeto_id = $dados['projeto_id'];

        if ($this->model->salvarItemOrcamento($dados)) {
            $this->setFlashMessage('success', 'Item do orçamento salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o item do orçamento.');
        }

        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/orcamento');
        exit();
    }

    /**
     * Salva um registro de ART/RRT de um projeto.
     */
    public function salvarArt()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        $dados = array_merge($_POST, [
            'documento_art' => $_FILES['documento_art'] ?? null,
            'boleto' => $_FILES['boleto'] ?? null,
            'comprovante_pgto' => $_FILES['comprovante_pgto'] ?? null,
        ]);
        $projeto_id = $dados['projeto_id'];

        if ($this->model->salvarArt($dados)) {
            $this->setFlashMessage('success', 'Registro de ART/RRT salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o registro de ART/RRT.');
        }

        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/art');
        exit();
    }

    /**
     * Exclui um registro de ART/RRT.
     */
    public function excluirArt(int $art_id, int $projeto_id)
    {
        if ($this->model->excluirArt($art_id)) {
            $this->setFlashMessage('success', 'Registro de ART/RRT excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o registro.');
        }
        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/art');
        exit();
    }

    /**
     * Salva um documento técnico (CDT) de um projeto.
     */
    public function salvarCDT()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        $dados = array_merge($_POST, ['documento' => $_FILES['documento'] ?? null]);
        $projeto_id = $dados['projeto_id'];

        // Validação básica
        if (empty($dados['nome_documento']) || empty($dados['tipo_documento']) || (empty($dados['id']) && empty($dados['documento']['name']))) {
            $this->setFlashMessage('error', 'Nome, Tipo e Arquivo são obrigatórios para um novo documento.');
            header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/cdt');
            exit();
        }

        if ($this->model->salvarCDT($dados)) {
            $this->setFlashMessage('success', 'Documento salvo com sucesso no CDT!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o documento.');
        }

        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/cdt');
        exit();
    }

    /**
     * Exclui um documento técnico (CDT).
     */
    public function excluirCDT(int $cdt_id, int $projeto_id)
    {
        if ($this->model->excluirCDT($cdt_id)) {
            $this->setFlashMessage('success', 'Documento excluído com sucesso do CDT!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o documento.');
        }
        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/cdt');
        exit();
    }

    /**
     * Salva um mapa (CM) de um projeto.
     */
    public function salvarMapa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        $dados = array_merge($_POST, ['mapa_arquivo' => $_FILES['mapa_arquivo'] ?? null]);
        $projeto_id = $dados['projeto_id'];

        // Validação básica
        if (empty($dados['nome_mapa']) || empty($dados['categoria_mapa']) || (empty($dados['id']) && empty($dados['mapa_arquivo']['name']))) {
            $this->setFlashMessage('error', 'Nome, Categoria e Arquivo são obrigatórios para um novo mapa.');
            header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/mapas');
            exit();
        }

        if ($this->model->salvarMapa($dados)) {
            $this->setFlashMessage('success', 'Mapa salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o mapa.');
        }

        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/mapas');
        exit();
    }

    /**
     * Exclui um mapa (CM).
     */
    public function excluirMapa(int $mapa_id, int $projeto_id)
    {
        if ($this->model->excluirMapa($mapa_id)) {
            $this->setFlashMessage('success', 'Mapa excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o mapa.');
        }
        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/mapas');
        exit();
    }

    /**
     * Salva um arquivo geral do projeto.
     */
    public function salvarArquivo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        $dados = array_merge($_POST, ['arquivo' => $_FILES['arquivo'] ?? null]);
        $projeto_id = $dados['projeto_id'];

        // Validação básica
        if (empty($dados['nome_arquivo']) || empty($dados['categoria']) || (empty($dados['id']) && empty($dados['arquivo']['name']))) {
            $this->setFlashMessage('error', 'Nome, Categoria e Arquivo são obrigatórios.');
            header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/arquivos');
            exit();
        }

        if ($this->model->salvarArquivo($dados)) {
            $this->setFlashMessage('success', 'Arquivo salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o arquivo.');
        }

        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/arquivos');
        exit();
    }

    /**
     * Exclui um arquivo geral do projeto.
     */
    public function excluirArquivo(int $arquivo_id, int $projeto_id)
    {
        if ($this->model->excluirArquivo($arquivo_id)) {
            $this->setFlashMessage('success', 'Arquivo excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o arquivo.');
        }
        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/arquivos');
        exit();
    }

    // ... (outros métodos)


    /**
     * Salva um novo projeto ou atualiza um existente.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        $dados = $_POST;

        // TODO: Adicionar validação dos dados aqui antes de salvar.

        // Assumindo que o método no model se chamará 'salvarProjeto'
        // e que ele lida tanto com inserção quanto com atualização.
        try {
            if ($this->model->salvarProjeto($dados)) {
                $this->setFlashMessage('success', 'Projeto salvo com sucesso!');
            } else {
                $this->setFlashMessage('error', 'Ocorreu um erro desconhecido ao salvar o projeto.');
            }
        } catch (\PDOException $e) {
            // Captura o erro do banco de dados e o exibe na tela para depuração.
            $this->setFlashMessage('error', 'Erro de Banco de Dados: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/projetos');
        exit();
    }

    /**
     * Retorna o HTML do formulário de criação/edição de projetos.
     * Usado para carregar o formulário dinamicamente em uma modal via AJAX.
     */
    public function getFormulario()
    {
        // Carrega os dados necessários para o formulário, como a lista de clientes.
        $clientes = $this->clientesModel->getAllClientes();

        // Prepara os dados para a view do formulário
        $data = [
            'projeto' => [], // Array vazio para um novo projeto
            'clientes' => $clientes
        ];

        // Renderiza apenas a view do formulário, sem o template principal.
        $this->renderPartial('projetos/form', $data); // Usa renderPartial para não incluir o layout
    }

    /**
     * Exibe a lista de projetos arquivados (concluídos).
     */
    public function arquivados()
    {
        // Define o filtro para buscar apenas projetos concluídos
        $filtros = ['status' => 'Concluído'];

        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10; // Aumentamos para 10 itens por página na listagem de arquivados
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Busca os projetos concluídos no modelo
        $projetos = $this->model->getProjetos($filtros, $itensPorPagina, $offset);
        $totalProjetos = $this->model->getProjetosCount($filtros);
        $totalPaginas = ceil($totalProjetos / $itensPorPagina);

        // Reutiliza o resumo geral (não zerar os cards) para manter as contagens informativas
        $summary = $this->model->getProjetosSummary();

        // Busca os clientes para o formulário da modal
        $clientes = $this->clientesModel->getAllClientes();

        // Prepara os dados para a view, mantendo os cards com valores reais
        $data = array_merge($summary, [
            'pageTitle' => 'Projetos Arquivados',
            'projetos' => $projetos,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtros' => $filtros,
            'baseUrl' => '/projetos/arquivados', // Define a URL base para a paginação e links
            'clientes' => $clientes, // Adiciona os clientes para a modal
        ]);

        $this->renderView('projetos/index', $data);
    }
}
