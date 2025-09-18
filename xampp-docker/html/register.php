<?php
// Includi il file di configurazione
require_once 'config.php';

// Avvia la sessione
session_start();

// Se l'utente è già loggato, reindirizza alla home
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Inizializzazione variabili
$username = "";
$email = "";
$password = "";
$error = "";
$success = "";

// Processo di registrazione
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se i parametri sono presenti e non vuoti
    if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
        $error = "Tutti i campi sono obbligatori.";
    } else {
        // Recupero dati dal form
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password']; // In sito reale, dovrebbe essere hashata

        // Verifica se l'username è già in uso (Vulnerabile a SQL Injection)
        $check_query = "SELECT * FROM users WHERE username='$username'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username già in uso. Scegline un altro.";
        } else {
            // Query di inserimento (Vulnerabile a SQL Injection)
            $query = "INSERT INTO users (username, password, email, role) 
                     VALUES ('$username', '$password', '$email', 'user')";
            
            if (mysqli_query($conn, $query)) {
                $success = "Registrazione completata con successo! Ora puoi effettuare il login.";
                // Reset dei campi dopo la registrazione
                $username = "";
                $email = "";
                $password = "";
            } else {
                $error = "Errore durante la registrazione: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #45a049;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
        }
        .footer a {
            color: #4CAF50;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .hint {
            margin-top: 30px;
            background: #e7f3fe;
            border-left: 3px solid #2196F3;
            padding: 10px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Crea un account</h1>
        
        <?php if ($error): ?>
            <div class="error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Registrati</button>
        </form>
        
        <div class="footer">
            <p>Hai già un account? <a href="login.php">Accedi</a></p>
            <p><a href="index.php">Torna alla Home</a></p>
        </div>
</body>
</html>