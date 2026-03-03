<?php
/**
 * Email Service - Maneja el envío de correos electrónicos
 * Usa PHPMailer con SMTP (Gmail) — no requiere sendmail ni MTA del sistema
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Autoloader de Composer (PHPMailer)
require_once __DIR__ . '/../vendor/autoload.php';
// Configuración SMTP
require_once __DIR__ . '/../config/email.php';

class EmailService {
    private $fromEmail;
    private $fromName;
    private $baseUrl;

    public function __construct() {
        $this->fromEmail = SMTP_FROM_EMAIL;
        $this->fromName  = SMTP_FROM_NAME;

        // URL base para links de activación
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $this->baseUrl = $protocol . '://' . $host . '/Energy';
    }

    /**
     * Enviar email de activación de cuenta
     */
    public function sendActivationEmail($email, $username, $token) {
        $activationLink = $this->baseUrl . '/#activate?token=' . urlencode($token);
        
        $subject = 'Activa tu cuenta - ' . $this->fromName;
        
        $htmlMessage = $this->getActivationEmailTemplate($username, $activationLink);
        
        return $this->sendEmail($email, $subject, $htmlMessage);
    }

    /**
     * Template HTML para email de activación
     */
    private function getActivationEmailTemplate($username, $activationLink) {
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activa tu cuenta</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f4f4; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 40px 20px; text-align: center;">
                            <h1 style="margin: 0; color: white; font-size: 32px; font-weight: 900;">⚡ ¡Bienvenido a Energy App!</h1>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 40px 20px 40px;">
                            <h2 style="margin: 0 0 20px 0; color: #333; font-size: 24px; font-weight: 700;">
                                Hola {$username},
                            </h2>
                            <p style="margin: 0 0 20px 0; color: #666; font-size: 16px; line-height: 1.6;">
                                Gracias por registrarte en <strong>{$this->fromName}</strong>. Estamos emocionados de ayudarte a encontrar el mejor proveedor de energía.
                            </p>
                            <p style="margin: 0 0 20px 0; color: #666; font-size: 16px; line-height: 1.6;">
                                Para completar tu registro y comenzar a explorar las mejores ofertas de energía, por favor activa tu cuenta haciendo clic en el botón de abajo:
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Button -->
                    <tr>
                        <td style="padding: 0 40px 40px 40px;" align="center">
                            <a href="{$activationLink}" style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);">
                                ✓ Activar Mi Cuenta
                            </a>
                        </td>
                    </tr>
                    
                    <!-- Alternative Link -->
                    <tr>
                        <td style="padding: 0 40px 40px 40px;">
                            <p style="margin: 0 0 10px 0; color: #999; font-size: 14px; line-height: 1.6;">
                                Si el botón no funciona, copia y pega este enlace en tu navegador:
                            </p>
                            <p style="margin: 0; word-break: break-all;">
                                <a href="{$activationLink}" style="color: #10b981; font-size: 14px;">{$activationLink}</a>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Warning -->
                    <tr>
                        <td style="padding: 20px 40px; background-color: #fef3c7; border-top: 2px solid #fbbf24;">
                            <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.6;">
                                ⚠️ <strong>Importante:</strong> Este enlace expirará en 24 horas por seguridad.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; background-color: #f9f9f9; border-top: 1px solid #e0e0e0; text-align: center;">
                            <p style="margin: 0 0 10px 0; color: #999; font-size: 14px;">
                                Si no creaste esta cuenta, puedes ignorar este correo.
                            </p>
                            <p style="margin: 0; color: #999; font-size: 12px;">
                                © 2026 {$this->fromName}. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * Función principal de envío de email via PHPMailer + SMTP
     */
    private function sendEmail($to, $subject, $htmlMessage) {
        $mail = new PHPMailer(true); // true = lanza excepciones

        try {
            // ── Configuración del servidor SMTP ──────────────────────────
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            // ── Remitente y destinatario ──────────────────────────────────
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($this->fromEmail, $this->fromName);

            // ── Contenido ─────────────────────────────────────────────────
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlMessage;
            $mail->AltBody = strip_tags($htmlMessage);

            $mail->send();
            error_log("✉️ Email enviado exitosamente a: {$to}");
            return true;

        } catch (Exception $e) {
            error_log("❌ Error al enviar email a {$to}: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Generar token seguro de activación
     */
    public static function generateActivationToken() {
        return bin2hex(random_bytes(32)); // Token de 64 caracteres hexadecimales
    }
}
?>
