<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once 'config.php';
$eroare = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $eroare = "Te rugăm să completezi ambele câmpuri.";
    } else {
        try {
        
            $stmt = $pdo->prepare('SELECT id_utilizator, parola, rol FROM utilizatori WHERE username = :username');
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();
            
            
            if ($user && password_verify($password, $user['parola'])) {
                $_SESSION['user_id'] = $user['id_utilizator'];
                $_SESSION['username'] = $username;
                $_SESSION['rol'] = $user['rol'];
                
                header("Location: dashboard.php");
                exit;
            } else {
                $eroare = "Nume de utilizator sau parolă incorecte.";
            }
        } catch (PDOException $e) {
            $eroare = "A aparut o eroare: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestiune Facultate - Autentificare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <h2 class="text-primary fw-bold">Gestiune Facultate</h2>
            <p class="text-muted">Conectare la catalogul electronic</p>
        </div>
        
        <?php if (!empty($eroare)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($eroare); ?></div>
        <?php endif; ?>
        
        <form action="index.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Nume utilizator</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Parolă</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">Autentificare</button>
        </form>
    </div>

</body>
</html>