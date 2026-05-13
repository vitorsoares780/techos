-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS sistema_ordens_servico;
USE sistema_ordens_servico;

-- Tabela de clientes
CREATE TABLE clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_completo VARCHAR(100) NOT NULL,
    cpf_rg VARCHAR(20) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    endereco TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de ordens de serviço
CREATE TABLE ordens_servico (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_ordem VARCHAR(20) UNIQUE NOT NULL,
    data_entrada DATETIME NOT NULL,
    cliente_id INT NOT NULL,
    marca_aparelho VARCHAR(50) NOT NULL,
    modelo_aparelho VARCHAR(50) NOT NULL,
    imei VARCHAR(20),
    estado_fisico TEXT,
    defeito_relatado TEXT NOT NULL,
    servico_realizar TEXT NOT NULL,
    pecas_utilizadas TEXT,
    valor_servico DECIMAL(10,2) NOT NULL,
    valor_pecas DECIMAL(10,2) DEFAULT 0,
    valor_total DECIMAL(10,2) NOT NULL,
    forma_pagamento VARCHAR(50),
    prazo_entrega DATE,
    status VARCHAR(50) DEFAULT 'Em orçamento',
    observacoes_tecnicas TEXT,
    data_conclusao DATETIME,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Inserir configurações do prestador (ajustar conforme necessário)
CREATE TABLE configuracao (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_prestador VARCHAR(100) NOT NULL,
    telefone_prestador VARCHAR(20) NOT NULL
);

INSERT INTO configuracao (nome_prestador, telefone_prestador) 
VALUES ('Técnico de Celulares', '(11) 99999-9999');