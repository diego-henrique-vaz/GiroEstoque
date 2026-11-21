CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comercio VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS produtos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    nome VARCHAR(120) NOT NULL,
    estoque_minimo INT NOT NULL DEFAULT 0 CHECK (estoque_minimo >= 0),
    saldo INT NOT NULL DEFAULT 0 CHECK (saldo >= 0),
    INDEX idx_produtos_usuario (usuario_id, nome),
    CONSTRAINT fk_produtos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS movimentacoes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id INT UNSIGNED NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL CHECK (quantidade > 0),
    saldo_apos INT NOT NULL CHECK (saldo_apos >= 0),
    registrado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_movimentacoes_produto (produto_id, id),
    CONSTRAINT fk_movimentacoes_produto FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
