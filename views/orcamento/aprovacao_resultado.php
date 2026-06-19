<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Resultado da Aprovação</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS -->
    <link href="<?= BASE_URL ?>/css/output.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
        <!-- Barra de Destaque Superior -->
        <div class="h-2 bg-sky-600"></div>

        <div class="p-8 sm:p-10 text-center">
            <?php if ($ok): ?>
                <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full mb-4">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <h1 class="text-2xl font-black text-emerald-700 tracking-tight mb-2">Proposta aprovada!</h1>
                <p class="text-gray-500 text-sm leading-relaxed">Sua aprovação foi registrada com sucesso. Em breve entraremos em contato.</p>
            <?php else: ?>
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 text-red-600 rounded-full mb-4">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
                <h1 class="text-2xl font-black text-red-700 tracking-tight mb-2">Link inválido ou expirado</h1>
                <p class="text-gray-500 text-sm leading-relaxed">Este link de aprovação não é mais válido. Entre em contato conosco.</p>
            <?php endif; ?>
        </div>
