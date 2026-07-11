<?php $pageTitle = $pageTitle ?? 'Detalhe do CT-e'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">CT-e #<?= htmlspecialchars($cte['numero']) ?></h1>
            <p class="text-sm text-gray-500 mt-1">Detalhes do Conhecimento de Transporte Eletrônico</p>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($cte['status'] === 'Pendente'): ?>
                <a href="<?= BASE_URL ?>/cte/form/<?= $cte['id'] ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Editar</a>
                <a href="<?= BASE_URL ?>/cte/emitir/<?= $cte['id'] ?>" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm" onclick="return confirm('Emitir este CT-e para a SEFAZ?')">Emitir CT-e</a>
            <?php elseif ($cte['status'] === 'Autorizada'): ?>
                <?php if (!empty($cte['xml_file'])): ?>
                    <a href="<?= BASE_URL ?>/cte/downloadXml/<?= $cte['id'] ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Download XML</a>
                <?php endif; ?>
                <?php if (!empty($cte['dacte_file'])): ?>
                    <a href="<?= BASE_URL ?>/cte/downloadDacte/<?= $cte['id'] ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Download DACTE</a>
                <?php endif; ?>
                <button onclick="abrirCancelamento(<?= $cte['id'] ?>)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">Cancelar CT-e</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informações do CT-e</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Número</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($cte['numero']) ?></dd></div>
                    <div><dt class="text-gray-500">Série</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($cte['serie'] ?? '1') ?></dd></div>
                    <div><dt class="text-gray-500">Emissão</dt><dd class="font-medium text-gray-800"><?= date('d/m/Y', strtotime($cte['data_emissao'])) ?></dd></div>
                    <div><dt class="text-gray-500">Modal</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars(ucfirst($cte['modal'] ?? 'Rodoviário')) ?></dd></div>
                    <div><dt class="text-gray-500">Tipo</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars(ucfirst($cte['tipo_cte'] ?? 'Normal')) ?></dd></div>
                    <div><dt class="text-gray-500">CFOP</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($cte['cfop'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Status</dt>
                        <dd>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                <?= match($cte['status']) {
                                    'Autorizada' => 'bg-emerald-100 text-emerald-700',
                                    'Cancelada' => 'bg-red-100 text-red-700',
                                    'Rejeitada' => 'bg-rose-100 text-rose-700',
                                    'Encerrado' => 'bg-blue-100 text-blue-700',
                                    'Erro' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-gray-100 text-gray-600',
                                } ?>">
                                <?= htmlspecialchars($cte['status'] ?? 'Pendente') ?>
                            </span>
                        </dd>
                    </div>
                    <div><dt class="text-gray-500">Chave Acesso</dt><dd class="font-medium text-gray-800 text-xs break-all"><?= htmlspecialchars($cte['chave_acesso'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Protocolo</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($cte['protocolo'] ?? '-') ?></dd></div>
                </dl>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Partes Envolvidas</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Tomador</h3>
                        <dl class="text-sm space-y-1">
                            <dd class="font-medium"><?= htmlspecialchars($cte['tomador_nome']) ?></dd>
                            <dd class="text-gray-500"><?= htmlspecialchars($cte['tomador_cpf_cnpj']) ?></dd>
                            <dd class="text-gray-500"><?= htmlspecialchars($cte['tomador_endereco'] ?? '') ?></dd>
                            <dd class="text-gray-500"><?= htmlspecialchars($cte['tomador_municipio'] ?? '') ?> / <?= htmlspecialchars($cte['tomador_uf'] ?? '') ?></dd>
                        </dl>
                    </div>
                    <?php if (!empty($cte['remetente_nome'])): ?>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Remetente</h3>
                        <dl class="text-sm space-y-1">
                            <dd class="font-medium"><?= htmlspecialchars($cte['remetente_nome']) ?></dd>
                            <dd class="text-gray-500"><?= htmlspecialchars($cte['remetente_cpf_cnpj'] ?? '') ?></dd>
                            <dd class="text-gray-500"><?= htmlspecialchars($cte['remetente_endereco'] ?? '') ?></dd>
                            <dd class="text-gray-500"><?= htmlspecialchars($cte['remetente_municipio'] ?? '') ?> / <?= htmlspecialchars($cte['remetente_uf'] ?? '') ?></dd>
                        </dl>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($cte['destinatario_nome'])): ?>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Destinatário</h3>
                        <dl class="text-sm space-y-1">
                            <dd class="font-medium"><?= htmlspecialchars($cte['destinatario_nome']) ?></dd>
                            <dd class="text-gray-500"><?= htmlspecialchars($cte['destinatario_cpf_cnpj'] ?? '') ?></dd>
                            <dd class="text-gray-500"><?= htmlspecialchars($cte['destinatario_endereco'] ?? '') ?></dd>
                            <dd class="text-gray-500"><?= htmlspecialchars($cte['destinatario_municipio'] ?? '') ?> / <?= htmlspecialchars($cte['destinatario_uf'] ?? '') ?></dd>
                        </dl>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Valores</h2>
                <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div><dt class="text-gray-500">Mercadorias</dt><dd class="font-medium">R$ <?= number_format((float)($cte['valor_mercadorias'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">Frete</dt><dd class="font-medium">R$ <?= number_format((float)($cte['valor_frete'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">Recebido</dt><dd class="font-medium">R$ <?= number_format((float)($cte['valor_recebido'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">Total</dt><dd class="font-medium">R$ <?= number_format((float)($cte['valor_total'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">Base ICMS</dt><dd class="font-medium">R$ <?= number_format((float)($cte['base_calculo_icms'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">Alíq. ICMS</dt><dd class="font-medium"><?= number_format((float)($cte['aliquota_icms'] ?? 0), 2) ?>%</dd></div>
                    <div><dt class="text-gray-500">Valor ICMS</dt><dd class="font-medium">R$ <?= number_format((float)($cte['valor_icms'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">Red. BC</dt><dd class="font-medium"><?= number_format((float)($cte['perc_red_base_calc_icms'] ?? 0), 2) ?>%</dd></div>
                </dl>
            </div>

            <?php if (!empty($notasFiscais)): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Notas Fiscais Vinculadas</h2>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500"><th>Chave Acesso</th><th>Cliente</th><th class="text-right">Valor</th></tr></thead>
                    <tbody>
                        <?php foreach ($notasFiscais as $nf): ?>
                        <tr class="border-t border-gray-100">
                            <td class="py-2 font-mono text-xs"><?= htmlspecialchars($nf['nf_chave'] ?? $nf['chave_acesso'] ?? '-') ?></td>
                            <td class="py-2"><?= htmlspecialchars($nf['cliente_fornecedor'] ?? '-') ?></td>
                            <td class="py-2 text-right">R$ <?= number_format((float)($nf['nf_valor'] ?? $nf['valor'] ?? 0), 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Arquivos</h2>
                <?php if (empty($cte['xml_file']) && empty($cte['dacte_file'])): ?>
                    <p class="text-sm text-gray-400">Nenhum arquivo disponível.</p>
                <?php else: ?>
                    <ul class="space-y-2 text-sm">
                        <?php if (!empty($cte['xml_file'])): ?>
                            <li><a href="<?= BASE_URL ?>/cte/downloadXml/<?= $cte['id'] ?>" class="text-orange-600 hover:underline"><i class='bx bx-file'></i> XML do CT-e</a></li>
                        <?php endif; ?>
                        <?php if (!empty($cte['dacte_file'])): ?>
                            <li><a href="<?= BASE_URL ?>/cte/downloadDacte/<?= $cte['id'] ?>" class="text-orange-600 hover:underline"><i class='bx bxs-file-pdf'></i> DACTE</a></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="modalCancelamento" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Cancelar CT-e</h3>
            <form method="POST" action="<?= BASE_URL ?>/cte/cancelar/<?= $cte['id'] ?>">
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
</div>
