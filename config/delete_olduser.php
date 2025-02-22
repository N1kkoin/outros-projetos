<?php
require_once 'config.php';

// Verificar e deletar usuários que não completaram o registro em 24 horas
$stmt = $db->prepare('DELETE FROM users WHERE password IS NULL AND created_at < NOW() - INTERVAL 1 DAY');
$stmt->execute();

echo "Usuários não registrados há mais de 24 horas foram removidos.";
?>
