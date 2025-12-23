<h2 class="text-2xl font-bold mb-4">Novo Usuário</h2>

<form action="<?php echo BASE_URL; ?>/usuario/salvar" method="post" class="max-w-md">
    <div class="mb-4">
        <label for="nome" class="block text-gray-700 text-sm font-bold mb-2">Nome:</label>
        <input type="text" id="nome" name="nome" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
    </div>

    <div class="mb-4">
        <label for="cargo" class="block text-gray-700 text-sm font-bold mb-2">Cargo:</label>
        <input type="text" id="cargo" name="cargo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
    </div>

    <div class="mb-4">
        <label for="perfil" class="block text-gray-700 text-sm font-bold mb-2">Perfil de Acesso:</label>
        <select id="perfil" name="perfil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="admin">Administrador</option>
            <option value="editor">Editor</option>
            <option value="visualizador">Visualizador</option>
        </select>
    </div>

    <div class="mb-4">
        <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
        <select id="status" name="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="ativo">Ativo</option>
            <option value="inativo">Inativo</option>
        </select>
    </div>

    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
        Salvar
    </button>
</form>