<?php

// Definir 'email_step' se ainda não estiver definido
if (!isset($_SESSION['email_step'])) {
    $_SESSION['email_step'] = 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register_step1'])) {
        // Passo 1: Solicitar o código de verificação
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

        if (!$email) {
            echo 'E-mail inválido';
            exit;
        }

        // **Verificar se o e-mail já existe no banco**
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            echo 'Este e-mail já está cadastrado. Faça login em vez de criar uma nova conta.';
            exit;
        }

        // Gerar um código de verificação
        $verification_code = rand(100000, 999999);

        // Armazenar o código na sessão
        $_SESSION['verification_code'] = $verification_code;
        $_SESSION['email'] = $email;
        $_SESSION['email_step'] = 2;

        // Enviar o código por e-mail
        if (sendVerificationEmail($email, $verification_code)) {
            echo 'Código enviado para o seu e-mail.';
        } else {
            echo 'Falha ao enviar o código. Tente novamente.';
        }
    } elseif (isset($_POST['verify_code'])) {
        // Passo 2: Verificar o código
        $entered_code = $_POST['code'];

        if ($entered_code == $_SESSION['verification_code']) {
            $_SESSION['email_verified'] = true;
            $_SESSION['email_step'] = 3;
            echo 'Código verificado com sucesso! Agora, defina sua senha.';
        } else {
            echo 'Código incorreto.';
        }
    } elseif (isset($_POST['register_step2'])) {
        // Passo 3: Criar a conta com a senha
        if (!isset($_SESSION['email_verified']) || !$_SESSION['email_verified']) {
            echo 'Por favor, verifique seu e-mail primeiro.';
            exit;
        }

        $password = $_POST['password'];
        $password_confirm = $_POST['confirm_password'];

        if ($password !== $password_confirm) {
            echo 'As senhas não coincidem.';
            exit;
        }

        // Hash da senha
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Inserir no banco de dados
        $stmt = $db->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
        $stmt->execute([$_SESSION['email'], $password_hash]);

        echo 'Conta criada com sucesso!';
        session_destroy(); // Limpar sessão
    }
}
