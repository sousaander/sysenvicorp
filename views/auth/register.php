<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Cadastro'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="w-full max-w-md">
        <form action="<?php echo BASE_URL; ?>/auth/processRegister" method="POST" class="bg-white shadow-lg rounded-xl px-8 pt-6 pb-8 mb-4">
            <div class="mb-8 text-center">
                <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Logo da Empresa" class="mx-auto h-16 w-auto mb-4">
                <h2 class="text-2xl font-bold text-gray-800">SysEnviCorp</h2>
                <p class="text-gray-600">Crie sua conta para começar</p>
            </div>

            <?php
            // Renderiza a flash message, se houver
            $session = \App\Core\SessionManager::getInstance();
            $flash = $session->getFlash();
            if ($flash) {
                $typeClass = $flash['type'] === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
                echo "<div class=\"border {$typeClass} px-4 py-3 rounded relative mb-4\" role=\"alert\">";
                echo "<span class=\"block sm:inline\">{$flash['message']}</span>";
                echo "</div>";
            }
            ?>

            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nome">Nome Completo</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nome" name="nome" type="text" placeholder="Seu nome" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">E-mail</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" placeholder="seuemail@empresa.com" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="senha">Senha</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="senha" name="senha" type="password" placeholder="******************" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="senha_confirm">Confirmar Senha</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="senha_confirm" name="senha_confirm" type="password" placeholder="******************" required>
            </div>
            <div class="flex items-center justify-between mb-4">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" type="submit">
                    Cadastrar
                </button>
            </div>
            <p class="text-center text-sm text-gray-600">Já tem uma conta? <a href="<?php echo BASE_URL; ?>/auth/login" class="font-bold text-blue-500 hover:text-blue-800">Faça login</a>.</p>
        </form>
        <p class="text-center text-gray-500 text-xs">
            &copy;<?php echo date('Y'); ?> SysEnviCorp. Todos os direitos reservados.
        </p>
    </div>

</body>

</html>