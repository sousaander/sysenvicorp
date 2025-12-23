<?php

if (!function_exists('get_bank_flag_url')) {
    /**
     * Retorna a URL da imagem da bandeira do banco com base no nome.
     * Procura por correspondências no nome do banco para associar ao arquivo de imagem.
     *
     * @param string $bankName O nome do banco.
     * @return string A URL completa para a imagem da bandeira.
     */
    function get_bank_flag_url(string $bankName): string
    {
        $base_url = BASE_URL . '/img/bank_flags/';
        $default_flag = $base_url . 'default.svg'; // Ícone padrão

        $normalized_name = strtolower($bankName);

        // Mapeamento de palavras-chave para nomes de arquivos de imagem
        $map = [
            'caixa principal (escritório)' => 'caixa-principal-escritorio.png',
            'itau' => 'itau.svg',
            'itaú' => 'itau.svg',
            'bradesco' => 'bradesco.svg',
            'caixa' => 'caixa.svg',
            'santander' => 'santander.png',
            'banco do brasil' => 'bancodobrasil.svg',
            'bb' => 'bancodobrasil.svg',
            'sicoob' => 'sicoob.svg',
            'sicredi' => 'sicredi.svg',
            'inter' => 'inter.svg',
            'nubank' => 'nubank.svg',
            'cora' => 'cora.png',
        ];

        foreach ($map as $keyword => $file) {
            if (strpos($normalized_name, $keyword) !== false) {
                return $base_url . $file;
            }
        }

        return $default_flag;
    }
}

if (!function_exists('get_tipo_transacao_texto')) {
    /**
     * Retorna o texto legível para o tipo de transação.
     * Ex: 'R' => 'Receita', 'P' => 'Despesa'.
     */
    function get_tipo_transacao_texto(?string $tipo): string
    {
        $map = [
            'R' => 'Receita',
            'P' => 'Despesa',
            'T' => 'Transferência',
        ];
        return $map[$tipo] ?? 'N/D';
    }
}

if (!function_exists('get_tipo_transacao_classes')) {
    /**
     * Retorna classes CSS para o badge do tipo de transação.
     */
    function get_tipo_transacao_classes(?string $tipo): string
    {
        $map = [
            'R' => 'bg-green-100 text-green-800',
            'P' => 'bg-red-100 text-red-800',
            'T' => 'bg-sky-100 text-sky-800',
        ];
        return $map[$tipo] ?? 'bg-gray-100 text-gray-800';
    }
}

if (!function_exists('get_status_config')) {
    /**
     * Retorna um array com 'texto' e 'classes' para exibição do status.
     * Se o tipo for receita ('R'), adapta o texto de 'Pago' para 'Recebido'.
     */
    function get_status_config(string $status, ?string $tipo = null): array
    {
        $statusMap = [
            'Pago' => ['texto' => ($tipo === 'R' ? 'Recebido' : 'Pago'), 'classes' => 'bg-emerald-200 text-emerald-800'],
            'Pendente' => ['texto' => 'Pendente', 'classes' => 'bg-yellow-200 text-yellow-800'],
            'Atrasado' => ['texto' => 'Atrasado', 'classes' => 'bg-red-200 text-red-800'],
            'Cancelado' => ['texto' => 'Cancelado', 'classes' => 'bg-gray-200 text-gray-800'],
        ];

        return $statusMap[$status] ?? ['texto' => htmlspecialchars($status), 'classes' => 'bg-gray-200 text-gray-800'];
    }
}

if (!function_exists('get_transfer_type')) {
    /**
     * Determina se uma transação é uma entrada ou saída de transferência.
     * Retorna 'in', 'out', ou null.
     *
     * @param array $transacao
     * @return string|null
     */
    function get_transfer_type(array $transacao): ?string
    {
        $doc = trim((string)($transacao['documento_vinculado'] ?? ''));
        $obs = trim((string)($transacao['observacoes'] ?? ''));
        $desc = trim((string)($transacao['descricao'] ?? ''));

        // Prioridade 1: 'documento_vinculado' (mais confiável)
        if ($doc !== '') {
            if (stripos($doc, 'transfer_out:') !== false) return 'out';
            if (stripos($doc, 'transfer_in:') !== false) return 'in';
        }

        // Prioridade 2: 'observacoes' (legado ou fallback)
        if ($obs !== '') {
            if (stripos($obs, 'transferencia_out') !== false) return 'out';
            if (stripos($obs, 'transferencia_in') !== false) return 'in';
        }

        // Prioridade 3: 'descricao' (menos confiável, baseado em texto)
        // "Transferência para" significa que o dinheiro está SAINDO desta conta.
        if (stripos($desc, 'Transferência para') !== false) {
            return 'out';
        }
        // "Transferência de" significa que o dinheiro está ENTRANDO nesta conta.
        if (stripos($desc, 'Transferência de') !== false) {
            return 'in';
        }

        return null;
    }
}
