<h2 class="text-2xl font-bold mb-4">Proposta #<?php echo $proposta['id']; ?> - <?php echo htmlspecialchars($proposta['titulo']); ?></h2>

<div class="bg-white p-6 rounded-lg shadow-md">
    <p class="text-sm text-gray-600">Status: <strong><?php echo htmlspecialchars($proposta['status']); ?></strong></p>
    <p class="mt-4"><strong>Valor Total:</strong> <?php echo !empty($proposta['valor_total']) ? 'R$ ' . number_format($proposta['valor_total'], 2, ',', '.') : '-'; ?></p>

    <h3 class="mt-4 font-semibold">Descrição Técnica</h3>
    <div class="proposta-descricao mt-2 p-3 border rounded bg-gray-50">
        <?php echo nl2br(html_entity_decode($proposta['descricao_tecnica'] ?? '')); ?>
    </div>

    <h3 class="mt-4 font-semibold">Condições Comerciais</h3>
    <div class="proposta-condicoes mt-2 p-3 border rounded bg-gray-50">
        <?php echo nl2br(htmlspecialchars(html_entity_decode($proposta['condicoes'] ?? ''))); ?>
    </div>

    <div class="mt-4 flex gap-2">
        <a href="<?php echo BASE_URL; ?>/orcamento/pdf/<?php echo $proposta['id']; ?>" target="_blank" class="px-3 py-2 bg-rose-600 text-white rounded">Gerar PDF</a>
        <a href="<?php echo BASE_URL; ?>/orcamento/editar/<?php echo $proposta['id']; ?>" class="px-3 py-2 bg-yellow-500 text-white rounded">Editar</a>
        <button onclick="openEmailModal()" class="px-3 py-2 bg-green-600 text-white rounded">Enviar por E-mail</button>
        <button onclick="obterLinkPublico(<?php echo $proposta['id']; ?>)" class="px-3 py-2 bg-sky-600 text-white rounded">Link Público</button>
        <button onclick="enviarWhatsApp(<?php echo $proposta['id']; ?>, '<?php echo addslashes($proposta['nome_proposta'] ?? $proposta['titulo']); ?>', '<?php echo $proposta['cliente_telefone'] ?? ''; ?>')" class="px-3 py-2 bg-emerald-500 text-white rounded flex items-center gap-1">
            <i class="fab fa-whatsapp"></i> WhatsApp
        </button>
        <a href="<?php echo BASE_URL; ?>/orcamento/historico/<?php echo $proposta['id']; ?>" class="px-3 py-2 bg-blue-500 text-white rounded">Histórico</a>
        <a href="<?php echo BASE_URL; ?>/orcamento/index" class="px-3 py-2 bg-gray-300 rounded">Voltar</a>
    </div>
</div>

<!-- Modal de Envio de E-mail -->
<div id="emailModal" class="fixed inset-0 bg-gray-800 dark:bg-black bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl">
        <form action="<?php echo BASE_URL; ?>/orcamento/enviarEmail/<?php echo $proposta['id']; ?>" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>" />
            <h3 class="text-xl font-bold mb-4 dark:text-white">Enviar Proposta por E-mail</h3>

            <div class="mb-4">
                <label for="email_destinatario" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Para:</label>
                <input type="email" name="email_destinatario" id="email_destinatario" required class="w-full border dark:border-gray-700 dark:bg-gray-700 dark:text-white rounded p-2 mt-1" placeholder="email@cliente.com">
            </div>

            <div class="mb-4">
                <label for="email_assunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assunto:</label>
                <input type="text" name="email_assunto" id="email_assunto" required class="w-full border dark:border-gray-700 dark:bg-gray-700 dark:text-white rounded p-2 mt-1" value="Proposta Comercial: <?php echo htmlspecialchars($proposta['titulo']); ?>">
            </div>

            <div class="mb-4">
                <label for="email_corpo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mensagem:</label>
                <textarea name="email_corpo" id="email_corpo" rows="6" class="w-full border dark:border-gray-700 dark:bg-gray-700 dark:text-white rounded p-2 mt-1">Prezado(a),

Segue em anexo nossa proposta comercial referente a "<?php echo htmlspecialchars($proposta['titulo']); ?>".

Ficamos à disposição para qualquer esclarecimento.

Atenciosamente,
Sua Empresa.</textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeEmailModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 dark:text-white rounded font-bold">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded font-bold">Enviar</button>
            </div>
        </form>
    </div>
</div>

<div id="linkModal" class="fixed inset-0 bg-gray-800 dark:bg-black bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg p-6">
        <h3 class="text-xl font-bold mb-4 tracking-tight text-gray-800 dark:text-white">Link de Aprovação Pública</h3>
        <p class="text-sm text-gray-500 mb-4">Envie este link para o cliente. Ele permite visualizar os detalhes básicos e aprovar/rejeitar a proposta sem necessidade de login no sistema.</p>
        
        <div class="flex gap-2 mb-6">
            <input type="text" id="inputLinkPublico" readonly class="flex-1 bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-600 outline-none">
            <button onclick="copyToClipboard()" class="bg-sky-600 text-white px-4 py-2 rounded-lg hover:bg-sky-700 transition flex items-center gap-2 font-bold text-sm">
                <i class="fas fa-copy"></i> Copiar
            </button>
        </div>

        <div class="flex justify-end">
            <button type="button" onclick="closeLinkModal()" class="px-6 py-2 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">Fechar</button>
        </div>
    </div>
</div>

<script>
    const emailModal = document.getElementById('emailModal');
    const linkModal = document.getElementById('linkModal');
    const inputLink = document.getElementById('inputLinkPublico');

    function openEmailModal() {
        emailModal.classList.remove('hidden');
    }

    function closeEmailModal() {
        emailModal.classList.add('hidden');
    }

    function openLinkModal() {
        linkModal.classList.remove('hidden');
    }

    function closeLinkModal() {
        linkModal.classList.add('hidden');
    }

    function obterLinkPublico(id) {
        fetch(`<?php echo BASE_URL; ?>/orcamento/gerarLinkPublico/${id}?origem=link`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    inputLink.value = data.link;
                    openLinkModal();
                } else {
                    alert(data.message || 'Erro ao gerar link.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Falha na comunicação com o servidor.');
            });
    }

    function copyToClipboard() {
        let url = inputLink.value.trim();
        
        // Garantia de protocolo: Se o link não começar com http, força o uso da origem atual
        if (url && !url.startsWith('http://') && !url.startsWith('https://')) {
            const protocol = window.location.protocol + "//";
            const host = window.location.host;
            url = protocol + host + (url.startsWith('/') ? '' : '/') + url;
        }

        if (!url) return;

        navigator.clipboard.writeText(url).then(() => {
            const btn = document.querySelector('button[onclick="copyToClipboard()"]');
            const icon = btn.querySelector('i');
            const originalText = btn.innerText;

            // Feedback Visual
            btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
            btn.classList.replace('bg-sky-600', 'bg-green-600');
            
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-copy"></i> Copiar';
                btn.classList.replace('bg-green-600', 'bg-sky-600');
            }, 2000);
        }).catch(err => {
            // Fallback para navegadores antigos
            inputLink.select();
            document.execCommand('copy');
            alert('Link copiado!');
        });
    }

    function enviarWhatsApp(id, titulo, telefone) {
        // Feedback visual de carregamento
        const originalCursor = document.body.style.cursor;
        document.body.style.cursor = 'wait';

        fetch(`<?php echo BASE_URL; ?>/orcamento/gerarLinkPublico/${id}?origem=whatsapp`)
            .then(res => res.json())
            .then(data => {
                document.body.style.cursor = originalCursor;
                if (data.success) {
                    // Garantimos que o link tenha espaços ao redor para melhor detecção
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

    async function updateProposalStatus(id, newStatus) {
        if (!confirm(`Tem certeza que deseja ${newStatus === 'Aprovada' ? 'aprovar' : 'rejeitar'} esta proposta?`)) {
            return;
        }

        const motivo = newStatus === 'Rejeitada' ? prompt('Por favor, informe o motivo da rejeição:') : '';

        const formData = new FormData();
        formData.append('status', newStatus);
        formData.append('motivo', motivo);

        const response = await fetch(`<?php echo BASE_URL; ?>/orcamento/updateStatusAjax/${id}`, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            alert(result.message);
            window.location.reload(); // Recarrega a página para refletir o novo status
        } else {
            alert(result.message);
        }
    }
</script>