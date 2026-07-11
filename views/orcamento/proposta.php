<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SysEnviCorp - Propostas Comerciais</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css">
    <link href="<?php echo BASE_URL; ?>/css/output.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
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

<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
    <form method="GET" action="<?php echo BASE_URL; ?>/orcamento/propostas" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Título, cliente..." class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
            </div>
            <div>
                <label for="status_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select name="status_filter" id="status_filter" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                    <option value="">Todos</option>
                    <option value="Rascunho" <?php echo (($_GET['status_filter'] ?? '') === 'Rascunho') ? 'selected' : ''; ?>>Rascunho</option>
                    <option value="Enviada" <?php echo (($_GET['status_filter'] ?? '') === 'Enviada') ? 'selected' : ''; ?>>Enviada</option>
                    <option value="Aprovada" <?php echo (($_GET['status_filter'] ?? '') === 'Aprovada') ? 'selected' : ''; ?>>Aprovada</option>
                    <option value="Rejeitada" <?php echo (($_GET['status_filter'] ?? '') === 'Rejeitada') ? 'selected' : ''; ?>>Rejeitada</option>
                    <option value="Cancelada" <?php echo (($_GET['status_filter'] ?? '') === 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-indigo-600 dark:bg-indigo-500 text-white py-2 px-4 rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600">Filtrar</button>
            </div>
        </div>
    </form>
</div>

<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold mb-4 border-b pb-2 dark:text-white dark:border-gray-600">Lista de Propostas</h3>
    <?php if (empty($propostas)): ?>
        <p class="text-gray-600 dark:text-gray-400">Nenhuma proposta encontrada.</p>
    <?php else: ?>
        <table class="w-full table-auto">
            <thead>
                <tr>
                    <th class="text-left p-2 dark:text-gray-300">Número</th>
                    <th class="text-left p-2 dark:text-gray-300">Título</th>
                    <th class="text-left p-2 dark:text-gray-300">Cliente</th>
                    <th class="text-left p-2 dark:text-gray-300">Valor</th>
                    <th class="text-left p-2 dark:text-gray-300">Status</th>
                    <th class="text-left p-2 dark:text-gray-300">Dir.</th>
                    <th class="text-left p-2 dark:text-gray-300">Ações</th>
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
                                if ($p['status'] === 'Aprovada') echo 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
                                elseif ($p['status'] === 'Enviada') echo 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300';
                                elseif ($p['status'] === 'Rascunho') echo 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300';
                                else echo 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
                                ?>">
                                <?php echo htmlspecialchars($p['status']); ?>
                            </span>
                        </td>
                        <td class="p-2">
                            <?php
                                $dirSt = $p['aprovacao_diretor_status'] ?? 'nao_solicitado';
                                $dsl = $diretorStatusLabels[$dirSt] ?? ['label' => 'N/A', 'cor' => 'gray'];
                            ?>
                            <?php if ($dirSt === 'pendente'): ?>
                                <button onclick="abrirModalDiretor(<?= $p['id'] ?>)" class="px-2 py-1 text-xs font-bold uppercase rounded bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/50 transition" title="Aprovar ou rejeitar">
                                    <i class="fas fa-hourglass-half mr-1"></i><?= $dsl['label'] ?>
                                </button>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
    <?php
    $cor = $dsl['cor'] ?? 'gray';
    if ($cor === 'gray') echo 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
    elseif ($cor === 'emerald') echo 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300';
    elseif ($cor === 'rose') echo 'bg-rose-100 dark:bg-rose-900/30 text-rose-800 dark:text-rose-300';
    elseif ($cor === 'amber') echo 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300';
    else echo 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
    ?>">
                                    <?php if ($dirSt === 'aprovado'): ?><i class="fas fa-check-circle mr-1"></i><?php endif; ?>
                                    <?php if ($dirSt === 'rejeitado'): ?><i class="fas fa-times-circle mr-1"></i><?php endif; ?>
                                    <?= $dsl['label'] ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="p-2">
                            <?php if ($p['aprovacao_diretor_status'] === 'nao_solicitado' || $p['aprovacao_diretor_status'] === 'rejeitado'): ?>
                            <?php if ($p['status'] !== 'Aprovada'): ?>
                            <button onclick="enviarParaDiretor(<?= $p['id'] ?>)" class="text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 mr-2" title="Enviar para aprovação do diretor">
                                <i class="fas fa-user-tie"></i> Diretor
                            </button>
                            <?php endif; ?>
                            <?php elseif ($p['aprovacao_diretor_status'] === 'pendente'): ?>
                            <span class="text-amber-500 dark:text-amber-400 mr-2" title="Aguardando diretor"><i class="fas fa-hourglass"></i></span>
                            <?php elseif ($p['aprovacao_diretor_status'] === 'aprovado'): ?>
                            <span class="text-emerald-500 dark:text-emerald-400 mr-2" title="Aprovado pelo diretor"><i class="fas fa-check-circle"></i></span>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/orcamento/ver/<?php echo $p['id']; ?>" class="text-sky-600 dark:text-sky-400 mr-2" title="Visualizar">Ver</a>
                            <?php if ($isAdmin || $p['status'] !== 'Aprovada'): ?>
                            <a href="#" onclick="openPropostaModal('<?php echo BASE_URL; ?>/orcamento/editar/<?php echo $p['id']; ?>')" class="text-yellow-600 dark:text-yellow-400 mr-2" title="Editar">Editar</a>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/orcamento/pdf/<?php echo $p['id']; ?>" target="_blank" class="text-rose-600 dark:text-rose-400 mr-2" title="Baixar PDF">PDF</a>
                            <a href="#" onclick="openPropostaModal('<?php echo BASE_URL; ?>/orcamento/clonar/<?php echo $p['id']; ?>')" class="text-green-600 dark:text-green-400 mr-2" title="Duplicar">Clonar</a>
                            <button onclick="enviarWhatsApp(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nome_proposta']); ?>', '<?php echo $p['cliente_telefone'] ?? ''; ?>')" class="text-emerald-500 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 mr-2" title="Enviar via WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                            <?php if ($isAdmin || $p['status'] !== 'Aprovada'): ?>
                            <button onclick="excluirProposta(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nome_proposta']); ?>')" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Excluir proposta">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <?php endif; ?>
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
                <a href="<?php echo BASE_URL; ?>/orcamento/propostas?<?php echo $queryString . ($paginaAtual - 1); ?>" class="<?php echo ($paginaAtual <= 1) ? 'pointer-events-none text-gray-400 dark:text-gray-500' : 'text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400'; ?> px-3 py-1 rounded-md text-sm font-medium">
                    Anterior
                </a>

                <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                    <a href="<?php echo BASE_URL; ?>/orcamento/propostas?<?php echo $queryString . $i; ?>" class="<?php echo ($i == $paginaAtual) ? 'bg-indigo-500 text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'; ?> px-3 py-1 rounded-md text-sm font-medium border dark:border-gray-600">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <a href="<?php echo BASE_URL; ?>/orcamento/propostas?<?php echo $queryString . ($paginaAtual + 1); ?>" class="<?php echo ($paginaAtual >= $totalPaginas) ? 'pointer-events-none text-gray-400 dark:text-gray-500' : 'text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400'; ?> px-3 py-1 rounded-md text-sm font-medium">
                    Próxima
                </a>
            </nav>
        <?php endif; ?>
    </div>
    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 13px; color: #4b5563;" class="dark:!border-gray-600 dark:!text-gray-400">
        ENVICORP ENGENHARIA E NEGOCIOS LTDA &middot; CNPJ 49.787.357/0001-50<br>
        Avenida dos Oitis, 5941 | contato@envicorp.com.br
    </div>
</div>

<!-- Estrutura da Modal -->
<div id="propostaModal" class="fixed inset-0 bg-gray-800 dark:bg-black bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div id="propostaModalContent" class="p-6">
            <p class="text-center dark:text-gray-300">Carregando formulário...</p>
        </div>
    </div>
</div>

<div id="diretorModal" class="fixed inset-0 bg-gray-800 dark:bg-black bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center z-[60] hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div id="diretorModalContent" class="p-6">
            <div class="flex justify-center items-center py-16">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 dark:border-indigo-400 border-t-transparent"></div>
                <p class="ml-4 text-gray-600 dark:text-gray-300 font-semibold">Carregando...</p>
            </div>
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

    // Delegação de eventos para checkboxes de projeto/contrato (scripts inline não executam em innerHTML)
    modalContent.addEventListener('change', function(e) {
        if (e.target.id === 'has-projeto-checkbox' && !e.target.checked) {
            const sel = document.getElementById('projeto_id');
            if (sel) sel.value = '';
            document.getElementById('project-details-container')?.classList.add('hidden');
        }
        if (e.target.id === 'has-contrato-checkbox' && !e.target.checked) {
            const sel = document.getElementById('contrato_id');
            if (sel) sel.value = '';
            document.getElementById('section-contrato-detalhes')?.classList.add('hidden');
        }
    });

    function enviarWhatsApp(id, titulo, telefone) {
        // Feedback visual de carregamento
        const originalCursor = document.body.style.cursor;
        document.body.style.cursor = 'wait';

        fetch(`<?php echo BASE_URL; ?>/orcamento/gerarLinkPublico/${id}?origem=whatsapp`)
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

    // ─── Funções de Aprovação do Diretor ───

    const diretorModal = document.getElementById('diretorModal');
    const diretorModalContent = document.getElementById('diretorModalContent');

    function abrirModalDiretor(id) {
        if (!diretorModal || !diretorModalContent) return;

        diretorModal.classList.remove('hidden');
        diretorModalContent.innerHTML = `
            <div class="flex justify-center items-center py-16">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent"></div>
                <p class="ml-4 text-gray-600 font-semibold">Carregando...</p>
            </div>`;

        fetch(`<?php echo BASE_URL; ?>/orcamento/getDiretorModalAjax/${id}`)
            .then(response => response.text())
            .then(html => {
                diretorModalContent.innerHTML = html;
            })
            .catch(error => {
                diretorModalContent.innerHTML = `
                    <div class="p-8 text-center">
                        <i class="fas fa-exclamation-circle text-red-500 text-4xl mb-4"></i>
                        <p class="text-gray-800 font-bold">Erro ao carregar.</p>
                        <button onclick="closeDiretorModal()" class="mt-4 text-indigo-600 underline">Fechar</button>
                    </div>`;
                console.error('Diretor Modal Error:', error);
            });
    }

    function closeDiretorModal() {
        if (diretorModal) diretorModal.classList.add('hidden');
        if (diretorModalContent) diretorModalContent.innerHTML = '';
    }

    function enviarParaDiretor(id) {
        if (!confirm('Enviar esta proposta para aprovação do diretor?')) return;

        const formData = new FormData();
        formData.append('csrf_token', '<?php echo $csrf_token ?? ''; ?>');

        fetch(`<?php echo BASE_URL; ?>/orcamento/enviarParaDiretorAjax/${id}`, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao enviar para diretor.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro de comunicação com o servidor.');
            });
    }

    function copiarLinkDireto(id) {
        const originalCursor = document.body.style.cursor;
        document.body.style.cursor = 'wait';

        fetch(`<?php echo BASE_URL; ?>/orcamento/gerarLinkPublico/${id}`)
            .then(res => res.json())
            .then(data => {
                document.body.style.cursor = originalCursor;
                if (data.success) {
                    let url = data.link.trim();
                    if (!url.startsWith('http')) {
                        url = window.location.origin + (url.startsWith('/') ? '' : '/') + url;
                    }
                    navigator.clipboard.writeText(url).then(() => {
                        alert('Link de aprovação copiado!');
                    });
                } else {
                    alert('Erro ao gerar link.');
                }
            })
            .catch(err => {
                document.body.style.cursor = originalCursor;
                console.error(err);
            });
    }

    function openPropostaView(id) {
        closeDiretorModal();
        window.open('<?php echo BASE_URL; ?>/orcamento/pdf/' + id, '_blank');
    }

    function aprovarDiretor(id, email, titulo, telefone) {
        Swal.fire({
            title: 'Confirmar aprovação?',
            text: 'Esta ação registrará sua aprovação como diretor.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Sim, aprovar',
            confirmButtonColor: '#059669',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            cancelButtonColor: '#6b7280',
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Aprovando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const formData = new FormData();
            formData.append('csrf_token', '<?php echo $csrf_token ?? ''; ?>');

            fetch('<?php echo BASE_URL; ?>/orcamento/aprovarDiretorAjax/' + id, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                closeDiretorModal();
                if (data.success) {
                    Swal.fire({
                        title: 'Proposta aprovada!',
                        text: 'O que deseja fazer agora?',
                        icon: 'success',
                        showCancelButton: true,
                        showDenyButton: true,
                        confirmButtonText: '<i class="fab fa-whatsapp"></i> WhatsApp',
                        confirmButtonColor: '#25D366',
                        denyButtonText: '<i class="fas fa-envelope"></i> E-mail',
                        denyButtonColor: '#2563eb',
                        cancelButtonText: '<i class="fas fa-check"></i> Fechar',
                        cancelButtonColor: '#6b7280',
                    }).then(result => {
                        if (result.isConfirmed) {
                            enviarWhatsApp(id, titulo, telefone);
                        } else if (result.isDenied) {
                            if (typeof openEmailModal === 'function') {
                                openEmailModal(id, titulo, email);
                            } else {
                                window.location.href = '<?php echo BASE_URL; ?>/orcamento/index';
                            }
                            return;
                        }
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao aprovar proposta.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
                closeDiretorModal();
            });
        });
    }

    function rejeitarDiretor(id) {
        Swal.fire({
            title: 'Rejeitar Proposta',
            text: 'Informe o motivo da rejeição:',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Descreva o motivo da rejeição...',
            inputAttributes: { 'aria-label': 'Justificativa' },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-times"></i> Rejeitar',
            confirmButtonColor: '#dc2626',
            cancelButtonText: '<i class="fas fa-arrow-left"></i> Voltar',
            cancelButtonColor: '#6b7280',
            preConfirm: (value) => {
                if (!value || value.trim() === '') {
                    Swal.showValidationMessage('A justificativa é obrigatória');
                    return false;
                }
                return value.trim();
            }
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Rejeitando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const formData = new FormData();
            formData.append('csrf_token', '<?php echo $csrf_token ?? ''; ?>');
            formData.append('justificativa', result.value);

            fetch('<?php echo BASE_URL; ?>/orcamento/rejeitarDiretorAjax/' + id, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                closeDiretorModal();
                if (data.success) {
                    Swal.fire({
                        title: 'Proposta rejeitada!',
                        text: 'A proposta foi retornada para edição.',
                        icon: 'info',
                        timer: 2500,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao rejeitar proposta.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
                closeDiretorModal();
            });
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