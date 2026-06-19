<?php
// config/env.php
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        $_ENV[$name] = $value;
        putenv("{$name}={$value}");
    }
}

/**
 * Função auxiliar para obter variáveis de ambiente.
 * 
 * @param string $key Chave da variável.
 * @param mixed $default Valor padrão caso a chave não exista.
 * @return mixed
 */
function env($key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key);
    
    // Se o valor encontrado for 'root', vazio ou falso, e tivermos um valor padrão definido,
    // usamos o padrão. Isso evita que variáveis de sistema local sobrescrevam a produção.
    if ($value === false || $value === null || $value === '' || $value === 'root') {
        return $default;
    }
    return $value;
}

// Carrega o arquivo .env
loadEnv(__DIR__ . '/../../.env');