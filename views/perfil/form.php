<?php
// CORREÇÃO: A verificação de edição foi tornada mais robusta para aceitar 'id' ou 'perfil_id'.
// O ID real é extraído para uso no formulário, e as permissões (já decodificadas pelo controller) são preparadas.
$isEdit = isset($perfil) && (!empty($perfil['perfil_id']) || !empty($perfil['id']));
$perfilId = $isEdit ? ($perfil['perfil_id'] ?? $perfil['id']) : null;
$permissoesAtuais = $isEdit ? ($perfil['permissoes'] ?? []) : [];
?>

<div class="page-wrapper">
    <div class="page-content">
        <h2 class="text-2xl font-bold mb-4"><?php echo $isEdit ? 'Editar Perfil de Acesso' : 'Novo Perfil de Acesso'; ?></h2>

        <form action="<?php echo BASE_URL; ?>/perfil/salvar" method="post" class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($perfilId ?? ''); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">

            <div class="mb-4">
                <label for="nome_perfil" class="block text-gray-700 text-sm font-bold mb-2">Nome do Perfil:</label>
                <input type="text" id="nome_perfil" name="nome_perfil" value="<?php echo $isEdit ? htmlspecialchars($perfil['nome_perfil']) : ''; ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-6">
                <label for="descricao" class="block text-gray-700 text-sm font-bold mb-2">Descrição:</label>
                <textarea id="descricao" name="descricao" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo $isEdit ? htmlspecialchars($perfil['descricao']) : ''; ?></textarea>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-medium text-gray-900">Permissões do Perfil</h3>
                    <div class="flex gap-2">
                        <button type="button" id="marcar-todas" class="px-3 py-1 text-xs font-semibold text-white bg-green-500 rounded-md hover:bg-green-600 transition">Marcar Todas</button>
                        <button type="button" id="desmarcar-todas" class="px-3 py-1 text-xs font-semibold text-white bg-red-500 rounded-md hover:bg-red-600 transition">Desmarcar Todas</button>
                    </div>
                </div>

                <div class="space-y-6">
                    <?php foreach ($permissoes_agrupadas as $modulo => $permissoes): ?>
                        <div class="border p-4 rounded-md">
                            <h4 class="text-md font-semibold text-gray-800 mb-3 border-b pb-2"><?php echo htmlspecialchars($modulo); ?></h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <?php foreach ($permissoes as $chave => $descricao): ?>
                                    <label class="flex items-center space-x-3">
                                        <input type="checkbox" name="permissoes[]" value="<?php echo $chave; ?>" class="form-checkbox h-5 w-5 text-sky-600" <?php echo in_array($chave, $permissoesAtuais) ? 'checked' : ''; ?>>
                                        <span class="text-gray-700"><?php echo htmlspecialchars($descricao); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Salvar Perfil
                </button>
                <a href="<?php echo BASE_URL; ?>/perfil" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnMarcarTodas = document.getElementById('marcar-todas');
        const btnDesmarcarTodas = document.getElementById('desmarcar-todas');
        const checkboxes = document.querySelectorAll('input[name="permissoes[]"]');

        if (btnMarcarTodas && btnDesmarcarTodas && checkboxes.length > 0) {
            btnMarcarTodas.addEventListener('click', () => checkboxes.forEach(c => c.checked = true));
            btnDesmarcarTodas.addEventListener('click', () => checkboxes.forEach(c => c.checked = false));
        }
    });
</script>