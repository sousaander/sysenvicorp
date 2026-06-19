<?php
/**
 * Script de diagnóstico de extensões necessárias para PhpSpreadsheet e Dompdf
 */
$extensions = ['zip', 'gd', 'xml', 'mbstring', 'dom', 'iconv'];

echo "<h1>Diagnóstico de Extensões PHP - SysEnviCorp</h1>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #eee;'><th>Extensão</th><th>Status</th></tr>";

foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '<span style="color:green;">✅ Ativa</span>' : '<span style="color:red;">❌ Inativa</span>';
    echo "<tr><td><strong>$ext</strong></td><td>$status</td></tr>";
}
echo "</table>";

if (extension_loaded('gd')) {
    echo "<h3>Detalhes da Biblioteca GD:</h3><pre>";
    print_r(gd_info());
    echo "</pre>";
}

if (class_exists('ZipArchive')) {
    echo "<p style='color:green;'>✅ <strong>ZipArchive</strong> (classe necessária para Excel) está disponível.</p>";
} else {
    echo "<p style='color:red;'>❌ <strong>ZipArchive</strong> está indisponível.</p>";
}