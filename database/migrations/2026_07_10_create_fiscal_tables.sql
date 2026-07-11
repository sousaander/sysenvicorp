-- Migration: Módulo Fiscal e Contábil
-- Cria as tabelas necessárias para o funcionamento do módulo

CREATE TABLE IF NOT EXISTS notas_fiscais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) NOT NULL,
    tipo ENUM('Entrada', 'Saida') NOT NULL,
    valor DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    emissao DATE NOT NULL,
    cliente_fornecedor VARCHAR(255) NOT NULL,
    cnpj_cpf VARCHAR(20),
    descricao TEXT,
    status VARCHAR(50) DEFAULT 'Pendente',
    chave_acesso VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lancamentos_contabeis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(500) NOT NULL,
    valor DECIMAL(15,2) NOT NULL,
    tipo ENUM('credito', 'debito') NOT NULL,
    categoria VARCHAR(100),
    data_lancamento DATE NOT NULL,
    conta VARCHAR(100),
    centro_custo VARCHAR(100),
    nota_fiscal_id INT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (nota_fiscal_id) REFERENCES notas_fiscais(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS plano_contas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('analitico', 'sintetico') NOT NULL DEFAULT 'analitico',
    natureza ENUM('devedora', 'credora') NOT NULL,
    conta_pai_id INT,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_pai_id) REFERENCES plano_contas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
