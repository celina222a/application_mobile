<?php
require_once __DIR__ . '/auth.php';
require_role('CHEF_PARC');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mail_functions.php';

$reservationId = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$motif = trim($_POST['motif'] ?? '');

if (!$reservationId || !in_array($action, ['accepted', 'cancelled'], true)) {
    $_SESSION['flash_error'] = "Requête invalide.";
    header("Location: chef_parc.php");
    exit;
}

// Vérifier que la réservation existe et récupérer infos
$sql = "SELECT r.id, r.trajet, r.depart, r.arrivee, r.date_depart, r.date_retour, r.nb_personnes, r.chauffeur, u.email 
        FROM reservations r
        JOIN utilisateurs u ON r.user_id = u.id
        WHERE r.id = ? AND r.etat = 'new'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reservationId);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    $_SESSION['flash_error'] = "Action impossible : réservation déjà traitée ou introuvable.";
    header("Location: chef_parc.php");
    exit;
}

$employeEmail = $reservation['email'];
$chefParcEmail = getConfigValue("CHEFPARC_EMAIL");

// ======================
// Traitement annulation
// ======================
if ($action === 'cancelled') {
    if (empty($motif)) {
        $_SESSION['flash_error'] = "Veuillez indiquer un motif pour l'annulation.";
        header("Location: reservation_detail.php?id={$reservationId}");
        exit;
    }

    $update = $conn->prepare("UPDATE reservations SET etat = 'cancelled', motif= ? WHERE id = ?");
    $update->bind_param("si", $motif, $reservationId);
    $update->execute();

    // Contenu email
    $sujet = "❌ Votre réservation #{$reservationId} a été annulée";
    $message = "
        <h2>Réservation annulée</h2>
        <p>Votre demande de réservation a été <b>annulée</b>.</p>
        <p><b>Motif :</b> {$motif}</p>
        <hr>
        <p><b>Trajet :</b> {$reservation['depart']} → {$reservation['arrivee']}</p>
        <p><b>Date départ :</b> {$reservation['date_depart']}</p>
        ".(!empty($reservation['date_retour']) ? "<p><b>Date retour :</b> {$reservation['date_retour']}</p>" : "")."
        <p><b>Nombre de personnes :</b> {$reservation['nb_personnes']}</p>
        <p><b>Chauffeur :</b> {$reservation['chauffeur']}</p>
    ";

    envoyerMail($employeEmail, $chefParcEmail, $sujet, $message);
    $_SESSION['flash_success'] = "❌ Réservation annulée et email envoyé.";
}

// ======================
// Traitement acceptation
// ======================
if ($action === 'accepted') {
    $update = $conn->prepare("UPDATE reservations SET etat = 'accepted', motif= NULL WHERE id = ?");
    $update->bind_param("i", $reservationId);
    $update->execute();

    // Contenu email
    $sujet = "✅ Votre réservation #{$reservationId} a été acceptée";
    $message = "
        <h2>Réservation acceptée</h2>
        <p>Bonne nouvelle, votre demande de réservation a été <b>acceptée</b>.</p>
        <hr>
        <p><b>Trajet :</b> {$reservation['depart']} → {$reservation['arrivee']}</p>
        <p><b>Date départ :</b> {$reservation['date_depart']}</p>
        ".(!empty($reservation['date_retour']) ? "<p><b>Date retour :</b> {$reservation['date_retour']}</p>" : "")."
        <p><b>Nombre de personnes :</b> {$reservation['nb_personnes']}</p>
        <p><b>Chauffeur :</b> {$reservation['chauffeur']}</p>
    ";

    envoyerMail($employeEmail, $chefParcEmail, $sujet, $message);
    $_SESSION['flash_success'] = "✅ Réservation acceptée et email envoyé.";
}

header("Location: chef_parc.php");
exit;
