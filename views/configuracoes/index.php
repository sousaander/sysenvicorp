<div class="bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Configurações Gerais do Sistema</h2>
    <p class="text-gray-600">
        Esta seção está em desenvolvimento. Em breve, você poderá gerenciar as configurações globais do sistema aqui.
    </p>
    <!-- Exemplo de futuras configurações -->
    <div class="mt-6 border-t pt-6">
        <h3 class="text-lg font-semibold text-gray-700">Configurações de E-mail</h3>
        <p class="text-gray-500 text-sm mb-4">Configurações para envio de notificações por e-mail.</p>
        <!-- Formulário de exemplo desabilitado -->
        <form>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="smtp_host" class="block text-sm font-medium text-gray-700">Servidor SMTP</label>
                    <input type="text" id="smtp_host" name="smtp_host" class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" placeholder="smtp.example.com" disabled>
                </div>
                <div>
                    <label for="smtp_port" class="block text-sm font-medium text-gray-700">Porta</label>
                    <input type="text" id="smtp_port" name="smtp_port" class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" placeholder="587" disabled>
                </div>
            </div>
        </form>
    </div>
</div>