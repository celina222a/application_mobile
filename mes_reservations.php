<?php
require_once __DIR__ . '/auth.php';
require_role('EMPLOYE');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mail_functions.php'; // on charge la fonction d’envoi de mail

// --- Récupération des données du formulaire ---
$userId       = $_SESSION['user_id'];
$chauffeur    = $_POST['chauffeur'] ?? '';
$trajet       = $_POST['trajet'] ?? '';
$nbPersonnes  = (int)($_POST['nb_personnes'] ?? 0);
$depart       = $_POST['depart'] ?? '';
$arrivee      = $_POST['arrivee'] ?? '';
$dateDebut    = $_POST['date_depart'] . ' ' . $_POST['heure_depart'];
$dateFin      = (!empty($_POST['date_retour']) && !empty($_POST['heure_retour']))
                  ? $_POST['date_retour'] . ' ' . $_POST['heure_retour'] : null;

// --- Insertion en base ---
$stmt = $conn->prepare("INSERT INTO reservations 
(user_id, chauffeur, trajet, nb_personnes, depart, arrivee, date_depart, date_retour, etat)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'new')");
$stmt->bind_param("ississss", $userId, $chauffeur, $trajet, $nbPersonnes, $depart, $arrivee, $dateDebut, $dateFin);

if ($stmt->execute()) {
    // ✅ Réservation enregistrée
    $_SESSION['flash_success'] = "✅ Votre demande a été bien enregistrée.";

    // --- Envoi d’un mail au chef de parc ---
    $chefParcEmail = getConfigValue("CHEFPARC_EMAIL");
    $employeEmail  = getConfigValue("TEST_EMAIL"); // l’adresse de l’employé (à remplacer par l’email réel si tu l’as dans la DB)

    $sujet = "Nouvelle réservation de voiture";
    $message = "
        Une nouvelle réservation a été faite :<br><br>
         Chauffeur : <b>$chauffeur</b><br>
          Départ : <b>$depart</b><br>
         Arrivée : <b>$arrivee</b><br>
         Nombre de personnes : <b>$nbPersonnes</b><br>
         Départ le : <b>$dateDebut</b><br>
        " . ($dateFin ? " Retour prévu le : <b>$dateFin</b><br>" : "") . "
    ";

    // Appel de la fonction centralisée
    if (!envoyerMail($chefParcEmail, $sujet, $message, $employeEmail)) {
        $_SESSION['flash_error'] = "⚠️ Réservation enregistrée mais échec de l’envoi de l’email.";
    }

} else {
    // ❌ Erreur SQL
    $_SESSION['flash_error'] = "❌ Votre demande n’a pas été acceptée. " . $stmt->error;
}

header("Location: employe.php");
exit;
