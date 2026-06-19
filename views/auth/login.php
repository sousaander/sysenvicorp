<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Login - SysEnviCorp'; ?></title>
    <!-- Google Fonts: Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        .login-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }

        .btn-envicorp {
            background-color: #10b981;
            transition: all 0.3s ease;
        }

        .btn-envicorp:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="bg-gray-100 antialiased">
    <div class="flex min-h-screen">
        <!-- Lado Esquerdo: Branding (Oculto em mobile) -->
        <div class="hidden lg:flex lg:w-1/2 bg-white items-center justify-center relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-50 rounded-full opacity-50"></div>
            <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-emerald-50 rounded-full opacity-50"></div>

            <div class="z-10 text-center px-12">
                <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="ENVICORP" class="max-w-xs mb-8 mx-auto">
                <h1 class="text-4xl font-extrabold text-blue-900 tracking-tight">SysEnviCorp</h1>
                <p class="text-xl text-gray-500 mt-4 max-w-sm mx-auto">Excelência em gestão ambiental e corporativa integrada.</p>
            </div>

            <!-- Rodapé do Lado Esquerdo -->
            <div class="absolute bottom-8 w-full text-center text-gray-400 text-xs z-10 hidden lg:block">
                &copy; <?php echo date('Y'); ?> ENVICORP. Todos os direitos reservados. Desenvolvido por SOUSATECH-Solutions.
            </div>
        </div>

        <!-- Lado Direito: Formulário de Login -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 login-gradient">
            <div class="glass-card p-8 md:p-12 rounded-3xl shadow-2xl w-full max-w-md border border-white/20">
                <!-- Logo Mobile -->
                <div class="lg:hidden flex justify-center mb-8">
                    <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="ENVICORP" class="h-12">
                </div>

                <header class="mb-10 text-center">
                    <h2 class="text-3xl font-bold text-gray-900">Login</h2>
                    <p class="text-gray-600 mt-2">Bem-vindo de volta! Por favor, insira seus dados.</p>
                </header>

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

                <form action="<?php echo BASE_URL; ?>/auth/processLogin" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                    <input type="hidden" name="next" value="<?php echo htmlspecialchars($next ?? ''); ?>">

                    <div class="space-y-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="email">E-mail Corporativo</label>
                            <input class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none" id="email" name="email" type="email" placeholder="exemplo@envicorp.com.br" required>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-gray-700 text-sm font-semibold" for="senha">Senha</label>
                                <a href="<?php echo BASE_URL; ?>/auth/forgotPassword" class="text-sm font-medium text-blue-600 hover:text-blue-700">Esqueceu a senha?</a>
                            </div>
                            <div class="relative">
                                <input class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none pr-10" id="senha" name="senha" type="password" placeholder="••••••••" required>
                                <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-blue-600 transition-colors">
                                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember_me" class="ml-2 block text-sm text-gray-700">Manter conectado</label>
                        </div>

                        <button class="w-full btn-envicorp text-white font-bold py-3.5 px-4 rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2" type="submit">
                            Entrar no Sistema
                        </button>
                    </div>
                </form>

                <!-- Rodapé visível apenas em dispositivos móveis (já que o lado esquerdo some) -->
                <p class="text-center text-white/70 text-xs mt-8 lg:hidden">
                    &copy; <?php echo date('Y'); ?> ENVICORP.
                </p>
            </div>
        </div>

        <script>
            function togglePassword() {
                const passwordInput = document.getElementById('senha');
                const eyeIcon = document.getElementById('eye-icon');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />';
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />';
                }
            }
        </script>

</body>

</html>