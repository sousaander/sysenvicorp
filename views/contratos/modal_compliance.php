<div class="flex justify-between items-center border-b pb-3 mb-4">
    <div>
        <h3 class="text-xl font-bold text-gray-900">Análise de Compliance e Risco</h3>
        <p class="text-sm text-gray-500">Contrato: <?php echo htmlspecialchars(substr($contrato['objeto'], 0, 70)) . '...'; ?></p>
    </div>
    <button class="close-modal-btn text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
</div>

<form id="form-compliance">
    <input type="hidden" name="contrato_id" value="<?php echo $contrato['id']; ?>">

    <div class="space-y-4">
        <!-- Controle de Cláusulas Obrigatórias -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Controle de Cláusulas Obrigatórias</label>
            <div class="bg-gray-50 p-3 rounded-lg border">
                <label for="clausula_lgpd" class="flex items-center">
                    <span class="mr-3 text-sm text-gray-800">Possui cláusula de conformidade com a LGPD?</span>
                    <select id="clausula_lgpd" name="clausula_lgpd" class="w-auto border-gray-300 rounded-lg shadow-sm p-2 text-sm focus:border-sky-500 focus:ring-sky-500">
                        <option value="N/A" <?php echo ($contrato['clausula_lgpd'] ?? 'N/A') === 'N/A' ? 'selected' : ''; ?>>Não se aplica</option>
                        <option value="Sim" <?php echo ($contrato['clausula_lgpd'] ?? '') === 'Sim' ? 'selected' : ''; ?>>Sim</option>
                        <option value="Não" <?php echo ($contrato['clausula_lgpd'] ?? '') === 'Não' ? 'selected' : ''; ?>>Não</option>
                    </select>
                </label>
                <!-- Outras cláusulas podem ser adicionadas aqui -->
            </div>
        </div>

        <!-- Gestão de Riscos Contratuais -->
        <div>
            <label for="risco_contratual" class="block text-sm font-medium text-gray-700 mb-1">Gestão de Riscos Contratuais</label>
            <select id="risco_contratual" name="risco_contratual" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm focus:border-sky-500 focus:ring-sky-500">
                <option value="Baixo" <?php echo ($contrato['risco_contratual'] ?? 'Baixo') === 'Baixo' ? 'selected' : ''; ?>>Baixo</option>
                <option value="Médio" <?php echo ($contrato['risco_contratual'] ?? '') === 'Médio' ? 'selected' : ''; ?>>Médio</option>
                <option value="Alto" <?php echo ($contrato['risco_contratual'] ?? '') === 'Alto' ? 'selected' : ''; ?>>Alto</option>
            </select>
        </div>

        <!-- Parecer Jurídico -->
        <div>
            <label for="parecer_juridico" class="block text-sm font-medium text-gray-700 mb-1">Parecer Jurídico / Observações</label>
            <textarea id="parecer_juridico" name="parecer_juridico" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" placeholder="Adicione aqui notas, observações ou o parecer jurídico sobre este contrato."><?php echo htmlspecialchars($contrato['parecer_juridico'] ?? ''); ?></textarea>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
        <button type="button" class="close-modal-btn px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
            Cancelar
        </button>
        <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transition">
            Salvar Análise
        </button>
    </div>
</form>