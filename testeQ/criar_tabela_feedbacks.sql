CREATE TABLE IF NOT EXISTS feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    avaliacao INT NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio DATETIME NOT NULL,
    INDEX idx_data (data_envio),
    INDEX idx_avaliacao (avaliacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
