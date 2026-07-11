<?php $pageTitle = $pageTitle ?? 'Emitir Novo CT-e'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $pageTitle ?></h1>
            <p class="text-sm text-gray-500 mt-1">Preencha os dados do Conhecimento de Transporte Eletrônico</p>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/cte/salvar" class="space-y-6">
        <input type="hidden" name="id" value="<?= $cte['id'] ?? '' ?>">

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Dados do CT-e</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                    <input type="text" name="numero" value="<?= htmlspecialchars($cte['numero'] ?? $proximoNumero) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Série</label>
                    <input type="text" name="serie" value="<?= htmlspecialchars($cte['serie'] ?? '1') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Emissão</label>
                    <input type="date" name="data_emissao" value="<?= htmlspecialchars($cte['data_emissao'] ?? date('Y-m-d')) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="Pendente" <?= (($cte['status'] ?? 'Pendente') === 'Pendente') ? 'selected' : '' ?>>Pendente</option>
                        <option value="Autorizada" <?= (($cte['status'] ?? '') === 'Autorizada') ? 'selected' : '' ?>>Autorizada</option>
                        <option value="Cancelada" <?= (($cte['status'] ?? '') === 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CFOP</label>
                    <input type="text" name="cfop" value="<?= htmlspecialchars($cte['cfop'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="5353">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Natureza da Operação</label>
                    <input type="text" name="natureza_operacao" value="<?= htmlspecialchars($cte['natureza_operacao'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Prestação de serviço de transporte">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo CT-e</label>
                    <select name="tipo_cte" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="normal" <?= (($cte['tipo_cte'] ?? 'normal') === 'normal') ? 'selected' : '' ?>>Normal</option>
                        <option value="complementar" <?= (($cte['tipo_cte'] ?? '') === 'complementar') ? 'selected' : '' ?>>Complementar</option>
                        <option value="anulacao" <?= (($cte['tipo_cte'] ?? '') === 'anulacao') ? 'selected' : '' ?>>Anulação</option>
                        <option value="substituto" <?= (($cte['tipo_cte'] ?? '') === 'substituto') ? 'selected' : '' ?>>Substituto</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Modal</label>
                    <select name="modal" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="rodoviario" <?= (($cte['modal'] ?? 'rodoviario') === 'rodoviario') ? 'selected' : '' ?>>Rodoviário</option>
                        <option value="aereo" <?= (($cte['modal'] ?? '') === 'aereo') ? 'selected' : '' ?>>Aéreo</option>
                        <option value="aquaviario" <?= (($cte['modal'] ?? '') === 'aquaviario') ? 'selected' : '' ?>>Aquaviário</option>
                        <option value="ferroviario" <?= (($cte['modal'] ?? '') === 'ferroviario') ? 'selected' : '' ?>>Ferroviário</option>
                        <option value="dutoviario" <?= (($cte['modal'] ?? '') === 'dutoviario') ? 'selected' : '' ?>>Dutoviário</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Serviço</label>
                    <select name="tipo_servico" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="normal" <?= (($cte['tipo_servico'] ?? 'normal') === 'normal') ? 'selected' : '' ?>>Normal</option>
                        <option value="subcontratacao" <?= (($cte['tipo_servico'] ?? '') === 'subcontratacao') ? 'selected' : '' ?>>Subcontratação</option>
                        <option value="redespacho_intermediario" <?= (($cte['tipo_servico'] ?? '') === 'redespacho_intermediario') ? 'selected' : '' ?>>Redespacho Intermediário</option>
                        <option value="servico_municipal" <?= (($cte['tipo_servico'] ?? '') === 'servico_municipal') ? 'selected' : '' ?>>Serviço Municipal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Forma Pagamento</label>
                    <select name="forma_pagamento" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="pagamento_contra entrega" <?= (($cte['forma_pagamento'] ?? '') === 'pagamento_contra entrega') ? 'selected' : '' ?>>Pagamento Contra-entrega</option>
                        <option value="pagamento_antes" <?= (($cte['forma_pagamento'] ?? '') === 'pagamento_antes') ? 'selected' : '' ?>>Pagamento Antes</option>
                        <option value="pagamento_apos" <?= (($cte['forma_pagamento'] ?? '') === 'pagamento_apos') ? 'selected' : '' ?>>Pagamento Após</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Tomador</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tomador</label>
                    <select name="tomador_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="preencherTomador(this)">
                        <option value="">Selecione</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                data-nome="<?= htmlspecialchars($c['nome'] ?? $c['razao_social'] ?? '') ?>"
                                data-cnpj="<?= htmlspecialchars($c['cnpj_cpf'] ?? '') ?>"
                                data-endereco="<?= htmlspecialchars($c['endereco'] ?? '') ?>"
                                data-uf="<?= htmlspecialchars($c['uf'] ?? '') ?>"
                                data-cidade="<?= htmlspecialchars($c['cidade'] ?? '') ?>"
                                data-cep="<?= htmlspecialchars($c['cep'] ?? '') ?>"
                                data-email="<?= htmlspecialchars($c['email'] ?? '') ?>"
                                <?= (($cte['tomador_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome'] ?? $c['razao_social'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ/CPF</label>
                    <input type="text" name="tomador_cpf_cnpj" id="tomador_cpf_cnpj" value="<?= htmlspecialchars($cte['tomador_cpf_cnpj'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label><input type="text" name="tomador_endereco" id="tomador_endereco" value="<?= htmlspecialchars($cte['tomador_endereco'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div class="grid grid-cols-2 gap-2">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Município</label><input type="text" name="tomador_municipio" id="tomador_municipio" value="<?= htmlspecialchars($cte['tomador_municipio'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">UF</label><select name="tomador_uf" id="tomador_uf" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"><?php foreach (['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf): ?><option value="<?= $uf ?>" <?= (($cte['tomador_uf'] ?? '') === $uf) ? 'selected' : '' ?>><?= $uf ?></option><?php endforeach; ?></select></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Remetente / Destinatário</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Remetente</h3>
                    <div class="space-y-2">
                        <input type="text" name="remetente_nome" placeholder="Nome" value="<?= htmlspecialchars($cte['remetente_nome'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <input type="text" name="remetente_cpf_cnpj" placeholder="CNPJ/CPF" value="<?= htmlspecialchars($cte['remetente_cpf_cnpj'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <input type="text" name="remetente_endereco" placeholder="Endereço" value="<?= htmlspecialchars($cte['remetente_endereco'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="remetente_municipio" placeholder="Município" value="<?= htmlspecialchars($cte['remetente_municipio'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <select name="remetente_uf" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"><option value="">UF</option><?php foreach (['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf): ?><option value="<?= $uf ?>" <?= (($cte['remetente_uf'] ?? '') === $uf) ? 'selected' : '' ?>><?= $uf ?></option><?php endforeach; ?></select>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Destinatário</h3>
                    <div class="space-y-2">
                        <input type="text" name="destinatario_nome" placeholder="Nome" value="<?= htmlspecialchars($cte['destinatario_nome'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <input type="text" name="destinatario_cpf_cnpj" placeholder="CNPJ/CPF" value="<?= htmlspecialchars($cte['destinatario_cpf_cnpj'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <input type="text" name="destinatario_endereco" placeholder="Endereço" value="<?= htmlspecialchars($cte['destinatario_endereco'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="destinatario_municipio" placeholder="Município" value="<?= htmlspecialchars($cte['destinatario_municipio'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <select name="destinatario_uf" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"><option value="">UF</option><?php foreach (['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf): ?><option value="<?= $uf ?>" <?= (($cte['destinatario_uf'] ?? '') === $uf) ? 'selected' : '' ?>><?= $uf ?></option><?php endforeach; ?></select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Valores</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Valor Mercadorias</label><input type="text" name="valor_mercadorias" value="<?= htmlspecialchars($cte['valor_mercadorias'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Valor Frete</label><input type="text" name="valor_frete" value="<?= htmlspecialchars($cte['valor_frete'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Valor Recebido</label><input type="text" name="valor_recebido" value="<?= htmlspecialchars($cte['valor_recebido'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Valor Total</label><input type="text" name="valor_total" value="<?= htmlspecialchars($cte['valor_total'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
            </div>
            <h3 class="text-sm font-semibold text-gray-700 mt-4">ICMS</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Base Cálculo ICMS</label><input type="text" name="base_calculo_icms" value="<?= htmlspecialchars($cte['base_calculo_icms'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Alíquota ICMS (%)</label><input type="text" name="aliquota_icms" value="<?= htmlspecialchars($cte['aliquota_icms'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Valor ICMS</label><input type="text" name="valor_icms" value="<?= htmlspecialchars($cte['valor_icms'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Redução BC (%)</label><input type="text" name="perc_red_base_calc_icms" value="<?= htmlspecialchars($cte['perc_red_base_calc_icms'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Observações</h2>
            <textarea name="observacoes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"><?= htmlspecialchars($cte['observacoes'] ?? '') ?></textarea>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors font-medium text-sm">Salvar CT-e</button>
            <a href="<?= BASE_URL ?>/cte" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">Cancelar</a>
        </div>
    </form>
</div>

<script>
function preencherTomador(select) {
    const option = select.options[select.selectedIndex];
    if (!option.value) return;
    document.getElementById('tomador_cpf_cnpj').value = option.dataset.cnpj || '';
    document.getElementById('tomador_endereco').value = option.dataset.endereco || '';
    document.getElementById('tomador_municipio').value = option.dataset.cidade || '';
    document.getElementById('tomador_uf').value = option.dataset.uf || '';
}
</script>
