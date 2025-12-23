<div class="container mx-auto">
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Upload de Documento de Contrato (PDF)</h1>
            <?php if (!isset($isModal) || !$isModal) : ?>
                <a href="<?php echo BASE_URL; ?>/contratos" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium">
                    &larr; Voltar
                </a>
            <?php endif; ?>
        </div>

        <!-- Formulário para Upload -->
        <form action="<?php echo BASE_URL; ?>/contratos/processarUpload" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Campo de Seleção de Contrato -->
                <div>
                    <label for="contrato_id_upload" class="block text-sm font-medium text-gray-700 mb-1">Associar ao Contrato</label>
                    <!-- Campo de filtro -->
                    <input type="text" id="filtro-contrato-upload" placeholder="Digite para filtrar os contratos..." class="w-full border-gray-300 rounded-lg shadow-sm p-2 mb-1 text-sm">
                    <select id="contrato_id_upload" name="contrato_id" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500" required>
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

                <!-- Campo de Upload de Arquivo -->
                <div>
                    <label for="documento_pdf" class="block text-sm font-medium text-gray-700 mb-1">Arquivo PDF</label>
                    <input type="file" id="documento_pdf" name="documento_pdf" accept=".pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100" required />
                    <p class="mt-1 text-xs text-gray-500">Apenas arquivos no formato PDF são permitidos.</p>
                </div>
            </div>

            <!-- Botão de Envio -->
            <div class="mt-6">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                    <i class="fas fa-upload mr-2"></i> Enviar Documento
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filtroContratoInput = document.getElementById('filtro-contrato-upload');
        const contratoSelect = document.getElementById('contrato_id_upload');

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