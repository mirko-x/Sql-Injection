<?php
// Includi il file di configurazione
require_once 'config.php';
require_once 'cookie.php';

// Avvia la sessione
session_start();

// Inizializzazione variabili
$username = "";
$password = "";
$error = "";
$role ="";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error = "Username e password sono obbligatori.";
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];
		
        // Query vulnerabile (senza protezione contro SQL injection)
        $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
        //echo $query;
		
        if (mysqli_multi_query($conn, $query)) {
         do {
            $result = mysqli_store_result($conn);
            $user = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            break; // Appena trovi la prima SELECT, esci altrimenti cerco corrispondenze con altre query
            }  while (mysqli_next_result($conn));
			
            if ($user){
             $_SESSION['user_id'] = $user['id'];
             $_SESSION['username'] = $user['username'];
             $_SESSION['user_role'] = $user['role'];
			
             log_login_attempt($conn2, $username, $password, $user['role'],$success=1); // file cookie.php salva su altro database dati completi
			
             // Reindirizza a seconda del ruolo
             if ($user['role'] == 'admin') {  header("Location: admin.php"); } else { header("Location: index.php"); } 
             exit;
		   	//  file cookie.php salva anche in caso di fallimento login
			} else { $error = "Username o password non validi."; log_login_attempt($conn2, $username, $password, null , $success=0);} 
			 
		} else { $error = "ERRORE CRITICO"; } //stampa errore vulnerabile a raccolta dati 
    }
}
?>




<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
        input[type="text"], input[type="password"] {
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
        <h1>Accedi al tuo account</h1>
        
        <?php if ($error): ?>
            <div class="error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Accedi</button>
        </form>
        
        <div class="footer">
            <p>Non hai un account? <a href="register.php">Registrati</a></p>
            <p><a href="index.php">Torna alla Home</a></p>
        </div>
    </div>
</body>
</html>

