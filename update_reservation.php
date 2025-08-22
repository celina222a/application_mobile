<?php
session_start();
require_once __DIR__ . '/auth.php';
require_role('CHEF_PARC');
require_once __DIR__ . '/config.php';

$reservationId = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$motif = trim($_POST['motif'] ?? '');

if (!$reservationId || !in_array($action, ['accepted', 'cancelled'])) {
    $_SESSION['flash_error'] = "Requête invalide !";
    header("Location: chef_parc.php");
    exit;
}

// Récupérer l’email du demandeur
$sql = "SELECT r.id, u.email, u.nom 
        FROM reservations r
        LEFT JOIN utilisateurs u ON r.user_id = u.id
        WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reservationId);
$stmt->execute();
$result = $stmt->get_result();
$res = $result->fetch_assoc();

if (!$res) {
    $_SESSION['flash_error'] = "❌ Réservation introuvable !";
    header("Location: chef_parc.php");
    exit;
}

// Mettre à jour le statut et le motif
if ($action === 'cancelled') {
    if (empty($motif)) {
        $_SESSION['flash_error'] = "Veuillez indiquer un motif pour l'annulation.";
        header("Location: reservation_detail.php?id={$reservationId}");
        exit;
    }
    $update = $conn->prepare("UPDATE reservations SET etat = ?, motif_annulation = ? WHERE id = ?");
    $update->bind_param("ssi", $action, $motif, $reservationId);
    $update->execute();
} else {
    $update = $conn->prepare("UPDATE reservations SET etat = ?, motif_annulation = NULL WHERE id = ?");
    $update->bind_param("si", $action, $reservationId);
    $update->execute();
}

// Préparer email (ne marche pas toujours en local sans SMTP)
$to = $res['email'];
$subject = "Mise à jour de votre réservation";
$message = "";

if ($action === "accepted") {
    $message = "Bonjour " . $res['nom'] . ",\n\n";
    $message .= "✅ Votre réservation a été acceptée.\n";
} else {
    $message = "Bonjour " . $res['nom'] . ",\n\n";
    $message .= "❌ Votre réservation a été annulée.\n";
    $message .= "Motif : " . $motif . "\n";
}

// Headers email
$headers = "From: noreply@tonsite.com\r\n";
@mail($to, $subject, $message, $headers);

$_SESSION['flash_success'] = "Mise à jour effectuée.";
header("Location: chef_parc.php");
exit;