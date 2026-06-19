<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Orçamento <?= htmlspecialchars($orc['numero']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/orcamento.css">
    <style>
        body { font-size: 13px; color: #212529; }
        .header-proposta { border-bottom: 3px solid #0d6efd; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .logo-empresa { max-height: 60px; }
        .assinatura-box { border: 1px dashed #adb5bd; border-radius: .5rem; padding: 2rem 1rem; text-align: center; }
        @media print {
            .no-print { display: none !important; }
            a[href]:after { content: none !important; }
        }
    </style>
</head>
<body class="p-4">

    <!-- Botão imprimir (some no print) -->
    <div class="d-flex justify-content-end gap-2 mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            <i class="bi bi-printer me-1"></i> Imprimir / Salvar PDF
        </button>
        <a href="orcamento.php?acao=ver&id=<?= $orc['id'] ?>" class="btn btn-outline-secondary btn-sm">
            ← Voltar
        </a>
    </div>

    <!-- CABEÇALHO DA PROPOSTA -->
    <div class="header-proposta d-flex justify-content-between align-items-start">
        <div>
            <!-- Substitua pelo logo da sua empresa -->
            <h4 class="fw-bold mb-0">Sua Empresa Ltda.</h4>
            <small class="text-muted">CNPJ: 00.000.000/0001-00 | contato@suaempresa.com</small>
        </div>
        <div class="text-end">
            <h5 class="fw-bold text-primary mb-0">PROPOSTA COMERCIAL</h5>
            <div class="fs-6"><code><?= htmlspecialchars($orc['numero']) ?></code></div>
            <small class="text-muted">Emitido em: <?= date('d/m/Y') ?></small>
        </div>
    </div>

    <!-- DADOS DO CLIENTE -->
    <div class="row mb-3">
        <div class="col-sm-7">
            <h6 class="fw-bold border-bottom pb-1 mb-2">CLIENTE</h6>
            <p class="mb-0 fw-semibold"><?= htmlspecialchars($orc['razao_social']) ?></p>
            <?php if ($orc['nome_fantasia']): ?>
                <p class="mb-0 text-muted small"><?= htmlspecialchars($orc['nome_fantasia']) ?></p>
            <?php endif; ?>
            <p class="mb-0 small">CNPJ/CPF: <?= htmlspecialchars($orc['cnpj_cpf'] ?? '—') ?></p>
            <p class="mb-0 small"><?= htmlspecialchars($orc['cliente_email'] ?? '') ?></p>
            <p class="mb-0 small"><?= htmlspecialchars($orc['cliente_telefone'] ?? '') ?></p>
        </div>
        <div class="col-sm-5 text-sm-end">
            <h6 class="fw-bold border-bottom pb-1 mb-2">VALIDADE</h6>
            <p class="mb-0">
                <?= $orc['data_validade'] ? date('d/m/Y', strtotime($orc['data_validade'])) : '—' ?>
            </p>
            <small class="text-muted">válido por <?= $orc['validade_dias'] ?> dias</small>
        </div>
    </div>

    <!-- TÍTULO E ESCOPO -->
    <h6 class="fw-bold border-bottom pb-1 mb-2">OBJETO / ESCOPO</h6>
    <p class="fw-semibold mb-1"><?= htmlspecialchars($orc['titulo']) ?></p>
    <?php if ($orc['descricao_geral']): ?>
        <p class="text-muted small mb-3"><?= nl2br(htmlspecialchars($orc['descricao_geral'])) ?></p>
    <?php endif; ?>

    <!-- TABELA DE ITENS -->
    <h6 class="fw-bold border-bottom pb-1 mb-2">ITENS DA PROPOSTA</h6>
    <table class="table table-sm table-bordered tabela-itens mb-3">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Descrição</th>
                <th class="text-center">Unid.</th>
                <th class="text-end">Qtd.</th>
                <th class="text-end">Valor Unit.</th>
                <th class="text-end">Desc.%</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orc['itens'] as $idx => $item): ?>
            <tr>
                <td class="text-muted"><?= $idx + 1 ?></td>
                <td>
                    <div><?= htmlspecialchars($item['descricao']) ?></div>
                    <?php if ($item['detalhes']): ?>
                        <small class="text-muted"><?= htmlspecialchars($item['detalhes']) ?></small>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?= htmlspecialchars($item['unidade']) ?></td>
                <td class="text-end"><?= number_format((float)$item['quantidade'], 2, ',', '.') ?></td>
                <td class="text-end">R$ <?= number_format((float)$item['valor_unit'], 2, ',', '.') ?></td>
                <td class="text-end"><?= $item['desconto_item'] > 0 ? $item['desconto_item'] . '%' : '—' ?></td>
                <td class="text-end fw-semibold">R$ <?= number_format((float)$item['total_item'], 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="table-light">
                <td colspan="6" class="text-end">Subtotal</td>
                <td class="text-end">R$ <?= number_format((float)$orc['subtotal'], 2, ',', '.') ?></td>
            </tr>
            <?php if ((float)$orc['desconto_valor'] > 0): ?>
            <tr>
                <td colspan="6" class="text-end text-danger">Desconto</td>
                <td class="text-end text-danger">- R$ <?= number_format((float)$orc['desconto_valor'], 2, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if ((float)$orc['impostos_perc'] > 0): ?>
            <tr>
                <td colspan="6" class="text-end">Impostos (<?= $orc['impostos_perc'] ?>%)</td>
                <td class="text-end">
                    R$ <?php
                        $imp = ($orc['subtotal'] - $orc['desconto_valor']) * ($orc['impostos_perc'] / 100);
                        echo number_format($imp, 2, ',', '.');
                    ?>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="6" class="text-end fw-bold fs-6">TOTAL GERAL</td>
                <td class="text-end fw-bold fs-6 text-success">
                    R$ <?= number_format((float)$orc['total'], 2, ',', '.') ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- CONDIÇÕES -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h6 class="fw-bold border-bottom pb-1 mb-2">CONDIÇÕES DE PAGAMENTO</h6>
            <p class="mb-1"><strong>Condição:</strong> <?= htmlspecialchars($orc['condicao_pagamento'] ?? '—') ?></p>
            <p class="mb-1"><strong>Forma:</strong> <?= htmlspecialchars($orc['forma_pagamento'] ?? '—') ?></p>
            <p class="mb-0"><strong>Prazo de entrega:</strong> <?= htmlspecialchars($orc['prazo_entrega'] ?? '—') ?></p>
        </div>
        <?php if ($orc['observacoes']): ?>
        <div class="col-sm-6">
            <h6 class="fw-bold border-bottom pb-1 mb-2">OBSERVAÇÕES</h6>
            <p class="text-muted small mb-0"><?= nl2br(htmlspecialchars($orc['observacoes'])) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- ASSINATURA -->
    <div class="row mt-5 page-break-avoid">
        <div class="col-sm-5">
            <div class="assinatura-box">
                <p class="mb-3">_____________________________</p>
                <p class="mb-0 fw-semibold">Responsável pela proposta</p>
                <p class="small text-muted mb-0">Sua Empresa Ltda.</p>
            </div>
        </div>
        <div class="col-sm-2"></div>
        <div class="col-sm-5">
            <div class="assinatura-box">
                <p class="mb-3">_____________________________</p>
                <p class="mb-0 fw-semibold">Aprovação do cliente</p>
                <p class="small text-muted mb-0"><?= htmlspecialchars($orc['razao_social']) ?></p>
                <p class="small text-muted mb-0">Data: ___/___/______</p>
            </div>
        </div>
    </div>

    <hr class="mt-4">
    <p class="text-center text-muted small">
        Este documento é válido até <?= $orc['data_validade'] ? date('d/m/Y', strtotime($orc['data_validade'])) : '—' ?> |
        Orçamento Nº <?= htmlspecialchars($orc['numero']) ?>
    </p>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</body>
</html>
