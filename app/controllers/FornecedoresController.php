<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\ContratosModel;
use App\Models\FornecedoresModel;
use App\Models\FinancialModel;
use App\Models\EmpresaModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FornecedoresController extends BaseController
{
    private $model;
    private $contratosModel;
    private $financialModel;
    private $empresaModel;

    /**
     * Mapeia ações para as permissões necessárias.
     * O BaseController usará este mapa para verificar o acesso.
     * @var array
     */
    protected $requiredPermissions = [
        'index' => 'fornecedores_view',
        'exportarPdf' => 'fornecedores_view',
        'exportarExcel' => 'fornecedores_view',
        'detalhe' => 'fornecedores_view',
        'novo' => 'fornecedores_create',
        'editar' => 'fornecedores_edit',
        'salvar' => 'fornecedores_manage',
        'excluir' => 'fornecedores_delete', // Alias para compatibilidade
        'arquivar' => 'fornecedores_delete', // Renomeado de 'excluir'
        'restaurar' => 'fornecedores_edit', // Alias para compatibilidade
        'ativar' => 'fornecedores_edit', // Renomeado de 'restaurar'
        'salvarOcorrencia' => 'fornecedores_edit',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->contratosModel = new ContratosModel();
        $this->financialModel = new FinancialModel();
        $this->model = new FornecedoresModel();
        $this->empresaModel = new EmpresaModel();
    }

    public function index()
    {
        // Coleta filtros da URL
        $filtros = [
            'busca' => filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        // Melhoria Sênior: Só força 'Ativo' se não houver uma busca por texto em andamento.
        // Isso permite localizar fornecedores inativos pela busca global sem precisar trocar o select primeiro.
        if (empty($filtros['busca']) && empty($filtros['status']) && !isset($_GET['status'])) {
            $filtros['status'] = 'Ativo';
        }

        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Coleta dados do modelo
        $summary = $this->model->getFornecedoresSummary();
        $fornecedores = $this->model->getFornecedores($filtros, $itensPorPagina, $offset);
        $totalFornecedores = $this->model->getFornecedoresCount($filtros);
        $totalPaginas = ceil($totalFornecedores / $itensPorPagina);

        $paginacao = [
            'pagina_atual' => $paginaAtual,
            'total_paginas' => $totalPaginas,
            'total'         => $totalFornecedores,
            'inicio'        => ($totalFornecedores > 0) ? $offset + 1 : 0,
            'fim'           => min($offset + $itensPorPagina, $totalFornecedores)
        ];

        $data = array_merge([
            'pageTitle' => 'Gestão e Conformidade',
            'fornecedores' => $fornecedores,
            'fornecedor' => null, // Garante que a variável exista para o form.php no modal
            'paginacao' => $paginacao,
            'filtros' => $filtros,
        ], $summary);

        $this->renderView('fornecedores/index', $data);
    }

    /**
     * Exibe o formulário para adicionar um novo fornecedor.
     */
    public function novo()
    {
        $data = [
            'pageTitle' => 'Novo Fornecedor',
            'fornecedor' => null,
        ];

        $this->renderView('fornecedores/form', $data);
    }

    /**
     * Exibe o formulário para editar um fornecedor existente.
     * @param int $id O ID do fornecedor.
     */
    public function editar(int $id)
    {
        $fornecedor = $this->model->getFornecedorById($id);

        if (!$fornecedor) {
            $this->setFlashMessage('error', 'Fornecedor não encontrado.');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        $data = [
            'pageTitle' => 'Editar Fornecedor',
            'fornecedor' => $fornecedor,
        ];

        $this->renderView('fornecedores/form', $data);
    }

    /**
     * Exibe os detalhes de um fornecedor.
     * @param int $id O ID do fornecedor.
     */
    public function detalhe(int $id)
    {
        $fornecedor = $this->model->getFornecedorById($id);

        if (!$fornecedor) {
            $this->setFlashMessage('error', 'Fornecedor não encontrado.');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        // Busca dados relacionados
        $contratos = $this->contratosModel->getContratosByPessoaId($id);
        
        // Paginação do Histórico Financeiro
        $paginaAtualFinanceiro = filter_input(INPUT_GET, 'page_fin', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtualFinanceiro - 1) * $itensPorPagina;
        
        $historicoCompras = $this->financialModel->getTransacoesPorPessoaId($id, 'P', $itensPorPagina, $offset);
        $totalTransacoes = $this->financialModel->getCountTransacoesPorPessoaId($id, 'P');
        $totalPaginasFinanceiro = ceil($totalTransacoes / $itensPorPagina);
        
        $ocorrencias = $this->model->getOcorrenciasByFornecedorId($id);

        $data = [
            'pageTitle' => 'Detalhes do Fornecedor',
            'fornecedor' => $fornecedor,
            'contratos' => $contratos,
            'historicoCompras' => $historicoCompras,
            'paginaAtualFinanceiro' => $paginaAtualFinanceiro,
            'totalPaginasFinanceiro' => $totalPaginasFinanceiro,
            'ocorrencias' => $ocorrencias,
        ];

        $this->renderView('fornecedores/detalhe', $data);
    }

    /**
     * Salva um novo fornecedor ou atualiza um existente.
     */
    public function salvar()
    {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            } else {
                header('Location: ' . BASE_URL . '/fornecedores');
            }
            exit();
        }

        // Coleta e sanitiza os dados do formulário
        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS), // Razão Social
            'cnpj_cpf' => preg_replace('/\D/', '', $_POST['cnpj'] ?? ''), // Sanitiza para apenas números e mapeia para a coluna correta
            'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'tipo_pessoa' => filter_input(INPUT_POST, 'tipo_pessoa', FILTER_SANITIZE_SPECIAL_CHARS),
            'nome_fantasia' => filter_input(INPUT_POST, 'nome_fantasia', FILTER_SANITIZE_SPECIAL_CHARS),
            'categoria_fornecimento' => filter_input(INPUT_POST, 'categoria_fornecimento', FILTER_SANITIZE_SPECIAL_CHARS),
            'ie_isento' => isset($_POST['ie_isento']) ? 1 : 0,
            'motivo_inativacao' => filter_input(INPUT_POST, 'motivo_inativacao', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_inativacao' => filter_input(INPUT_POST, 'data_inativacao') ?: null,
            'inscricao_estadual' => filter_input(INPUT_POST, 'inscricao_estadual', FILTER_SANITIZE_SPECIAL_CHARS),
            'inscricao_municipal' => filter_input(INPUT_POST, 'inscricao_municipal', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        // Validação de duplicidade de CNPJ/CPF para garantir integridade da base
        if (!empty($dados['cnpj_cpf']) && $this->model->cnpjExiste($dados['cnpj_cpf'], $dados['id'])) {
            $msg = "O documento '{$dados['cnpj_cpf']}' já está cadastrado para outro fornecedor.";
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
            } else {
                $this->setFlashMessage('error', $msg);
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/fornecedores'));
            }
            exit();
        }

        // Processamento dos campos JSON
        
        // 1. Endereço
        $endereco = $_POST['endereco'] ?? [];
        $dados['endereco_json'] = !empty($endereco) ? json_encode($endereco, JSON_UNESCAPED_UNICODE) : null;

        // 2. Contato
        $contato = $_POST['contato'] ?? [];
        $dados['contato_json'] = !empty($contato) ? json_encode($contato, JSON_UNESCAPED_UNICODE) : null;

        // 3. Dados Financeiros
        $dadosFinanceiros = $_POST['dados_financeiros'] ?? [];
        $dados['dados_financeiros_json'] = !empty($dadosFinanceiros) ? json_encode($dadosFinanceiros, JSON_UNESCAPED_UNICODE) : null;

        // 4. Informações Comerciais
        $infoComerciais = $_POST['info_comerciais'] ?? [];
        $dados['info_comerciais_json'] = !empty($infoComerciais) ? json_encode($infoComerciais, JSON_UNESCAPED_UNICODE) : null;

        // 5. Documentação (Uploads)
        $documentacao = [];
        // Se estiver editando, busca os documentos atuais para mesclar e não perder arquivos
        if ($dados['id']) {
            $fornecedorAtual = $this->model->getFornecedorById($dados['id']);
            if ($fornecedorAtual && !empty($fornecedorAtual['documentacao_json'])) {
                $documentacao = json_decode($fornecedorAtual['documentacao_json'], true) ?? [];
            }
        }

        if (isset($_FILES['documentacao'])) {
            $uploadDir = ROOT_PATH . '/storage/fornecedores/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

            foreach ($_FILES['documentacao']['name'] as $key => $value) {
                if (is_array($value)) {
                    // Trata campos 'multiple' (ex: certidões)
                    if (!isset($documentacao[$key])) $documentacao[$key] = [];
                    foreach ($value as $index => $filename) {
                        if ($_FILES['documentacao']['error'][$key][$index] === UPLOAD_ERR_OK) {
                            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            $newFilename = "doc_{$key}_{$index}_" . uniqid() . ".{$ext}";
                            if (move_uploaded_file($_FILES['documentacao']['tmp_name'][$key][$index], $uploadDir . $newFilename)) {
                                $documentacao[$key][] = $newFilename;
                            }
                        }
                    }
                } else {
                    // Trata campos simples
                    if ($_FILES['documentacao']['error'][$key] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));
                        $newFilename = "doc_{$key}_" . uniqid() . ".{$ext}";
                        if (move_uploaded_file($_FILES['documentacao']['tmp_name'][$key], $uploadDir . $newFilename)) {
                            $documentacao[$key] = $newFilename;
                        }
                    }
                }
            }
        }
        $dados['documentacao_json'] = !empty($documentacao) ? json_encode($documentacao, JSON_UNESCAPED_UNICODE) : null;

        try {
            $result = $this->model->salvarFornecedor($dados);
            if ($result) {
                // Se for insert e não temos o ID retornado diretamente, tentamos recuperá-lo
                $newId = $dados['id'];
                if (!$newId) {
                    if (is_numeric($result) && $result > 0) {
                        $newId = $result;
                    } else {
                        // Fallback: busca pelo nome
                        $db = Connection::getInstance();
                        $stmt = $db->prepare("SELECT id FROM fornecedores WHERE nome = ? ORDER BY id DESC LIMIT 1");
                        $stmt->execute([$dados['nome']]);
                        $newId = $stmt->fetchColumn();
                    }
                }

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Fornecedor salvo com sucesso!', 'data' => ['id' => $newId, 'nome' => $dados['nome']]]);
                    exit();
                } else {
                    $message = $dados['id'] ? 'Fornecedor atualizado com sucesso!' : 'Fornecedor cadastrado com sucesso!';
                    $this->setFlashMessage('success', $message);
                }
            } else {
                $msg = 'Ocorreu um erro desconhecido ao salvar o fornecedor.';
                if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit(); }
                $this->setFlashMessage('error', $msg);
            }
        } catch (\PDOException $e) {
            // Captura o erro do banco de dados e exibe uma mensagem mais informativa.
            $msg = 'Erro de Banco de Dados: ' . $e->getMessage();
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit(); }
            $this->setFlashMessage('error', $msg);
        }

        header('Location: ' . BASE_URL . '/fornecedores');
        exit();
    }

    /**
     * Salva uma nova ocorrência para um fornecedor.
     */
    public function salvarOcorrencia()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        $fornecedor_id = filter_input(INPUT_POST, 'fornecedor_id', FILTER_VALIDATE_INT);

        if (!$fornecedor_id) {
            $this->setFlashMessage('error', 'ID do fornecedor inválido.');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        $dados = [
            'fornecedor_id' => $fornecedor_id,
            'data_ocorrencia' => filter_input(INPUT_POST, 'data_ocorrencia'),
            'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS),
            'responsavel' => filter_input(INPUT_POST, 'responsavel', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        if ($this->model->salvarOcorrencia($dados)) {
            $this->setFlashMessage('success', 'Ocorrência registrada com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao registrar a ocorrência.');
        }

        header('Location: ' . BASE_URL . '/fornecedores/detalhe/' . $fornecedor_id);
        exit();
    }

    /**
     * Exclui um fornecedor.
     * @param int $id O ID do fornecedor.
     */
    public function excluir($id = null)
    {
        // Encaminha para o método arquivar para manter compatibilidade com links antigos
        return $this->arquivar($id);
    }

    /**
     * Arquiva um fornecedor (Soft Delete).
     * @param int $id O ID do fornecedor.
     */
    public function arquivar($id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida.');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        // 2. Validação do token CSRF
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) { // Adicionado CSRF
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        // Captura de ID robusta: tenta parâmetro da rota, depois POST, depois URL segment
        $id = (int)$id;
        if ($id <= 0) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        }
        
        if ($id <= 0) {
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $segments = explode('/', trim($path, '/'));
            $last = end($segments);
            if (is_numeric($last)) $id = (int)$last;
        }
        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID de fornecedor inválido.');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        // Verifica se há contratos ativos vinculados antes de tentar excluir
        $contratos = $this->contratosModel->getContratosByPessoaId($id);
        foreach ($contratos as $contrato) {
            if ($contrato['status'] === 'Em Vigência') {
                $this->setFlashMessage('error', 'Não é possível arquivar: O fornecedor possui contratos ativos (Em Vigência).');
                header('Location: ' . BASE_URL . '/fornecedores');
                exit();
            }
        }

        // Verifica se há pagamentos pendentes vinculados (Garantia de integridade financeira)
        if ($this->financialModel->temTransacoesPendentes($id, 'fornecedor_id', 'P')) {
            $this->setFlashMessage('error', 'Não é possível arquivar: O fornecedor possui pagamentos pendentes no financeiro (Contas a Pagar).');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        if ($this->model->arquivarFornecedor($id)) {
            $this->setFlashMessage('success', 'Fornecedor arquivado com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao arquivar o fornecedor.');
        }

        header('Location: ' . BASE_URL . '/fornecedores');
        exit();
    }

    /**
     * Restaura um fornecedor inativo.
     * @param int $id O ID do fornecedor.
     */
    public function restaurar($id = null)
    {
        // Encaminha para o método ativar para manter compatibilidade com links antigos
        return $this->ativar($id);
    }

    /**
     * Ativa um fornecedor inativo.
     * @param int $id O ID do fornecedor.
     */
    public function ativar($id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida.');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        // 2. Validação do token CSRF
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) { // Adicionado CSRF
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        // Captura de ID robusta: tenta parâmetro da rota, depois POST, depois URL segment
        $id = (int)$id;
        if ($id <= 0) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        }

        if ($id <= 0) {
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $segments = explode('/', trim($path, '/'));
            $last = end($segments);
            if (is_numeric($last)) $id = (int)$last;
        }

        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID de fornecedor inválido.');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        if ($this->model->restaurarFornecedor($id)) {
            $this->setFlashMessage('success', 'Fornecedor restaurado com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao restaurar o fornecedor.');
        }

        header('Location: ' . BASE_URL . '/fornecedores/detalhe/' . $id);
        exit();
    }

    /**
     * Busca o HTML do formulário para um novo fornecedor (usado via AJAX).
     */
    public function getFormForNew()
    {
        $data = [
            'fornecedor' => null, // Garante que o formulário esteja no modo de criação
        ];

        // Renderiza apenas o formulário, sem o template principal
        $this->renderPartial('fornecedores/form', $data);
    }

    /**
     * Exporta a lista de fornecedores para PDF respeitando os filtros atuais.
     */
    public function exportarPdf()
    {
        // Verificação Sênior: Garante que a biblioteca existe antes de instanciar
        if (!class_exists('\Dompdf\Dompdf')) {
            $this->setFlashMessage('error', 'Biblioteca de exportação PDF não encontrada. Execute: composer require dompdf/dompdf');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        // Aumenta recursos para gerar arquivos maiores
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $filtros = [
            'busca'  => filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS)
        ];

        // Busca técnica sem paginação (limite alto de segurança)
        $fornecedores = $this->model->getFornecedores($filtros, 10000, 0);
        $empresa      = $this->empresaModel->getDadosEmpresa();

        // Busca a imagem de papel timbrado e converte para Base64 para garantir renderização estável
        $bgImage = null;
        $bgPath = ROOT_PATH . '/public/assets/img/papel_timbrado.png';
        if (file_exists($bgPath)) {
            $type = pathinfo($bgPath, PATHINFO_EXTENSION);
            $imgContent = file_get_contents($bgPath);
            $bgImage = 'data:image/' . $type . ';base64,' . base64_encode($imgContent);
        }

        $data = [
            'fornecedores' => $fornecedores,
            'filtros'      => $filtros,
            'empresa'      => $empresa,
            'dataGeracao'  => date('d/m/Y H:i:s'),
            'bg_image'     => $bgImage
        ];

        ob_start();
        $this->renderPartial('fornecedores/relatorio_pdf', $data);
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("fornecedores_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
        exit();
    }

    /**
     * Exporta a lista de fornecedores para Excel (XLSX).
     */
    public function exportarExcel()
    {
        // Verificação Sênior: Garante que a biblioteca existe antes de instanciar
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            $phpVer = PHP_VERSION;
            $pathCheck = file_exists(ROOT_PATH . '/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php') 
                ? "Pasta existe, mas autoloader falhou." 
                : "Pasta 'vendor/phpoffice' não encontrada no servidor.";
                
            $this->setFlashMessage('error', "Erro: Biblioteca Excel não carregada. PHP: $phpVer. Diag: $pathCheck");
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        // Aumenta recursos para gerar arquivos maiores
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $filtros = [
            'busca'  => filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS)
        ];

        $fornecedores = $this->model->getFornecedores($filtros, 10000, 0);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Fornecedores');

        $empresa = $this->empresaModel->getDadosEmpresa();

        // Cabeçalho institucional (Logo e Dados da Empresa)
        if (!empty($empresa['logo_path'])) {
            $logoPath = ROOT_PATH . '/public/uploads/logos/' . $empresa['logo_path'];
            if (file_exists($logoPath)) {
                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setPath($logoPath);
                $drawing->setHeight(50); // Altura em pixels
                $drawing->setCoordinates('A1');
                $drawing->setWorksheet($sheet);
            }
        }

        $sheet->setCellValue('B1', $empresa['razao_social'] ?? 'SysEnviCorp');
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('B2', 'Relatório de Fornecedores - Gestão de Conformidade');
        $sheet->setCellValue('B3', 'Gerado em: ' . date('d/m/Y H:i:s'));

        $headers = ['Razão Social', 'Nome Fantasia', 'CNPJ/CPF', 'Status', 'Categoria', 'Cidade', 'UF', 'Telefone', 'E-mail'];
        $sheet->fromArray($headers, NULL, 'A5');
        $sheet->getStyle('A5:I5')->getFont()->setBold(true);
        $sheet->getStyle('A5:I5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('EBF3FB');

        $rowNum = 6;
        foreach ($fornecedores as $f) {
            // Formatação do CNPJ/CPF
            $doc = preg_replace('/\D/', '', $f['cnpj_cpf'] ?? '');
            $docFormatado = $doc;
            if (strlen($doc) === 11) {
                $docFormatado = vsprintf('%s%s%s.%s%s%s.%s%s%s-%s%s', str_split($doc));
            } elseif (strlen($doc) === 14) {
                $docFormatado = vsprintf('%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s', str_split($doc));
            } else {
                $docFormatado = $f['cnpj_cpf'] ?: '—';
            }

            // Formatação do Telefone (Padrão: (00) 00000-0000)
            $tel = preg_replace('/\D/', '', $f['telefone'] ?? '');
            $telFormatado = $f['telefone'] ?: '—';
            if (strlen($tel) === 11) {
                $telFormatado = vsprintf('(%s%s) %s%s%s%s%s-%s%s%s%s', str_split($tel));
            } elseif (strlen($tel) === 10) {
                $telFormatado = vsprintf('(%s%s) %s%s%s%s-%s%s%s%s', str_split($tel));
            }

            $sheet->fromArray([$f['nome'], $f['nome_fantasia'], $docFormatado, $f['status'], $f['categoria_fornecimento'], $f['cidade'], $f['uf'], $telFormatado, $f['email']], NULL, 'A' . $rowNum);

            // Estilização semântica da linha baseada no Status
            $status = $f['status'] ?? '';
            $range = 'A' . $rowNum . ':I' . $rowNum;

            if ($status === 'Inativo') {
                // Fundo Vermelho claro e fonte vermelha escura
                $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFCEBEB');
                $sheet->getStyle($range)->getFont()->getColor()->setARGB('FFE24B4A');
            } elseif ($status === 'Em Homologação') {
                // Fundo Amarelo claro e fonte marrom/laranja
                $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFAEEDA');
                $sheet->getStyle($range)->getFont()->getColor()->setARGB('FFBA7517');
            } elseif ($status === 'Ativo') {
                // Fundo Verde claro e fonte verde escura
                $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE1F5EE');
                $sheet->getStyle($range)->getFont()->getColor()->setARGB('FF1D9E75');
            }

            $rowNum++;
        }

        // Auto-ajuste das colunas e definição de formato de texto
        foreach (range('A', 'I') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
        
        // Define as colunas C (CNPJ/CPF) e H (Telefone) como formato Texto puro
        $sheet->getStyle('C6:C' . ($rowNum - 1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('H6:H' . ($rowNum - 1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="fornecedores_' . date('Y-m-d') . '.xlsx"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}
