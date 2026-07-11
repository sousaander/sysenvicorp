<?php $pageTitle = $pageTitle ?? 'Detalhe da NFS-e'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">NFS-e #<?= htmlspecialchars($nota['numero']) ?></h1>
            <p class="text-sm text-gray-500 mt-1">Detalhes completos da Nota Fiscal de Serviço Eletrônica</p>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($nota['status'] === 'Pendente'): ?>
                <a href="<?= BASE_URL ?>/nfse/form/<?= $nota['id'] ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Editar</a>
                <a href="<?= BASE_URL ?>/nfse/emitir/<?= $nota['id'] ?>" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm" onclick="return confirm('Emitir esta NFS-e para a prefeitura?')">Emitir NFS-e</a>
            <?php elseif ($nota['status'] === 'Autorizada'): ?>
                <?php if (!empty($nota['xml_file'])): ?>
                    <a href="<?= BASE_URL ?>/nfse/downloadXml/<?= $nota['id'] ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Download XML</a>
                <?php endif; ?>
                <?php if (!empty($nota['pdf_file'])): ?>
                    <a href="<?= BASE_URL ?>/nfse/downloadPdf/<?= $nota['id'] ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Download PDF</a>
                <?php endif; ?>
                <button onclick="abrirCancelamento(<?= $nota['id'] ?>)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">Cancelar NFS-e</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informações da NFS-e</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Número</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['numero']) ?></dd></div>
                    <div><dt class="text-gray-500">Série</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['serie'] ?? '1') ?></dd></div>
                    <div><dt class="text-gray-500">Emissão</dt><dd class="font-medium text-gray-800"><?= date('d/m/Y', strtotime($nota['data_emissao'])) ?></dd></div>
                    <div><dt class="text-gray-500">Competência</dt><dd class="font-medium text-gray-800"><?= !empty($nota['data_competencia']) ? date('d/m/Y', strtotime($nota['data_competencia'])) : '-' ?></dd></div>
                    <div><dt class="text-gray-500">Natureza</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['natureza_operacao'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Status</dt>
                        <dd>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                <?= match($nota['status']) {
                                    'Autorizada' => 'bg-emerald-100 text-emerald-700',
                                    'Cancelada' => 'bg-red-100 text-red-700',
                                    'Rejeitada' => 'bg-rose-100 text-rose-700',
                                    'Erro' => 'bg-rose-100 text-rose-700',
                                    'Substituida' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-gray-100 text-gray-600',
                                } ?>">
                                <?= htmlspecialchars($nota['status'] ?? 'Pendente') ?>
                            </span>
                        </dd>
                    </div>
                    <div><dt class="text-gray-500">Cód. Verificação</dt><dd class="font-medium text-gray-800 text-xs break-all"><?= htmlspecialchars($nota['codigo_verificacao'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Protocolo</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['protocolo'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Valor Total</dt><dd class="font-medium text-gray-800">R$ <?= number_format((float)$nota['valor_total'], 2, ',', '.') ?></dd></div>
                </dl>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Serviço</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div class="col-span-2"><dt class="text-gray-500">Descrição</dt><dd class="font-medium text-gray-800"><?= nl2br(htmlspecialchars($nota['servico_descricao'] ?? '-')) ?></dd></div>
                    <div><dt class="text-gray-500">Código Tributação</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['servico_codigo_tributacao'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">CNAE</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['servico_codigo_cnae'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Valor Bruto</dt><dd class="font-medium">R$ <?= number_format((float)($nota['servico_valor_total'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">Base Cálculo</dt><dd class="font-medium">R$ <?= number_format((float)($nota['servico_base_calculo'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">Alíquota ISS</dt><dd class="font-medium"><?= number_format((float)($nota['servico_aliquota_iss'] ?? 0), 2) ?>%</dd></div>
                    <div><dt class="text-gray-500">Valor ISS</dt><dd class="font-medium">R$ <?= number_format((float)($nota['servico_valor_iss'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">Valor Líquido</dt><dd class="font-medium">R$ <?= number_format((float)($nota['servico_valor_liquido'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">PIS</dt><dd class="font-medium">R$ <?= number_format((float)($nota['servico_valor_pis'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">COFINS</dt><dd class="font-medium">R$ <?= number_format((float)($nota['servico_valor_cofins'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">INSS</dt><dd class="font-medium">R$ <?= number_format((float)($nota['servico_valor_inss'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">IR</dt><dd class="font-medium">R$ <?= number_format((float)($nota['servico_valor_ir'] ?? 0), 2, ',', '.') ?></dd></div>
                    <div><dt class="text-gray-500">CSLL</dt><dd class="font-medium">R$ <?= number_format((float)($nota['servico_valor_csll'] ?? 0), 2, ',', '.') ?></dd></div>
                </dl>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Tomador</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Nome</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cliente_nome']) ?></dd></div>
                    <div><dt class="text-gray-500">CNPJ/CPF</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cliente_cpf_cnpj']) ?></dd></div>
                    <div><dt class="text-gray-500">Endereço</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cliente_endereco'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Bairro</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cliente_bairro'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Município</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cliente_municipio'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">UF</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cliente_uf'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cliente_email'] ?? '-') ?></dd></div>
                    <div><dt class="text-gray-500">Telefone</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($nota['cliente_telefone'] ?? '-') ?></dd></div>
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Arquivos</h2>
                <?php if (empty($nota['xml_file']) && empty($nota['pdf_file'])): ?>
                    <p class="text-sm text-gray-400">Nenhum arquivo disponível.</p>
                <?php else: ?>
                    <ul class="space-y-2 text-sm">
                        <?php if (!empty($nota['xml_file'])): ?>
                            <li><a href="<?= BASE_URL ?>/nfse/downloadXml/<?= $nota['id'] ?>" class="text-orange-600 hover:underline"><i class='bx bx-file'></i> XML da NFS-e</a></li>
                        <?php endif; ?>
                        <?php if (!empty($nota['pdf_file'])): ?>
                            <li><a href="<?= BASE_URL ?>/nfse/downloadPdf/<?= $nota['id'] ?>" class="text-orange-600 hover:underline"><i class='bx bxs-file-pdf'></i> PDF da NFS-e</a></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="modalCancelamento" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Cancelar NFS-e</h3>
            <form method="POST" action="<?= BASE_URL ?>/nfse/cancelar/<?= $nota['id'] ?>">
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
