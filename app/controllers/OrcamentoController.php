<?php

namespace App\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use App\Models\ClientesModel;
use App\Models\UsuarioModel;

/**
 * Controlador para a seção de Orçamentos do sistema.
 */
class OrcamentoController extends BaseController
{
    /** @var \App\Models\PropostaModel|null */
    private $propostaModel;
    /** @var \App\Models\ProjetosModel|null */
    private $projetosModel;
    /** @var \App\Models\ClientesModel|null */
    private $clientesModel;
    /** @var \App\Models\UsuarioModel|null */
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        // Inicializa o model de propostas quando necessário
        $this->propostaModel = new \App\Models\PropostaModel();
        // Inicializa o model de projetos para usar na listagem
        $this->clientesModel = new ClientesModel();
        $this->usuarioModel = new UsuarioModel();
        $this->projetosModel = new \App\Models\ProjetosModel();
    }

    /**
     * Exibe a página de Orçamento-Proposta.
     */
    public function proposta()
    {
        $this->propostas();
    }

    /**
     * Lista as propostas existentes.
     */
    public function propostas()
    {
        $propostas = $this->propostaModel->getPropostas();
        $data = [
            'pageTitle' => 'Propostas',
            'propostas' => $propostas
        ];
        $this->renderView('orcamento/proposta', $data);
    }

    /** Exibe o formulário para nova proposta */
    public function novaProposta()
    {
        $proposta = [];
        $pageTitle = 'Nova Proposta';

        if (!empty($_GET['id'])) {
            $id = (int)$_GET['id'];
            $proposta = $this->propostaModel->getPropostaById($id);
            $pageTitle = 'Editar Proposta';
        }

        // Busca a lista de projetos para o dropdown
        $projetos = $this->projetosModel->getAllProjetosParaSelect();
        $clientes = $this->clientesModel->getAllClientes();
        $usuarios = $this->usuarioModel->getListaUsuarios();

        $data = [
            'pageTitle' => $pageTitle,
            'proposta' => $proposta,
            'projetos' => $projetos,
            'clientes' => $clientes,
            'usuarios' => $usuarios,
            'csrf_token' => $this->session->get('csrf_token') // Adiciona o token para o formulário
        ];

        // Se a requisição for via AJAX (da modal), renderiza só o formulário.
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            $this->renderPartial('orcamento/proposta_form', $data);
        } else {
            $this->renderView('orcamento/proposta_form', $data);
        }
    }

    /**
     * Carrega o formulário com os dados de uma proposta existente para clonagem.
     * @param int $id O ID da proposta a ser clonada.
     */
    public function clonarProposta($id)
    {
        $id = (int)$id;
        $propostaOriginal = $this->propostaModel->getPropostaById($id);

        if (!$propostaOriginal) {
            $this->setFlashMessage('error', 'Proposta original não encontrada para clonagem.');
            header('Location: ' . BASE_URL . '/orcamento/propostas');
            exit();
        }

        // Prepara os dados para a nova proposta clonada
        $propostaClonada = $propostaOriginal;
        unset($propostaClonada['id']); // Remove o ID para criar uma nova
        $propostaClonada['titulo'] = $propostaOriginal['titulo'] . ' (Cópia)';
        $propostaClonada['status'] = 'Rascunho'; // Define o status inicial

        // Busca a lista de projetos para o dropdown
        $projetos = $this->projetosModel->getAllProjetosParaSelect();
        $clientes = $this->clientesModel->getAllClientes();
        $usuarios = $this->usuarioModel->getListaUsuarios();

        $data = [
            'pageTitle' => 'Clonar Proposta',
            'proposta' => $propostaClonada,
            'projetos' => $projetos,
            'clientes' => $clientes,
            'usuarios' => $usuarios,
            'csrf_token' => $this->session->get('csrf_token') // Adiciona o token para o formulário
        ];

        // Renderiza o formulário dentro da modal (ou em página cheia, se acessado diretamente)
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            $this->renderPartial('orcamento/proposta_form', $data);
        } else {
            $this->renderView('orcamento/proposta_form', $data);
        }
    }

    /** Salva uma proposta (criação/atualização) */
    public function salvarProposta()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/orcamento/propostas');
            exit();
        }

        // Validação simples de CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $this->session->get('csrf_token')) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF).');
            header('Location: ' . BASE_URL . '/orcamento/propostas');
            exit();
        }

        $dados = $_POST;

        if ($this->propostaModel->salvarProposta($dados)) {
            $this->setFlashMessage('success', 'Proposta salva com sucesso.');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar proposta.');
        }

        header('Location: ' . BASE_URL . '/orcamento/propostas');
        exit();
    }

    /** Visualiza uma proposta */
    public function verProposta($id)
    {
        $id = (int)$id;
        $proposta = $this->propostaModel->getPropostaById($id);
        if (!$proposta) {
            $this->setFlashMessage('error', 'Proposta não encontrada.');
            header('Location: ' . BASE_URL . '/orcamento/propostas');
            exit();
        }

        $data = [
            'pageTitle' => 'Visualizar Proposta',
            'proposta' => $proposta
        ];
        $this->renderView('orcamento/proposta_view', $data);
    }

    /** Gera PDF de uma proposta usando DOMPDF */
    public function pdfProposta($id)
    {
        $id = (int)$id;
        $proposta = $this->propostaModel->getPropostaById($id);
        if (!$proposta) {
            $this->setFlashMessage('error', 'Proposta não encontrada.');
            header('Location: ' . BASE_URL . '/orcamento/propostas');
            exit();
        }

        // Gera o HTML a partir da view de PDF
        $html = '';
        ob_start();
        $proposta_pdf = $proposta; // variável usada pela view
        require ROOT_PATH . '/views/orcamento/proposta_pdf.php';
        $html = ob_get_clean();

        // Usa Dompdf para gerar PDF
        require_once ROOT_PATH . '/vendor/autoload.php';
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        // Envia para o navegador
        $dompdf->stream('proposta_' . $id . '.pdf', ['Attachment' => false]);
    }

    /** Exibe o histórico de uma proposta */
    public function historicoProposta($id)
    {
        $id = (int)$id;
        $proposta = $this->propostaModel->getPropostaById($id);
        if (!$proposta) {
            $this->setFlashMessage('error', 'Proposta não encontrada.');
            header('Location: ' . BASE_URL . '/orcamento/propostas');
            exit();
        }

        $historico = $this->propostaModel->getHistoricoByPropostaId($id);

        $data = [
            'pageTitle' => 'Histórico da Proposta #' . $id,
            'proposta' => $proposta,
            'historico' => $historico
        ];

        $this->renderView('orcamento/proposta_historico', $data);
    }

    /**
     * Mostra os detalhes de uma versão do histórico, comparando com a próxima.
     * @param int $historicoId O ID do registro em propostas_historico.
     */
    public function verHistoricoDetalhe($historicoId)
    {
        $historicoId = (int)$historicoId;
        $versaoAntiga = $this->propostaModel->getHistoricoById($historicoId);

        if (!$versaoAntiga) {
            $this->setFlashMessage('error', 'Versão do histórico não encontrada.');
            header('Location: ' . BASE_URL . '/orcamento/propostas');
            exit();
        }

        // Decodifica o JSON para um array
        $dadosAntigos = json_decode($versaoAntiga['dados_proposta_json'], true);

        // Encontra a próxima versão para comparação
        $versaoNova = $this->propostaModel->getHistoricoByPropostaIdEVersao($versaoAntiga['proposta_id'], $versaoAntiga['versao'] + 1);

        if ($versaoNova) {
            // Se encontrou uma versão mais nova no histórico, usa ela
            $dadosNovos = json_decode($versaoNova['dados_proposta_json'], true);
            $tituloComparacao = "Comparando v{$versaoAntiga['versao']} com v{$versaoNova['versao']}";
        } else {
            // Se não, compara com a versão atual da proposta na tabela principal
            $dadosNovos = $this->propostaModel->getPropostaById($versaoAntiga['proposta_id']);
            $tituloComparacao = "Comparando v{$versaoAntiga['versao']} com a Versão Atual";
        }

        // Campos que queremos comparar
        $camposParaComparar = ['titulo', 'descricao_tecnica', 'condicoes', 'valor_total', 'status', 'orcamento_id'];
        $diferencas = [];

        // Opções para o renderizador de diff
        $rendererOptions = [
            'detailLevel' => 'word',
            'language' => 'eng',
            'lineNumbers' => false,
        ];

        foreach ($camposParaComparar as $campo) {
            $textoAntigo = (string)($dadosAntigos[$campo] ?? '');
            $textoNovo = (string)($dadosNovos[$campo] ?? '');

            if ($textoAntigo !== $textoNovo) {
                // Usa DiffHelper para gerar o diff em formato HTML
                $diferencas[$campo] = \Jfcherng\Diff\DiffHelper::calculate($textoAntigo, $textoNovo, 'Inline', [], $rendererOptions);
            } else {
                $diferencas[$campo] = htmlspecialchars($textoNovo); // Sem alterações
            }
        }

        $data = [
            'pageTitle' => 'Detalhes da Revisão',
            'tituloComparacao' => $tituloComparacao,
            'proposta' => $dadosNovos, // Para referência
            'diferencas' => $diferencas
        ];

        $this->renderView('orcamento/proposta_historico_detalhe', $data);
    }

    /**
     * Envia a proposta por e-mail com o PDF em anexo.
     * @param int $id O ID da proposta.
     */
    public function enviarEmailProposta($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/orcamento/verProposta/' . $id);
            exit();
        }

        // Validação de CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $this->session->get('csrf_token')) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF).');
            header('Location: ' . BASE_URL . '/orcamento/verProposta/' . $id);
            exit();
        }

        $id = (int)$id;
        $proposta = $this->propostaModel->getPropostaById($id);
        if (!$proposta) {
            $this->setFlashMessage('error', 'Proposta não encontrada para envio.');
            header('Location: ' . BASE_URL . '/orcamento/propostas');
            exit();
        }

        // 1. Gerar o HTML do PDF
        ob_start();
        $proposta_pdf = $proposta;
        require ROOT_PATH . '/views/orcamento/proposta_pdf.php';
        $html = ob_get_clean();

        // 2. Gerar o PDF em memória
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output(); // Pega o conteúdo do PDF como string

        // 3. Configurar e enviar o e-mail com PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Configurações do servidor SMTP (vindas de settings.php)
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';

            // Remetente e Destinatário
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($_POST['email_destinatario']);
            $mail->addReplyTo(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

            // Anexo
            $nomeArquivo = 'proposta_' . str_pad($id, 4, '0', STR_PAD_LEFT) . '.pdf';
            $mail->addStringAttachment($pdfOutput, $nomeArquivo);

            // Conteúdo do E-mail
            $mail->isHTML(false); // E-mail como texto plano
            $mail->Subject = $_POST['email_assunto'];
            $mail->Body    = $_POST['email_corpo'];

            $mail->send();
            $this->setFlashMessage('success', 'E-mail enviado com sucesso!');
        } catch (Exception $e) {
            $this->setFlashMessage('error', "Erro ao enviar e-mail: {$mail->ErrorInfo}");
        }

        header('Location: ' . BASE_URL . '/orcamento/verProposta/' . $id);
        exit();
    }

    /**
     * Retorna os orçamentos de um projeto em JSON (para AJAX).
     */
    public function getOrcamentosAjax()
    {
        header('Content-Type: application/json');

        if (empty($_GET['projeto_id'])) {
            echo json_encode(['success' => false, 'orcamentos' => [], 'projeto_orcamento_id' => null]);
            exit();
        }

        $projeto_id = (int)$_GET['projeto_id'];
        $orcamentos = $this->projetosModel->getOrcamentosParaSelect($projeto_id);

        // Busca o projeto para obter o campo orcamento_id (código do orçamento do projeto)
        $projeto = $this->projetosModel->getProjetoById($projeto_id);
        $projetoOrcamentoId = $projeto['orcamento_id'] ?? null;

        echo json_encode([
            'success' => true,
            'orcamentos' => $orcamentos,
            'projeto_orcamento_id' => $projetoOrcamentoId
        ]);
        exit();
    }

    /**
     * Retorna os detalhes de um projeto em JSON (para AJAX).
     * @param int $id O ID do projeto.
     */
    public function getProjectDetailsAjax(int $id)
    {
        header('Content-Type: application/json');

        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID do projeto não fornecido.']);
            exit();
        }

        $projeto = $this->projetosModel->getProjetoById($id);

        echo json_encode([
            'success' => $projeto ? true : false,
            'data' => $projeto
        ]);
        exit();
    }

    /**
     * Exibe a página de Orçamento-Comercial.
     */
    public function comercial()
    {
        $data = ['pageTitle' => 'Orçamento - Comercial'];
        $this->renderView('orcamento/comercial', $data);
    }
}
