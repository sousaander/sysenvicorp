-- Migration: Módulo de Parâmetros Contábeis
-- Expande lançamentos_contabeis para suporte a partida dobrada

-- Melhoria da tabela lancamentos_contabeis para partida dobrada
ALTER TABLE lancamentos_contabeis
ADD COLUMN IF NOT EXISTS debito_conta_id INT AFTER conta,
ADD COLUMN IF NOT EXISTS credito_conta_id INT AFTER debito_conta_id,
ADD COLUMN IF NOT EXISTS origem ENUM('manual', 'financeiro', 'folha', 'estoque', 'contrato') DEFAULT 'manual' AFTER credito_conta_id,
ADD COLUMN IF NOT EXISTS origem_id INT AFTER origem,
ADD COLUMN IF NOT EXISTS conciliado TINYINT(1) DEFAULT 0 AFTER observacoes,
ADD COLUMN IF NOT EXISTS usuario_id INT AFTER conciliado,
ADD INDEX IF NOT EXISTS idx_debito_conta (debito_conta_id),
ADD INDEX IF NOT EXISTS idx_credito_conta (credito_conta_id);

-- Tabela de parâmetros contábeis
CREATE TABLE IF NOT EXISTS parametros_contabeis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    descricao VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de conciliação bancária
CREATE TABLE IF NOT EXISTS conciliacao_bancaria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    banco_id INT NOT NULL,
    periodo_inicio DATE NOT NULL,
    periodo_fim DATE NOT NULL,
    saldo_extrato DECIMAL(15,2) DEFAULT 0,
    saldo_sistema DECIMAL(15,2) DEFAULT 0,
    diferenca DECIMAL(15,2) DEFAULT 0,
    status ENUM('aberta', 'conciliada', 'divergente') DEFAULT 'aberta',
    observacoes TEXT,
    usuario_id INT,
    conciliada_em DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (banco_id) REFERENCES bancos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Itens da conciliação bancária
CREATE TABLE IF NOT EXISTS conciliacao_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conciliacao_id INT NOT NULL,
    transacao_id INT,
    tipo ENUM('extrato', 'sistema') NOT NULL,
    data_operacao DATE,
    descricao VARCHAR(500),
    valor DECIMAL(15,2),
    status_conciliacao ENUM('pendente', 'conciliado', 'divergente') DEFAULT 'pendente',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conciliacao_id) REFERENCES conciliacao_bancaria(id) ON DELETE CASCADE,
    FOREIGN KEY (transacao_id) REFERENCES transacoes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insere parâmetros contábeis padrão
INSERT IGNORE INTO parametros_contabeis (chave, valor, descricao) VALUES
('metodo_depreciacao', 'linear', 'Método de depreciação (linear/sac)'),
('regime_tributario', 'lucro_presumido', 'Regime tributário (simples_nacional/lucro_presumido/lucro_real)'),
('competencias_mes_fechamento', '12', 'Mês de fechamento do exercício fiscal'),
('rateio_por_centro_custo', '1', 'Habilitar rateio automático por centro de custo'),
('integrar_financeiro', '1', 'Integrar lançamentos do financeiro automaticamente'),
('integrar_folha', '1', 'Integrar lançamentos da folha de pagamento automaticamente'),
('integrar_estoque', '0', 'Integrar lançamentos de estoque automaticamente');
