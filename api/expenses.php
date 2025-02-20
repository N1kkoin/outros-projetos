<?php
require_once '../config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->query('
        SELECT e.*, t.name as tag_name, t.color as tag_color 
        FROM expenses e 
        JOIN tags t ON e.tag_id = t.id 
        ORDER BY e.created_at DESC 
        LIMIT 50
    ');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare('INSERT INTO expenses (amount, tag_id, description) VALUES (?, ?, ?)');
    $stmt->execute([$data['amount'], $data['tag_id'], $data['description']]);
    http_response_code(201);
}

// Nova rota para atualizar despesa
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare('UPDATE expenses SET amount = ?, tag_id = ?, description = ? WHERE id = ?');
    $stmt->execute([$data['amount'], $data['tag_id'], $data['description'], $data['id']]);
    http_response_code(200);
}

// Nova rota para deletar despesa
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'];
    $stmt = $db->prepare('DELETE FROM expenses WHERE id = ?');
    $stmt->execute([$id]);
    http_response_code(200);
}