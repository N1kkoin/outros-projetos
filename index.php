<?php
session_start();
require_once 'config.php';
require_once 'config/email.php';
require_once 'config/delete_olduser.php';

error_reporting(E_ALL);
ini_set('display_errors', 0); // Desabilitar exibição de erros para evitar poluir o JSON

ob_start();

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

$message = '';

// Processamento do envio de email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    try {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new Exception('E-mail inválido');
        }

        $verification_code = rand(100000, 999999);
        $_SESSION['verification_code'] = $verification_code;
        $_SESSION['email'] = $email;

        // Primeiro, verificar se o email já existe
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            // Email existe - apenas atualizar o código
            $stmt = $db->prepare('UPDATE users SET verification_code = ? WHERE email = ?');
            $stmt->execute([$verification_code, $email]);
        } else {
            // Email não existe - inserir novo registro
            $stmt = $db->prepare('INSERT INTO users (email, verification_code) VALUES (?, ?)');
            $stmt->execute([$email, $verification_code]);
        }

        if (sendVerificationEmail($email, $verification_code)) {
            $message = 'Código enviado para o seu e-mail.';
        } else {
            throw new Exception('Falha ao enviar o código. Tente novamente.');
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
}

// Processamento da criação da conta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && isset($_SESSION['email_verified']) && $_SESSION['email_verified']) {
    try {
        if ($_POST['password'] !== $_POST['confirm_password']) {
            throw new Exception('As senhas não coincidem.');
        }

        $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $db->prepare('UPDATE users SET password = ? WHERE email = ?');
        $stmt->execute([$password_hash, $_SESSION['email']]);
        $message = 'Conta criada com sucesso!';
        session_destroy();
    } catch (Exception $e) {
        $message = $e->getMessage();
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
        .field-group {
            margin-bottom: 15px;
        }

        .message {
            margin: 10px 0;
        }

        .message.error {
            color: #e74c3c;
        }

        .message.success {
            color: #2ecc71;
        }

        .disabled {
            background-color: #eee;
        }

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