CREATE TABLE IF NOT EXISTS aliquotas_fiscais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uf CHAR(2),
    municipio VARCHAR(100),
    codigo_servico VARCHAR(20),
    aliquota_iss DECIMAL(5,2) DEFAULT 0,
    cnae VARCHAR(10),
    cst_pis VARCHAR(3),
    cst_cofins VARCHAR(3),
    cfop_padrao VARCHAR(4),
    regime_tributario ENUM('Simples Nacional','Lucro Presumido','Lucro Real') DEFAULT NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
