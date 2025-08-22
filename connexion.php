<?php
require_once __DIR__.'/auth.php'; 
require_once __DIR__.'/config.php'; 

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pwd   = $_POST['mot_de_passe'] ?? '';

    if ($email && $pwd) {
        $stmt = $conn->prepare("SELECT id, nom, email, mot_de_passe, role
                                 FROM utilisateurs
                                 WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        echo $user['email'];
        echo $user['mot_de_passe'];

        if ($user /*&& password_verify(password: $pwd, $user['mot_de_passe'])*/) {
            // Stockage des infos utilisateur
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['role']    = strtoupper($user['role']); // sécuriser casse

            // Redirection selon rôle
            if ($_SESSION['role'] === 'CHEF_PARC') {
                header('Location: chef_parc.php');
            } elseif ($_SESSION['role'] === 'EMPLOYE') {
                header('Location: employe.php');
            } else {
                // Rôle inconnu
                http_response_code(403);
                echo "Accès refusé : rôle inconnu.";
            }
            exit;
        } else {
            $err = "Identifiants incorrects.";
        }
    } else {
        $err = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-tr from-indigo-500 via-blue-400 to-purple-400">
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md flex flex-col items-center">
  
    <h1 class="text-2xl font-extrabold mb-6 text-center text-gray-800">Connexion</h1>

    <?php if ($err): ?>
      <p class="mb-4 text-red-500 text-center"><?= htmlspecialchars($err) ?></p>
    <?php endif; ?>

    <form method="post" class="space-y-5 w-full">
      <div class="relative">
        <input type="email" name="email" placeholder="Email" required 
          class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
        <!-- Icône email -->
        <span class="absolute left-3 top-2.5 text-indigo-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12l-4 4m0 0l-4-4m4 4V4"/></svg>
        </span>
      </div>
      <div class="relative">
        <input type="password" name="mot_de_passe" placeholder="Mot de passe" required 
          class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
        <!-- Icône password -->
        <span class="absolute left-3 top-2.5 text-indigo-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 11V7a5 5 0 10-10 0v4a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2z"/></svg>
        </span>
      </div>
      <button type="submit" 
        class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-2 rounded-xl shadow transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-indigo-300">
        Se connecter
    </button>
    </form>

  </div>
</body>
</html>
