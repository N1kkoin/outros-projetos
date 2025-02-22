<?php
session_start();
require_once 'config.php';
require_once 'config/email.php';
require_once 'config/delete_olduser.php';

// Tratamento da requisição AJAX separado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_code') {
    header('Content-Type: application/json');
    
    // Verifica se o código existe na sessão
    if (!isset($_SESSION['verification_code'])) {
        echo json_encode(['status' => 'error', 'message' => 'Nenhum código encontrado']);
        exit;
    }

    $entered_code = $_POST['code'];
    $stored_code = $_SESSION['verification_code'];
    
    if ($entered_code == $stored_code) {
        $_SESSION['email_verified'] = true;
        echo json_encode(['status' => 'success', 'message' => 'Código verificado com sucesso']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Código inválido']);
    }
    exit;
}

$message = '';

// Processamento do envio de email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $message = 'E-mail inválido';
    } else {
        $verification_code = rand(100000, 999999);
        $_SESSION['verification_code'] = $verification_code;
        $_SESSION['email'] = $email;

        $stmt = $db->prepare('INSERT INTO users (email, verification_code) VALUES (?, ?) ON DUPLICATE KEY UPDATE verification_code = ?');
        $stmt->execute([$email, $verification_code, $verification_code]);

        if (sendVerificationEmail($email, $verification_code)) {
            $message = 'Código enviado para o seu e-mail.';
        } else {
            $message = 'Falha ao enviar o código. Tente novamente.';
        }
    }
}

// Processamento da criação da conta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && isset($_SESSION['email_verified']) && $_SESSION['email_verified']) {
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $message = 'As senhas não coincidem.';
    } else {
        $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $db->prepare('UPDATE users SET password = ? WHERE email = ?');
        $stmt->execute([$password_hash, $_SESSION['email']]);
        $message = 'Conta criada com sucesso!';
        session_destroy();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta</title>
    <style>
        .field-group { margin-bottom: 15px; }
        .message { margin: 10px 0; }
        .message.error { color: #e74c3c; }
        .message.success { color: #2ecc71; }
        .disabled { background-color: #eee; }
        .verification-status {
            display: none;
            margin-left: 10px;
            font-size: 14px;
        }
        .verification-status.success {
            color: #2ecc71;
            display: inline;
        }
        .verification-status.error {
            color: #e74c3c;
            display: inline;
        }
        #debug {
            background: #f5f5f5;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
            display: none;
        }
    </style>
</head>
<body>
    <h2>Criar Conta</h2>
    
    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div id="debug">
        <strong>Debug Info:</strong>
        <pre id="debugInfo"></pre>
    </div>

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
        function debugLog(message) {
            console.log(message);
            const debugInfo = document.getElementById('debugInfo');
            debugInfo.textContent += message + '\n';
            document.getElementById('debug').style.display = 'block';
        }

        document.getElementById('code').addEventListener('input', function(e) {
            const code = e.target.value;
            debugLog('Code entered: ' + code);

            const passwordFields = document.querySelectorAll('input[type="password"]');
            const createAccountBtn = document.getElementById('createAccountBtn');
            const successMsg = document.getElementById('verificationSuccess');
            const errorMsg = document.getElementById('verificationError');

            if (code.length === 6) {
                debugLog('Code length is 6, preparing to send request');
                
                const formData = new FormData();
                formData.append('action', 'verify_code');
                formData.append('code', code);

                debugLog('Sending fetch request...');

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    debugLog('Response received');
                    return response.json();
                })
                .then(data => {
                    debugLog('Response data: ' + JSON.stringify(data));
                    
                    if (data.status === 'success') {
                        debugLog('Verification successful');
                        successMsg.style.display = 'inline';
                        errorMsg.style.display = 'none';
                        passwordFields.forEach(field => field.disabled = false);
                        createAccountBtn.disabled = false;
                        e.target.readOnly = true;
                    } else {
                        debugLog('Verification failed: ' + data.message);
                        successMsg.style.display = 'none';
                        errorMsg.style.display = 'inline';
                        passwordFields.forEach(field => field.disabled = true);
                        createAccountBtn.disabled = true;
                    }
                })
                .catch(error => {
                    debugLog('Error occurred: ' + error);
                    errorMsg.style.display = 'inline';
                });
            }
        });
    </script>
</body>
</html>