<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

/**
 * Récupère une valeur de conf.properties
 */
function getConfigValue($key) {
    static $config = null;
    if ($config === null) {
        $config = parse_ini_file(__DIR__ . "/conf.properties");
    }
    return $config[$key] ?? null;
}

/**
 * Fonction générique d’envoi d’email
 */
function envoyerMail($destinataire, $sujet, $message, $copie = null) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = getConfigValue('SMTP_SERVER');
        $mail->SMTPAuth   = true;
        $mail->Username   = getConfigValue('USER_NAME');
        $mail->Password   = getConfigValue('USER_PASSWORD');
        $mail->SMTPSecure = 'tls';
        $mail->Port       = getConfigValue('SMTP_PORT');

        $mail->setFrom(getConfigValue('USER_NAME'), 'Application Réservation');
        $mail->addAddress($destinataire);

        if (!empty($copie)) {
            $mail->addCC($copie);
        }

        $mail->isHTML(true);
        $mail->Subject = $sujet;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur mail: {$mail->ErrorInfo}");
        return false;
    }
}
