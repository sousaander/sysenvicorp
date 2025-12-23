<?php
$isEdit = isset($bem) && !empty($bem['id']);
$actionUrl = BASE_URL . '/patrimonio/salvar'; // A action que receberá os dados do form
?>

<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Preencha os dados abaixo para incluir um novo bem no patrimônio da empresa.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/patrimonio" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para o Dashboard
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
    <form action="<?php echo $actionUrl; ?>" method="POST">
        <?php if ($isEdit) : ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($bem['id']); ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nome do Bem -->
            <div class="md:col-span-2">
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome / Descrição do Bem <span class="text-red-500">*</span></label>
                <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($bem['nome'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
            </div>

            <!-- Número de Patrimônio / Plaqueta -->
            <div>
                <label for="numero_patrimonio" class="block text-sm font-medium text-gray-700 mb-1">Nº de Patrimônio / Plaqueta</label>
                <input type="text" id="numero_patrimonio" name="numero_patrimonio" value="<?php echo htmlspecialchars($bem['numero_patrimonio'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
            </div>

            <!-- Classificação / Tipo -->
            <div>
                <label for="classificacao" class="block text-sm font-medium text-gray-700 mb-1">Classificação / Tipo <span class="text-red-500">*</span></label>
                <select id="classificacao" name="classificacao" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
                    <option value="">Selecione...</option>
                    <option value="Imóvel" <?php echo (($bem['classificacao'] ?? '') === 'Imóvel') ? 'selected' : ''; ?>>Imóvel</option>
                    <option value="Veículo" <?php echo (($bem['classificacao'] ?? '') === 'Veículo') ? 'selected' : ''; ?>>Veículo</option>
                    <option value="Equipamento de TI" <?php echo (($bem['classificacao'] ?? '') === 'Equipamento de TI') ? 'selected' : ''; ?>>Equipamento de TI</option>
                    <option value="Mobiliário" <?php echo (($bem['classificacao'] ?? '') === 'Mobiliário') ? 'selected' : ''; ?>>Mobiliário</option>
                    <option value="Máquina / Ferramenta" <?php echo (($bem['classificacao'] ?? '') === 'Máquina / Ferramenta') ? 'selected' : ''; ?>>Máquina / Ferramenta</option>
                    <option value="Software / Licença" <?php echo (($bem['classificacao'] ?? '') === 'Software / Licença') ? 'selected' : ''; ?>>Software / Licença</option>
                    <option value="Outro" <?php echo (($bem['classificacao'] ?? '') === 'Outro') ? 'selected' : ''; ?>>Outro</option>
                </select>
            </div>

            <!-- Localização -->
            <div>
                <label for="localizacao" class="block text-sm font-medium text-gray-700 mb-1">Localização / Setor <span class="text-red-500">*</span></label>
                <input type="text" id="localizacao" name="localizacao" required value="<?php echo htmlspecialchars($bem['localizacao'] ?? ''); ?>" placeholder="Ex: Sala de TI, Campo, Administrativo" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
            </div>

            <!-- Responsável -->
            <div>
                <label for="responsavel" class="block text-sm font-medium text-gray-700 mb-1">Responsável pelo Bem</label>
                <input type="text" id="responsavel" name="responsavel" value="<?php echo htmlspecialchars($bem['responsavel'] ?? ''); ?>" placeholder="Nome do colaborador" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
            </div>

            <!-- Observações -->
            <div class="md:col-span-2">
                <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea id="observacoes" name="observacoes" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500"><?php echo htmlspecialchars($bem['observacoes'] ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Seção de Dados Contábeis -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Dados Contábeis e de Depreciação</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Data de Aquisição -->
                <div>
                    <label for="data_aquisicao" class="block text-sm font-medium text-gray-700 mb-1">Data de Aquisição</label>
                    <input type="date" id="data_aquisicao" name="data_aquisicao" value="<?php echo htmlspecialchars($bem['data_aquisicao'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                </div>

                <!-- Valor de Aquisição -->
                <div>
                    <label for="valor_aquisicao" class="block text-sm font-medium text-gray-700 mb-1">Valor de Aquisição (R$)</label>
                    <input type="text" id="valor_aquisicao" name="valor_aquisicao" value="<?php echo htmlspecialchars($bem['valor_aquisicao'] ?? ''); ?>" placeholder="1500,00" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                </div>

                <!-- Vida Útil (Meses) -->
                <div>
                    <label for="vida_util_meses" class="block text-sm font-medium text-gray-700 mb-1">Vida Útil (Meses)</label>
                    <input type="number" id="vida_util_meses" name="vida_util_meses" value="<?php echo htmlspecialchars($bem['vida_util_meses'] ?? ''); ?>" placeholder="60" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                </div>

                <!-- Centro de Custo -->
                <div>
                    <label for="centro_custo" class="block text-sm font-medium text-gray-700 mb-1">Centro de Custo</label>
                    <input type="text" id="centro_custo" name="centro_custo" value="<?php echo htmlspecialchars($bem['centro_custo'] ?? ''); ?>" placeholder="Ex: TI, Administrativo" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                </div>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end">
            <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
                <?php echo $isEdit ? 'Atualizar Bem' : 'Salvar Bem'; ?>
            </button>
        </div>
    </form>
</div>