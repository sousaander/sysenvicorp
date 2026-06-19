<div class="bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Cadastros Gerais</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Bancos e Contas -->
        <a href="<?php echo BASE_URL; ?>/banco" class="block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:shadow-md transition-all group">
            <div class="flex items-center mb-3">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-200 transition-colors">
                    <i class='bx bxs-bank text-2xl'></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-800">Bancos e Contas</h3>
            </div>
            <p class="text-gray-600 text-sm">Gerenciar contas bancárias e saldos iniciais.</p>
        </a>

        <!-- Categorias Financeiras -->
        <a href="<?php echo BASE_URL; ?>/classificacao" class="block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:shadow-md transition-all group">
            <div class="flex items-center mb-3">
                <div class="p-3 rounded-full bg-green-100 text-green-600 group-hover:bg-green-200 transition-colors">
                    <i class='bx bx-category text-2xl'></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-800">Categorias Financeiras</h3>
            </div>
            <p class="text-gray-600 text-sm">Classificação de receitas e despesas.</p>
        </a>

        <!-- Centros de Custo -->
        <a href="<?php echo BASE_URL; ?>/centrocusto" class="block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:shadow-md transition-all group">
            <div class="flex items-center mb-3">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 group-hover:bg-purple-200 transition-colors">
                    <i class='bx bx-buildings text-2xl'></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-800">Centros de Custo</h3>
            </div>
            <p class="text-gray-600 text-sm">Gerenciar centros de custo para alocação.</p>
        </a>

        <!-- Categorias de Clientes -->
        <a href="<?php echo BASE_URL; ?>/categorias" class="block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:shadow-md transition-all group">
            <div class="flex items-center mb-3">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600 group-hover:bg-orange-200 transition-colors">
                    <i class='bx bx-user-tag text-2xl'></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-800">Categorias (Clientes)</h3>
            </div>
            <p class="text-gray-600 text-sm">Segmentação e categorias de clientes.</p>
        </a>
    </div>
</div>