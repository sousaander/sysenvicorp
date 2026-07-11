<?php $pageTitle = $pageTitle ?? 'Emitir Nova Nota Fiscal'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $pageTitle ?></h1>
            <p class="text-sm text-gray-500 mt-1">Preencha os dados da nota fiscal</p>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/notaFiscal/salvar" class="space-y-6">
        <input type="hidden" name="id" value="<?= $nota['id'] ?? '' ?>">

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Dados da Nota</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                    <input type="text" name="numero" value="<?= htmlspecialchars($nota['numero'] ?? $proximoNumero) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Série</label>
                    <input type="text" name="serie" value="<?= htmlspecialchars($nota['serie'] ?? '1') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de Emissão</label>
                    <input type="date" name="emissao" value="<?= htmlspecialchars($nota['emissao'] ?? date('Y-m-d')) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="Saida" <?= (($nota['tipo'] ?? 'Saida') === 'Saida') ? 'selected' : '' ?>>Saída</option>
                        <option value="Entrada" <?= (($nota['tipo'] ?? '') === 'Entrada') ? 'selected' : '' ?>>Entrada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CFOP</label>
                    <input type="text" name="cfop" value="<?= htmlspecialchars($nota['cfop'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="5.102">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Natureza da Operação</label>
                    <input type="text" name="natureza_operacao" value="<?= htmlspecialchars($nota['natureza_operacao'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="Venda de mercadoria">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Finalidade</label>
                    <select name="finalidade" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="1" <?= (($nota['finalidade'] ?? '1') === '1') ? 'selected' : '' ?>>NF-e normal</option>
                        <option value="2" <?= (($nota['finalidade'] ?? '') === '2') ? 'selected' : '' ?>>NF-e complementar</option>
                        <option value="3" <?= (($nota['finalidade'] ?? '') === '3') ? 'selected' : '' ?>>NF-e de ajuste</option>
                        <option value="4" <?= (($nota['finalidade'] ?? '') === '4') ? 'selected' : '' ?>>Devolução</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chave de Acesso</label>
                    <input type="text" name="chave_acesso" value="<?= htmlspecialchars($nota['chave_acesso'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="44 dígitos">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="Pendente" <?= (($nota['status'] ?? 'Pendente') === 'Pendente') ? 'selected' : '' ?>>Pendente</option>
                        <option value="Autorizada" <?= (($nota['status'] ?? '') === 'Autorizada') ? 'selected' : '' ?>>Autorizada</option>
                        <option value="Cancelada" <?= (($nota['status'] ?? '') === 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                        <option value="Rejeitada" <?= (($nota['status'] ?? '') === 'Rejeitada') ? 'selected' : '' ?>>Rejeitada</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Cliente / Destinatário</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                    <select name="cliente_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500" onchange="preencherCliente(this)">
                        <option value="">Selecione um cliente</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id'] ?>" data-cnpj="<?= htmlspecialchars($c['cnpj_cpf'] ?? '') ?>" data-ie="<?= htmlspecialchars($c['ie'] ?? '') ?>" data-endereco="<?= htmlspecialchars($c['endereco'] ?? '') ?>" data-uf="<?= htmlspecialchars($c['uf'] ?? '') ?>" <?= (($nota['cliente_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nome'] ?? $c['razao_social'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ/CPF</label>
                    <input type="text" name="cnpj_cpf" id="cnpj_cpf" value="<?= htmlspecialchars($nota['cnpj_cpf'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Inscrição Estadual</label>
                    <input type="text" name="cliente_ie" id="cliente_ie" value="<?= htmlspecialchars($nota['cliente_ie'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                    <input type="text" name="cliente_endereco" id="cliente_endereco" value="<?= htmlspecialchars($nota['cliente_endereco'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                        <select name="cliente_uf" id="cliente_uf" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="">Selecione</option>
                            <?php foreach (['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf): ?>
                                <option value="<?= $uf ?>" <?= (($nota['cliente_uf'] ?? '') === $uf) ? 'selected' : '' ?>><?= $uf ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total</label>
                        <input type="text" name="valor" value="<?= htmlspecialchars($nota['valor'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500" required>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-800">Impostos</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Base ICMS</label>
                        <input type="text" name="base_calculo_icms" value="<?= htmlspecialchars($nota['base_calculo_icms'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor ICMS</label>
                        <input type="text" name="valor_icms" value="<?= htmlspecialchars($nota['valor_icms'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor ISS</label>
                        <input type="text" name="valor_iss" value="<?= htmlspecialchars($nota['valor_iss'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-800">Retenções</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">IRRF</label><input type="text" name="retencao_irrf" value="<?= htmlspecialchars($nota['retencao_irrf'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">INSS</label><input type="text" name="retencao_inss" value="<?= htmlspecialchars($nota['retencao_inss'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">ISS</label><input type="text" name="retencao_iss" value="<?= htmlspecialchars($nota['retencao_iss'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">PIS</label><input type="text" name="retencao_pis" value="<?= htmlspecialchars($nota['retencao_pis'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">COFINS</label><input type="text" name="retencao_cofins" value="<?= htmlspecialchars($nota['retencao_cofins'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">CSLL</label><input type="text" name="retencao_csll" value="<?= htmlspecialchars($nota['retencao_csll'] ?? '0,00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-800">Observações</h2>
                <textarea name="observacoes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"><?= htmlspecialchars($nota['observacoes'] ?? '') ?></textarea>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors font-medium text-sm">Salvar Nota Fiscal</button>
                <a href="<?= BASE_URL ?>/fiscal/notas" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
    function preencherCliente(select) {
        const option = select.options[select.selectedIndex];
        if (!option.value) return;
        document.getElementById('cnpj_cpf').value = option.dataset.cnpj || '';
        document.getElementById('cliente_ie').value = option.dataset.ie || '';
        document.getElementById('cliente_endereco').value = option.dataset.endereco || '';
        document.getElementById('cliente_uf').value = option.dataset.uf || '';
    }
    </script>
