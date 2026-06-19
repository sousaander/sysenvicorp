<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Redefinir Senha'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="flex items-center justify-center h-screen relative bg-gray-100">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?q=80&w=2074&auto=format&fit=crop');"></div>
        <div class="absolute inset-0 bg-black/40"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <form action="<?php echo BASE_URL; ?>/auth/processResetPassword" method="POST" class="bg-white shadow-lg rounded-xl px-8 pt-6 pb-8 mb-4">
            <div class="mb-8 text-center">
                <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Logo da Empresa" class="mx-auto h-16 w-auto mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Nova Senha</h2>
                <p class="text-gray-600">Defina sua nova senha de acesso</p>
            </div>

            <?php
            $session = \App\Core\SessionManager::getInstance();
            $flash = $session->getFlash();
            if ($flash) {
                $typeClass = $flash['type'] === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
                echo "<div class=\"border {$typeClass} px-4 py-3 rounded relative mb-4\" role=\"alert\">";
                echo "<span class=\"block sm:inline\">{$flash['message']}</span>";
                echo "</div>";
            }
            ?>

            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="senha">Nova Senha</label>
                <div class="relative">
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline pr-10" id="senha" name="senha" type="password" placeholder="********" required minlength="6">
                    <button type="button" onclick="togglePassword('senha', 'eye-icon-senha')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-600 focus:outline-none">
                        <svg id="eye-icon-senha" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="senha_confirm">Confirmar Senha</label>
                <div class="relative">
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline pr-10" id="senha_confirm" name="senha_confirm" type="password" placeholder="********" required minlength="6">
                    <button type="button" onclick="togglePassword('senha_confirm', 'eye-icon-confirm')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-600 focus:outline-none">
                        <svg id="eye-icon-confirm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between mb-4">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" type="submit">
                    Redefinir Senha
                </button>
            </div>
        </form>
        <p class="text-center text-gray-500 text-xs">
            &copy;<?php echo date('Y'); ?> SysEnviCorp. Todos os direitos reservados.
        </p>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            const eyeIconPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />';
            const eyeSlashIconPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />';

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = eyeSlashIconPath;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = eyeIconPath;
            }
        }
    </script>
</body>

</html>