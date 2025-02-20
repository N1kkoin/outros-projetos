<?php
require_once '../config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $db->query('SELECT * FROM tags ORDER BY name');
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tags);
    }
    

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare('INSERT INTO tags (name, color) VALUES (?, ?)');
    $stmt->execute([$data['name'], $data['color']]);
    http_response_code(201);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare('UPDATE tags SET name = ?, color = ? WHERE id = ?');
    $stmt->execute([$data['name'], $data['color'], $data['id']]);
    http_response_code(200);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'];
    $stmt = $db->prepare('DELETE FROM tags WHERE id = ?');
    $stmt->execute([$id]);
}
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}