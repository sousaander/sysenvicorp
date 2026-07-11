-- Migração: adiciona coluna valor_pago para suporte a pagamento parcial
ALTER TABLE transacoes
ADD COLUMN valor_pago DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER valor;

-- Atualiza registros existentes com status 'Pago' para considerar valor total como pago
UPDATE transacoes SET valor_pago = valor WHERE status = 'Pago';

SELECT 'ok' AS status;
