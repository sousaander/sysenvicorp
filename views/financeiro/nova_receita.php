<h2 class="text-2xl font-bold mb-4">Adicionar Nova Receita</h2>
<p class="mb-6 text-gray-600">Preencha os detalhes da receita abaixo.</p>

<div class="bg-white p-8 rounded-lg shadow-md max-w-2xl mx-auto">
    <form action="<?php echo BASE_URL; ?>/financeiro/salvarReceita" method="POST">
        <!-- Campo Descrição -->
        <div class="mb-4">
            <label for="descricao" class="block text-gray-700 text-sm font-bold mb-2">Descrição:</label>
            <input type="text" id="descricao" name="descricao" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <!-- Campo Valor -->
        <div class="mb-4">
            <label for="valor" class="block text-gray-700 text-sm font-bold mb-2">Valor (R$):</label>
            <input type="number" step="0.01" id="valor" name="valor" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <!-- Campo Data -->
        <div class="mb-6">
            <label for="data" class="block text-gray-700 text-sm font-bold mb-2">Data de Recebimento:</label>
            <input type="date" id="data" name="data" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <!-- Botão de Envio -->
        <div class="flex items-center justify-end">
            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Salvar Receita
            </button>
        </div>
    </form>
</div>