CREATE TABLE IF NOT EXISTS retencoes_impostos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nota_fiscal_id INT,
    tipo_retencao ENUM('IRRF','INSS','ISS','PIS','COFINS','CSLL') NOT NULL,
    base_calculo DECIMAL(15,2) NOT NULL DEFAULT 0,
    aliquota DECIMAL(5,2) NOT NULL DEFAULT 0,
    valor DECIMAL(15,2) NOT NULL DEFAULT 0,
    codigo_receita VARCHAR(10),
    competencia DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nota_fiscal_id) REFERENCES notas_fiscais(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
