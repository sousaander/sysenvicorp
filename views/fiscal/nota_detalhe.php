<?php $pageTitle = $pageTitle ?? 'Detalhe da Nota Fiscal'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Nota Fiscal #<?= htmlspecialchars($nota['numero']) ?></h1>
            <p class="text-sm text-gray-500 mt-1">Detalhes completos da nota fiscal</p>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($nota['status'] === 'Pendente'): ?>
                <a href="<?= BASE_URL ?>/notaFiscal/form/<?= $nota['id'] ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Editar</a>
                <a href="<?= BASE_URL ?>/notaFiscal/emitirNfe/<?= $nota['id'] ?>" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm" onclick="return confirm('Emitir esta NF-e para a SEFAZ?')">Emitir NF-e</a>
            <?php elseif ($nota['status'] === 'Autorizada'): ?>
                <a href="<?= BASE_URL ?>/notaFiscal/danfe/<?= $nota['id'] ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Download XML</a>
                <button onclick="abrirCancelamento(<?= $nota['id'] ?>)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">Cancelar NF-e</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informações da Nota</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Número</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['numero']) ?></dd></div>
                    <div><dt class="text-gray-500">Série</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['serie'] ?? '1') ?></dd></div>
                    <div><dt class="text-gray-500">Emissão</dt><dd class="font-medium text-gray-800"><?= date('d/m/Y', strtotime($nota['emissao'])) ?></dd></div>
                    <div><dt class="text-gray-500">Tipo</dt><dd class="font-medium text-gray-800"><?= $nota['tipo'] === 'Entrada' ? 'Entrada' : 'Saída' ?></dd></div>
                    <div><dt class="text-gray-500">CFOP</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cfop'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Status</dt>
                        <dd>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                <?= match($nota['status']) {
                                    'Autorizada' => 'bg-emerald-100 text-emerald-700',
                                    'Cancelada' => 'bg-red-100 text-red-700',
                                    'Rejeitada' => 'bg-rose-100 text-rose-700',
                                    'Erro' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-gray-100 text-gray-600',
                                } ?>">
                                <?= htmlspecialchars($nota['status'] ?? 'Pendente') ?>
                            </span>
                        </dd>
                    </div>
                    <div><dt class="text-gray-500">Chave de Acesso</dt><dd class="font-medium text-gray-800 text-xs break-all"><?= htmlspecialchars($nota['chave_acesso'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Protocolo</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['protocolo'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">CFOP</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cfop'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Valor Total</dt><dd class="font-medium text-gray-800">R$ <?= number_format((float)$nota['valor'], 2, ',', '.') ?></dd></div>
                </dl>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Cliente / Destinatário</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Nome</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cliente_fornecedor']) ?></dd></div>
                    <div><dt class="text-gray-500">CNPJ/CPF</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cnpj_cpf']) ?></dd></div>
                    <div><dt class="text-gray-500">IE</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cliente_ie'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">UF</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cliente_uf'] ?? '-') ?></dd></div>
                </dl>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Impostos</h2>
                <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div><dt class="text-gray-500">Base ICMS</dt><dd class="font-medium">R$ <?= number_format((float)($nota['base_calculo_icms'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">Valor ICMS</dt><dd class="font-medium">R$ <?= number_format((float)($nota['valor_icms'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">Valor ISS</dt><dd class="font-medium">R$ <?= number_format((float)($nota['valor_iss'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">PIS</dt><dd class="font-medium">R$ <?= number_format((float)($nota['valor_pis'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">COFINS</dt><dd class="font-medium">R$ <?= number_format((float)($nota['valor_cofins'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">IRRF</dt><dd class="font-medium">R$ <?= number_format((float)($nota['valor_irrf'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">INSS</dt><dd class="font-medium">R$ <?= number_format((float)($nota['valor_inss'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">CSLL</dt><dd class="font-medium">R$ <?= number_format((float)($nota['valor_csll'] ?? 0), 2, ',', '.') ?></dd></div>
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Retenções</h2>
                <?php if (empty($retencoes)): ?>
                    <p class="text-sm text-gray-400">Nenhuma retenção registrada.</p>
                <?php else: ?>
                    <table class="w-full text-sm">
                        <thead><tr class="text-left text-gray-500"><th>Tipo</th><th class="text-right">Valor</th></tr></thead>
                        <tbody>
                            <?php foreach ($retencoes as $r): ?>
                                <tr><td class="py-1"><?= htmlspecialchars($r['tipo_retencao']) ?></td><td class="text-right">R$ <?= number_format($r['valor'], 2, ',', '.') ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="modalCancelamento" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Cancelar NF-e</h3>
            <form method="POST" action="<?= BASE_URL ?>/notaFiscal/cancelarNfe/<?= $nota['id'] ?>">
                <label class="block text-sm font-medium text-gray-700 mb-2">Justificativa (mín. 15 caracteres)</label>
                <textarea name="justificativa" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required minlength="15"></textarea>
                <div class="flex gap-2 mt-4">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">Confirmar Cancelamento</button>
                    <button type="button" onclick="fecharCancelamento()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Voltar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function abrirCancelamento(id) {
        document.getElementById('modalCancelamento').classList.remove('hidden');
        document.getElementById('modalCancelamento').classList.add('flex');
    }
    function fecharCancelamento() {
        document.getElementById('modalCancelamento').classList.add('hidden');
        document.getElementById('modalCancelamento').classList.remove('flex');
    }
    </script>
