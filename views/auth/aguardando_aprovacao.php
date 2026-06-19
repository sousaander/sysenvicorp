<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
        <div class="mb-6 flex justify-center">
            <svg class="h-16 w-16 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Aguardando Aprovação</h2>
        <p class="text-gray-600 mb-6">
            Seu cadastro foi realizado com sucesso, mas sua conta ainda não possui permissões de acesso ao sistema.
        </p>
        <p class="text-gray-600 mb-8 text-sm">
            Por favor, aguarde a liberação de um administrador ou entre em contato com o suporte.
        </p>
        <a href="<?php echo BASE_URL; ?>/auth/logout" class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition duration-200">
            Sair / Logout
        </a>
    </div>
</body>

</html>