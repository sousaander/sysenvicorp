<style>
    /* Estilos para a visualização de diff */
    .diff-wrapper {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .diff-wrapper .diff {
        padding: 1rem;
        white-space: pre-wrap;
        /* Mantém quebras de linha */
        word-wrap: break-word;
        font-family: monospace;
        line-height: 1.6;
    }

    .diff-wrapper ins.diff-ins {
        background-color: #ddfbe6;
        color: #166534;
        text-decoration: none;
    }

    .diff-wrapper del.diff-del {
        background-color: #fee2e2;
        color: #b91c1c;
        text-decoration: line-through;
    }
</style>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600"><?php echo htmlspecialchars($tituloComparacao); ?></p>
    </div>
    <a href="<?php echo BASE_URL; ?>/orcamento/historicoProposta/<?php echo $proposta['id']; ?>" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para o Histórico
    </a>
</div>

<div class="space-y-6">
    <?php
    $labels = [
        'titulo' => 'Título',
        'descricao_tecnica' => 'Descrição Técnica',
        'condicoes' => 'Condições Comerciais',
        'valor_total' => 'Valor Total',
        'status' => 'Status'
    ];
    ?>
    <?php foreach ($diferencas as $campo => $diffHtml): ?>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold mb-2"><?php echo $labels[$campo]; ?></h3>
            <div class="diff-wrapper"><?php echo $diffHtml; // O HTML já é gerado pela biblioteca 
                                        ?></div>
        </div>
    <?php endforeach; ?>
</div>