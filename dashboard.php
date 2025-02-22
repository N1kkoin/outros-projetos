<?php
session_start();
require_once 'config/delete_olduser.php';

// Verificar se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redireciona para o login se não estiver autenticado
    exit;
}

// Informações do usuário
$user_email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle</title>
</head>
<body>
    <h2>Bem-vindo, <?php echo htmlspecialchars($user_email); ?>!</h2>
    <p>Você está logado no painel de controle.</p>
    
    <a href="config/logout.php">Sair</a>
</body>
</html>
