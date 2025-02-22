<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './vendor/autoload.php';

function sendVerificationEmail($toEmail, $code)
{
    $mail = new PHPMailer(true);

    $my_email = "noreply@nikkoin.art";
    $minha_senha = 'I$L!ijyLwk\S?X9W"TY';

    try {
        // Configurações do servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = $my_email;
        $mail->Password = $minha_senha;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Alterado para SMTPS
        $mail->Port = 465;

        // Configurações de debug
        $mail->SMTPDebug = 0;


        // Configurações do email
        $mail->setFrom($my_email, 'Nikkoin Design');
        $mail->addAddress($toEmail);
        $mail->CharSet = 'UTF-8';
        
        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = 'Seu código de verificação';
        $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; padding: 20px;'>
                <h2>Código de Verificação</h2>
                <p>Seu código de verificação é: <strong style='font-size: 24px; color: #333;'>$code</strong></p>
                <p style='color: #666;'>Este código expira em 30 minutos.</p>
                <hr>
                <p style='font-size: 12px; color: #999;'>Este é um email automático, não responda.</p>
            </body>
            </html>";
        $mail->AltBody = "Seu código de verificação é: $code";

        // Tentativa de envio
        if($mail->send()) {
            error_log("Email enviado com sucesso para $toEmail");
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Erro ao enviar email para $toEmail: " . $mail->ErrorInfo);
        return false;
    }
}