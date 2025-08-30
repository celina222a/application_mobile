<?php
$servers = [
    ['host' => 'smtp.gmail.com', 'port' => 465],
    ['host' => 'smtp.gmail.com', 'port' => 587]
];

foreach ($servers as $s) {
    echo "Test de connexion à {$s['host']}:{$s['port']} ...<br>";
    $connection = @fsockopen($s['host'], $s['port'], $errno, $errstr, 10);
    if ($connection) {
        echo "✅ Connexion réussie à {$s['host']}:{$s['port']}<br>";
        fclose($connection);
    } else {
        echo "❌ Impossible de se connecter à {$s['host']}:{$s['port']}<br>";
        echo "Erreur $errno : $errstr<br>";
    }
}
