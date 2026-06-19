<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold">Relatórios e Indicadores de RH</h2>
        <p class="text-gray-600">Análise de dados e métricas de gestão de pessoas.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/rh" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para RH
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Card Relatório de Funcionários -->
    <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-indigo-500">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Lista Geral de Funcionários</h3>
        <p class="text-sm text-gray-600 mb-4">Gera uma listagem completa dos colaboradores com opção de filtro por status.</p>

        <form action="<?php echo BASE_URL; ?>/rh/gerarRelatorioFuncionariosPdf" method="GET" target="_blank">
            <div class="mb-4">
                <label for="status_func" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Status:</label>
                <select name="status" id="status_func" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="Todos">Todos</option>
                    <option value="Ativo">Ativos</option>
                    <option value="Inativo">Inativos</option>
                </select>
            </div>
            <button type="submit" class="w-full flex justify-center items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Gerar PDF
            </button>
        </form>
    </div>
</div>