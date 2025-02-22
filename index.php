<?php
session_start();
require_once 'config.php';
require_once 'config/email.php';
require_once 'config/delete_olduser.php';

require 'config/register.php';
require 'config/login.php';

$error_message = '';

// Verificação AJAX do código
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_code') {
    ob_end_clean(); // Limpar qualquer saída anterior
    header('Content-Type: application/json');

    try {
        if (!isset($_SESSION['verification_code'])) {
            throw new Exception('Nenhum código de verificação encontrado');
        }

        $entered_code = $_POST['code'];
        $stored_code = $_SESSION['verification_code'];

        if ($entered_code == $stored_code) {
            $_SESSION['email_verified'] = true;
            echo json_encode(['status' => 'success', 'message' => 'Código verificado com sucesso']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Código inválido']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// Processamento do envio de e-mail e verificação do e-mail no banco
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    try {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new Exception('E-mail inválido');
        }

        // Verificar se o email já existe
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            // O e-mail já está cadastrado, informando ao usuário
            $error_message = 'Este e-mail já está cadastrado. Faça login em vez de criar uma nova conta.';
            throw new Exception($error_message);
        }

        $verification_code = rand(100000, 999999);
        $_SESSION['verification_code'] = $verification_code;
        $_SESSION['email'] = $email;

        // Enviar o código de verificação
        if (sendVerificationEmail($email, $verification_code)) {
            $_SESSION['email_step'] = 2; // Passar para a próxima etapa
            $error_message = 'Código enviado para o seu e-mail.';
        } else {
            throw new Exception('Falha ao enviar o código. Tente novamente.');
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Processamento da criação da conta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && isset($_SESSION['email_verified']) && $_SESSION['email_verified']) {
    try {
        if ($_POST['password'] !== $_POST['confirm_password']) {
            throw new Exception('As senhas não coincidem.');
        }

        $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Inserir no banco de dados
        $stmt = $db->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
        $stmt->execute([$_SESSION['email'], $password_hash]);

        // Iniciar sessão e armazenar login do usuário
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_email'] = $_SESSION['email'];

        // Redirecionar para o dashboard
        header("Location: dashboard.php");
        exit();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta</title>
   
</head>
<body>
    <h2>Login</h2>

    <?php if ($error_message): ?>
        <div class="message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="login_email">E-mail:</label>
        <input type="email" name="login_email" id="login_email" required>

        <label for="login_password">Senha:</label>
        <input type="password" name="login_password" id="login_password" required>

        <button type="submit">Entrar</button>
    </form>

    <hr>
    <h2>Criar Conta</h2>


    <form method="POST" id="registrationForm">
        <div class="field-group">
            <label for="email">E-mail:</label><br>
            <input type="email" name="email" id="email" required
                value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>"
                <?php echo isset($_SESSION['email_verified']) && $_SESSION['email_verified'] ? 'readonly class="disabled"' : ''; ?>>
            <?php if (!isset($_SESSION['email_verified']) || !$_SESSION['email_verified']): ?>
                <button type="submit" id="sendCodeBtn">Enviar/Reenviar Código</button>
            <?php endif; ?>
        </div>

        <div class="field-group">
            <label for="code">Código de Verificação:</label><br>
            <input type="text" name="code" id="code" maxlength="6">
            <span id="verificationSuccess" class="verification-status success">✓ Código válido</span>
            <span id="verificationError" class="verification-status error">✗ Código inválido</span>
        </div>

        <div class="field-group">
            <label for="password">Senha:</label><br>
            <input type="password" name="password" id="password" disabled required>
        </div>

        <div class="field-group">
            <label for="confirm_password">Confirmar Senha:</label><br>
            <input type="password" name="confirm_password" id="confirm_password" disabled required>
        </div>

        <button type="submit" id="createAccountBtn" disabled>Criar Conta</button>
    </form>

    
    <script>
        document.getElementById('code').addEventListener('input', function(e) {
            const code = e.target.value;

            if (code.length === 6) {
                console.log('Enviando código:', code);

                const formData = new FormData();
                formData.append('action', 'verify_code');
                formData.append('code', code);

                fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        return response.text().then(text => {
                            console.log('Resposta raw:', text);
                            try {
                                // Encontra a parte JSON na resposta
                                const jsonMatch = text.match(/\{.*\}$/s);
                                if (!jsonMatch) throw new Error('Nenhum JSON válido encontrado na resposta');

                                return JSON.parse(jsonMatch[0]); // Faz parse do JSON encontrado
                            } catch (e) {
                                console.error('Erro ao fazer parse do JSON. Resposta completa:', text);
                                throw new Error('Resposta inválida do servidor');
                            }
                        });
                    })

                    .then(data => {
                        console.log('Dados processados:', data);
                        const successMsg = document.getElementById('verificationSuccess');
                        const errorMsg = document.getElementById('verificationError');
                        const passwordFields = document.querySelectorAll('input[type="password"]');
                        const createAccountBtn = document.getElementById('createAccountBtn');

                        if (data.status === 'success') {
                            successMsg.style.display = 'inline';
                            errorMsg.style.display = 'none';
                            passwordFields.forEach(field => field.disabled = false);
                            createAccountBtn.disabled = false;
                            e.target.readOnly = true;
                        } else {
                            successMsg.style.display = 'none';
                            errorMsg.style.display = 'inline';
                            passwordFields.forEach(field => field.disabled = true);
                            createAccountBtn.disabled = true;
                        }
                    })
                    .catch(error => {
                        console.error('Erro completo:', error);
                        document.getElementById('verificationError').style.display = 'inline';
                    });
            }
        });
    </script>
</body>
</html>
