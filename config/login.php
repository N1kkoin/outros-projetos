<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_email']) && isset($_POST['login_password'])) {
    try {
        $login_email = filter_var($_POST['login_email'], FILTER_VALIDATE_EMAIL);
        if (!$login_email) {
            throw new Exception('E-mail inválido');
        }

        $login_password = $_POST['login_password'];

        // Verificar se o e-mail existe no banco de dados
        $stmt = $db->prepare('SELECT id, password FROM users WHERE email = ?');
        $stmt->execute([$login_email]);

        if ($stmt->rowCount() === 0) {
            throw new Exception('E-mail ou senha incorretos');
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!password_verify($login_password, $user['password'])) {
            throw new Exception('E-mail ou senha incorretos');
        }

        // Iniciar sessão e armazenar login do usuário
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_email'] = $login_email;

        // Redirecionar para o dashboard ou página principal
        header("Location: dashboard.php");
        exit();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
