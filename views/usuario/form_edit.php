<h2 class="text-2xl font-bold mb-4">Editar Usuário</h2>

<form action="<?php echo BASE_URL; ?>/usuario/atualizar/<?php echo $usuario['id']; ?>" method="post" class="max-w-md">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">

    <div class="mb-4">
        <label for="nome" class="block text-gray-700 text-sm font-bold mb-2">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
    </div>

    <div class="mb-4">
        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">E-mail:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
    </div>

    <div class="mb-4">
        <label for="cargo_id" class="block text-gray-700 text-sm font-bold mb-2">Cargo:</label>
        <select id="cargo_id" name="cargo_id" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="">Selecione um cargo</option>
            <?php if (!empty($cargos)): ?>
                <?php foreach ($cargos as $cargo): ?>
                    <option value="<?php echo htmlspecialchars($cargo['cargo_id']); ?>" <?php echo (isset($usuario['cargo_id']) && $usuario['cargo_id'] == $cargo['cargo_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cargo['nome_cargo']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="mb-4">
        <label for="perfil_id" class="block text-gray-700 text-sm font-bold mb-2">Perfil de Acesso:</label>
        <select id="perfil_id" name="perfil_id" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="">Selecione um perfil</option>
            <?php if (!empty($perfis)): ?>
                <?php foreach ($perfis as $perfil): ?>
                    <option value="<?php echo htmlspecialchars($perfil['perfil_id']); ?>" <?php echo (isset($usuario['perfil_id']) && $usuario['perfil_id'] == $perfil['perfil_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($perfil['nome_perfil']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="mb-4">
        <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
        <select id="status" name="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="Ativo" <?php echo ($usuario['status'] === 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
            <option value="Inativo" <?php echo ($usuario['status'] === 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
        </select>
    </div>

    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
        Atualizar
    </button>
</form>