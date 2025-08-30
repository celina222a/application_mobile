<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

// Lecture du fichier de configuration
$config = parse_ini_file("conf.properties");

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $config['SMTP_SERVER'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['USER_NAME'];
    $mail->Password   = $config['USER_PASSWORD'];
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom($config['USER_NAME'], 'Test Application');
    $mail->addAddress($config['TEST_EMAIL']);

    $mail->isHTML(true);
    $mail->Subject = "Hello World";
    $mail->Body    = "Hello World ! Ceci est un test d'envoi de mail.";

    $mail->send();
    echo "✅ Mail envoyé avec succès !";
} catch (Exception $e) {
    echo "❌ Erreur lors de l'envoi : {$mail->ErrorInfo}";
}
