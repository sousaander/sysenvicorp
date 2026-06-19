<style>
    /* Estilos para a visualização de diferenças */
    .diff-wrapper {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .diff-wrapper .diff { /* Elemento gerado pela biblioteca de diff */
        padding: 1rem;
        white-space: pre-wrap;
        /* Mantém quebras de linha e espaços */
        word-wrap: break-word;
        font-family: monospace;
        line-height: 1.6;
    }

    .diff-wrapper ins.diff-ins {
        background-color: #ddfbe6; /* Fundo verde claro para adições */
        color: #166534; /* Texto verde escuro */
        text-decoration: none;
    }

    .diff-wrapper del.diff-del {
        background-color: #fee2e2; /* Fundo vermelho claro para remoções */
        color: #b91c1c; /* Texto vermelho escuro */
        text-decoration: line-through;
    }
</style>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600"><?php echo htmlspecialchars($tituloComparacao); ?></p>
    </div>
    <a href="<?php echo BASE_URL; ?>/orcamento/historicoProposta/<?php echo $proposta['id']; ?>" class="px-2 py-1 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para o Histórico da Proposta
    </a>
</div>

<div class="space-y-6">
    <?php
    $labels = [
        'titulo' => 'Título',
        'nome_proposta' => 'Título da Proposta',
        'descricao_tecnica' => 'Descrição Técnica', // Mantido para compatibilidade com dados antigos
        'descricao' => 'Descrição Geral',
        'objetivo' => 'Objetivo',
        'condicoes' => 'Condições Comerciais',
        'data_proposta' => 'Data da Proposta',
        'validade' => 'Validade (dias)',
        'total_servicos' => 'Total Serviços',
        'total_materiais' => 'Total Materiais',
        'impostos_valor' => 'Impostos',
        'descontos_valor' => 'Descontos',
        'total_final' => 'Valor Total',
        'forma_pagamento' => 'Forma de Pagamento',
        'prazo_execucao' => 'Prazo de Execução',
        'garantias' => 'Garantias',
        'status' => 'Status',
        'servicos_json' => 'Serviços Detalhados (JSON)',
        'materiais_json' => 'Materiais Detalhados (JSON)',
        'custos_extras_json' => 'Custos Extras Detalhados (JSON)',
        'impostos_valor' => 'Impostos',
        'descontos_valor' => 'Descontos',
    ];
    // Inclui a biblioteca DiffHelper
    require_once ROOT_PATH . '/vendor/autoload.php';
    ?>
    <?php foreach ($diferencas as $campo => $diffHtml): ?>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold mb-2"><?php echo $labels[$campo]; ?></h3>
            <div class="diff-wrapper text-sm"><?php echo $diffHtml; ?></div>
        </div>
    <?php endforeach; ?>
</div>