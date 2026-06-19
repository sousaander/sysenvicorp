<?php

namespace App\Controllers;

use App\Core\Connection;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\ContratosModel;
use App\Models\ClientesModel;
use App\Models\FornecedoresModel;
use App\Models\ProjetosModel;
use App\Models\FinancialModel; // Importa o FinancialModel
use App\Models\EmpresaModel;
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
    private $empresaModel;

    /**
     * Mapeia ações para as permissões necessárias.
     * @var array
     */
    protected $requiredPermissions = [
        'index'                         => 'contratos_view',
        'configuracoes'                 => 'contratos_edit',
        'salvarConfiguracoes'           => 'contratos_edit',
        'wizard'                        => 'contratos_create',
        'clonar'                        => 'contratos_create',
        'novo'                          => 'contratos_create',
        'salvar'                        => 'contratos_create',
        'detalhe'                       => 'contratos_view',
        'excluir'                       => 'contratos_delete',
        'exportar'                      => 'contratos_view',
        'gerarPdfFinal'                 => 'contratos_view',
        'gerarPdfWizard'                => 'contratos_view',
        'vigencia'                      => 'contratos_view',
        'obrigacoes'                    => 'contratos_obrigacoes_manage',
        'financeiro'                    => 'contratos_financeiro_manage',
        'compliance'                    => 'contratos_view',
        'gerenciarCompliance'           => 'contratos_compliance_manage',
        'salvarCompliance'              => 'contratos_compliance_manage',
        'relatorios'                    => 'contratos_view',
        'exportarRelatorioVigenciaPdf'  => 'contratos_view',
        'salvarAditivo'                 => 'contratos_edit',
        'excluirAditivo'                => 'contratos_delete',
        'gerenciarObrigacoes'           => 'contratos_obrigacoes_manage',
        'salvarObrigacao'               => 'contratos_obrigacoes_manage',
        'atualizarStatusObrigacao'      => 'contratos_obrigacoes_manage',
        'excluirObrigacao'              => 'contratos_obrigacoes_manage',
        'gerenciarFinanceiro'           => 'contratos_financeiro_manage',
        'salvarParcela'                 => 'contratos_financeiro_manage',
        'lancarParcela'                 => 'contratos_financeiro_manage',
        'processarAlerta'               => 'contratos_view',
        'processarUpload'               => 'contratos_create',
        'download'                      => 'contratos_view',
        'getContratoDados'              => 'contratos_view',
        'removerDocumento'              => 'contratos_delete',
        'enviarParaAssinatura'          => 'contratos_edit',
        'assinarDigitalmente'           => '*',
        'relatorioCompliance'           => 'contratos_view',
        'exportarJson'                  => 'contratos_view',
    ];

    public function __construct()
    {
        parent::__construct();

        // Libera a ação pública de assinatura
        $action = $this->getCurrentActionName();
        if ($action === 'assinarDigitalmente' && !$this->session->isAuthenticated()) {
            // Permite acesso público sem redirecionar para login
        }

        $this->model = new ContratosModel(); // Correção
        $this->clientesModel = new ClientesModel(); // Correção
        $this->fornecedoresModel = new FornecedoresModel(); // Correção
        $this->projetosModel = new ProjetosModel(); // Correção
        $this->financialModel = new FinancialModel(); // Correção
        $this->empresaModel = new EmpresaModel();
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getContratosSummary();
        $settings = $this->model->getSettings();

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
            'settings' => $settings,
        ], $summary);

        $this->renderView('contratos/index', $data);
    }

    /**
     * Exibe a página de configurações de modelos de contrato.
     */
    public function configuracoes()
    {
        $settings = $this->model->getSettings();
        $data = [
            'pageTitle' => 'Configurações e Modelos de Contrato',
            'settings' => $settings
        ];
        $this->renderView('contratos/configuracoes', $data);
    }

    /**
     * Salva as configurações do módulo.
     */
    public function salvarConfiguracoes()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/contratos/configuracoes');
            exit();
        }

        $settings = [
            'modelo_padrao' => $_POST['modelo_padrao'] ?? '',
            'modelo_responsabilidades_contratante' => $_POST['modelo_responsabilidades_contratante'] ?? '',
            'modelo_responsabilidades_contratado' => $_POST['modelo_responsabilidades_contratado'] ?? '',
            'modelo_clausulas_adicionais' => $_POST['modelo_clausulas_adicionais'] ?? ''
        ];

        if ($this->model->saveSettings($settings)) {
            $this->setFlashMessage('success', 'Configurações atualizadas com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar configurações.');
        }
        header('Location: ' . BASE_URL . '/contratos/configuracoes');
        exit();
    }

    /**
     * Exibe o formulário para criar um novo contrato.
     */
    public function novo()
    {
        // Redireciona para o Wizard de criação
        header('Location: ' . BASE_URL . '/contratos/wizard');
        exit();
    }

    /**
     * Fluxo de criação/edição em 3 etapas (Wizard).
     */
    public function wizard($id = null)
    {
        // Permitir id=0 corretamente. Somente null ou string vazia devem ser tratados como não informados.
        if ($id === null || $id === '') {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($id === false) {
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            }
        }

        if ($id === null || $id === '') {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $segments = explode('/', trim($path, '/'));
            foreach (array_reverse($segments) as $segment) {
                if (is_numeric($segment)) {
                    $id = (int)$segment;
                    break;
                }
            }
        }

        $id = ($id === null || $id === '') ? null : (int)$id;

        $contrato = null;
        if ($id !== null) {
            $contrato = $this->model->getContratoById($id);

            // Bloqueia edição de contratos finalizados no Wizard
            if ($contrato && $contrato['status'] === 'Finalizado') {
                $this->setFlashMessage('info', 'Contratos com status "Finalizado" não podem ser editados.');
                header('Location: ' . BASE_URL . '/contratos');
                exit();
            }
        }
        
        $isEdit = ($contrato !== null);

        // Se for um novo contrato, gera o número sequencial sugerido (ex: CTR-2026-0001)
        if (!$isEdit) {
            $contrato = [
                'numero_contrato' => $this->model->getNextContractNumber(),
                'status'          => 'Rascunho' // Status padrão sugerido
            ];
        }

        $settings = $this->model->getSettings();
        $clientes = $this->clientesModel->getAllClientes() ?? [];
        $fornecedores = $this->fornecedoresModel->getAllFornecedores() ?? [];
        $projetos = $this->projetosModel->getProjetos([], 999, 0) ?? [];

        $data = [
            'pageTitle' => $isEdit ? 'Editar Contrato' : 'Novo Contrato',
            'contrato' => $contrato,
            'clientes' => $clientes,
            'fornecedores' => $fornecedores,
            'projetos' => $projetos,
            'settings' => $settings,
            'isEdit' => $isEdit,
            'baseUrl' => defined('BASE_URL') ? BASE_URL : ''
        ];

        $this->renderView('contratos/form', $data);
    }

    /**
     * Carrega o formulário com os dados de um contrato existente para clonagem.
     * @param int $id O ID do contrato a ser clonado.
     */
    public function clonar($id)
    {
        $id = (int)$id;
        $contratoOriginal = $this->model->getContratoById($id);

        if (!$contratoOriginal) {
            $this->setFlashMessage('error', 'Contrato original não encontrado para clonagem.');
            header('Location: ' . BASE_URL . '/contratos');
            exit();
        }

        // Validação de Segurança: Impede a clonagem de contratos cancelados
        if ($contratoOriginal['status'] === 'Cancelado') {
            $this->setFlashMessage('error', 'Não é permitido duplicar um contrato com status "Cancelado".');
            header('Location: ' . BASE_URL . '/contratos');
            exit();
        }

        // Prepara os dados para o novo contrato clonado
        $contratoClonado = $contratoOriginal;
        unset($contratoClonado['id']); // Remove o ID para que o salvamento gere um novo registro
        $contratoClonado['cloned_from_id'] = $id; // Identifica a origem para duplicar parcelas depois
        unset($contratoClonado['dataCriacao']);
        
        $contratoClonado['titulo'] = ($contratoOriginal['titulo'] ?? '') . ' (Cópia)';
        $contratoClonado['numero_contrato'] = $this->model->getNextContractNumber();
        $contratoClonado['status'] = 'Rascunho'; // Define como rascunho por segurança
        $contratoClonado['documento_path'] = null; // Não clona o arquivo PDF/DOCX físico

        $data = [
            'pageTitle' => 'Clonar Contrato',
            'contrato' => $contratoClonado,
            'clientes' => $this->clientesModel->getAllClientes() ?? [],
            'fornecedores' => $this->fornecedoresModel->getAllFornecedores() ?? [],
            'projetos' => $this->projetosModel->getProjetos([], 999, 0) ?? [],
            'settings' => $this->model->getSettings(),
            'isEdit' => false, // Importante: força o formulário a se comportar como uma nova criação
            'baseUrl' => BASE_URL
        ];

        $this->renderView('contratos/form', $data);
    }

    /**
     * Gera o PDF final baseado no conteúdo enviado pelo Wizard (Etapa 3).
     */
    public function gerarPdfWizard()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/contratos');
            exit();
        }

        $objeto = $_POST['objeto'] ?? '';
        $tipo = $_POST['tipo'] ?? 'Contrato';
        
        // Busca o nome do projeto para exibição no cabeçalho da prévia
        $projetoNome = '';
        if (!empty($_POST['projeto_id'])) {
            $proj = $this->projetosModel->getProjetoById((int)$_POST['projeto_id']);
            $projetoNome = $proj['nome'] ?? '';
        }

        $data = [
            'objeto' => $objeto,
            'tipo' => $tipo,
            'empresa' => $this->empresaModel->getDadosEmpresa(),
            'dataGeracao' => date('d/m/Y H:i'),
            'status' => 'Rascunho',
            'contrato' => [
                'projeto_nome' => $projetoNome,
                'contratante_nome' => $_POST['contratante_nome'] ?? '',
                'contratante_documento' => $_POST['contratante_documento'] ?? '',
                'contratado_nome' => $_POST['contratado_nome'] ?? '',
                'contratado_documento' => $_POST['contratado_documento'] ?? '',
                'contratante_endereco' => $_POST['contratante_endereco'] ?? '',
                'contratante_email' => $_POST['contratante_email'] ?? '',
                'contratado_endereco' => $_POST['contratado_endereco'] ?? '',
                'contratado_email' => $_POST['contratado_email'] ?? '',
                'pix_tipo_chave' => $_POST['pix_tipo_chave'] ?? '',
                'base_referencia' => $_POST['base_referencia'] ?? '',
                'local_assinatura' => $_POST['local_assinatura'] ?? '',
            ]
        ];

        ob_start();
        $this->renderPartial('contratos/pdf_final', $data);
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        ini_set('pcre.backtrack_limit', '5000000');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $dompdf->stream("Contrato_Previa_" . date('Ymd_His') . ".pdf", ["Attachment" => false]);
        exit();
    }

    /**
     * Gera o PDF final de um contrato já salvo no banco de dados.
     */
    public function gerarPdfFinal($id)
    {
        $id = (int)$id;
        $contrato = $this->model->getContratoById($id);

        if (!$contrato) {
            $this->setFlashMessage('error', 'Contrato não encontrado.');
            header('Location: ' . BASE_URL . '/contratos');
            exit();
        }

        $data = [
            'objeto' => $contrato['objeto'],
            'tipo' => $contrato['tipo'],
            'status' => $contrato['status'],
            'contrato' => $contrato,
            'empresa' => $this->empresaModel->getDadosEmpresa(),
            'dataGeracao' => date('d/m/Y H:i')
        ];

        ob_start();
        $this->renderPartial('contratos/pdf_final', $data);
        $html = ob_get_clean();

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        ini_set('pcre.backtrack_limit', '5000000');
        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Contrato_Final_{$id}.pdf", ["Attachment" => false]);
        exit();
    }

    /**
     * Envia o link para assinatura digital ao cliente via e-mail.
     */
    public function enviarParaAssinatura($id)
    {
        $id = (int)$id;
        $contrato = $this->model->getContratoById($id);

        if (!$contrato || empty($contrato['contratante_email'])) {
            $this->setFlashMessage('error', 'Contrato não encontrado ou e-mail do contratante não definido.');
            header('Location: ' . BASE_URL . '/contratos');
            exit();
        }

        // Gera token de segurança único e validade de 7 dias
        $token = bin2hex(random_bytes(32));
        $validade = date('Y-m-d H:i:s', strtotime('+7 days'));

        if ($this->model->saveSignatureToken($id, $token, $validade)) {
            $link = BASE_URL . "/contratos/assinarDigitalmente/" . $token;

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = defined('MAIL_HOST') ? MAIL_HOST : 'localhost';
                $mail->SMTPAuth   = true;
                $mail->Username   = defined('MAIL_USERNAME') ? MAIL_USERNAME : '';
                $mail->Password   = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '';
                $mail->SMTPSecure = (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = defined('MAIL_PORT') ? MAIL_PORT : 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
                $mail->addAddress($contrato['contratante_email'], $contrato['contratante_nome']);

                $mail->isHTML(true);
                $mail->Subject = "Assinatura Digital de Instrumento: " . ($contrato['titulo'] ?: 'Contrato');
                
                $corpo = "<div style='font-family: sans-serif; color: #333;'>";
                $corpo .= "<h2>Olá, " . htmlspecialchars($contrato['contratante_nome']) . "</h2>";
                $corpo .= "<p>O contrato <strong>" . htmlspecialchars($contrato['titulo']) . "</strong> está disponível para sua conferência e assinatura eletrônica.</p>";
                $corpo .= "<p>Para assinar, clique no link seguro abaixo:</p>";
                $corpo .= "<p style='margin: 30px 0; text-align:center;'>";
                $corpo .= "<a href='$link' style='background:#1B4F8C; color:#fff; padding:15px 30px; text-decoration:none; border-radius:8px; font-weight:bold;'>VISUALIZAR E ASSINAR DOCUMENTO</a>";
                $corpo .= "</p>";
                $corpo .= "<p style='font-size:12px; color:#999;'>Este link expirará em 7 dias.</p>";
                $corpo .= "<br><p>Atenciosamente,<br>Equipe " . MAIL_FROM_NAME . "</p>";
                $corpo .= "</div>";
                
                $mail->Body = $corpo;
                $mail->send();

                // Atualiza status para Pendência Assinatura
                $this->model->atualizarStatus($id, 'Pendência Assinatura');

                $this->setFlashMessage('success', 'Convite enviado com sucesso para ' . $contrato['contratante_email']);
            } catch (Exception $e) {
                $this->setFlashMessage('error', 'Erro ao disparar e-mail: ' . $mail->ErrorInfo);
            }
        } else {
            $this->setFlashMessage('error', 'Falha técnica ao gerar link de assinatura.');
        }

        header('Location: ' . BASE_URL . '/contratos');
        exit();
    }

    /**
     * Página pública para aceite do contrato pelo cliente.
     */
    public function assinarDigitalmente($token)
    {
        $contrato = $this->model->getContratoByToken($token);

        if (!$contrato) {
            die("<div style='text-align:center; padding:100px; font-family:sans-serif;'><h1>Link Inválido</h1><p>Este link expirou ou o contrato já foi assinado.</p></div>");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ip = $_SERVER['REMOTE_ADDR'];
            if ($this->model->marcarComoAssinado($contrato['id'], $ip)) {
                
                // Notifica o gestor interno via sistema
                $this->notificacoesModel->criarNotificacao(
                    1, 
                    'Contrato Assinado Digitalmente',
                    "O cliente " . $contrato['contratante_nome'] . " assinou o contrato #{$contrato['id']}.",
                    BASE_URL . "/contratos/detalhe/{$contrato['id']}"
                );

                echo "<div style='text-align:center; padding:100px; font-family:sans-serif;'><h1>Assinado!</h1><p>Obrigado. O contrato foi validado com sucesso.</p></div>";
                exit();
            }
        }

        // Interface simples de visualização pública
        echo "
        <div style='max-width:800px; margin: 40px auto; font-family: sans-serif; border: 1px solid #ddd; padding: 40px; background:#fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);'>
            <h2 style='color:#1B4F8C;'>Conferência de Contrato</h2>
            <p><strong>Título:</strong> ".htmlspecialchars($contrato['titulo'])."</p>
            <div style='background:#f9f9f9; padding:20px; border:1px dashed #ccc; height:350px; overflow-y:auto; margin-bottom:20px; white-space: pre-wrap;'>".htmlspecialchars($contrato['objeto'])."</div>
            <form method='POST' style='text-align:center; border-top:1px solid #eee; padding-top:20px;'>
                <p>Ao clicar abaixo, você concorda com todos os termos e realiza a <strong>Assinatura Eletrônica</strong>.</p>
                <button type='submit' style='background:#22A05B; color:#fff; border:none; padding:18px 40px; font-weight:bold; cursor:pointer; border-radius:8px; font-size:16px;'>CONFIRMAR ASSINATURA DIGITAL</button>
                <p style='font-size:11px; color:#999; margin-top:15px;'>Seu IP: ". $_SERVER['REMOTE_ADDR'] ."</p>
            </form>
        </div>
        ";
        exit();
    }

    /**
     * Retorna o modelo padrão em JSON para o editor.
     */
    public function getModeloPadrao()
    {
        header('Content-Type: application/json');
        $settings = $this->model->getSettings();
        echo json_encode(['modelo' => $settings['modelo_padrao'] ?? '']);
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

        // Validação rigorosa do formato do número do contrato (CTR-YYYY-NNN)
        $numeroContrato = $_POST['numero_contrato'] ?? '';
        if (!empty($numeroContrato) && !preg_match('/^CTR-\d{4}-\d{3}$/', $numeroContrato)) {
            $this->setFlashMessage('error', 'Formato inválido! O campo "Número / Código" deve seguir o padrão CTR-YYYY-NNN (ex: CTR-2026-001).');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/contratos'));
            exit();
        }

        // Verificação de duplicidade do número do contrato
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
        if (!empty($numeroContrato) && $this->model->numeroContratoExiste($numeroContrato, $id)) {
            $this->setFlashMessage('error', "O número de contrato '{$numeroContrato}' já está em uso por outro registro.");
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/contratos'));
            exit();
        }

        // Coleta e sanitiza os dados do formulário
        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'numero_contrato' => filter_input(INPUT_POST, 'numero_contrato', FILTER_SANITIZE_SPECIAL_CHARS),
            'base_referencia' => filter_input(INPUT_POST, 'base_referencia', FILTER_SANITIZE_SPECIAL_CHARS),
            'numero_contrato_cliente' => filter_input(INPUT_POST, 'numero_contrato_cliente', FILTER_SANITIZE_SPECIAL_CHARS),
            'titulo' => filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS),
            'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'foro_eleicao' => filter_input(INPUT_POST, 'foro_eleicao', FILTER_SANITIZE_SPECIAL_CHARS),
            'lei_aplicavel' => filter_input(INPUT_POST, 'lei_aplicavel', FILTER_SANITIZE_SPECIAL_CHARS),
            'resolucao_disputas' => filter_input(INPUT_POST, 'resolucao_disputas', FILTER_SANITIZE_SPECIAL_CHARS),
            'local_assinatura' => filter_input(INPUT_POST, 'local_assinatura', FILTER_SANITIZE_SPECIAL_CHARS),

            'contratante_nome' => filter_input(INPUT_POST, 'contratante_nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'contratante_documento' => filter_input(INPUT_POST, 'contratante_documento', FILTER_SANITIZE_SPECIAL_CHARS),
            'cliente_id' => filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT) ?: null,
            'pessoa_id' => filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT) ?: null,
            'contratante_endereco' => filter_input(INPUT_POST, 'contratante_endereco', FILTER_SANITIZE_SPECIAL_CHARS),
            'contratante_email' => filter_input(INPUT_POST, 'contratante_email', FILTER_SANITIZE_EMAIL),
            'contratante_telefone' => filter_input(INPUT_POST, 'contratante_telefone', FILTER_SANITIZE_SPECIAL_CHARS),
            'contratante_representante' => filter_input(INPUT_POST, 'contratante_representante', FILTER_SANITIZE_SPECIAL_CHARS),
            'contratante_rg_cpf_rep' => filter_input(INPUT_POST, 'contratante_rg_cpf_rep', FILTER_SANITIZE_SPECIAL_CHARS),

            'contratado_nome' => filter_input(INPUT_POST, 'contratado_nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'contratado_documento' => filter_input(INPUT_POST, 'contratado_documento', FILTER_SANITIZE_SPECIAL_CHARS),
            'contratado_endereco' => filter_input(INPUT_POST, 'contratado_endereco', FILTER_SANITIZE_SPECIAL_CHARS),
            'contratado_email' => filter_input(INPUT_POST, 'contratado_email', FILTER_SANITIZE_EMAIL),
            'contratado_telefone' => filter_input(INPUT_POST, 'contratado_telefone', FILTER_SANITIZE_SPECIAL_CHARS),
            'contratado_representante' => filter_input(INPUT_POST, 'contratado_representante', FILTER_SANITIZE_SPECIAL_CHARS),
            'contratado_rg_cpf_rep' => filter_input(INPUT_POST, 'contratado_rg_cpf_rep', FILTER_SANITIZE_SPECIAL_CHARS),

            'valor' => !empty($_POST['valor']) ? (float)str_replace(['.', ','], ['', '.'], $_POST['valor']) : null,
            'valor_sinal' => !empty($_POST['valor_sinal']) ? (float)str_replace(['.', ','], ['', '.'], $_POST['valor_sinal']) : 0.0,
            'forma_pagamento' => filter_input(INPUT_POST, 'forma_pagamento', FILTER_SANITIZE_SPECIAL_CHARS),
            'dados_bancarios' => filter_input(INPUT_POST, 'dados_bancarios', FILTER_SANITIZE_SPECIAL_CHARS),
            'condicao_pagamento' => filter_input(INPUT_POST, 'condicao_pagamento', FILTER_SANITIZE_SPECIAL_CHARS),
            'dia_vencimento' => filter_input(INPUT_POST, 'dia_vencimento', FILTER_VALIDATE_INT),
            'numero_parcelas' => filter_input(INPUT_POST, 'numero_parcelas', FILTER_VALIDATE_INT),
            'multa_atraso' => filter_input(INPUT_POST, 'multa_atraso', FILTER_VALIDATE_FLOAT),
            'pix_tipo_chave' => filter_input(INPUT_POST, 'pix_tipo_chave', FILTER_SANITIZE_SPECIAL_CHARS),
            'juros_mora' => filter_input(INPUT_POST, 'juros_mora', FILTER_VALIDATE_FLOAT),
            'correcao_monetaria' => filter_input(INPUT_POST, 'correcao_monetaria', FILTER_SANITIZE_SPECIAL_CHARS),
            'prazo_carencia_multa' => filter_input(INPUT_POST, 'prazo_carencia_multa', FILTER_VALIDATE_INT),
            'penalidade_descumprimento' => filter_input(INPUT_POST, 'penalidade_descumprimento', FILTER_SANITIZE_SPECIAL_CHARS),
            'multa_rescisao_antecipada' => filter_input(INPUT_POST, 'multa_rescisao_antecipada', FILTER_SANITIZE_SPECIAL_CHARS),
            'observacoes_financeiras' => filter_input(INPUT_POST, 'observacoes_financeiras', FILTER_SANITIZE_SPECIAL_CHARS),
            
            'confidencialidade_tags' => $_POST['confidencialidade_tags'] ?? null,
            'prazo_sigilo' => filter_input(INPUT_POST, 'prazo_sigilo', FILTER_SANITIZE_SPECIAL_CHARS),
            'penalidade_violacao_sigilo' => filter_input(INPUT_POST, 'penalidade_violacao_sigilo', FILTER_SANITIZE_SPECIAL_CHARS),
            'dpo_encarregado' => filter_input(INPUT_POST, 'dpo_encarregado', FILTER_SANITIZE_SPECIAL_CHARS),
            'transferencia_internacional' => isset($_POST['transferencia_internacional']) ? 1 : 0,
            'subcontratacao_dados' => isset($_POST['subcontratacao_dados']) ? 1 : 0,
            'base_legal_lgpd' => filter_input(INPUT_POST, 'base_legal_lgpd', FILTER_SANITIZE_SPECIAL_CHARS),
            'lgpd_conformidade' => isset($_POST['lgpd_conformidade']) ? 1 : 0,
            'clausula_confidencialidade' => filter_input(INPUT_POST, 'clausula_confidencialidade', FILTER_SANITIZE_SPECIAL_CHARS),
            
            'aviso_previo_rescisao' => filter_input(INPUT_POST, 'aviso_previo_rescisao', FILTER_SANITIZE_SPECIAL_CHARS),
            'rescisao_descumprimento' => filter_input(INPUT_POST, 'rescisao_descumprimento', FILTER_SANITIZE_SPECIAL_CHARS),
            'nao_concorrencia' => filter_input(INPUT_POST, 'nao_concorrencia', FILTER_SANITIZE_SPECIAL_CHARS),
            'indenizacao_rescisao' => filter_input(INPUT_POST, 'indenizacao_rescisao', FILTER_SANITIZE_SPECIAL_CHARS),
            'causas_rescisao_imotivada' => filter_input(INPUT_POST, 'causas_rescisao_imotivada', FILTER_SANITIZE_SPECIAL_CHARS),
            'causas_justa_causa' => filter_input(INPUT_POST, 'causas_justa_causa', FILTER_SANITIZE_SPECIAL_CHARS),
            'obrigacoes_pos_encerramento' => filter_input(INPUT_POST, 'obrigacoes_pos_encerramento', FILTER_SANITIZE_SPECIAL_CHARS),
            'responsabilidades_contratante' => filter_input(INPUT_POST, 'responsabilidades_contratante', FILTER_SANITIZE_SPECIAL_CHARS),
            'responsabilidades_contratado' => filter_input(INPUT_POST, 'responsabilidades_contratado', FILTER_SANITIZE_SPECIAL_CHARS),
            'criterios_aceite' => filter_input(INPUT_POST, 'criterios_aceite', FILTER_SANITIZE_SPECIAL_CHARS),
            'renovacao_automatica' => filter_input(INPUT_POST, 'renovacao_automatica', FILTER_SANITIZE_SPECIAL_CHARS),
            
            'clausulas_adicionais' => filter_input(INPUT_POST, 'clausulas_adicionais', FILTER_SANITIZE_SPECIAL_CHARS),
            'assinatura_tipo' => filter_input(INPUT_POST, 'assinatura_tipo', FILTER_SANITIZE_SPECIAL_CHARS),
            'numero_vias' => filter_input(INPUT_POST, 'numero_vias', FILTER_SANITIZE_SPECIAL_CHARS),

            'data_inicio' => filter_input(INPUT_POST, 'data_inicio'),
            'vencimento' => filter_input(INPUT_POST, 'vencimento'),
            'duracao_meses' => filter_input(INPUT_POST, 'duracao_meses', FILTER_VALIDATE_INT),
            'observacoes' => filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS),
            'projeto_id' => filter_input(INPUT_POST, 'projeto_id', FILTER_VALIDATE_INT) ?: null,
            'objeto' => $_POST['objeto'] ?? '', 
        ];

        $clonedFromId = filter_input(INPUT_POST, 'cloned_from_id', FILTER_VALIDATE_INT);

        // Proteção extra no salvamento: impede alteração se o contrato já estiver finalizado
        if ($dados['id']) {
            $contratoAtual = $this->model->getContratoById($dados['id']);
            if ($contratoAtual && $contratoAtual['status'] === 'Finalizado') {
                $this->setFlashMessage('error', 'Erro: Este contrato já foi finalizado e não permite mais alterações.');
                header('Location: ' . BASE_URL . '/contratos');
                exit();
            }
        }

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
            $savedId = $this->model->salvarContrato($dados);
            if ($savedId) {
                // Se for um novo contrato vindo de uma clonagem, duplica as parcelas financeiras
                if (!$dados['id'] && $clonedFromId) {
                    $this->model->duplicarParcelas($clonedFromId, (int)$savedId);
                }

                $message = $dados['id'] ? 'Contrato atualizado com sucesso!' : 'Contrato cadastrado com sucesso e parcelas duplicadas!';
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
     * @param mixed $id O ID do contrato.
     */
    public function getFormForEdit($id = null)
    {
        try {
            // Impede o cache da resposta AJAX pelo navegador
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Permitir id=0 corretamente. Somente null ou string vazia devem ser tratados como não informados.
            if ($id === null || $id === '') {
                $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
                if ($id === false) {
                    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                }
            }

            if ($id === null || $id === '') {
                $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $segments = explode('/', trim($path, '/'));
                foreach (array_reverse($segments) as $segment) {
                    if (is_numeric($segment)) {
                        $id = (int)$segment;
                        break;
                    }
                }
            }

            if ($id === null || $id === '') {
                http_response_code(400);
                echo "ID do contrato não informado.";
                exit();
            }

            $id = (int)$id;

            // PONTO DE VERIFICAÇÃO 1: O ID foi identificado?
            error_log("ContratosController::getFormForEdit - ID detectado: " . var_export($id, true));

            $contrato = $this->model->getContratoById($id);

            if (!$contrato) {
                error_log("ContratosController::getFormForEdit - Contrato não encontrado para o ID: " . var_export($id, true));
                http_response_code(404);
                echo "Contrato não encontrado.";
                exit();
            }

            // Verificação de Coincidência de Nomes: Log das chaves retornadas pelo BD
            $colunasBD = array_keys($contrato);
            error_log("ContratosController::getFormForEdit - Colunas retornadas pelo BD: " . implode(', ', $colunasBD));

            // Bloqueia a abertura do formulário via Modal para contratos finalizados
            if ($contrato['status'] === 'Finalizado') {
                http_response_code(403);
                echo "Edição bloqueada: Este contrato já está finalizado.";
                exit();
            }

            $settings = $this->model->getSettings();
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
                'settings' => $settings,
                'isEdit' => true, // Força true pois este método é específico para edição
                'baseUrl' => BASE_URL
            ];

            // Renderiza apenas o formulário, sem o template principal
            $this->renderPartial('contratos/form', $data);
        } catch (Exception $e) {
            error_log("Erro em getFormForEdit: " . $e->getMessage());
            http_response_code(500);
            echo "Erro interno do servidor: " . $e->getMessage();
        }
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
            'empresa' => $this->empresaModel->getDadosEmpresa(),
        ];

        // 2. Captura o HTML da view do relatório em uma variável
        ob_start();
        $this->renderPartial('contratos/relatorio_vigencia_pdf', $data);
        $html = ob_get_clean();

        // 3. Configura e instancia o Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        ini_set('pcre.backtrack_limit', '5000000');
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
        $settings = $this->model->getSettings();

        $data = [
            'pageTitle' => 'Detalhes do Contrato',
            'contrato' => $contrato,
            'aditivos' => $aditivos,
            'clientes' => $clientes,
            'fornecedores' => $fornecedores,
            'projetos' => $projetos,
            'settings' => $settings,
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

        // --- Lógica de Envio de E-mail via PHPMailer ---
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = defined('MAIL_HOST') ? MAIL_HOST : 'localhost';
            $mail->SMTPAuth   = true;
            $mail->Username   = defined('MAIL_USERNAME') ? MAIL_USERNAME : '';
            $mail->Password   = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '';
            $mail->SMTPSecure = (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = defined('MAIL_PORT') ? MAIL_PORT : 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress(MAIL_ADMIN_RECIPIENT);

            $mail->isHTML(true);
            $mail->Subject = "Alerta de Renovação de Contrato: #" . $contrato['id'];
            
            $corpo = "<h1>Alerta de Renovação</h1>";
            $corpo .= "<p>Contrato ID: " . $contrato['id'] . "</p>";
            $corpo .= "<ul><li><strong>Objeto:</strong> " . htmlspecialchars($contrato['objeto']) . "</li>";
            $corpo .= "<li><strong>Vencimento:</strong> " . date('d/m/Y', strtotime($contrato['vencimento'])) . "</li></ul>";
            if (!empty($mensagemAdicional)) {
                $corpo .= "<p><strong>Mensagem:</strong> " . nl2br(htmlspecialchars($mensagemAdicional)) . "</p>";
            }
            
            $mail->Body = $corpo;
            $mail->send();
            $this->setFlashMessage('success', 'Alerta de renovação enviado com sucesso!');
        } catch (Exception $e) {
            error_log("Erro no alerta de contrato: {$mail->ErrorInfo}");
            $this->setFlashMessage('error', 'Erro ao enviar e-mail de alerta.');
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
            'empresa' => $this->empresaModel->getDadosEmpresa(),
        ];

        ob_start();
        $this->renderPartial('contratos/relatorio_compliance_pdf', $data);
        $html = ob_get_clean();

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        ini_set('pcre.backtrack_limit', '5000000');
        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("relatorio_compliance_{$tipo}_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
        exit();
    }

    /**
     * Busca dados de um CNPJ via BrasilAPI (Ajax).
     * @param string $cnpj
     */
    public function buscarCnpjAjax($cnpj)
    {
        header('Content-Type: application/json');
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            echo json_encode(['success' => false, 'message' => 'O CNPJ deve conter exatamente 14 dígitos para a busca.']);
            exit;
        }

        $urlBrasilApi = "https://brasilapi.com.br/api/cnpj/v1/{$cnpj}";
        $urlReceitaWs = "https://www.receitaws.com.br/v1/cnpj/{$cnpj}";

        try {
            // 1. Tenta BrasilAPI
            $res = $this->executarCurlCnpj($urlBrasilApi);

            if ($res['httpCode'] === 200) {
                $data = json_decode($res['body'], true);
                echo json_encode(['success' => true, 'data' => $data]);
                exit;
            }

            // 2. Se falhar por limite (429) ou erro de servidor (5xx), tenta ReceitaWS (Fallback)
            if ($res['httpCode'] === 429 || $res['httpCode'] >= 500 || $res['body'] === false) {
                $resFallback = $this->executarCurlCnpj($urlReceitaWs);

                if ($resFallback['httpCode'] === 200) {
                    $dataRaw = json_decode($resFallback['body'], true);
                    
                    if (($dataRaw['status'] ?? '') !== 'ERROR') {
                        // Mapeia o formato da ReceitaWS para o padrão da BrasilAPI esperado pelo seu JS
                        $mapped = [
                            'razao_social'  => $dataRaw['nome'] ?? '',
                            'nome_fantasia' => $dataRaw['fantasia'] ?? '',
                            'logradouro'    => $dataRaw['logradouro'] ?? '',
                            'numero'        => $dataRaw['numero'] ?? '',
                            'complemento'   => $dataRaw['complemento'] ?? '',
                            'bairro'        => $dataRaw['bairro'] ?? '',
                            'municipio'     => $dataRaw['municipio'] ?? '',
                            'uf'            => $dataRaw['uf'] ?? '',
                            'cep'           => preg_replace('/\D/', '', $dataRaw['cep'] ?? ''),
                            'email'         => $dataRaw['email'] ?? '',
                            'telefone'      => $dataRaw['telefone'] ?? '',
                            'ddd_telefone_1'=> '' // ReceitaWS já traz o DDD no campo telefone
                        ];
                        echo json_encode(['success' => true, 'data' => $mapped]);
                        exit;
                    }
                }
            }

            // 3. Tratamento de erros finais
            if ($res['httpCode'] === 404) {
                echo json_encode(['success' => false, 'message' => 'CNPJ não encontrado na base da Receita Federal.']);
            } elseif ($res['httpCode'] === 429) {
                echo json_encode(['success' => false, 'message' => 'Limite de consultas excedido em todos os serviços gratuitos. Por favor, tente novamente em 1 minuto.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Os serviços de consulta estão instáveis no momento.']);
            }
        } catch (\Exception $e) {
            error_log("Erro em buscarCnpjAjax: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno ao processar a consulta.']);
        }
        exit;
    }

    /**
     * Helper para executar requisições cURL para as APIs de CNPJ.
     */
    private function executarCurlCnpj(string $url): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['body' => $body, 'httpCode' => $httpCode];
    }

    /**
     * Verifica via AJAX se o documento já existe no banco.
     */
    public function verificarDocumentoExistente($doc)
    {
        header('Content-Type: application/json');
        try {
            // Garante que o documento seja tratado como string
            $resultado = $this->model->buscarEntidadePorDocumento((string)$doc);
            echo json_encode(['exists' => !empty($resultado), 'entidade' => $resultado]);
        } catch (\Exception $e) {
            error_log("Erro em verificarDocumentoExistente: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['exists' => false, 'error' => true, 'message' => 'Erro interno ao processar consulta local.']);
        }
        exit;
    }

    /**
     * Exporta os metadados do contrato em formato JSON para integração com sistemas de assinatura.
     * @param int $id O ID do contrato.
     */
    public function exportarJson(int $id)
    {
        $contrato = $this->model->getContratoDetalhadoById($id);

        if (!$contrato) {
            $this->setFlashMessage('error', 'Contrato não encontrado.');
            header('Location: ' . BASE_URL . '/contratos');
            exit();
        }

        // Estrutura os dados para exportação amigável e organizada
        $data = [
            'metadata_info' => [
                'sistema' => 'SysEnviCorp',
                'data_exportacao' => date('Y-m-d H:i:s'),
                'versao_schema' => '1.0'
            ],
            'contrato' => [
                'id' => $contrato['id'],
                'titulo' => $contrato['titulo'] ?? '',
                'tipo' => $contrato['tipo'],
                'status' => $contrato['status'],
                'foro' => $contrato['foro_eleicao'] ?? '',
                'valor_total' => (float)($contrato['valor'] ?? 0),
                'forma_pagamento' => $contrato['forma_pagamento'] ?? '',
                'data_inicio' => $contrato['data_inicio'],
                'data_vencimento' => $contrato['vencimento'],
                'contratante' => ['nome' => $contrato['contratante_nome'], 'documento' => $contrato['contratante_documento'], 'email' => $contrato['contratante_email'], 'endereco' => $contrato['contratante_endereco']],
                'contratado' => ['nome' => $contrato['contratado_nome'], 'documento' => $contrato['contratado_documento'], 'email' => $contrato['contratado_email'], 'endereco' => $contrato['contratado_endereco']],
                'texto_contrato' => $contrato['objeto']
            ]
        ];

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="metadata_contrato_' . $id . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Exporta a lista de contratos em formato CSV para planilhas.
     * Resolve o erro 404 ao tentar acessar a ação 'exportar'.
     */
    public function exportar()
    {
        // Busca todos os contratos vigentes com os dados necessários para o relatório
        $contratos = $this->model->getTodosContratosParaRelatorio();

        if (empty($contratos)) {
            $this->setFlashMessage('info', 'Não há contratos vigentes disponíveis para exportação.');
            header('Location: ' . BASE_URL . '/contratos');
            exit();
        }

        $filename = "export_contratos_" . date('Y-m-d_H-i') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        
        // Adiciona o BOM para garantir que o Excel reconheça caracteres acentuados (UTF-8)
        fputs($output, "\xEF\xBB\xBF");

        // Cabeçalhos do arquivo CSV
        fputcsv($output, ['ID/Código', 'ID/CTR-CLIENTE', 'Base de Referência', 'Objeto', 'Tipo', 'Parte Contratada', 'Valor (R$)', 'Data Início', 'Vencimento', 'Status'], ';');

        foreach ($contratos as $c) {
            fputcsv($output, [
                $c['numero_contrato'] ?? $c['id'],
                $c['numero_contrato_cliente'] ?? 'N/A',
                $c['base_referencia'] ?? 'N/A',
                $c['objeto'],
                $c['tipo'],
                $c['parteContratada'] ?? 'N/A',
                number_format((float)$c['valor'], 2, ',', ''),
                $c['data_inicio'] ? date('d/m/Y', strtotime($c['data_inicio'])) : '',
                $c['vencimento'] ? date('d/m/Y', strtotime($c['vencimento'])) : 'Indeterminado',
                $c['status']
            ], ';');
        }

        fclose($output);
        exit();
    }

    /**
     * Busca os dados de um contrato via AJAX (para o formulário de orçamento).
     * @param int $id
     */
    public function getContratoDados(int $id)
    {
        header('Content-Type: application/json');
        $id = (int)$id;
        $contrato = $this->model->getContratoById($id);
        if ($contrato) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $contrato['id'],
                    'vencimento' => $contrato['vencimento'] ? date('d/m/Y', strtotime($contrato['vencimento'])) : 'Indeterminado',
                    'valor' => (float)($contrato['valor'] ?? 0),
                    'cliente_id' => $contrato['cliente_id'] ?? null
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Contrato não encontrado.']);
        }
        exit;
    }
}
