<?php $pageTitle = $pageTitle ?? 'Emitir Nova NFS-e'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $pageTitle ?></h1>
            <p class="text-sm text-gray-500 mt-1">Preencha os dados da NFS-e</p>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/nfse/salvar" class="space-y-6">
        <input type="hidden" name="id" value="<?= $nota['id'] ?? '' ?>">

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Dados da NFS-e</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                    <input type="text" name="numero" value="<?= htmlspecialchars($nota['numero'] ?? $proximoNumero) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Série</label>
                    <input type="text" name="serie" value="<?= htmlspecialchars($nota['serie'] ?? '1') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de Emissão</label>
                    <input type="date" name="data_emissao" value="<?= htmlspecialchars($nota['data_emissao'] ?? date('Y-m-d')) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Natureza da Operação</label>
                    <input type="text" name="natureza_operacao" value="<?= htmlspecialchars($nota['natureza_operacao'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Regime Especial</label>
                    <select name="regime_especial_tributacao" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="nenhum">Nenhum</option>
                        <option value="microempresa_municipal" <?= (($nota['regime_especial_tributacao'] ?? '') === 'microempresa_municipal') ? 'selected' : '' ?>>Microempresa Municipal</option>
                        <option value="estimativa" <?= (($nota['regime_especial_tributacao'] ?? '') === 'estimativa') ? 'selected' : '' ?>>Estimativa</option>
                        <option value="sociedade_profissionais" <?= (($nota['regime_especial_tributacao'] ?? '') === 'sociedade_profissionais') ? 'selected' : '' ?>>Sociedade de Profissionais</option>
                        <option value="mei" <?= (($nota['regime_especial_tributacao'] ?? '') === 'mei') ? 'selected' : '' ?>>MEI</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="Pendente" <?= (($nota['status'] ?? 'Pendente') === 'Pendente') ? 'selected' : '' ?>>Pendente</option>
                        <option value="Autorizada" <?= (($nota['status'] ?? '') === 'Autorizada') ? 'selected' : '' ?>>Autorizada</option>
                        <option value="Cancelada" <?= (($nota['status'] ?? '') === 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="optante_simples_nacional" value="1" <?= (!isset($nota['optante_simples_nacional']) || $nota['optante_simples_nacional']) ? 'checked' : '' ?>>
                    <span>Optante pelo Simples Nacional</span>
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="incentivo_fiscal" value="1" <?= !empty($nota['incentivo_fiscal']) ? 'checked' : '' ?>>
                    <span>Incentivo Fiscal</span>
                </label>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Serviço</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição do Serviço</label>
                <textarea name="servico_descricao" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"><?= htmlspecialchars($nota['servico_descricao'] ?? '') ?></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código Tributação</label>
                    <input type="text" name="servico_codigo_tributacao" value="<?= htmlspecialchars($nota['servico_codigo_tributacao'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="01.01">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNAE</label>
                    <input type="text" name="servico_codigo_cnae" value="<?= htmlspecialchars($nota['servico_codigo_cnae'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alíquota ISS (%)</label>
                    <input type="text" name="servico_aliquota_iss" value="<?= htmlspecialchars($nota['servico_aliquota_iss'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor Serviços</label>
                    <input type="text" name="servico_valor_total" id="servico_valor_total" value="<?= htmlspecialchars($nota['servico_valor_total'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Base Cálculo</label>
                    <input type="text" name="servico_base_calculo" id="servico_base_calculo" value="<?= htmlspecialchars($nota['servico_base_calculo'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor ISS</label>
                    <input type="text" name="servico_valor_iss" id="servico_valor_iss" value="<?= htmlspecialchars($nota['servico_valor_iss'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor Líquido</label>
                    <input type="text" name="servico_valor_liquido" id="servico_valor_liquido" value="<?= htmlspecialchars($nota['servico_valor_liquido'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Valor PIS</label><input type="text" name="servico_valor_pis" value="<?= htmlspecialchars($nota['servico_valor_pis'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Valor COFINS</label><input type="text" name="servico_valor_cofins" value="<?= htmlspecialchars($nota['servico_valor_cofins'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Valor INSS</label><input type="text" name="servico_valor_inss" value="<?= htmlspecialchars($nota['servico_valor_inss'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Valor IR</label><input type="text" name="servico_valor_ir" value="<?= htmlspecialchars($nota['servico_valor_ir'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Valor CSLL</label><input type="text" name="servico_valor_csll" value="<?= htmlspecialchars($nota['servico_valor_csll'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Outras Retenções</label><input type="text" name="servico_outras_retencoes" value="<?= htmlspecialchars($nota['servico_outras_retencoes'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Tomador</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tomador</label>
                    <select name="cliente_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="preencherCliente(this)">
                        <option value="">Selecione um cliente</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                data-nome="<?= htmlspecialchars($c['nome'] ?? $c['razao_social'] ?? '') ?>"
                                data-cnpj="<?= htmlspecialchars($c['cnpj_cpf'] ?? '') ?>"
                                data-ie="<?= htmlspecialchars($c['ie'] ?? '') ?>"
                                data-endereco="<?= htmlspecialchars($c['endereco'] ?? '') ?>"
                                data-uf="<?= htmlspecialchars($c['uf'] ?? '') ?>"
                                data-cidade="<?= htmlspecialchars($c['cidade'] ?? '') ?>"
                                data-bairro="<?= htmlspecialchars($c['bairro'] ?? '') ?>"
                                data-cep="<?= htmlspecialchars($c['cep'] ?? '') ?>"
                                data-email="<?= htmlspecialchars($c['email'] ?? '') ?>"
                                data-telefone="<?= htmlspecialchars($c['telefone'] ?? '') ?>"
                                <?= (($nota['cliente_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome'] ?? $c['razao_social'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ/CPF</label>
                    <input type="text" name="cliente_cpf_cnpj" id="cliente_cpf_cnpj" value="<?= htmlspecialchars($nota['cliente_cpf_cnpj'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                    <input type="text" name="cliente_endereco" id="cliente_endereco" value="<?= htmlspecialchars($nota['cliente_endereco'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                    <input type="text" name="cliente_numero" id="cliente_numero" value="<?= htmlspecialchars($nota['cliente_numero'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                    <input type="text" name="cliente_bairro" id="cliente_bairro" value="<?= htmlspecialchars($nota['cliente_bairro'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                    <input type="text" name="cliente_municipio" id="cliente_municipio" value="<?= htmlspecialchars($nota['cliente_municipio'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                    <select name="cliente_uf" id="cliente_uf" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        <?php foreach (['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf): ?>
                            <option value="<?= $uf ?>" <?= (($nota['cliente_uf'] ?? '') === $uf) ? 'selected' : '' ?>><?= $uf ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                    <input type="text" name="cliente_cep" id="cliente_cep" value="<?= htmlspecialchars($nota['cliente_cep'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="cliente_email" id="cliente_email" value="<?= htmlspecialchars($nota['cliente_email'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" name="cliente_telefone" id="cliente_telefone" value="<?= htmlspecialchars($nota['cliente_telefone'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Observações</h2>
            <textarea name="observacoes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"><?= htmlspecialchars($nota['observacoes'] ?? '') ?></textarea>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors font-medium text-sm">Salvar NFS-e</button>
            <a href="<?= BASE_URL ?>/nfse" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">Cancelar</a>
        </div>
    </form>
</div>

<script>
function preencherCliente(select) {
    const option = select.options[select.selectedIndex];
    if (!option.value) return;
    document.getElementById('cliente_cpf_cnpj').value = option.dataset.cnpj || '';
    document.getElementById('cliente_endereco').value = option.dataset.endereco || '';
    document.getElementById('cliente_numero').value = option.dataset.numero || '';
    document.getElementById('cliente_bairro').value = option.dataset.bairro || '';
    document.getElementById('cliente_municipio').value = option.dataset.cidade || '';
    document.getElementById('cliente_uf').value = option.dataset.uf || '';
    document.getElementById('cliente_cep').value = option.dataset.cep || '';
    document.getElementById('cliente_email').value = option.dataset.email || '';
    document.getElementById('cliente_telefone').value = option.dataset.telefone || '';
    document.getElementById('cliente_ie').value = option.dataset.ie || '';
}
</script>
