<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\ContratosModel;
use App\Models\ClientesModel;
use App\Models\FornecedoresModel;
use App\Models\ProjetosModel;
use App\Models\FinancialModel; // Importa o FinancialModel
// Importa as classes da biblioteca Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

class ContratosController extends BaseController
{
    private $model;
    private $clientesModel;
    private $fornecedoresModel;
    private $projetosModel;
    private $financialModel; // Adiciona a propriedade para o FinancialModel

    public function __construct()
    {
        parent::__construct();
        $this->model = new ContratosModel(); // Correção
        $this->clientesModel = new ClientesModel(); // Correção
        $this->fornecedoresModel = new FornecedoresModel(); // Correção
        $this->projetosModel = new ProjetosModel(); // Correção
        $this->financialModel = new FinancialModel(); // Correção
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getContratosSummary();

        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 5; // Define quantos contratos por página
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Busca os contratos da página atual
        $contratos = $this->model->getContratos([], $itensPorPagina, $offset);
        // Conta o total de contratos para calcular o total de páginas
        $totalContratos = $this->model->getContratosCount([]);
        $totalPaginas = ceil($totalContratos / $itensPorPagina);

        // Busca dados para o formulário do modal
        $clientes = $this->clientesModel->getAllClientes() ?? [];
        $fornecedores = $this->fornecedoresModel->getAllFornecedores() ?? [];
        $projetos = $this->projetosModel->getProjetos([], 999, 0) ?? [];

        $data = array_merge([
            'pageTitle' => 'Contratos - Gestão e Prazos',
            'contratos' => $contratos,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtros' => [], // Para uso futuro com filtros
            'clientes' => $clientes,
            'fornecedores' => $fornecedores,
            'projetos' => $projetos,
        ], $summary);

        $this->renderView('contratos/index', $data);
    }

    /**
     * Exibe o formulário para criar um novo contrato.
     */
    public function novo()
    {
        // Redireciona para a página de índice com um parâmetro para abrir o modal
        header('Location: ' . BASE_URL . '/contratos?action=novo');
        exit();
    }

    /**
     * Salva um novo contrato ou atualiza um existente.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/contratos');
            exit();
        }

        // Coleta e sanitiza os dados do formulário
        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'cliente_id' => filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT),
            'pessoa_id' => filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT),
            'projeto_id' => filter_input(INPUT_POST, 'projeto_id', FILTER_VALIDATE_INT) ?: null,
            'objeto' => filter_input(INPUT_POST, 'objeto', FILTER_SANITIZE_SPECIAL_CHARS),
            'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_inicio' => filter_input(INPUT_POST, 'data_inicio'),
            'vencimento' => filter_input(INPUT_POST, 'vencimento'),
            // Trata o valor monetário que vem formatado (ex: "1.234,56")
            'valor' => !empty($_POST['valor']) ? (float)str_replace(['.', ','], ['', '.'], $_POST['valor']) : null,
        ];

        // --- Lógica de Upload de Arquivo ---
        if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/storage/contratos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileInfo = pathinfo($_FILES['documento']['name']);
            $extension = strtolower($fileInfo['extension']);

            // Validação simples de extensão (pode ser aprimorada)
            if ($extension !== 'pdf') {
                $this->setFlashMessage('error', 'Erro no upload: Apenas arquivos PDF são permitidos.');
                header('Location: ' . BASE_URL . '/contratos');
                exit();
            }

            // Gera um nome de arquivo único para evitar conflitos
            $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $fileInfo['filename']);
            $newFilename = $safeFilename . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $newFilename;

            if (move_uploaded_file($_FILES['documento']['tmp_name'], $destination)) {
                $dados['documento_path'] = $newFilename; // Salva apenas o nome do arquivo no banco
            } else {
                $this->setFlashMessage('error', 'Erro ao mover o arquivo enviado.');
                header('Location: ' . BASE_URL . '/contratos');
                exit();
            }
        }

        // Chama o método no model para salvar os dados
        try {
            if ($this->model->salvarContrato($dados)) {
                $message = $dados['id'] ? 'Contrato atualizado com sucesso!' : 'Contrato cadastrado com sucesso!';
                $this->setFlashMessage('success', $message);
            } else {
                $this->setFlashMessage('error', 'Ocorreu um erro desconhecido ao salvar o contrato.');
            }
        } catch (\PDOException $e) {
            // Captura o erro do banco de dados e o exibe na tela
            $this->setFlashMessage('error', 'Erro de Banco de Dados: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/contratos');
        exit();
    }

    /**
     * Busca os dados de um contrato e retorna o HTML do formulário para edição via AJAX.
     * @param int $id O ID do contrato.
     */
    public function getFormForEdit(int $id)
    {
        $contrato = $this->model->getContratoById($id);

        if (!$contrato) {
            http_response_code(404);
            echo "Contrato não encontrado.";
            exit();
        }

        // Busca listas para os selects do formulário
        $clientes = $this->clientesModel->getAllClientes() ?? [];
        $fornecedores = $this->fornecedoresModel->getAllFornecedores() ?? [];
        $projetos = $this->projetosModel->getProjetos([], 999, 0) ?? [];

        $data = [
            'pageTitle' => 'Editar Contrato',
            'contrato' => $contrato,
            'clientes' => $clientes,
            'fornecedores' => $fornecedores,
            'projetos' => $projetos,
        ];

        // Renderiza apenas o formulário, sem o template principal
        $this->renderPartial('contratos/form', $data);
    }

    /**
     * Exibe a página de Gestão de Vigência, com contratos categorizados por prazo.
     */
    public function vigencia()
    {
        // Busca os contratos categorizados por status de vencimento
        $vencidos = $this->model->getContratosPorVigencia('vencidos');
        $vencendo30 = $this->model->getContratosPorVigencia('vencendo_30');
        $vencendo60 = $this->model->getContratosPorVigencia('vencendo_60');
        $vencendo90 = $this->model->getContratosPorVigencia('vencendo_90');
        $vigenciaLonga = $this->model->getContratosPorVigencia('vigencia_longa');

        $data = [
            'pageTitle' => 'Gestão de Vigência de Contratos',
            'vencidos' => $vencidos,
            'vencendo30' => $vencendo30,
            'vencendo60' => $vencendo60,
            'vencendo90' => $vencendo90,
            'vigenciaLonga' => $vigenciaLonga,
        ];

        $this->renderView('contratos/vigencia', $data);
    }

    /**
     * Exibe a página para gestão de obrigações e cláusulas dos contratos.
     */
    public function obrigacoes()
    {
        // Busca apenas os contratos ativos, que são o foco da gestão de obrigações
        $contratosAtivos = $this->model->getContratosAtivosParaObrigacoes();

        $data = [
            'pageTitle' => 'Gestão de Obrigações e Cláusulas',
            'contratos' => $contratosAtivos,
        ];

        $this->renderView('contratos/obrigacoes', $data);
    }

    /**
     * Exibe a página para a integração financeira dos contratos.
     */
    public function financeiro()
    {
        // Reutiliza a busca de contratos ativos
        $contratosAtivos = $this->model->getContratosAtivosParaFinanceiro();

        $data = [
            'pageTitle' => 'Financeiro Integrado de Contratos',
            'contratos' => $contratosAtivos,
        ];

        $this->renderView('contratos/financeiro', $data);
    }

    /**
     * Exibe a página para a gestão de compliance e jurídico dos contratos.
     */
    public function compliance()
    {
        // Utiliza uma busca específica para compliance, que traz os novos campos
        $contratosAtivos = $this->model->getContratosParaCompliance();

        $data = [
            'pageTitle' => 'Compliance e Jurídico de Contratos',
            'contratos' => $contratosAtivos,
        ];

        $this->renderView('contratos/compliance', $data);
    }

    /**
     * Busca os dados de compliance de um contrato e retorna o HTML do modal via AJAX.
     * @param int $contratoId
     */
    public function gerenciarCompliance(int $contratoId)
    {
        $contrato = $this->model->getContratoById($contratoId);
        if (!$contrato) {
            http_response_code(404);
            echo "Contrato não encontrado.";
            exit();
        }

        $data = [
            'contrato' => $contrato,
        ];

        $this->renderPartial('contratos/modal_compliance', $data);
    }

    /**
     * Salva os dados de compliance de um contrato via AJAX.
     */
    public function salvarCompliance()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'contrato_id', FILTER_VALIDATE_INT),
            'clausula_lgpd' => filter_input(INPUT_POST, 'clausula_lgpd', FILTER_SANITIZE_SPECIAL_CHARS),
            'risco_contratual' => filter_input(INPUT_POST, 'risco_contratual', FILTER_SANITIZE_SPECIAL_CHARS),
            'parecer_juridico' => filter_input(INPUT_POST, 'parecer_juridico', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        if ($this->model->salvarDadosCompliance($dados)) {
            echo json_encode(['success' => true, 'message' => 'Dados de compliance salvos com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar os dados de compliance.']);
        }
        exit();
    }

    /**
     * Exibe a página de relatórios e indicadores de contratos.
     */
    public function relatorios()
    {
        // Busca dados para os indicadores e relatórios
        $summary = $this->model->getContratosSummary();
        $valoresPorTipo = $this->model->getContratosSumValorByType();
        $obrigacoesSummary = $this->model->getObrigacoesSummary();

        // Calcula o total de obrigações pendentes aqui, em vez de na view.
        $obrigacoesPendentes = array_sum(array_column(array_filter($obrigacoesSummary, fn($o) => $o['status'] === 'Pendente'), 'total'));

        $data = [
            'pageTitle' => 'Relatórios e Indicadores de Contratos',
            'totalVigentes' => $summary['totalVigentes'],
            'totalVencidos' => $summary['totalVencidos'],
            'vencendo30dias' => $summary['vencendo30dias'],
            'valoresPorTipo' => $valoresPorTipo,
            'obrigacoesPendentes' => $obrigacoesPendentes,
            'obrigacoesSummary' => $obrigacoesSummary,
        ];

        $this->renderView('contratos/relatorios', $data);
    }

    /**
     * Gera e exporta o Relatório de Vigência de Contratos em PDF.
     */
    public function exportarRelatorioVigenciaPdf()
    {
        // 1. Busca os dados no model
        $contratos = $this->model->getTodosContratosParaRelatorio();

        $data = [
            'pageTitle' => 'Relatório de Vigência de Contratos',
            'contratos' => $contratos,
            'dataGeracao' => date('d/m/Y H:i:s'),
        ];

        // 2. Captura o HTML da view do relatório em uma variável
        ob_start();
        $this->renderPartial('contratos/relatorio_vigencia_pdf', $data);
        $html = ob_get_clean();

        // 3. Configura e instancia o Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // 4. Carrega o HTML no Dompdf
        $dompdf->loadHtml($html);

        // 5. Define o tamanho e a orientação do papel
        $dompdf->setPaper('A4', 'landscape'); // Paisagem para caber mais colunas

        // 6. Renderiza o HTML como PDF e envia para o navegador
        $dompdf->render();
        $dompdf->stream("relatorio_vigencia_contratos_" . date('Y-m-d') . ".pdf", ["Attachment" => false]); // false para abrir no navegador
        exit();
    }

    /**
     * Exibe os detalhes de um contrato para edição.
     * @param int $id O ID do contrato.
     */
    public function detalhe(int $id)
    {
        // Usamos a query principal que já faz os joins necessários
        $contrato = $this->model->getContratoDetalhadoById($id);

        if (!$contrato) {
            $this->setFlashMessage('error', 'Contrato não encontrado.');
            header('Location: ' . BASE_URL . '/contratos');
            exit();
        }

        // Busca listas para os selects do formulário, necessário para o modal de edição
        $clientes = $this->clientesModel->getAllClientes() ?? [];
        $fornecedores = $this->fornecedoresModel->getAllFornecedores() ?? [];
        $projetos = $this->projetosModel->getProjetos([], 999, 0) ?? [];
        $aditivos = $this->model->getAditivosByContratoId($id);

        $data = [
            'pageTitle' => 'Detalhes do Contrato',
            'contrato' => $contrato,
            'aditivos' => $aditivos,
            'clientes' => $clientes,
            'fornecedores' => $fornecedores,
            'projetos' => $projetos,
            'isDetalhePage' => true, // Flag para a view de detalhes
        ];
        $this->renderView('contratos/detalhe', $data);
    }

    /**
     * Exclui um contrato.
     * @param int $id O ID do contrato a ser excluído.
     */
    public function excluir(int $id)
    {
        if ($this->model->excluirContrato($id)) {
            $this->setFlashMessage('success', 'Contrato excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o contrato.');
        }

        header('Location: ' . BASE_URL . '/contratos');
        exit();
    }

    /**
     * Salva um novo aditivo para um contrato.
     */
    public function salvarAditivo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/contratos');
            exit();
        }

        $contrato_id = filter_input(INPUT_POST, 'contrato_id', FILTER_VALIDATE_INT);
        if (!$contrato_id) {
            $this->setFlashMessage('error', 'ID do contrato inválido.');
            header('Location: ' . BASE_URL . '/contratos');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'aditivo_id', FILTER_VALIDATE_INT) ?: null,
            'contrato_id' => $contrato_id,
            'tipo_aditivo' => filter_input(INPUT_POST, 'tipo_aditivo', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_aditivo' => filter_input(INPUT_POST, 'data_aditivo'),
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS),
            'valor_alteracao' => !empty($_POST['valor_alteracao']) ? (float)str_replace(['.', ','], ['', '.'], $_POST['valor_alteracao']) : null,
            'novo_vencimento' => filter_input(INPUT_POST, 'novo_vencimento') ?: null,
        ];

        // Lógica de Upload do documento do aditivo
        if (isset($_FILES['documento_aditivo']) && $_FILES['documento_aditivo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/storage/contratos/aditivos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileInfo = pathinfo($_FILES['documento_aditivo']['name']);
            $extension = strtolower($fileInfo['extension']);
            if ($extension !== 'pdf') {
                $this->setFlashMessage('error', 'Apenas arquivos PDF são permitidos para o aditivo.');
                header('Location: ' . BASE_URL . '/contratos/detalhe/' . $contrato_id);
                exit();
            }

            $newFilename = 'aditivo_' . $contrato_id . '_' . time() . '.' . $extension;
            if (move_uploaded_file($_FILES['documento_aditivo']['tmp_name'], $uploadDir . $newFilename)) {
                $dados['documento_path'] = $newFilename;

                // Se for uma edição, remove o arquivo antigo
                if ($dados['id']) {
                    $aditivoAntigo = $this->model->getAditivoById($dados['id']);
                    if ($aditivoAntigo && !empty($aditivoAntigo['documento_path'])) {
                        $oldFilePath = $uploadDir . $aditivoAntigo['documento_path'];
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                }
            }
        }

        if ($this->model->salvarAditivo($dados)) {
            $message = $dados['id'] ? 'Aditivo atualizado com sucesso!' : 'Aditivo registrado com sucesso!';
            $this->setFlashMessage('success', $message);
        } else {
            $this->setFlashMessage('error', 'Erro ao registrar o aditivo.');
        }

        header('Location: ' . BASE_URL . '/contratos/detalhe/' . $contrato_id);
        exit();
    }

    /**
     * Exclui um aditivo de contrato.
     * @param int $id O ID do aditivo.
     */
    public function excluirAditivo(int $id)
    {
        $aditivo = $this->model->getAditivoById($id);
        if (!$aditivo) {
            $this->setFlashMessage('error', 'Aditivo não encontrado.');
            header('Location: ' . BASE_URL . '/contratos');
            exit();
        }

        if ($this->model->excluirAditivo($id)) {
            $this->setFlashMessage('success', 'Aditivo excluído com sucesso e alterações revertidas.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o aditivo.');
        }

        header('Location: ' . BASE_URL . '/contratos/detalhe/' . $aditivo['contrato_id']);
        exit();
    }

    /**
     * Busca os dados de um aditivo e retorna o HTML do formulário para edição.
     * @param int $id O ID do aditivo.
     */
    public function getFormForEditAditivo(int $id)
    {
        $aditivo = null;
        $contrato_id = null;

        if ($id > 0) {
            $aditivo = $this->model->getAditivoById($id);
            if (!$aditivo) {
                http_response_code(404);
                echo "Aditivo não encontrado.";
                exit();
            }
            $contrato_id = $aditivo['contrato_id'];
        } else {
            // É um novo aditivo, pega o contrato_id do GET
            $contrato_id = filter_input(INPUT_GET, 'contrato_id', FILTER_VALIDATE_INT);
            if (!$contrato_id) {
                http_response_code(400);
                echo "ID do contrato é necessário para criar um novo aditivo.";
                exit();
            }
        }

        // Passa os dados para a view do formulário
        $data = ['aditivo' => $aditivo, 'contrato_id' => $contrato_id];

        $this->renderPartial('contratos/form_aditivo', $data);
    }

    /**
     * Busca as obrigações de um contrato e retorna o HTML do modal via AJAX.
     * @param int $contratoId
     */
    public function gerenciarObrigacoes(int $contratoId)
    {
        $contrato = $this->model->getContratoById($contratoId);
        if (!$contrato) {
            http_response_code(404);
            echo "Contrato não encontrado.";
            exit();
        }

        $obrigacoes = $this->model->getObrigacoesByContratoId($contratoId);

        $data = [
            'contrato' => $contrato,
            'obrigacoes' => $obrigacoes,
        ];

        $this->renderPartial('contratos/modal_obrigacoes', $data);
    }

    /**
     * Salva uma nova obrigação contratual via AJAX.
     */
    public function salvarObrigacao()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit();
        }

        $dados = [
            'contrato_id' => filter_input(INPUT_POST, 'contrato_id', FILTER_VALIDATE_INT),
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS),
            'tipo_clausula' => filter_input(INPUT_POST, 'tipo_clausula', FILTER_SANITIZE_SPECIAL_CHARS),
            'responsavel' => filter_input(INPUT_POST, 'responsavel', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_prevista' => filter_input(INPUT_POST, 'data_prevista') ?: null,
        ];

        if (empty($dados['contrato_id']) || empty($dados['descricao']) || empty($dados['tipo_clausula'])) {
            echo json_encode(['success' => false, 'message' => 'Dados obrigatórios ausentes.']);
            exit();
        }

        if ($this->model->salvarObrigacao($dados)) {
            echo json_encode(['success' => true, 'message' => 'Obrigação registrada com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar obrigação.']);
        }
        exit();
    }

    /**
     * Atualiza o status de uma obrigação via AJAX.
     */
    public function atualizarStatusObrigacao()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit();
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($id) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
            exit();
        }

        if ($this->model->updateStatusObrigacao($id, $status)) {
            echo json_encode(['success' => true, 'message' => 'Status atualizado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status.']);
        }
        exit();
    }

    /**
     * Exclui uma obrigação via AJAX.
     */
    public function excluirObrigacao()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit();
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID da obrigação inválido.']);
            exit();
        }

        if ($this->model->excluirObrigacao($id)) {
            echo json_encode(['success' => true, 'message' => 'Obrigação excluída.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir obrigação.']);
        }
        exit();
    }

    /**
     * Busca os dados financeiros de um contrato e retorna o HTML do modal.
     * @param int $contratoId
     */
    public function gerenciarFinanceiro(int $contratoId)
    {
        $contrato = $this->model->getContratoById($contratoId);
        if (!$contrato) {
            http_response_code(404);
            echo "Contrato não encontrado.";
            exit();
        }

        $parcelas = $this->model->getParcelasByContratoId($contratoId);

        $data = [
            'contrato' => $contrato,
            'parcelas' => $parcelas,
        ];

        $this->renderPartial('contratos/modal_financeiro', $data);
    }

    /**
     * Salva uma nova parcela de contrato via AJAX.
     */
    public function salvarParcela()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit();
        }

        $valorFormatado = $_POST['valor'] ?? '0';
        $valorLimpo = str_replace(['.', ','], ['', '.'], $valorFormatado);

        $dados = [
            'contrato_id' => filter_input(INPUT_POST, 'contrato_id', FILTER_VALIDATE_INT),
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS),
            'valor' => (float) $valorLimpo,
            'data_vencimento' => filter_input(INPUT_POST, 'data_vencimento'),
        ];

        if (empty($dados['contrato_id']) || empty($dados['descricao']) || empty($dados['data_vencimento']) || $dados['valor'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Dados obrigatórios ausentes ou inválidos.']);
            exit();
        }

        if ($this->model->salvarParcela($dados)) {
            echo json_encode(['success' => true, 'message' => 'Parcela registrada com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar parcela.']);
        }
        exit();
    }

    /**
     * Lança uma parcela no financeiro (Contas a Pagar/Receber).
     * @param int $parcelaId
     */
    public function lancarParcela(int $parcelaId)
    {
        header('Content-Type: application/json');

        $parcela = $this->model->getParcelaById($parcelaId);

        if (!$parcela) {
            echo json_encode(['success' => false, 'message' => 'Parcela não encontrada.']);
            exit();
        }

        if ($parcela['status'] !== 'Pendente') {
            echo json_encode(['success' => false, 'message' => 'Esta parcela já foi lançada ou paga.']);
            exit();
        }

        $contrato = $this->model->getContratoById($parcela['contrato_id']);

        // Prepara os dados para o FinancialModel
        $dadosTransacao = [
            'id' => null,
            'tipo' => ($contrato['tipo'] === 'Venda') ? 'R' : 'P', // R para Receita, P para Despesa
            'descricao' => "Contrato #" . $contrato['id'] . ": " . $parcela['descricao'],
            'valor' => $parcela['valor'],
            'vencimento' => $parcela['data_vencimento'],
            'status' => 'Pendente',
            'contrato_parcela_id' => $parcela['id'], // Vínculo com a parcela
            // Outros campos podem ser adicionados aqui, como 'classificacao_id'
            'data_pagamento' => null,
            'dataEmissao' => date('Y-m-d'),
            'documentoVinculado' => null,
            'observacoes' => 'Lançamento automático via Módulo de Contratos.',
            'banco_id' => null,
            'classificacao_id' => null, // Pode ser definido um padrão
            'centro_custo_id' => null,
        ];

        // Salva a transação no financeiro
        $transacaoId = $this->financialModel->salvarTransacao($dadosTransacao);

        if ($transacaoId) {
            // Vincula a transação à parcela e atualiza o status
            if ($this->model->vincularTransacao($parcela['id'], $transacaoId)) {
                echo json_encode(['success' => true, 'message' => 'Parcela lançada com sucesso no financeiro!']);
            } else {
                // Caso de erro: a transação foi criada, mas o vínculo falhou. Requer atenção.
                error_log("ERRO CRÍTICO: Transação {$transacaoId} criada, mas falha ao vincular à parcela {$parcela['id']}.");
                echo json_encode(['success' => false, 'message' => 'Lançamento criado, mas falha ao vincular. Contate o suporte.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar o lançamento no financeiro.']);
        }
        exit();
    }

    /**
     * Exibe a página para enviar alertas de renovação, carregando os contratos.
     */
    public function enviarAlerta()
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Busca todos os contratos para o select
        $contratos = $this->model->getContratos([], 999, 0);

        $data = [
            'pageTitle' => 'Enviar Alerta de Renovação',
            'contratos' => $contratos,
            'isModal' => $isAjax, // Informa a view se ela está em um modal
        ];

        // Se for uma requisição AJAX, renderiza apenas o conteúdo do formulário.
        // Caso contrário, renderiza a página inteira.
        $isAjax ? $this->renderPartial('contratos/enviarAlerta', $data) : $this->renderView('contratos/enviarAlerta', $data);
    }

    /**
     * Exibe a página para fazer upload de documentos, carregando os contratos.
     */
    public function uploadDocumento()
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Busca todos os contratos para o select
        $contratos = $this->model->getContratos([], 999, 0);

        $data = [
            'pageTitle' => 'Upload de Documento (PDF)',
            'contratos' => $contratos,
            'isModal' => $isAjax,
        ];

        // Se for uma requisição AJAX, renderiza apenas o conteúdo do formulário.
        // Caso contrário, renderiza a página inteira.
        $isAjax ? $this->renderPartial('contratos/uploadDocumento', $data) : $this->renderView('contratos/uploadDocumento', $data);
    }

    /**
     * Processa o envio do formulário de alerta. (Placeholder)
     */
    public function processarAlerta()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/contratos/enviarAlerta');
            exit();
        }

        $contratoId = filter_input(INPUT_POST, 'contrato_id', FILTER_VALIDATE_INT);
        $mensagemAdicional = filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$contratoId) {
            $this->setFlashMessage('error', 'Contrato inválido. Por favor, selecione um contrato da lista.');
            header('Location: ' . BASE_URL . '/contratos/enviarAlerta');
            exit();
        }

        $contrato = $this->model->getContratoDetalhadoById($contratoId);

        if (!$contrato) {
            $this->setFlashMessage('error', 'Contrato não encontrado.');
            header('Location: ' . BASE_URL . '/contratos/enviarAlerta');
            exit();
        }

        // --- Lógica de Envio de E-mail ---
        // Em um ambiente de produção, é altamente recomendável usar uma biblioteca como PHPMailer ou Symfony Mailer.
        $destinatario = 'admin@sysenvicorp.com'; // E-mail do responsável/gestor de contratos
        $assunto = "Alerta de Renovação de Contrato: #" . $contrato['id'];

        $corpoEmail = "<h1>Alerta de Renovação de Contrato</h1>";
        $corpoEmail .= "<p>Um alerta de renovação foi emitido para o seguinte contrato:</p>";
        $corpoEmail .= "<ul>";
        $corpoEmail .= "<li><strong>Contrato ID:</strong> " . $contrato['id'] . "</li>";
        $corpoEmail .= "<li><strong>Objeto:</strong> " . htmlspecialchars($contrato['objeto']) . "</li>";
        $corpoEmail .= "<li><strong>Parte Contratada:</strong> " . htmlspecialchars($contrato['parteContratada']) . "</li>";
        $corpoEmail .= "<li><strong>Data de Vencimento:</strong> " . date('d/m/Y', strtotime($contrato['vencimento'])) . "</li>";
        $corpoEmail .= "</ul>";
        if (!empty($mensagemAdicional)) {
            $corpoEmail .= "<h2>Mensagem Adicional:</h2>";
            $corpoEmail .= "<p>" . nl2br(htmlspecialchars($mensagemAdicional)) . "</p>";
        }
        $corpoEmail .= "<p>Por favor, tome as ações necessárias para a renovação.</p>";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <noreply@sysenvicorp.com>' . "\r\n";

        // A função mail() pode não funcionar em ambientes locais (localhost) sem configuração de um servidor SMTP.
        if (mail($destinatario, $assunto, $corpoEmail, $headers)) {
            $this->setFlashMessage('success', 'Alerta de renovação enviado com sucesso!');
        } else {
            // Em ambiente de desenvolvimento ou localhost, simulamos o sucesso para não bloquear o fluxo.
            $is_localhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);
            $is_dev_env = (defined('ENVIRONMENT') && ENVIRONMENT === 'development');

            if ($is_localhost || $is_dev_env) {
                $this->setFlashMessage('success', 'Alerta de renovação enviado com sucesso! (Simulação em ambiente local)');
            } else {
                $this->setFlashMessage('error', 'Falha ao enviar o e-mail de alerta. Verifique as configurações do servidor.');
            }
        }

        header('Location: ' . BASE_URL . '/contratos/enviarAlerta');
        exit();
    }

    /**
     * Processa o upload de um novo documento. (Placeholder)
     */
    public function processarUpload()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/contratos/uploadDocumento');
            exit();
        }

        $contratoId = filter_input(INPUT_POST, 'contrato_id', FILTER_VALIDATE_INT);
        $arquivo = $_FILES['documento_pdf'] ?? null;

        // Validações
        if (!$contratoId) {
            $this->setFlashMessage('error', 'Por favor, selecione um contrato para associar o documento.');
            header('Location: ' . BASE_URL . '/contratos/uploadDocumento');
            exit();
        }

        if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK) {
            $this->setFlashMessage('error', 'Erro no upload do arquivo. Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/contratos/uploadDocumento');
            exit();
        }

        // --- Lógica de Upload de Arquivo ---
        $uploadDir = ROOT_PATH . '/storage/contratos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $fileInfo = pathinfo($arquivo['name']);
        $extension = strtolower($fileInfo['extension']);

        if ($extension !== 'pdf') {
            $this->setFlashMessage('error', 'Erro no upload: Apenas arquivos PDF são permitidos.');
            header('Location: ' . BASE_URL . '/contratos/uploadDocumento');
            exit();
        }

        // Gera um nome de arquivo único e seguro
        $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $fileInfo['filename']);
        $newFilename = 'contrato_' . $contratoId . '_' . time() . '.' . $extension;
        $destination = $uploadDir . $newFilename;

        if (move_uploaded_file($arquivo['tmp_name'], $destination)) {
            // Atualiza o caminho do documento no banco de dados
            $this->model->updateDocumentoPath($contratoId, $newFilename);
            $this->setFlashMessage('success', 'Documento enviado e associado ao contrato com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao mover o arquivo enviado para o destino final.');
        }

        header('Location: ' . BASE_URL . '/contratos');
        exit();
    }

    /**
     * Força o download de um documento de contrato de forma segura.
     * @param string $filename O nome do arquivo a ser baixado.
     */
    public function download(string $filename)
    {
        // Sanitiza o nome do arquivo para evitar ataques de "directory traversal"
        $filename = basename($filename);
        $filePath = ROOT_PATH . '/storage/contratos/' . $filename;

        if (file_exists($filePath)) {
            // Define os cabeçalhos para forçar o download
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '"'); // 'inline' tenta abrir no navegador, 'attachment' força o download
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));

            // Limpa o buffer de saída para evitar corrupção do arquivo
            flush();

            // Lê o arquivo e o envia para o navegador
            readfile($filePath);
            exit;
        } else {
            // Se o arquivo não for encontrado, exibe um erro 404
            http_response_code(404);
            echo "<h1>404 - Arquivo não encontrado</h1>";
            exit;
        }
    }

    /**
     * Remove o documento associado a um contrato.
     * @param int $id O ID do contrato.
     */
    public function removerDocumento(int $id)
    {
        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID de contrato inválido.');
        } else {
            if ($this->model->removerDocumento($id)) {
                $this->setFlashMessage('success', 'Documento do contrato removido com sucesso!');
            } else {
                $this->setFlashMessage('error', 'Erro ao remover o documento do contrato.');
            }
        }
        // Redireciona de volta para a página de onde veio (seja a lista ou detalhes)
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/contratos'));
        exit();
    }

    /**
     * Gera relatórios de compliance em PDF.
     * @param string $tipo O tipo de relatório (ex: 'lgpd', 'risco_alto').
     */
    public function relatorioCompliance(string $tipo)
    {
        $dadosRelatorio = [];
        $tituloRelatorio = 'Relatório de Compliance';

        switch ($tipo) {
            case 'lgpd':
                $tituloRelatorio = 'Relatório de Contratos sem Cláusula LGPD';
                $dadosRelatorio = $this->model->getContratosPorFiltroCompliance(['clausula_lgpd' => 'Não']);
                break;
            case 'risco_alto':
                $tituloRelatorio = 'Relatório de Contratos com Risco Alto';
                $dadosRelatorio = $this->model->getContratosPorFiltroCompliance(['risco_contratual' => 'Alto']);
                break;
            case 'geral':
                $tituloRelatorio = 'Relatório Geral de Conformidade de Contratos';
                $dadosRelatorio = $this->model->getContratosPorFiltroCompliance([]); // Sem filtro, busca todos
                break;
            default:
                $this->setFlashMessage('error', 'Tipo de relatório de compliance desconhecido.');
                header('Location: ' . BASE_URL . '/contratos/compliance');
                exit();
        }

        $data = [
            'pageTitle' => $tituloRelatorio,
            'contratos' => $dadosRelatorio,
            'dataGeracao' => date('d/m/Y H:i:s'),
        ];

        ob_start();
        $this->renderPartial('contratos/relatorio_compliance_pdf', $data);
        $html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("relatorio_compliance_{$tipo}_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
        exit();
    }
}
