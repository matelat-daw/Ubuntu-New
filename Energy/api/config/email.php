<?php
/**
 * Configuración SMTP para envío de emails (PHPMailer)
 *
 * Credenciales via variables de entorno (recomendado) o constantes aquí abajo.
 * Para Gmail: usa una "Contraseña de aplicación" generada en:
 * https://myaccount.google.com/apppasswords
 * (Requiere verificación en 2 pasos activada en la cuenta Gmail)
 */

define('SMTP_HOST',       getenv('SMTP_HOST')     ?: 'smtp.gmail.com');
define('SMTP_PORT',       (int)(getenv('SMTP_PORT')    ?: 587));
define('SMTP_USER',       getenv('SMTP_USER')     ?: 'matelat@gmail.com');
define('SMTP_PASS',       getenv('SMTP_PASS')     ?: 'cmpazrwvngjpmbhw');
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'matelat@gmail.com');
define('SMTP_FROM_NAME',  getenv('SMTP_FROM_NAME')  ?: 'Energy App');
define('SMTP_ENCRYPTION', 'tls');   // 'tls' para puerto 587, 'ssl' para 465
