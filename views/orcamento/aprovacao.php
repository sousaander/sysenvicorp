<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Aprovação de Proposta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS -->
    <link href="<?= BASE_URL ?>/css/output.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 flex items-center justify-center min-h-screen p-4 transition-colors duration-200">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <!-- Barra de Destaque Superior -->
        <div class="h-2 bg-sky-600"></div>

        <div class="p-8 sm:p-10">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-sky-100 text-sky-600 rounded-full mb-4">
                    <i class="fas fa-check-double text-2xl"></i>
                </div>
                <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight">Confirmar Aprovação</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm leading-relaxed">
                    Para validar a execução deste orçamento, informe seu nome completo abaixo.
                </p>
            </div>

            <form method="POST" action="orcamento.php?acao=aprovar&token=<?= htmlspecialchars($token) ?>" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Seu Nome Completo</label>
                    <input type="text" name="nome" required
                           class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-2xl px-5 py-4 focus:bg-white dark:focus:bg-gray-600 focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none transition-all text-gray-700 dark:text-white placeholder-gray-300"
                           placeholder="Ex: João Silva da Silva">
                </div>

                <button type="submit" 
                        class="w-full bg-sky-600 hover:bg-sky-700 text-white font-extrabold py-4 rounded-2xl shadow-xl shadow-sky-200 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3">
                    <i class="fas fa-signature"></i>
                    Aprovar Proposta
                </button>
            </form>
        </div>

        <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700">
            <p class="text-center text-[10px] text-gray-400 font-medium leading-tight">
                Ao clicar em aprovar, você declara estar de acordo com todos os termos, escopos e condições comerciais apresentadas no documento.
            </p>
        </div>
    </div>
</body>
</html>
