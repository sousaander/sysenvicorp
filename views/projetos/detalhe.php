<?php
// Definições de estilo para os links do submenu
$linkBaseStyle = "px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200";
$linkActiveStyle = "bg-violet-600 text-white shadow-md";
$linkInactiveStyle = "text-gray-600 hover:bg-gray-200";
?>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <div class="flex justify-between items-start">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($projeto['nome']); ?></h2>
            <p class="text-gray-600">Cliente: <?php echo htmlspecialchars($projeto['cliente_nome'] ?? 'Não definido'); ?></p>
        </div>
        <?php
        // Determina a URL de retorno com base no status do projeto
        $urlVoltar = ($projeto['status'] === 'Concluído')
            ? BASE_URL . '/projetos/arquivados'
            : BASE_URL . '/projetos';
        ?>
        <a href="<?php echo $urlVoltar; ?>" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium">
            &larr; Voltar para a Lista
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md">
    <!-- Barra de Navegação dos Submenus -->
    <div class="border-b border-gray-200">
        <nav class="flex space-x-2 p-4" aria-label="Tabs">
            <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/resumo"
                class="<?php echo $submenu === 'resumo' ? $linkActiveStyle : $linkInactiveStyle; ?> <?php echo $linkBaseStyle; ?>">
                Resumo do Projeto
            </a>
            <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/dados_gerais"
                class="<?php echo $submenu === 'dados_gerais' ? $linkActiveStyle : $linkInactiveStyle; ?> <?php echo $linkBaseStyle; ?>">
                Dados Gerais
            </a>
            <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/orcamento"
                class="<?php echo $submenu === 'orcamento' ? $linkActiveStyle : $linkInactiveStyle; ?> <?php echo $linkBaseStyle; ?>">
                Controle de Orçamento
            </a>
            <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/cronograma"
                class="<?php echo $submenu === 'cronograma' ? $linkActiveStyle : $linkInactiveStyle; ?> <?php echo $linkBaseStyle; ?>">
                Cronograma
            </a>
            <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/cdt"
                class="<?php echo $submenu === 'cdt' ? $linkActiveStyle : $linkInactiveStyle; ?> <?php echo $linkBaseStyle; ?>">
                CDT
            </a>
            <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/mapas"
                class="<?php echo $submenu === 'mapas' ? $linkActiveStyle : $linkInactiveStyle; ?> <?php echo $linkBaseStyle; ?>">
                Mapas
            </a>
            <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/arquivos"
                class="<?php echo $submenu === 'arquivos' ? $linkActiveStyle : $linkInactiveStyle; ?> <?php echo $linkBaseStyle; ?>">
                Arquivos do Projeto
            </a>
            <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/art"
                class="<?php echo $submenu === 'art' ? $linkActiveStyle : $linkInactiveStyle; ?> <?php echo $linkBaseStyle; ?>">
                Controle de ART
            </a>
        </nav>
    </div>

    <!-- Conteúdo do Submenu Carregado Dinamicamente -->
    <div class="p-6">
        <?php
        // SOLUÇÃO DEFINITIVA: Disponibiliza as variáveis necessárias de forma explícita para a sub-view.
        // A variável $projeto já está disponível neste escopo.
        // As variáveis $clientes e $arts são extraídas dos arrays $data e $submenuData, se existirem.
        // Isso evita erros caso $data ou $submenuData não estejam definidos no controller.
        $clientes = (isset($data['clientes']) ? $data['clientes'] : (isset($submenuData['clientes']) ? $submenuData['clientes'] : []));
        $arts = (isset($submenuData['arts']) ? $submenuData['arts'] : []);
        // Torna variáveis específicas do submenu disponíveis para a view parcial.
        // Ex: $summary, $itens_orcamento, etc.
        if (!empty($submenuData) && is_array($submenuData)) {
            foreach ($submenuData as $k => $v) {
                ${$k} = $v;
            }
        }

        // Inclui a view parcial do submenu
        require_once ROOT_PATH . '/views/' . $submenuView . '.php';
        ?>
    </div>
</div>