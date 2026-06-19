<?php
// gerar_chave.php - Execute e depois delete!
echo "Sua chave secreta: " . bin2hex(random_bytes(32));
echo "\n\nCopie este valor para o .env em WEBHOOK_SECRET_KEY\n";