<div class="container mx-auto">
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Enviar Alerta de Renovação de Contrato</h1>
            <?php if (!isset($isModal) || !$isModal) : ?>
                <a href="<?php echo BASE_URL; ?>/contratos" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium">
                    &larr; Voltar
                </a>
            <?php endif; ?>
        </div>

        <!-- Formulário para Envio de Alerta -->
        <form action="<?php echo BASE_URL; ?>/contratos/processarAlerta" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Campo de Seleção de Contrato -->
                <div>
                    <label for="contrato_id" class="block text-sm font-medium text-gray-700 mb-1">Selecionar Contrato</label>
                    <!-- Campo de filtro -->
                    <input type="text" id="filtro-contrato" placeholder="Digite para filtrar os contratos..." class="w-full border-gray-300 rounded-lg shadow-sm p-2 mb-1 text-sm">
                    <select id="contrato_id" name="contrato_id" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500" required>
                        <option value="">Selecione um contrato...</option>
                        <?php if (!empty($contratos)) : ?>
                            <?php foreach ($contratos as $contrato) : ?>
                                <option value="<?php echo $contrato['id']; ?>">
                                    <?php echo htmlspecialchars($contrato['parteContratada'] ?? 'N/A'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Campo de Mensagem -->
                <div>
                    <label for="mensagem" class="block text-sm font-medium text-gray-700 mb-1">Mensagem Adicional (Opcional)</label>
                    <textarea id="mensagem" name="mensagem" rows="3" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500" placeholder="Ex: Favor priorizar a renovação deste contrato."></textarea>
                </div>
            </div>

            <!-- Botão de Envio -->
            <div class="mt-6">
                <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                    <i class="fas fa-paper-plane mr-2"></i> Enviar Alerta
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filtroContratoInput = document.getElementById('filtro-contrato');
        const contratoSelect = document.getElementById('contrato_id');

        if (filtroContratoInput && contratoSelect) {
            // Guarda uma cópia de todas as opções originais
            const todasAsOpcoes = Array.from(contratoSelect.options);

            filtroContratoInput.addEventListener('input', function() {
                const filtro = this.value.toLowerCase().trim();
                const valorSelecionado = contratoSelect.value;

                // Limpa o select antes de adicionar as opções filtradas
                contratoSelect.innerHTML = '';

                // Filtra as opções e as adiciona de volta ao select
                const opcoesFiltradas = todasAsOpcoes.filter(option => {
                    // Mantém sempre a primeira opção e as que correspondem ao filtro
                    return option.value === '' || option.textContent.toLowerCase().includes(filtro);
                });

                opcoesFiltradas.forEach(option => contratoSelect.add(option));

                // Restaura a seleção anterior se ela ainda estiver na lista filtrada
                contratoSelect.value = valorSelecionado;
            });
        }
    });
</script>