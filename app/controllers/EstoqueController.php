<?php

namespace App\Controllers;

use App\Models\EstoqueModel;

class EstoqueController extends BaseController
{
    protected $requiredPermissions = [
        'index'        => 'estoque_view',
        'produtos'     => 'estoque_view',
        'produtoForm'  => 'estoque_manage',
        'salvarProduto' => 'estoque_manage',
        'excluirProduto' => 'estoque_manage',
        'movimentos'   => 'estoque_view',
        'entradaForm'  => 'estoque_movimentar',
        'saidaForm'    => 'estoque_movimentar',
        'registrarEntrada' => 'estoque_movimentar',
        'registrarSaida' => 'estoque_movimentar',
        'saldo'        => 'estoque_view',
        'inventarios'  => 'estoque_inventario',
        'novoInventario' => 'estoque_inventario',
        'verInventario' => 'estoque_inventario',
        'atualizarContagem' => 'estoque_inventario',
        'finalizarInventario' => 'estoque_inventario',
        'integrar'     => 'estoque_integrar',
        'integrarContabil' => 'estoque_integrar',
    ];

    private EstoqueModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new EstoqueModel();
        $this->db = \App\Core\Connection::getInstance();
    }

    // ========================
    // DASHBOARD
    // ========================

    public function index(): void
    {
        $resumo = $this->model->getResumo();
        $this->renderView('estoque/index', [
            'pageTitle' => 'Estoque e Inventário',
            'resumo' => $resumo,
        ]);
    }

    // ========================
    // PRODUTOS
    // ========================

    public function produtos(): void
    {
        $produtos = $this->model->getProdutos();
        $this->renderView('estoque/produtos', [
            'pageTitle' => 'Produtos',
            'produtos' => $produtos,
        ]);
    }

    public function produtoForm(?int $id = null): void
    {
        $produto = $id ? $this->model->getProdutoById($id) : null;
        $this->renderView('estoque/produto_form', [
            'pageTitle' => $id ? 'Editar Produto' : 'Novo Produto',
            'produto' => $produto,
        ]);
    }

    public function salvarProduto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/estoque/produtos');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'codigo' => filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_SPECIAL_CHARS),
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'categoria' => filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'unidade' => filter_input(INPUT_POST, 'unidade', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'UN',
            'ncm' => filter_input(INPUT_POST, 'ncm', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'cest' => filter_input(INPUT_POST, 'cest', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'aliquota_icms' => (float)(filter_input(INPUT_POST, 'aliquota_icms', FILTER_VALIDATE_FLOAT) ?: 0),
            'aliquota_ipi' => (float)(filter_input(INPUT_POST, 'aliquota_ipi', FILTER_VALIDATE_FLOAT) ?: 0),
            'aliquota_pis' => (float)(filter_input(INPUT_POST, 'aliquota_pis', FILTER_VALIDATE_FLOAT) ?: 0),
            'aliquota_cofins' => (float)(filter_input(INPUT_POST, 'aliquota_cofins', FILTER_VALIDATE_FLOAT) ?: 0),
            'custo_aquisicao' => (float)(filter_input(INPUT_POST, 'custo_aquisicao', FILTER_VALIDATE_FLOAT) ?: 0),
            'despesas_acessorias' => (float)(filter_input(INPUT_POST, 'despesas_acessorias', FILTER_VALIDATE_FLOAT) ?: 0),
            'margem_lucro' => (float)(filter_input(INPUT_POST, 'margem_lucro', FILTER_VALIDATE_FLOAT) ?: 0),
            'preco_venda' => (float)(filter_input(INPUT_POST, 'preco_venda', FILTER_VALIDATE_FLOAT) ?: 0),
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];

        if (empty($dados['codigo']) || empty($dados['nome'])) {
            $this->setFlashMessage('error', 'Código e Nome são obrigatórios.');
            header('Location: ' . BASE_URL . '/estoque/produtoForm/' . ($dados['id'] ?? ''));
            exit();
        }

        if ($this->model->salvarProduto($dados)) {
            $this->logAction('CREATE', 'Produto: ' . $dados['codigo'] . ' - ' . $dados['nome'], 'Estoque');
            $this->setFlashMessage('success', 'Produto salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar produto.');
        }

        header('Location: ' . BASE_URL . '/estoque/produtos');
        exit();
    }

    public function excluirProduto(int $id): void
    {
        if ($this->model->excluirProduto($id)) {
            $this->logAction('DELETE', 'Produto excluído #' . $id, 'Estoque', $id);
            $this->setFlashMessage('success', 'Produto excluído.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir produto.');
        }
        header('Location: ' . BASE_URL . '/estoque/produtos');
        exit();
    }

    // ========================
    // MOVIMENTOS
    // ========================

    public function movimentos(): void
    {
        $produtoId = filter_input(INPUT_GET, 'produto_id', FILTER_VALIDATE_INT) ?: null;
        $movimentos = $this->model->getMovimentos($produtoId);
        $produtos = $this->model->getProdutos();

        $this->renderView('estoque/movimentos', [
            'pageTitle' => 'Movimentações de Estoque',
            'movimentos' => $movimentos,
            'produtos' => $produtos,
            'produtoId' => $produtoId,
        ]);
    }

    public function entradaForm(): void
    {
        $produtos = $this->model->getProdutos();
        $this->renderView('estoque/entrada_form', [
            'pageTitle' => 'Registrar Entrada',
            'produtos' => $produtos,
        ]);
    }

    public function saidaForm(): void
    {
        $produtos = $this->model->getProdutos();
        $this->renderView('estoque/saida_form', [
            'pageTitle' => 'Registrar Saída',
            'produtos' => $produtos,
        ]);
    }

    public function registrarEntrada(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/estoque/movimentos');
            exit();
        }

        $produtoId = (int)filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
        $quantidade = (float)filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_FLOAT);
        $valorUnitario = (float)filter_input(INPUT_POST, 'valor_unitario', FILTER_VALIDATE_FLOAT);
        $data = filter_input(INPUT_POST, 'data_movimento');
        $documento = filter_input(INPUT_POST, 'documento', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
        $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

        if (!$produtoId || $quantidade <= 0 || $valorUnitario <= 0 || !$data) {
            $this->setFlashMessage('error', 'Produto, quantidade, valor unitário e data são obrigatórios.');
            header('Location: ' . BASE_URL . '/estoque/entradaForm');
            exit();
        }

        if ($this->model->registrarEntrada($produtoId, $quantidade, $valorUnitario, $data, $documento, $observacoes, $this->session->get('user_id'))) {
            $this->logAction('ENTRADA_ESTOQUE', "Entrada de $quantidade unidades do produto #$produtoId", 'Estoque');
            $this->setFlashMessage('success', 'Entrada registrada com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao registrar entrada.');
        }

        header('Location: ' . BASE_URL . '/estoque/movimentos');
        exit();
    }

    public function registrarSaida(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/estoque/movimentos');
            exit();
        }

        $produtoId = (int)filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
        $quantidade = (float)filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_FLOAT);
        $data = filter_input(INPUT_POST, 'data_movimento');
        $documento = filter_input(INPUT_POST, 'documento', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
        $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

        if (!$produtoId || $quantidade <= 0 || !$data) {
            $this->setFlashMessage('error', 'Produto, quantidade e data são obrigatórios.');
            header('Location: ' . BASE_URL . '/estoque/saidaForm');
            exit();
        }

        if ($this->model->registrarSaida($produtoId, $quantidade, $data, $documento, $observacoes, $this->session->get('user_id'))) {
            $this->logAction('SAIDA_ESTOQUE', "Saída de $quantidade unidades do produto #$produtoId", 'Estoque');
            $this->setFlashMessage('success', 'Saída registrada com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao registrar saída. Verifique o saldo disponível.');
        }

        header('Location: ' . BASE_URL . '/estoque/movimentos');
        exit();
    }

    // ========================
    // SALDO
    // ========================

    public function saldo(): void
    {
        $saldos = $this->model->getSaldoAll();
        $this->renderView('estoque/saldos', [
            'pageTitle' => 'Saldo de Estoque',
            'saldos' => $saldos,
        ]);
    }

    // ========================
    // INVENTÁRIO
    // ========================

    public function inventarios(): void
    {
        $inventarios = $this->model->getInventarios();
        $this->renderView('estoque/inventarios', [
            'pageTitle' => 'Inventário Físico',
            'inventarios' => $inventarios,
        ]);
    }

    public function novoInventario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->renderView('estoque/novo_inventario', [
                'pageTitle' => 'Novo Inventário',
            ]);
            return;
        }

        $data = filter_input(INPUT_POST, 'data_inventario');
        $tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'total';
        $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

        if (!$data) {
            $this->setFlashMessage('error', 'Data do inventário é obrigatória.');
            header('Location: ' . BASE_URL . '/estoque/novoInventario');
            exit();
        }

        $id = $this->model->criarInventario($data, $tipo, $observacoes, $this->session->get('user_id'));
        if ($id) {
            $this->logAction('INVENTARIO', "Inventário #$id criado", 'Estoque', $id);
            $this->setFlashMessage('success', 'Inventário criado! Prazo para contagem.');
            header('Location: ' . BASE_URL . '/estoque/verInventario/' . $id);
        } else {
            $this->setFlashMessage('error', 'Erro ao criar inventário.');
            header('Location: ' . BASE_URL . '/estoque/inventarios');
        }
        exit();
    }

    public function verInventario(int $id): void
    {
        $inventario = $this->model->getInventarioById($id);
        $itens = $this->model->getItensInventario($id);

        if (!$inventario) {
            $this->setFlashMessage('error', 'Inventário não encontrado.');
            header('Location: ' . BASE_URL . '/estoque/inventarios');
            exit();
        }

        $this->renderView('estoque/ver_inventario', [
            'pageTitle' => 'Inventário #' . $id,
            'inventario' => $inventario,
            'itens' => $itens,
        ]);
    }

    public function atualizarContagem(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/estoque/inventarios');
            exit();
        }

        $itemId = (int)filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
        $quantidade = (float)filter_input(INPUT_POST, 'quantidade_contada', FILTER_VALIDATE_FLOAT);
        $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

        if ($itemId && $quantidade >= 0) {
            $this->model->atualizarContagemInventario($itemId, $quantidade, $observacoes);
            $this->setFlashMessage('success', 'Contagem atualizada.');
        }

        $inventarioId = (int)filter_input(INPUT_POST, 'inventario_id', FILTER_VALIDATE_INT);
        header('Location: ' . BASE_URL . '/estoque/verInventario/' . $inventarioId);
        exit();
    }

    public function finalizarInventario(int $id): void
    {
        $ajustes = $this->model->finalizarInventario($id);
        $totalAjustes = count($ajustes);
        $this->logAction('INVENTARIO', "Inventário #$id finalizado com $totalAjustes ajustes", 'Estoque', $id);
        $this->setFlashMessage('success', "Inventário finalizado! $totalAjustes ajustes realizados no estoque.");
        header('Location: ' . BASE_URL . '/estoque/inventarios');
        exit();
    }

    // ========================
    // INTEGRAÇÃO CONTÁBIL
    // ========================

    public function integrar(): void
    {
        $this->renderView('estoque/integrar', [
            'pageTitle' => 'Integração Contábil - Estoque',
        ]);
    }

    public function integrarContabil(): void
    {
        $data = filter_input(INPUT_POST, 'data_movimento');
        if (!$data) $data = date('Y-m-d');

        $contabilModel = new \App\Models\ContabilModel();
        $contabilModel->ensureColumns();

        $movimentos = $this->model->getMovimentos(null, 500);
        $importados = 0;

        $contasCache = $contabilModel->getContasCache();
        $contaEstoque = $contasCache['1.1.3.01.001'] ?? null;
        $contaCusto = $contasCache['3.2.1.01.001'] ?? null;

        foreach ($movimentos as $m) {
            $jaExiste = $this->db->prepare(
                "SELECT COUNT(*) FROM lancamentos_contabeis WHERE origem = 'estoque' AND origem_id = ?"
            );
            $jaExiste->execute([$m['id']]);
            if ($jaExiste->fetchColumn() > 0) continue;

            $descricao = ($m['tipo_movimento'] === 'entrada' ? 'Entrada' : 'Saída') . ' - ' . $m['produto_nome'];
            $tipo = ($m['tipo_movimento'] === 'entrada') ? 'debito' : 'credito';

            $contaDebito = ($m['tipo_movimento'] === 'entrada') ? $contaEstoque : $contaCusto;
            $contaCredito = ($m['tipo_movimento'] === 'entrada') ? $contaCusto : $contaEstoque;

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO lancamentos_contabeis
                    (descricao, valor, tipo, categoria, data_lancamento, debito_conta_id, credito_conta_id,
                     origem, origem_id, observacoes, conciliado)
                    VALUES (?, ?, ?, 'Estoque', ?, ?, ?, 'estoque', ?, ?, 0)
                ");
                $stmt->execute([
                    $descricao,
                    abs((float)$m['valor_total']),
                    $tipo,
                    $m['data_movimento'],
                    $contaDebito,
                    $contaCredito,
                    $m['id'],
                    "Integrado automaticamente do módulo de Estoque. Movimento #{$m['id']}"
                ]);
                $importados++;
            } catch (\PDOException $e) {
                error_log("Erro ao integrar movimento #{$m['id']}: " . $e->getMessage());
            }
        }

        $this->logAction('INTEGRATE', "Integração estoque: $importados lançamentos", 'Estoque');
        $this->setFlashMessage($importados > 0 ? 'success' : 'info', "$importados movimentos integrados à contabilidade.");
        header('Location: ' . BASE_URL . '/estoque/integrar');
        exit();
    }

}
