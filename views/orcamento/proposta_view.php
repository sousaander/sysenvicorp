<h2 class="text-2xl font-bold mb-4">Proposta #<?php echo $proposta['id']; ?> - <?php echo htmlspecialchars($proposta['titulo']); ?></h2>

<div class="bg-white p-6 rounded-lg shadow-md">
    <p class="text-sm text-gray-600">Status: <strong><?php echo htmlspecialchars($proposta['status']); ?></strong></p>
    <p class="mt-4"><strong>Valor Total:</strong> <?php echo !empty($proposta['valor_total']) ? 'R$ ' . number_format($proposta['valor_total'], 2, ',', '.') : '-'; ?></p>

    <h3 class="mt-4 font-semibold">Descrição Técnica</h3>
    <div class="proposta-descricao mt-2 p-3 border rounded bg-gray-50">
        <?php echo nl2br(htmlspecialchars($proposta['descricao_tecnica'] ?? '')); ?>
    </div>

    <h3 class="mt-4 font-semibold">Condições Comerciais</h3>
    <div class="proposta-condicoes mt-2 p-3 border rounded bg-gray-50">
        <?php echo nl2br(htmlspecialchars($proposta['condicoes'] ?? '')); ?>
    </div>

    <div class="mt-4 flex gap-2">
        <a href="<?php echo BASE_URL; ?>/orcamento/pdfProposta/<?php echo $proposta['id']; ?>" target="_blank" class="px-3 py-2 bg-rose-600 text-white rounded">Gerar PDF</a>
        <a href="<?php echo BASE_URL; ?>/orcamento/novaProposta?id=<?php echo $proposta['id']; ?>" class="px-3 py-2 bg-yellow-500 text-white rounded">Editar</a>
        <button onclick="openEmailModal()" class="px-3 py-2 bg-green-600 text-white rounded">Enviar por E-mail</button>
        <a href="<?php echo BASE_URL; ?>/orcamento/historicoProposta/<?php echo $proposta['id']; ?>" class="px-3 py-2 bg-blue-500 text-white rounded">Histórico</a>
        <a href="<?php echo BASE_URL; ?>/orcamento/propostas" class="px-3 py-2 bg-gray-300 rounded">Voltar</a>
    </div>
</div>

<!-- Modal de Envio de E-mail -->
<div id="emailModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
        <form action="<?php echo BASE_URL; ?>/orcamento/enviarEmailProposta/<?php echo $proposta['id']; ?>" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>" />
            <h3 class="text-xl font-bold mb-4">Enviar Proposta por E-mail</h3>

            <div class="mb-4">
                <label for="email_destinatario" class="block text-sm font-medium text-gray-700">Para:</label>
                <input type="email" name="email_destinatario" id="email_destinatario" required class="w-full border rounded p-2 mt-1" placeholder="email@cliente.com">
            </div>

            <div class="mb-4">
                <label for="email_assunto" class="block text-sm font-medium text-gray-700">Assunto:</label>
                <input type="text" name="email_assunto" id="email_assunto" required class="w-full border rounded p-2 mt-1" value="Proposta Comercial: <?php echo htmlspecialchars($proposta['titulo']); ?>">
            </div>

            <div class="mb-4">
                <label for="email_corpo" class="block text-sm font-medium text-gray-700">Mensagem:</label>
                <textarea name="email_corpo" id="email_corpo" rows="6" class="w-full border rounded p-2 mt-1">Prezado(a),

Segue em anexo nossa proposta comercial referente a "<?php echo htmlspecialchars($proposta['titulo']); ?>".

Ficamos à disposição para qualquer esclarecimento.

Atenciosamente,
Sua Empresa.</textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeEmailModal()" class="px-4 py-2 bg-gray-300 rounded">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Enviar</button>
            </div>
        </form>
    </div>
</div>

<script>
    const emailModal = document.getElementById('emailModal');
    function openEmailModal() { emailModal.classList.remove('hidden'); }
    function closeEmailModal() { emailModal.classList.add('hidden'); }
</script>