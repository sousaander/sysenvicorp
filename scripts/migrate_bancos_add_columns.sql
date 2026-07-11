-- Migração: adiciona colunas necessárias ao schema de `bancos`
ALTER TABLE bancos
ADD COLUMN nome_titular VARCHAR(100) NULL
AFTER nome,
ADD COLUMN banco_codigo VARCHAR(20) NULL
AFTER logo,
    ADD COLUMN agencia VARCHAR(20) NULL
AFTER banco_codigo,
    ADD COLUMN agencia_dv VARCHAR(5) NULL
AFTER agencia,
    ADD COLUMN conta VARCHAR(30) NULL
AFTER agencia_dv,
    ADD COLUMN conta_dv VARCHAR(5) NULL
AFTER conta,
    ADD COLUMN pix_tipo VARCHAR(50) NULL
AFTER conta_dv,
    ADD COLUMN pix_chave VARCHAR(140) NULL
AFTER pix_tipo,
    ADD COLUMN limite_credito DECIMAL(15, 2) NOT NULL DEFAULT 0.00
AFTER saldo_inicial,
    ADD COLUMN cor VARCHAR(7) NOT NULL DEFAULT '#10b981'
AFTER limite_credito,
    ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1
AFTER cor,
    ADD COLUMN observacoes TEXT NULL
AFTER ativo;
-- Verificação final
SELECT 'ok' AS status;