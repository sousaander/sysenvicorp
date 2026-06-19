<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Mantenha os modelos padrão de cláusulas e objetos para agilizar novos cadastros.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/contratos" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium">
        &larr; Voltar
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl">
    <form action="<?php echo BASE_URL; ?>/contratos/salvarConfiguracoes" method="POST">
        <div class="mb-6">
            <label for="modelo_padrao" class="block text-lg font-semibold text-gray-800 mb-2">Modelo Padrão: Objeto e Cláusulas Principais</label>
            <p class="text-sm text-gray-500 mb-4">Este texto será sugerido como base ao criar novos contratos no sistema.</p>
            <textarea id="modelo_padrao" name="modelo_padrao" rows="15" class="w-full border-gray-300 rounded-lg shadow-sm p-4 focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" placeholder="Digite aqui o esqueleto padrão do contrato..."><?php echo htmlspecialchars($settings['modelo_padrao'] ?? ''); ?></textarea>
        </div>
        <div class="mb-6">
            <label for="modelo_responsabilidades_contratante" class="block text-lg font-semibold text-gray-800 mb-2">Modelo Padrão: Responsabilidades do Contratante</label>
            <p class="text-sm text-gray-500 mb-4">Defina um modelo de texto para as responsabilidades padrão do contratante.</p>
            <textarea id="modelo_responsabilidades_contratante" name="modelo_responsabilidades_contratante" rows="8" class="w-full border-gray-300 rounded-lg shadow-sm p-4 focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" placeholder="Ex: Fornecer acesso, informações, recursos, aprovações, pagamentos em prazo..."><?php echo htmlspecialchars($settings['modelo_responsabilidades_contratante'] ?? ''); ?></textarea>
        </div>
        <div class="mb-6">
            <label for="modelo_responsabilidades_contratado" class="block text-lg font-semibold text-gray-800 mb-2">Modelo Padrão: Responsabilidades do Contratado</label>
            <p class="text-sm text-gray-500 mb-4">Defina um modelo de texto para as responsabilidades padrão do contratado.</p>
            <textarea id="modelo_responsabilidades_contratado" name="modelo_responsabilidades_contratado" rows="8" class="w-full border-gray-300 rounded-lg shadow-sm p-4 focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" placeholder="Ex: Entregas, prazos, qualidade, padrões técnicos, SLAs, disponibilidade..."><?php echo htmlspecialchars($settings['modelo_responsabilidades_contratado'] ?? ''); ?></textarea>
        </div>
        <div class="mb-6">
            <label for="modelo_clausulas_adicionais" class="block text-lg font-semibold text-gray-800 mb-2">Modelo Padrão: Cláusulas Adicionais</label>
            <p class="text-sm text-gray-500 mb-4">Defina um modelo para disposições gerais e cláusulas extras que costuma utilizar.</p>
            <textarea id="modelo_clausulas_adicionais" name="modelo_clausulas_adicionais" rows="8" class="w-full border-gray-300 rounded-lg shadow-sm p-4 focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" placeholder="Ex: Propriedade intelectual, exclusividade, foro específico..."><?php echo htmlspecialchars($settings['modelo_clausulas_adicionais'] ?? ''); ?></textarea>
        </div>

        <div class="flex justify-end pt-4 border-t">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 font-bold transition">
                Salvar Modelo Padrão
            </button>
        </div>
    </form>
</div>

<div class="mt-8 p-4 bg-blue-50 border-l-4 border-blue-400 text-blue-700 text-sm">
    <p><strong>Dica:</strong> Você pode usar este campo para definir cláusulas de rescisão, foro, obrigações de confidencialidade e LGPD que se aplicam à maioria dos seus contratos.</p>
</div>