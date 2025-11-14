<?php
// Script de prueba para enviar email

$to = "cesarmatelat@gmail.com";
$subject = "Test desde Ubuntu Server - API Business";
$message = "
<html>
<head>
    <title>Test Email</title>
</head>
<body>
    <h2>Prueba de envío de correo</h2>
    <p>Este es un email de prueba desde el servidor Ubuntu con sendmail configurado para Gmail.</p>
    <p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>
    <p><strong>Servidor:</strong> " . php_uname() . "</p>
    <p><strong>PHP Version:</strong> " . phpversion() . "</p>
    <hr>
    <p>Si recibes este correo, la configuración de sendmail está funcionando correctamente.</p>
</body>
</html>
";

// Headers para HTML
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Business API <matelat@gmail.com>" . "\r\n";
$headers .= "Reply-To: matelat@gmail.com" . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

echo "Enviando correo de prueba...\n";
echo "Destinatario: $to\n";
echo "Asunto: $subject\n";
echo "---\n";

if (mail($to, $subject, $message, $headers)) {
    echo "✅ Correo enviado exitosamente!\n";
    echo "Verifica tu bandeja de entrada en: $to\n";
    echo "\nSi no lo ves, revisa la carpeta de SPAM.\n";
} else {
    echo "❌ Error al enviar el correo.\n";
    echo "Revisa los logs: /var/log/mail.log\n";
}

echo "\n--- Configuración PHP ---\n";
echo "sendmail_path: " . ini_get('sendmail_path') . "\n";
echo "sendmail_from: " . ini_get('sendmail_from') . "\n";
?>