<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SysEnviCorp - Propostas Comerciais</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="<?php echo BASE_URL; ?>/css/output.css" rel="stylesheet">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-200">
<div class="flex justify-between items-center mb-6">
    <div class="p-4">
        <h2 class="text-2xl font-bold dark:text-white">Propostas Comerciais</h2>
        <p class="text-gray-600 dark:text-gray-400">Crie, gerencie e acompanhe suas propostas comerciais.</p>
    </div>
    <button onclick="openPropostaModal('<?php echo BASE_URL; ?>/orcamento/novo')" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
        + Nova Proposta
    </button>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <form method="GET" action="<?php echo BASE_URL; ?>/orcamento/propostas" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Título, cliente..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="status_filter" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status_filter" id="status_filter" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Todos</option>
                    <option value="Rascunho" <?php echo (($_GET['status_filter'] ?? '') === 'Rascunho') ? 'selected' : ''; ?>>Rascunho</option>
                    <option value="Enviada" <?php echo (($_GET['status_filter'] ?? '') === 'Enviada') ? 'selected' : ''; ?>>Enviada</option>
                    <option value="Aprovada" <?php echo (($_GET['status_filter'] ?? '') === 'Aprovada') ? 'selected' : ''; ?>>Aprovada</option>
                    <option value="Rejeitada" <?php echo (($_GET['status_filter'] ?? '') === 'Rejeitada') ? 'selected' : ''; ?>>Rejeitada</option>
                    <option value="Cancelada" <?php echo (($_GET['status_filter'] ?? '') === 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">Filtrar</button>
            </div>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Lista de Propostas</h3>
    <?php if (empty($propostas)): ?>
        <p class="text-gray-600">Nenhuma proposta encontrada.</p>
    <?php else: ?>
        <table class="w-full table-auto">
            <thead>
                <tr>
                    <th class="text-left p-2">Número</th>
                    <th class="text-left p-2">Título</th>
                    <th class="text-left p-2">Cliente</th>
                    <th class="text-left p-2">Valor</th>
                    <th class="text-left p-2">Status</th>
                    <th class="text-left p-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($propostas as $p): ?>
                    <tr class="border-t">
                        <td class="p-2"><?php echo htmlspecialchars($p['numero_proposta'] ?? $p['id']); ?></td>
                        <td class="p-2"><?php echo htmlspecialchars($p['nome_proposta']); ?></td>
                        <td class="p-2"><?php echo htmlspecialchars($p['cliente_nome'] ?? 'N/A'); ?></td>
                        <td class="p-2"><?php echo !empty($p['valor_total']) ? 'R$ ' . number_format($p['valor_total'], 2, ',', '.') : 'R$ 0,00'; ?></td>
                        <td class="p-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php
                                if ($p['status'] === 'Aprovada') echo 'bg-green-100 text-green-800';
                                elseif ($p['status'] === 'Enviada') echo 'bg-blue-100 text-blue-800';
                                elseif ($p['status'] === 'Rascunho') echo 'bg-yellow-100 text-yellow-800';
                                else echo 'bg-red-100 text-red-800';
                                ?>">
                                <?php echo htmlspecialchars($p['status']); ?>
                            </span>
                        </td>
                        <td class="p-2">
                            <a href="<?php echo BASE_URL; ?>/orcamento/ver/<?php echo $p['id']; ?>" class="text-sky-600 mr-2" title="Visualizar">Ver</a>
                            <a href="#" onclick="openPropostaModal('<?php echo BASE_URL; ?>/orcamento/editar/<?php echo $p['id']; ?>')" class="text-yellow-600 mr-2" title="Editar">Editar</a>
                            <a href="<?php echo BASE_URL; ?>/orcamento/pdf/<?php echo $p['id']; ?>" target="_blank" class="text-rose-600 mr-2" title="Baixar PDF">PDF</a>
                            <a href="#" onclick="openPropostaModal('<?php echo BASE_URL; ?>/orcamento/clonar/<?php echo $p['id']; ?>')" class="text-green-600 mr-2" title="Duplicar">Clonar</a>
                            <button onclick="enviarWhatsApp(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nome_proposta']); ?>', '<?php echo $p['cliente_telefone'] ?? ''; ?>')" class="text-emerald-500 hover:text-emerald-700 mr-2" title="Enviar via WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                            <button onclick="excluirProposta(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nome_proposta']); ?>')" class="text-red-600 hover:text-red-800 <?php echo ($p['status'] === 'Aprovada') ? 'opacity-50 cursor-not-allowed' : ''; ?>" title="Excluir proposta" <?php echo ($p['status'] === 'Aprovada') ? 'disabled' : ''; ?>>
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Paginação -->
    <div class="mt-4 flex justify-end items-center">
        <?php if ($totalPaginas > 1) : ?>
            <nav class="flex items-center justify-end space-x-2">
                <?php
                $queryString = http_build_query(array_merge($_GET, ['page' => '']));
                ?>
                <a href="<?php echo BASE_URL; ?>/orcamento/propostas?<?php echo $queryString . ($paginaAtual - 1); ?>" class="<?php echo ($paginaAtual <= 1) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-indigo-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                    Anterior
                </a>

                <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                    <a href="<?php echo BASE_URL; ?>/orcamento/propostas?<?php echo $queryString . $i; ?>" class="<?php echo ($i == $paginaAtual) ? 'bg-indigo-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'; ?> px-3 py-1 rounded-md text-sm font-medium border">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <a href="<?php echo BASE_URL; ?>/orcamento/propostas?<?php echo $queryString . ($paginaAtual + 1); ?>" class="<?php echo ($paginaAtual >= $totalPaginas) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-indigo-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                    Próxima
                </a>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Estrutura da Modal -->
<div id="propostaModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <!-- Conteúdo da modal será carregado aqui -->
        <div id="propostaModalContent" class="p-6">
            <p class="text-center">Carregando formulário...</p>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('propostaModal');
    const modalContent = document.getElementById('propostaModalContent');

    function openPropostaModal(url) {
        modal.classList.remove('hidden');
        modalContent.innerHTML = '<p class="text-center p-8">Carregando formulário...</p>';

        // Adiciona um parâmetro para o controller saber que é uma requisição de modal
        const ajaxUrl = url.includes('?') ? `${url}&ajax=1` : `${url}?ajax=1`;

        fetch(ajaxUrl)
            .then(response => response.text())
            .then(html => {
                modalContent.innerHTML = html;
            })
            .catch(error => {
                modalContent.innerHTML = '<p class="text-center text-red-500 p-8">Erro ao carregar o formulário.</p>';
                console.error('Error loading form:', error);
            });
    }

    function closePropostaModal() {
        modal.classList.add('hidden');
        modalContent.innerHTML = '';
    }

    function enviarWhatsApp(id, titulo, telefone) {
        // Feedback visual de carregamento
        const originalCursor = document.body.style.cursor;
        document.body.style.cursor = 'wait';

        fetch(`<?php echo BASE_URL; ?>/orcamento/gerarLinkPublico/${id}`)
            .then(res => res.json())
            .then(data => {
                document.body.style.cursor = originalCursor;
                if (data.success) {
                    const texto = `Prezado cliente, segue o link para visualização e aprovação da proposta *${titulo}*:\n\n ${data.link} \n\nFicamos à disposição para qualquer dúvida através do nosso contato oficial: <?= WHATSAPP_COMERCIAL_FORMATTED ?>.`;
                    
                    const cleanPhone = telefone ? telefone.replace(/\D/g, '') : '';
                    const url = cleanPhone.length >= 10 
                        ? `https://wa.me/55${cleanPhone}?text=${encodeURIComponent(texto)}`
                        : `https://wa.me/?text=${encodeURIComponent(texto)}`;
                    
                    window.open(url, '_blank');
                } else {
                    alert(data.message || 'Erro ao gerar o link público.');
                }
            })
            .catch(err => {
                document.body.style.cursor = originalCursor;
                console.error(err);
                alert('Falha na comunicação com o servidor.');
            });
    }

    function excluirProposta(id, titulo) {
        if (!confirm(`Tem certeza que deseja excluir a proposta "${titulo}"?\n\nEsta ação não pode ser desfeita.`)) {
            return;
        }

        // Obtém o CSRF token da variável PHP
        const csrfToken = '<?php echo $csrf_token ?? ''; ?>';

        const formData = new FormData();
        formData.append('id', id);
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }

        const originalCursor = document.body.style.cursor;
        document.body.style.cursor = 'wait';

        fetch(`<?php echo BASE_URL; ?>/orcamento/excluir`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(html => {
                document.body.style.cursor = originalCursor;
                // Aguarda 1.5s e recarrega a página para refletir a exclusão
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(err => {
                document.body.style.cursor = originalCursor;
                console.error('Erro ao excluir proposta:', err);
                alert('Erro ao excluir a proposta. Tente novamente.');
            });
    }
</script>
</body>
</html>