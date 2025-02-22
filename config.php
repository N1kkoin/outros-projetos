<?php
$host = 'localhost';  // ou o IP do seu servidor de banco de dados
$dbname = 'expense_tracker';  // substitua pelo nome do seu banco de dados
$username = 'root';  // substitua pelo nome do seu usuário
$password = '';  // substitua pela senha do seu usuário

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Erro de conexão: ' . $e->getMessage();
}



/*
CREATE DATABASE IF NOT EXISTS expense_tracker;
USE expense_tracker;

CREATE TABLE tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(7) DEFAULT '#6c757d',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    amount DECIMAL(10,2) NOT NULL,
    tag_id INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tag_id) REFERENCES tags(id)
);

INSERT INTO tags (name, color) VALUES 
('Alimentação', '#28a745'),
('Transporte', '#007bff'),
('Lazer', '#17a2b8'),
('Contas', '#dc3545'),
('Saúde', '#ffc107');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255),   -- Adicionando o campo para a senha
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verification_code INT NOT NULL
);


*/