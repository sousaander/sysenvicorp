<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .field-group {
            margin-bottom: 0.5rem;
        }

        .field-label {
            font-size: 0.75rem;
            color: #6B7280;
        }

        .field-value {
            font-size: 0.875rem;
            font-weight: 500;
            color: #111827;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1F2937;
            border-bottom: 2px solid #E5E7EB;
            padding-bottom: 0.5rem;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body class="bg-gray-100">

    <div class="max-w-4xl mx-auto my-8 bg-white p-8 shadow-lg" id="print-area">
        <!-- Cabeçalho -->
        <div class="flex justify-between items-center border-b-2 pb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Ficha Cadastral de Funcionário</h1>
                <p class="text-sm text-gray-500">EnviCorp Soluções Ambientais</p>
            </div>
            <div class="no-print flex items-center gap-3">
                <button onclick="window.close()" class="bg-gray-500 text-white px-4 py-2 rounded-md shadow-sm hover:bg-gray-600">
                    &larr; Voltar
                </button>
                <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow-sm hover:bg-blue-700">
                    Imprimir / Salvar PDF
                </button>
            </div>
        </div>

        <!-- Dados Pessoais -->
        <h2 class="section-title">Dados Pessoais</h2>
        <div class="grid grid-cols-4 gap-x-6 gap-y-4">
            <div class="col-span-1">
                <img src="<?php echo htmlspecialchars($funcionario['foto_url'] ?? 'https://placehold.co/96x96'); ?>" alt="Foto do Funcionário" class="h-32 w-32 object-cover rounded-md border">
            </div>
            <div class="col-span-3 grid grid-cols-3 gap-x-6 gap-y-4">
                <div class="col-span-3 field-group">
                    <p class="field-label">Nome Completo</p>
                    <p class="field-value"><?php echo htmlspecialchars($funcionario['nome'] ?? 'N/A'); ?></p>
                </div>
                <div class="field-group">
                    <p class="field-label">Data de Nascimento</p>
                    <p class="field-value"><?php echo $funcionario['data_nascimento'] ? date('d/m/Y', strtotime($funcionario['data_nascimento'])) : 'N/A'; ?></p>
                </div>
                <div class="field-group">
                    <p class="field-label">Estado Civil</p>
                    <p class="field-value"><?php echo htmlspecialchars($funcionario['estado_civil'] ?? 'N/A'); ?></p>
                </div>
                <div class="field-group">
                    <p class="field-label">CPF</p>
                    <p class="field-value"><?php echo htmlspecialchars($funcionario['cpf'] ?? 'N/A'); ?></p>
                </div>
                <div class="field-group">
                    <p class="field-label">RG</p>
                    <p class="field-value"><?php echo htmlspecialchars($funcionario['rg'] ?? 'N/A'); ?></p>
                </div>
                <div class="field-group">
                    <p class="field-label">Celular</p>
                    <p class="field-value"><?php echo htmlspecialchars($funcionario['celular'] ?? 'N/A'); ?></p>
                </div>
                <div class="col-span-2 field-group">
                    <p class="field-label">E-mail Pessoal</p>
                    <p class="field-value"><?php echo htmlspecialchars($funcionario['email'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>

        <!-- Documentação -->
        <h2 class="section-title">Documentação</h2>
        <div class="grid grid-cols-4 gap-x-6 gap-y-4">
            <div class="field-group">
                <p class="field-label">Carteira de Trabalho (CTPS)</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['ctps'] ?? 'N/A'); ?></p>
            </div>
            <div class="field-group">
                <p class="field-label">PIS/PASEP</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['pis'] ?? 'N/A'); ?></p>
            </div>
            <div class="field-group">
                <p class="field-label">Título de Eleitor</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['titulo_eleitor'] ?? 'N/A'); ?></p>
            </div>
            <div class="field-group">
                <p class="field-label">Reservista</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['reservista'] ?? 'N/A'); ?></p>
            </div>
            <div class="field-group">
                <p class="field-label">CNH</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['cnh'] ?? 'N/A'); ?></p>
            </div>
            <div class="field-group">
                <p class="field-label">Categoria CNH</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['cnh_categoria'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <!-- Endereço -->
        <h2 class="section-title">Endereço Residencial</h2>
        <div class="grid grid-cols-6 gap-x-6 gap-y-4">
            <div class="col-span-4 field-group">
                <p class="field-label">Logradouro</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['endereco'] ?? 'N/A'); ?></p>
            </div>
            <div class="col-span-2 field-group">
                <p class="field-label">Bairro</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['bairro'] ?? 'N/A'); ?></p>
            </div>
            <div class="col-span-2 field-group">
                <p class="field-label">Cidade</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['cidade'] ?? 'N/A'); ?></p>
            </div>
            <div class="field-group">
                <p class="field-label">UF</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['uf'] ?? 'N/A'); ?></p>
            </div>
            <div class="field-group">
                <p class="field-label">CEP</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['cep'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <!-- Dados Profissionais -->
        <h2 class="section-title">Dados Profissionais</h2>
        <div class="grid grid-cols-4 gap-x-6 gap-y-4">
            <div class="field-group">
                <p class="field-label">Data de Admissão</p>
                <p class="field-value"><?php echo $funcionario['data_admissao'] ? date('d/m/Y', strtotime($funcionario['data_admissao'])) : 'N/A'; ?></p>
            </div>
            <div class="field-group">
                <p class="field-label">Cargo/Função</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['cargo'] ?? 'N/A'); ?></p>
            </div>
            <div class="field-group">
                <p class="field-label">Setor</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['setor'] ?? 'N/A'); ?></p>
            </div>
            <div class="field-group">
                <p class="field-label">Salário</p>
                <p class="field-value">R$ <?php echo number_format($funcionario['salario'] ?? 0, 2, ',', '.'); ?></p>
            </div>
        </div>

        <!-- Dados Bancários -->
        <h2 class="section-title">Dados Bancários</h2>
        <div class="grid grid-cols-4 gap-x-6 gap-y-4">
            <div class="field-group">
                <p class="field-label">Banco</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['banco'] ?? 'N/A'); ?></p>
            </div>
            <div class="field-group">
                <p class="field-label">Agência</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['agencia'] ?? 'N/A'); ?></p>
            </div>
            <div class="field-group">
                <p class="field-label">Conta</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['conta'] ?? 'N/A'); ?> (<?php echo htmlspecialchars(ucfirst($funcionario['tipo_conta'] ?? '')); ?>)</p>
            </div>
            <div class="field-group">
                <p class="field-label">Chave PIX</p>
                <p class="field-value"><?php echo htmlspecialchars($funcionario['chave_pix'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <!-- Assinatura -->
        <div class="mt-24 pt-4 border-t-2 text-center">
            <p class="text-sm">_________________________________________</p>
            <p class="text-sm font-medium mt-1"><?php echo htmlspecialchars($funcionario['nome'] ?? 'N/A'); ?></p>
            <p class="text-xs text-gray-600">Assinatura do Funcionário</p>
        </div>
    </div>

</body>

</html>