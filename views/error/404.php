<?php
// Define o título da página, que será usado pelo template principal.
$pageTitle = "Página Não Encontrada";
?>

<div class="flex flex-col items-center justify-center text-center h-full">
    <div class="bg-white p-12 rounded-lg shadow-xl max-w-2xl mx-auto">
        <h1 class="text-6xl font-bold text-sky-600">404</h1>
        <h2 class="text-3xl font-semibold text-gray-800 mt-4">Página Não Encontrada</h2>
        <p class="text-gray-600 mt-2">
            Desculpe, a página que você está procurando não existe ou foi movida.
        </p>
        <p class="text-gray-500 text-sm mt-1">
            Verifique se o endereço digitado está correto ou volte para a página inicial.
        </p>
        <a href="<?php echo BASE_URL; ?>/" class="mt-8 inline-block px-6 py-3 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition-colors">
            Voltar para o Dashboard
        </a>
    </div>
</div>