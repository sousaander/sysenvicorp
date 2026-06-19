<style>
  :root {
    --pont-border: var(--db-border, #e5e7eb);
    --pont-blue: #2563eb;
    --pont-amber: #f59e0b;
  }
  .dark-theme {
    --pont-border: #334155;
  }

  /* Card informativo com borda de destaque lateral */
  .pont-card-accent {
    transition: all 0.3s ease;
    border-left: 4px solid transparent !important;
  }
  .pont-card-accent:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
  }
  .pont-card-accent.blue { border-left-color: var(--pont-blue) !important; }
  .pont-card-accent.amber { border-left-color: var(--pont-amber) !important; }

  /* Divisórias entre parágrafos e itens de lista */
  .pont-sep-list p, .pont-sep-list li {
    padding: 10px 0;
    border-bottom: 1px solid var(--pont-border);
    margin: 0 !important;
  }
  .pont-sep-list p:last-child, .pont-sep-list li:last-child {
    border-bottom: none;
  }
  .pont-sep-list p:first-child, .pont-sep-list li:first-child {
    padding-top: 0;
  }
</style>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600 dark:text-gray-400">Análise detalhada do comportamento de pagamento da carteira de clientes.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/financeiro" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium flex items-center transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Voltar ao Dashboard
    </a>
</div>

<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-8 border border-gray-100 dark:border-gray-700">
    <form action="<?php echo BASE_URL; ?>/financeiro/pontualidade" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
        <div class="flex-grow max-w-xs w-full">
            <label for="mes_referencia" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mês de Referência (Análise Anual)</label>
            <input type="month" name="mes_referencia" id="mes_referencia" value="<?php echo htmlspecialchars($mesReferencia); ?>" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 p-2 border">
        </div>
        <button type="submit" class="w-full sm:w-auto bg-sky-600 text-white px-6 py-2 rounded-md hover:bg-sky-700 font-medium shadow-sm transition-all h-[42px]">
            Atualizar Relatório
        </button>
    </form>
</div>

<!-- Reutiliza a partial de análise para manter consistência visual nos KPIs e gráficos -->
<?php $this->renderPartial('financeiro/analise_clientes_pagamentos', ['analiseClientesPagamentos' => $analiseClientesPagamentos]); ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md border border-gray-100 dark:border-gray-700 pont-card-accent blue">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 border-b dark:border-gray-700 pb-2 flex items-center gap-2">
            <i class='bx bx-info-circle text-blue-500'></i> Entendendo a Análise
        </h3>
        <div class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed pont-sep-list">
            <p><strong>Inadimplência:</strong> Percentual de clientes que possuem pagamentos realizados após o vencimento ou títulos que já expiraram no ano de referência.</p>
            <p><strong>Antecipação:</strong> Indica a saúde do relacionamento; clientes que pagam antes do prazo ajudam na previsibilidade de caixa.</p>
            <p><strong>Impacto Líquido:</strong> Representa o saldo entre os valores antecipados (positivos) e os valores retidos em atraso (negativos).</p>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md border border-gray-100 dark:border-gray-700 pont-card-accent amber">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 border-b dark:border-gray-700 pb-2 flex items-center gap-2">
            <i class='bx bx-bulb text-amber-500'></i> Sugestões de Gestão
        </h3>
        <ul class="text-sm text-gray-600 dark:text-gray-400 list-none pont-sep-list">
            <li>Clientes com <strong>atraso médio acima de 5 dias</strong> devem ser notificados automaticamente via e-mail/WhatsApp.</li>
            <li>Considere oferecer benefícios para os <strong>Principais Adiantadores</strong> para garantir a continuidade dos contratos.</li>
            <li>Verifique se o <strong>Ticket Médio de Atraso</strong> está subindo; isso pode indicar problemas na qualidade do serviço ou na economia do setor.</li>
        </ul>
    </div>
</div>