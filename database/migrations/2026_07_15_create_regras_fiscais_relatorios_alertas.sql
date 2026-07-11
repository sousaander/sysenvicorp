-- Migration: Regras Fiscais, Relatórios Personalizados, Alertas e Legislação
-- Boas práticas para parametrização, auditoria e conformidade fiscal

CREATE TABLE IF NOT EXISTS regras_fiscais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT DEFAULT NULL,
    tipo_entidade ENUM('produto','servico','ambos') NOT NULL DEFAULT 'produto',
    regime_tributario ENUM('simples_nacional','lucro_presumido','lucro_real','mei') DEFAULT NULL,
    cfop VARCHAR(10) DEFAULT NULL COMMENT 'Código Fiscal de Operações e Prestações',
    cst_icms VARCHAR(4) DEFAULT NULL,
    csosn VARCHAR(4) DEFAULT NULL COMMENT 'Código de Situação da Operação Simples Nacional',
    cst_ipi VARCHAR(4) DEFAULT NULL,
    cst_pis VARCHAR(4) DEFAULT NULL,
    cst_cofins VARCHAR(4) DEFAULT NULL,
    aliquota_icms DECIMAL(5,2) DEFAULT 0.00,
    aliquota_ipi DECIMAL(5,2) DEFAULT 0.00,
    aliquota_pis DECIMAL(5,2) DEFAULT 0.00,
    aliquota_cofins DECIMAL(5,2) DEFAULT 0.00,
    aliquota_iss DECIMAL(5,2) DEFAULT 0.00,
    reducao_base_icms DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Percentual de redução da base de cálculo ICMS',
    margem_st DECIMAL(5,2) DEFAULT 0.00 COMMENT 'MARGEM DE VALOR AGREGADO - MVA',
    base_calculo DECIMAL(5,2) DEFAULT NULL COMMENT 'Base de cálculo fixa se aplicável',
    enquadramento VARCHAR(100) DEFAULT NULL,
    ncm_obrigatorio TINYINT(1) DEFAULT 1,
    cest_obrigatorio TINYINT(1) DEFAULT 0,
    beneficio_fiscal VARCHAR(255) DEFAULT NULL,
    uf_origem CHAR(2) DEFAULT NULL,
    uf_destino CHAR(2) DEFAULT NULL,
    data_vigencia_inicio DATE DEFAULT NULL,
    data_vigencia_fim DATE DEFAULT NULL,
    ativo TINYINT(1) DEFAULT 1,
    criado_por INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS regras_fiscais_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    regra_id INT NOT NULL,
    campos_alterados TEXT NOT NULL,
    valores_anteriores TEXT,
    valores_novos TEXT,
    alterado_por INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (regra_id) REFERENCES regras_fiscais(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS modelos_relatorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    modulo VARCHAR(50) NOT NULL COMMENT 'contabil, fiscal, estoque, rh, financeiro',
    tipo VARCHAR(50) NOT NULL DEFAULT 'personalizado',
    config JSON NOT NULL COMMENT 'Estrutura: colunas, filtros, agrupamento, ordenacao, layout',
    colunas_personalizadas TEXT COMMENT 'Lista de colunas selecionáveis',
    parametros_personalizados TEXT COMMENT 'Parâmetros adicionais em JSON',
    rodape TEXT COMMENT 'Texto do rodapé do relatório',
    orientacao ENUM('retrato','paisagem') DEFAULT 'retrato',
    formato_padrao ENUM('pdf','xlsx','csv','html') DEFAULT 'pdf',
    ativo TINYINT(1) DEFAULT 1,
    criado_por INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS obrigacoes_fiscais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    orgao ENUM('federal','estadual','municipal','outros') NOT NULL DEFAULT 'federal',
    periodicidade ENUM('mensal','bimestral','trimestral','semestral','anual','eventual') NOT NULL,
    dia_vencimento INT NOT NULL COMMENT 'Dia do mês de vencimento',
    mes_referencia INT DEFAULT NULL COMMENT 'Mês de referência para cálculo (ex: 1 para janeiro)',
    forma_entrega VARCHAR(100) DEFAULT NULL,
    base_legal TEXT COMMENT 'Fundamentação legal',
    obrigatorio TINYINT(1) DEFAULT 1,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO obrigacoes_fiscais (nome, descricao, orgao, periodicidade, dia_vencimento, forma_entrega, base_legal) VALUES
('DAS - Simples Nacional', 'Documento de Arrecadação do Simples Nacional', 'federal', 'mensal', 20, 'PGDAS-D', 'Lei Complementar 123/2006'),
('Declaração de Débitos e Créditos Tributários Federais (DCTF)', 'Declaração de Débitos e Créditos Tributários Federais', 'federal', 'mensal', 15, 'Sistema DCTF', 'IN RFB 2.005/2021'),
('SPED Fiscal (EFD ICMS/IPI)', 'Escrituração Fiscal Digital ICMS/IPI', 'estadual', 'mensal', 15, 'SPED', 'Convênio ICMS 143/2006'),
('SPED Contribuições (EFD PIS/COFINS)', 'Escrituração Fiscal Digital PIS/COFINS', 'federal', 'mensal', 15, 'SPED', 'IN RFB 1.252/2012'),
('eSocial', 'Sistema de Escrituração Digital das Obrigações Fiscais, Previdenciárias e Trabalhistas', 'federal', 'mensal', 7, 'eSocial', 'Decreto 8.373/2014'),
('Declaração de Imposto de Renda Retido na Fonte (DIRF)', 'Declaração do Imposto de Renda Retido na Fonte', 'federal', 'anual', 28, 'DIRF', 'IN RFB 1.990/2020'),
('ICMS - Apuração Estadual', 'Apuração e recolhimento do ICMS', 'estadual', 'mensal', 15, 'SEFAZ', 'Legislação Estadual'),
('ISS - Apuração Municipal', 'Apuração e recolhimento do ISS', 'municipal', 'mensal', 15, 'NFSe', 'Lei Complementar 116/2003'),
('Contribuição Sindical Patronal', 'Contribuição Sindical Patronal', 'federal', 'anual', 31, 'GPS', 'CLT Art. 578'),
('DEFIS', 'Declaração de Informações Socioeconômicas e Fiscais', 'federal', 'anual', 31, 'PGDAS-D', 'Lei Complementar 123/2006'),
('GFIP/SEFIP', 'Guia de Recolhimento do FGTS e Informações à Previdência Social', 'federal', 'mensal', 7, 'SEFIP', 'Lei 8.036/1990'),
('CAGED', 'Cadastro Geral de Empregados e Desempregados', 'federal', 'mensal', 7, 'CAGED', 'Lei 4.923/1965');

CREATE TABLE IF NOT EXISTS calendario_fiscal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    obrigacao_id INT NOT NULL,
    ano INT NOT NULL,
    mes INT DEFAULT NULL,
    data_vencimento DATE NOT NULL,
    data_entrega DATE DEFAULT NULL,
    status ENUM('pendente','entregue','atrasado','dispensado') NOT NULL DEFAULT 'pendente',
    observacoes TEXT,
    entregue_por INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (obrigacao_id) REFERENCES obrigacoes_fiscais(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS alertas_fiscais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    calendario_id INT DEFAULT NULL,
    tipo ENUM('vencimento_proximo','atrasado','alteracao_legislacao','nao_conformidade','recomendacao') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT,
    prioridade ENUM('baixa','media','alta','critica') NOT NULL DEFAULT 'media',
    lido TINYINT(1) DEFAULT 0,
    usuario_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS legislacao_versoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modulo VARCHAR(50) NOT NULL COMMENT 'icms, pis, cofins, ipi, iss, trabalhistas',
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    tipo_ato VARCHAR(50) DEFAULT NULL COMMENT 'Lei, Decreto, IN, Portaria, Convênio',
    numero_ato VARCHAR(50) DEFAULT NULL,
    orgao_emissor VARCHAR(100) DEFAULT NULL,
    data_publicacao DATE DEFAULT NULL,
    data_vigencia DATE DEFAULT NULL,
    data_revogacao DATE DEFAULT NULL,
    arquivo_anexo VARCHAR(255) DEFAULT NULL,
    resumo_mudancas TEXT,
    impacto_esperado TEXT,
    versao VARCHAR(20) DEFAULT NULL,
    obrigatorio TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS log_atualizacao_fiscal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL COMMENT 'regra, obrigacao, legislacao, relatorio',
    acao VARCHAR(50) NOT NULL COMMENT 'criar, atualizar, excluir, importar, aplicar',
    descricao TEXT,
    entidade_id INT DEFAULT NULL,
    dados_anteriores TEXT,
    dados_novos TEXT,
    usuario_id INT DEFAULT NULL,
    origem VARCHAR(50) DEFAULT 'manual' COMMENT 'manual, automatico, api',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
