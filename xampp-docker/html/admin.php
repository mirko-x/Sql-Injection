<?php
// Includi il file di configurazione per la connessione
require_once 'config.php';
// Avvia la sessione
session_start();

// Verifica se l'utente è loggato e ha il ruolo di admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    // Se non è loggato o non è un admin, reindirizza alla pagina di login
    header("Location: login.php");
    exit;
}
/* Vulnearbile ad attacchi ma un admin non si attacca da solo in realtà un malentenzionato se ha accesso a questa pagina 
puo eseguire modifiche eliminazioni e raccolta dati senza sql injection */

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Aggiunta di un nuovo utente - vulnerabile a SQL Injection
    if (isset($_POST['add_user'])) {
        $new_username = $_POST['username'];
        $new_password = $_POST['password']; 
        $new_email = $_POST['email'];
        $new_role = $_POST['role'];
        
        // Query vulnerabile - inserimento diretto dei valori senza escape
        $query = "INSERT INTO users (username, password, email, role) 
                 VALUES ('$new_username', '$new_password', '$new_email', '$new_role')";
        
        try {
         $result = mysqli_query($conn, $query);
         // Se la query fallisce, salva l'errore 
         if (!$result) {
            $error = mysqli_error($conn);
         }
        } catch (Exception $e) {
        $error = $e->getMessage();
        }
    }
    
    // Modifica di un utente esistente - vulnerabile a SQL Injection
    if (isset($_POST['edit_user'])) {
        $user_id = $_POST['user_id'];
        $edit_username = $_POST['edit_username'];
        $edit_email = $_POST['edit_email'];
        $edit_role = $_POST['edit_role'];
        
        // Query vulnerabile - inserimento diretto dei valori
        $query = "UPDATE users SET 
                 username = '$edit_username', 
                 email = '$edit_email', 
                 role = '$edit_role' 
                 WHERE id = $user_id";
        
        try {
         $result = mysqli_query($conn, $query);
         // Se la query fallisce, salva l'errore 
         if (!$result) {
            $error = mysqli_error($conn);
         }
        } catch (Exception $e) {
        $error = $e->getMessage();
        }
    }
    
    // Eliminazione di un utente - vulnerabile a SQL Injection
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        // Query vulnerabile - inserimento diretto dell'ID
        $query = "DELETE FROM users WHERE id = $user_id";
        try {
         $result = mysqli_query($conn, $query);
         // Se la query fallisce, salva l'errore  vulnerabile a raccolta dati
         if (!$result) {
            $error = mysqli_error($conn);
         }
        } catch (Exception $e) {
        $error = $e->getMessage();
        }
    }
}

$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// Query di ricerca vulnerabile
$query = "SELECT * FROM users WHERE username LIKE '%$search%' OR email LIKE '%$search%' ORDER BY id";
try {
    $result = mysqli_query($conn, $query);
    // Se la query fallisce, salva l'errore  vulnerabile a raccolta dati
    if (!$result) {
        $error = mysqli_error($conn);
    }
    }catch (Exception $e) {
       $error = $e->getMessage();
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello di Amministrazione</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        h1 {
            color: #333;
        }
        .search-form {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"], input[type="email"], select {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button, .btn {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover, .btn:hover {
            background: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        .edit-btn {
            background: #2196F3;
        }
        .edit-btn:hover {
            background: #0b7dda;
        }
        .delete-btn {
            background: #f44336;
        }
        .delete-btn:hover {
            background: #d32f2f;
        }
        .add-form, .edit-form {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pannello di Amministrazione</h1>
			            <div>
                <span>Benvenuto, Admin</span>
                <a href="index.php" class="btn">Home</a>
                <a href="logout.php" class="btn">Logout</a>
            </div>
        </div>
        
        <!-- Form di ricerca vulnerabile -->
        <div class="search-form">
            <form action="admin.php" method="GET">
                <input type="text" name="search" placeholder="Cerca utenti..." value="<?php echo $search; ?>">
                <button type="submit">Cerca</button>
            </form>
        </div>
        
        <!-- Tabella utenti -->
        <h2>Gestione Utenti</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Ruolo</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Visualizzazione degli utenti dal database
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['username'] . "</td>";
                    echo "<td>" . $row['email'] . "</td>";
                    echo "<td>" . $row['role'] . "</td>";
                    echo "<td class='actions'>";
                    echo "<button class='edit-btn' onclick='showEditForm(" . $row['id'] . ", \"" . $row['username'] . "\", \"" . $row['email'] . "\", \"" . $row['role'] . "\")'>Modifica</button>";
                    echo "<form method='POST' style='display:inline;'>";
                    echo "<input type='hidden' name='user_id' value='" . $row['id'] . "'>";
                    echo "<button type='submit' name='delete_user' class='delete-btn'>Elimina</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        
        <!-- Form per aggiungere un nuovo utente -->
        <div class="add-form">
            <h3>Aggiungi Nuovo Utente</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="role">Ruolo:</label>
                    <select id="role" name="role" required>
                        <option value="user">Utente</option>
                        <option value="editor">Editor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="add_user">Aggiungi Utente</button>
            </form>
        </div>
        
        <!-- Form per modificare un utente (inizialmente nascosto) -->
        <div id="editForm" class="edit-form" style="display: none;">
            <h3>Modifica Utente</h3>
            <form method="POST">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="form-group">
                    <label for="edit_username">Username:</label>
                    <input type="text" id="edit_username" name="edit_username" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email:</label>
                    <input type="email" id="edit_email" name="edit_email" required>
                </div>
                <div class="form-group">
                    <label for="edit_role">Ruolo:</label>
                    <select id="edit_role" name="edit_role" required>
                        <option value="user">Utente</option>
                        <option value="editor">Editor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="edit_user">Salva Modifiche</button>
                <button type="button" onclick="hideEditForm()">Annulla</button>
            </form>
        </div>
    </div>
    
    <script>
        // JavaScript per gestire il form di modifica
        function showEditForm(id, username, email, role) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById('editForm').style.display = 'block';
        }
        
        function hideEditForm() {
            document.getElementById('editForm').style.display = 'none';
        }
    </script>
</body>
</html>