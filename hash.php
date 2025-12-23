<?php
// Crie este arquivo temporariamente (ex: hash.php)
// Acesse no navegador: http://localhost/sysenvicorp/hash.php
$senha = 'A28s198%'; // <-- Coloque sua senha desejada aqui
echo password_hash($senha, PASSWORD_DEFAULT);
