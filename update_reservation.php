<?php
require_once __DIR__ . '/auth.php';
require_role('CHEF_PARC');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mail_functions.php';

// Fonction pour convertir l'état en label lisible
function etat_label($etat) {
    switch ($etat) {
        case 'new': return 'En attente';
        case 'accepted': return 'Acceptée';
        case 'cancelled': return 'Annulée';
        default: return htmlspecialchars($etat);
    }
}

// Fonction pour formater les dates et heures
function format_datetime($date, $heure, $default = "—") {
    $d = !empty($date) ? date("d/m/Y", strtotime($date)) : $default;
    $h = !empty($heure) ? date("H:i", strtotime($heure)) : $default;
    return [$d, $h];
}

// Fonction pour générer le contenu de l'email
function genererEmailReservation($reservation, $action, $motif = '') {
    [$dateReservation, $heureReservation] = format_datetime($reservation['date_reservation'], $reservation['date_reservation']);
    [$dateDepart, $heureDepart] = format_datetime($reservation['date_depart'], $reservation['heure_depart']);
    [$dateRetour, $heureRetour] = format_datetime($reservation['date_retour'], $reservation['heure_retour'], "");

    $trajetLabel = ($reservation['trajet'] === 'aller_simple') ? "Aller simple" : "Aller-retour";

    $sujet = ($action === 'accepted') 
        ? "✅ Votre réservation #{$reservation['id']} a été acceptée" 
        : "❌ Votre réservation #{$reservation['id']} a été annulée";

    $message = "
        <h2>Votre réservation a été " . ($action === 'accepted' ? "acceptée" : "annulée") . "</h2>
        " . ($action === 'cancelled' ? "<p><b>Motif :</b> " . nl2br(htmlspecialchars($motif)) . "</p><hr>" : "") . "
        <p><b>Employé :</b> {$reservation['nom']}</p>
        <p><b>Email :</b> {$reservation['email']}</p>
        <p><b>Chauffeur :</b> {$reservation['chauffeur']}</p>
        <p><b>Trajet :</b> {$trajetLabel}</p>
        <p><b>Départ :</b> {$reservation['depart']}</p>
        <p><b>Arrivée :</b> {$reservation['arrivee']}</p>
        <p><b>Date réservation :</b> {$dateReservation}</p>
        <p><b>Heure réservation :</b> {$heureReservation}</p>
        <p><b>Date départ :</b> {$dateDepart} à {$heureDepart}</p>" .
        (($reservation['trajet'] === 'aller_retour' && $dateRetour && $heureRetour)
            ? "<p><b>Date retour :</b> {$dateRetour} à {$heureRetour}</p>"
            : ""
        ) . "
        <p><b>Nombre de personnes :</b> {$reservation['nb_personnes']}</p>
        <p><b>État :</b> " . etat_label($action) . "</p>
    ";

    return [$sujet, $message];
}

// ======================
// Récupération des données
// ======================
$reservationId = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$motif = trim($_POST['motif'] ?? '');

if (!$reservationId || !in_array($action, ['accepted', 'cancelled'], true)) {
    $_SESSION['flash_error'] = "Requête invalide.";
    header("Location: chef_parc.php");
    exit;
}

// Récupérer la réservation
$sql = "SELECT r.id, r.trajet, r.depart, r.arrivee, 
               r.date_depart, r.heure_depart, 
               r.date_retour, r.heure_retour, 
               r.nb_personnes, r.chauffeur, 
               r.date_reservation, r.etat, 
               u.email, u.nom
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
// Mise à jour et email
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
} else { // accepted
    $update = $conn->prepare("UPDATE reservations SET etat = 'accepted', motif= NULL WHERE id = ?");
    $update->bind_param("i", $reservationId);
    $update->execute();
}

// Mettre à jour localement l'état pour l'email
$reservation['etat'] = $action;

// Générer et envoyer l'email
list($sujet, $message) = genererEmailReservation($reservation, $action, $motif);
envoyerMail($employeEmail, $chefParcEmail, $sujet, $message);

// Message flash et redirection
$_SESSION['flash_success'] = ($action === 'accepted') 
    ? "✅ Réservation acceptée et email envoyé." 
    : "❌ Réservation annulée et email envoyé.";

header("Location: chef_parc.php");
exit;
