 <?php
require_once __DIR__ . '/auth.php';
require_role('CHEF_PARC');
require_once __DIR__ . '/config.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    die("❌ Réservation introuvable.");
}

$message = '';
$message_type = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $motif = trim($_POST['motif'] ?? '');

    if ($action === 'accepter') {
        $stmt = $conn->prepare("UPDATE reservations SET etat='accepted', motif=NULL WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "Réservation acceptée ";
        $message_type = 'ok';
    } elseif ($action === 'annuler') {
        if ($motif === '') {
            $message = "❌ Veuillez renseigner un motif pour l'annulation.";
            $message_type = 'err';
        } else {
            $stmt = $conn->prepare("UPDATE reservations SET etat='cancelled', motif=? WHERE id=?");
            $stmt->bind_param("si", $motif, $id);
            $stmt->execute();
            $message = "Réservation annulée avec motif.";
            $message_type = 'cancelled';
        }
    }
}

// Charger la réservation après modification
$sql = "SELECT r.*, u.nom, u.email 
        FROM reservations r
        JOIN utilisateurs u ON r.user_id = u.id
        WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    die("❌ Réservation non trouvée !");
}

function etat_label($etat) {
    switch ($etat) {
        case 'new': return 'En attente';
        case 'accepted': return 'Acceptée';
        case 'cancelled': return 'Annulée';
        default: return htmlspecialchars($etat);
    }
}
function etat_class($etat) {
    switch ($etat) {
        case 'new': return 'etat-New';
        case 'accepted': return 'etat-Accepted';
        case 'cancelled': return 'etat-Cancelled';
        default: return 'etat-Default';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail réservation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background: linear-gradient(135deg, #93a5cf 0%, #a1c4fd 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 10px;
        }
        .card {
            background: #f7faff;
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(60,60,100,0.10);
            padding: 32px 24px 24px 24px;
            margin-top: 28px;
        }
        h1 {
            margin-top: 0;
            color: #3e3eb7;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 18px;
            text-align: center;
            letter-spacing: 0.02em;
        }
        ul {
            list-style: none;
            padding: 0;
            margin: 24px 0 18px 0;
        }
        li {
            padding: 10px 0 7px 0;
            border-bottom: 1px solid #e3eafc;
            font-size: 1.08rem;
        }
        li:last-child {
            border-bottom: none;
        }
        b {
            color: #374151;
            font-weight: 600;
        }
        .etat {
            display: inline-block;
            padding: 7px 20px;
            border-radius: 14px;
            font-weight: bold;
            font-size: 1.07rem;
            margin-left: 6px;
            box-shadow: 0 2px 8px #e8e8e8;
        }
        .etat-New { background:#dbeafe; color:#2563eb; }
        .etat-Accepted { background:#c8e6c9; color:#256029; }
        .etat-Cancelled { background:#ffbcbc; color:#b71c1c; border: 1.5px solid #e53935;}
        .etat-Default { background:#eee; color:#333; }

        .msg {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 1.08rem;
            font-weight: 500;
            box-shadow: 0 2px 8px #e8efe7cc;
            text-align: center;
        }
        .msg.ok { background:#d7fbd7; color:#256029;}
        .msg.err { background:#ffcdd2; color:#b71c1c;}
        .msg.cancelled { 
            background: linear-gradient(90deg, #ff5858 0%, #e53935 100%);
            color: #fff;
            border: 1.5px solid #b71c1c;
        }

        .actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .actions button {
            padding: 12px 26px;
            border: none;
            border-radius: 10px;
            font-size: 1.09rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s, box-shadow 0.15s;
            box-shadow: 0 2px 8px #e7e7e7cc;
        }
        .btn-accept {
            background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%);
            color: #fff;
        }
        .btn-accept:hover {
            background: linear-gradient(90deg, #34b972 20%, #3fd6c5 80%);
        }
        .btn-cancel {
            background: linear-gradient(90deg, #ff5858 0%, #e53935 100%);
            color: #fff;
            border: 1.5px solid #b71c1c;
        }
        .btn-cancel:hover {
            background: linear-gradient(90deg, #e53935 0%, #b71c1c 100%);
        }
        .motif-wrap {
            display: none;
            margin-top: 18px;
        }
        .motif-wrap.active { display:block;}
        .motif-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 4px;
            display: block;
            font-size: 1.03rem;
        }
        textarea {
            width:100%;
            border-radius: 8px;
            border: 1.5px solid #e53935;
            font-size: 1rem;
            padding: 10px;
            resize: vertical;
            min-height: 60px;
            box-sizing: border-box;
            margin-bottom: 8px;
        }
        .return-btn {
            display: block;
            width: fit-content;
            background: linear-gradient(90deg, #3f51b5, #2196f3);
            color: #fff;
            border: none;
            padding: 12px 26px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.08rem;
            text-decoration: none;
            margin: 32px auto 0 auto;
            box-shadow: 0 2px 8px #bcd4ee99;
            transition: background 0.2s;
            text-align: center;
        }
        .return-btn:hover {
            background: linear-gradient(90deg, #283593,#1976d2);
        }
        @media (max-width: 650px) {
            .card { padding: 11px 2vw 18px 2vw; }
            h1 { font-size: 1.25rem;}
            .return-btn { font-size: 1rem; padding: 10px 18px;}
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Détail de la réservation</h1>

        <?php if ($message): ?>
            <div class="msg <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
<ul>
    <li><b>Employé :</b> <?= htmlspecialchars($reservation['nom']); ?></li>
    <li><b>Email :</b> <?= htmlspecialchars($reservation['email']); ?></li>
    <li><b>Chauffeur :</b> <?= htmlspecialchars($reservation['chauffeur']); ?></li>
    <li><b>Trajet :</b> <?= ($reservation['trajet'] === 'aller_simple') ? 'Aller simple' : 'Aller-retour'; ?></li>
    <li><b>Nombre de personnes :</b> <?= (int)$reservation['nb_personnes']; ?></li>

    <li><b>Départ :</b> <?= htmlspecialchars($reservation['depart']); ?></li>
    <li><b>Arrivée :</b> <?= htmlspecialchars($reservation['arrivee']); ?></li>

    <!-- Date et heure de réservation -->
    <li><b>Date réservation :</b> 
        <?= !empty($reservation['date_reservation']) ? date("d/m/Y", strtotime($reservation['date_reservation'])) : '-' ?>
    </li>
    <li><b>Heure réservation :</b> 
        <?= !empty($reservation['date_reservation']) ? date("H:i", strtotime($reservation['date_reservation'])) : '-' ?>
    </li>

   <li><b>Date départ :</b> 
    <?= !empty($reservation['date_depart']) ? date("d/m/Y", strtotime($reservation['date_depart'])) : '-' ?>
</li>
<li><b>Heure départ :</b> 
    <?= !empty($reservation['heure_depart']) ? date("H:i", strtotime($reservation['heure_depart'])) : '-' ?>
</li>


 <?php if ($reservation['trajet'] === 'aller_retour' && !empty($reservation['date_retour'])): ?>
    <li><b>Date retour :</b> 
        <?= date("d/m/Y", strtotime($reservation['date_retour'])); ?>
    </li>
    <li><b>Heure retour :</b> 
        <?= !empty($reservation['heure_retour']) ? date("H:i", strtotime($reservation['heure_retour'])) : '-' ?>
    </li>
<?php endif; ?>


    <!-- État -->
    <li><b>État :</b> 
        <span class="etat <?= etat_class($reservation['etat']); ?>">
            <?= etat_label($reservation['etat']); ?>
        </span>
    </li>

    <!-- Motif si annulé -->
    <?php if ($reservation['etat'] === 'cancelled' && !empty($reservation['motif'])): ?>
        <li><b>Motif d'annulation :</b> <?= nl2br(htmlspecialchars($reservation['motif'])) ?></li>
    <?php endif; ?>
</ul>



    <?php if ($reservation['etat'] === 'new' || $reservation['etat'] === 'accepted'): ?>
    <form method="post" id="actionForm" action="update_reservation.php">
        <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
        <div class="actions">
            <?php if ($reservation['etat'] === 'new'): ?>
                <button type="submit" name="action" value="accepted" class="btn-accept">Accepter</button>
            <?php endif; ?>
            <button type="button" id="btnCancel" class="btn-cancel" onclick="test22()">Annuler</button>
        </div>
        <div class="motif-wrap" id="motifWrap">
            <label for="motif" class="motif-label">Motif d'annulation :</label>
            <textarea name="motif" id="motif" placeholder="Écrivez le motif ici..." oninput="toggleCancelButton(this)"></textarea>
            <button id="confirmCancel" type="submit" name="action" value="cancelled" class="btn-cancel"  style="margin-top:10px;" disabled>Confirmer l'annulation</button>
        </div>
    </form>
<?php endif; ?>


        <a href="chef_parc.php" class="return-btn">⬅ Retour</a>
    </div>
</div>
</body>
</html>