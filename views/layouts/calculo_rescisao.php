<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Calcule os valores devidos na rescisão de contrato de um colaborador.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/rh" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para RH
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
    <form action="#" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Seleção de Funcionário -->
            <div>
                <label for="funcionario_id" class="block text-sm font-medium text-gray-700 mb-1">Funcionário <span class="text-red-500">*</span></label>
                <select id="funcionario_id" name="funcionario_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    <option value="">Selecione um funcionário...</option>
                    <!-- Em um cenário real, esta lista seria preenchida dinamicamente -->
                    <option value="1">Funcionário Exemplo 1</option>
                    <option value="2">Funcionário Exemplo 2</option>
                </select>
            </div>

            <!-- Motivo da Rescisão -->
            <div>
                <label for="motivo_rescisao" class="block text-sm font-medium text-gray-700 mb-1">Motivo da Rescisão <span class="text-red-500">*</span></label>
                <select id="motivo_rescisao" name="motivo_rescisao" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    <option value="pedido_demissao">Pedido de Demissão</option>
                    <option value="demissao_sem_justa_causa">Demissão sem Justa Causa</option>
                    <option value="demissao_com_justa_causa">Demissão com Justa Causa</option>
                    <option value="termino_contrato">Término de Contrato de Experiência</option>
                </select>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end">
            <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transition">Calcular Rescisão</button>
        </div>
    </form>
</div>