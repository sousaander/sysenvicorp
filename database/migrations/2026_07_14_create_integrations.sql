-- Migration: Integrações Contábeis
-- Folha de Pagamento, Estoque e Mapeamento Contábil

-- ========================
-- FOLHA DE PAGAMENTO
-- ========================

-- Resultados persistidos da folha de pagamento
CREATE TABLE IF NOT EXISTS folha_pagamento_resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT,
    mes INT NOT NULL,
    ano INT NOT NULL,
    salario_bruto DECIMAL(15,2) DEFAULT 0,
    inss DECIMAL(15,2) DEFAULT 0,
    irrf DECIMAL(15,2) DEFAULT 0,
    salario_familia DECIMAL(15,2) DEFAULT 0,
    outros_descontos DECIMAL(15,2) DEFAULT 0,
    valor_liquido DECIMAL(15,2) DEFAULT 0,
    fgts DECIMAL(15,2) DEFAULT 0,
    base_calculo_inss DECIMAL(15,2) DEFAULT 0,
    base_calculo_irrf DECIMAL(15,2) DEFAULT 0,
    data_pagamento DATE,
    status VARCHAR(20) DEFAULT 'calculado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(colaborador_id) ON DELETE SET NULL,
    UNIQUE KEY uk_folha_mes_ano (colaborador_id, mes, ano)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Provisões contábeis trabalhistas
CREATE TABLE IF NOT EXISTS provisoes_contabeis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_provisao ENUM('13_salario', 'ferias', 'fgts', 'inss', 'irrf', 'rescisao') NOT NULL,
    colaborador_id INT,
    mes_competencia INT NOT NULL,
    ano_competencia INT NOT NULL,
    valor_provisionado DECIMAL(15,2) DEFAULT 0,
    valor_pago DECIMAL(15,2) DEFAULT 0,
    data_contabilizacao DATE,
    status VARCHAR(20) DEFAULT 'provisionado',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(colaborador_id) ON DELETE SET NULL,
    KEY idx_provisao_tipo (tipo_provisao, mes_competencia, ano_competencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================
-- ESTOQUE
-- ========================

-- Cadastro de produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    categoria VARCHAR(100),
    unidade VARCHAR(20) DEFAULT 'UN',
    ncm VARCHAR(10),
    cest VARCHAR(10),
    aliquota_icms DECIMAL(5,2) DEFAULT 0,
    aliquota_ipi DECIMAL(5,2) DEFAULT 0,
    aliquota_pis DECIMAL(5,2) DEFAULT 0,
    aliquota_cofins DECIMAL(5,2) DEFAULT 0,
    custo_aquisicao DECIMAL(15,2) DEFAULT 0,
    despesas_acessorias DECIMAL(15,2) DEFAULT 0,
    margem_lucro DECIMAL(5,2) DEFAULT 0,
    preco_venda DECIMAL(15,2) DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Movimentações de estoque
CREATE TABLE IF NOT EXISTS estoque_movimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    tipo_movimento ENUM('entrada', 'saida', 'ajuste', 'transferencia') NOT NULL,
    quantidade DECIMAL(15,3) NOT NULL,
    valor_unitario DECIMAL(15,4) DEFAULT 0,
    valor_total DECIMAL(15,2) DEFAULT 0,
    documento VARCHAR(100),
    data_movimento DATE NOT NULL,
    observacoes TEXT,
    usuario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    KEY idx_movimento_data (data_movimento),
    KEY idx_movimento_tipo (tipo_movimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Saldo atual por produto (custo médio)
CREATE TABLE IF NOT EXISTS estoque_saldo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL UNIQUE,
    quantidade DECIMAL(15,3) DEFAULT 0,
    custo_medio DECIMAL(15,4) DEFAULT 0,
    valor_total DECIMAL(15,2) DEFAULT 0,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventário físico
CREATE TABLE IF NOT EXISTS inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_inventario DATE NOT NULL,
    tipo ENUM('total', 'rotativo', 'amostragem') DEFAULT 'total',
    status VARCHAR(20) DEFAULT 'aberto',
    observacoes TEXT,
    usuario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Itens do inventário físico
CREATE TABLE IF NOT EXISTS inventario_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventario_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade_sistema DECIMAL(15,3) DEFAULT 0,
    quantidade_contada DECIMAL(15,3) DEFAULT 0,
    diferenca DECIMAL(15,3) DEFAULT 0,
    causa_ajuste VARCHAR(100),
    custo_medio DECIMAL(15,4) DEFAULT 0,
    valor_diferenca DECIMAL(15,2) DEFAULT 0,
    observacoes TEXT,
    FOREIGN KEY (inventario_id) REFERENCES inventario(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================
-- MAPEAMENTO CONTÁBIL
-- ========================

-- Mapeamento entre entidades (classificações, centros de custo, produtos) e contas contábeis
CREATE TABLE IF NOT EXISTS conta_contabil_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entidade_tipo ENUM('classificacao', 'centro_custo', 'produto_categoria') NOT NULL,
    entidade_id INT NOT NULL,
    conta_debito_id INT,
    conta_credito_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_debito_id) REFERENCES plano_contas(id) ON DELETE SET NULL,
    FOREIGN KEY (conta_credito_id) REFERENCES plano_contas(id) ON DELETE SET NULL,
    UNIQUE KEY uk_mapping (entidade_tipo, entidade_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
