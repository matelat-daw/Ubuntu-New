<?php
/**
 * Email Helper
 */

class EmailHelper {
    
    public static function sendVerificationEmail($email, $name, $token) {
        $verificationUrl = API_URL . "/controllers/auth/verify-email.php?token=" . urlencode($token);
        
        $subject = "Verifica tu cuenta - Business E-commerce";
        $body = self::getVerificationTemplate($name, $verificationUrl);
        
        return self::sendEmail($email, $subject, $body);
    }
    
    public static function sendPasswordResetEmail($email, $name, $token) {
        $resetUrl = FRONTEND_URL . "/reset-password.php?token=" . urlencode($token);
        
        $subject = "Restablece tu contraseña - Business E-commerce";
        $body = self::getPasswordResetTemplate($name, $resetUrl);
        
        return self::sendEmail($email, $subject, $body);
    }
    
    /**
     * Send a plain text email
     */
    public static function sendPlainEmail($to, $subject, $body) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/plain; charset=UTF-8" . "\r\n";
        $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">" . "\r\n";
        $headers .= "Reply-To: " . SMTP_FROM . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        $success = mail($to, $subject, $body, $headers);
        
        if (!$success) {
            error_log("Failed to send plain email to: " . $to);
        }
        
        return $success;
    }
    
    /**
     * Send a custom HTML email
     */
    public static function sendHtmlEmail($to, $subject, $htmlBody) {
        return self::sendEmail($to, $subject, $htmlBody);
    }
    
    private static function sendEmail($to, $subject, $htmlBody) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">" . "\r\n";
        $headers .= "Reply-To: " . SMTP_FROM . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        $success = mail($to, $subject, $htmlBody, $headers);
        
        if (!$success) {
            error_log("Failed to send email to: " . $to);
        }
        
        return $success;
    }
    
    private static function getVerificationTemplate($name, $verificationUrl) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 30px 20px; }
                .content h2 { color: #667eea; margin-top: 0; }
                .button { display: inline-block; padding: 14px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                .button:hover { opacity: 0.9; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 12px; }
                .link { word-break: break-all; color: #667eea; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🛒 Business E-commerce</h1>
                </div>
                <div class='content'>
                    <h2>¡Hola, {$name}!</h2>
                    <p>¡Bienvenido a Business E-commerce! Estamos emocionados de tenerte con nosotros.</p>
                    <p>Para activar tu cuenta y comenzar a disfrutar de todos nuestros servicios, por favor verifica tu dirección de email haciendo clic en el botón de abajo:</p>
                    <div style='text-align: center;'>
                        <a href='{$verificationUrl}' class='button'>✓ Verificar mi Email</a>
                    </div>
                    <p>O copia y pega este enlace en tu navegador:</p>
                    <p class='link'>{$verificationUrl}</p>
                    <p><strong>Este enlace expirará en 24 horas.</strong></p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 14px; color: #666;'>Si no creaste esta cuenta, puedes ignorar este email de forma segura.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Business E-commerce. Todos los derechos reservados.</p>
                    <p>Este es un email automático, por favor no respondas a este mensaje.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private static function getPasswordResetTemplate($name, $resetUrl) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px 20px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 30px 20px; }
                .content h2 { color: #f5576c; margin-top: 0; }
                .button { display: inline-block; padding: 14px 30px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white !important; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                .button:hover { opacity: 0.9; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 12px; }
                .link { word-break: break-all; color: #f5576c; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Restablecer Contraseña</h1>
                </div>
                <div class='content'>
                    <h2>Hola, {$name}</h2>
                    <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en Business E-commerce.</p>
                    <p>Haz clic en el botón de abajo para crear una nueva contraseña:</p>
                    <div style='text-align: center;'>
                        <a href='{$resetUrl}' class='button'>🔄 Restablecer Contraseña</a>
                    </div>
                    <p>O copia y pega este enlace en tu navegador:</p>
                    <p class='link'>{$resetUrl}</p>
                    <p><strong>Este enlace expirará en 1 hora.</strong></p>
                    <div class='warning'>
                        <p style='margin: 0;'><strong>⚠️ Importante:</strong> Si no solicitaste restablecer tu contraseña, ignora este email. Tu cuenta permanecerá segura.</p>
                    </div>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Business E-commerce. Todos los derechos reservados.</p>
                    <p>Este es un email automático, por favor no respondas a este mensaje.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
