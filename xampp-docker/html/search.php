<?php
// Inclusione del file di configurazione
require_once 'config.php';

// Avvia la sessione
session_start();


$search_term = "";
$category = "";
$results = null;
$error = "";
$query = "";
$buy_error = ""; // Messaggio di errore per la funzionalità di acquisto
$order_by = "id"; // Default order
$selectedLink = null;

// Verifica se è stata inviata una richiesta di ordinamento
if (isset($_GET['order_by'])) {
    // VULNERABILE: accetta qualsiasi input di ordinamento senza validazione
    $order_by = $_GET['order_by'];
}

// Verifica se è stata inviata una richiesta di ricerca
if (isset($_GET['search']) || isset($_GET['category'])) {
    $search_term = isset($_GET['search']) ? $_GET['search'] : "";
    $category = isset($_GET['category']) ? $_GET['category'] : "";
    
    // (VULNERABILE a SQL Injection)
    $query = "SELECT id, name, description, price, category, stock, image_url FROM products WHERE 1=1";
    
    if (!empty($search_term)) {
		// (VULNERABILE a SQL Injection)
        $query .= " AND name = '$search_term' OR description = '$search_term'";
    }
    
    if (!empty($category)) {
        // Vulnerabile: inserimento diretto della categoria senza escape
        $query .= " AND category = '$category'";
    }

    $query .= " ORDER BY $order_by";
    
    // Esecuzione della query
    try {
        $results = mysqli_query($conn, $query);
        
        // Se la query fallisce, salva l'errore  vulnerabile a raccolta dati
        if (!$results) {
            $error = mysqli_error($conn);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['buy_product_id'], $_POST['current_stock'], $_POST['buy_quantity'])) {
    $product_id = (int)$_POST['buy_product_id'];
    $stock = (int)$_POST['current_stock'];
    $buy_quantity = $_POST['buy_quantity'];
    
    // Verifica se la quantità richiesta è maggiore della disponibilità
    if ((int)$buy_quantity > $stock) {
        $buy_error = "Non ci sono abbastanza scorte disponibili. Richieste: $buy_quantity, Disponibili: $stock";
    } 
	else {
        // Query vulnerabile a SQL injection attraverso il parametro buy_quantity
        if ($stock - $buy_quantity > 0) {
            // VULNERABILE: inserimento diretto della quantità senza sanitizzazione
            $update_query = "UPDATE products SET stock = stock - $buy_quantity WHERE id = $product_id";
			try {
                $results = mysqli_query($conn, $update_query);
                // Se la query fallisce, salva l'errore 
                if (!$results) {
                 $error = mysqli_error($conn);
				}
            } catch (Exception $e) {
                $error = $e->getMessage();
				}
        } 
		else {
            $delete_query = "DELETE FROM products WHERE stock = $buy_quantity AND id = $product_id "; 
			try {
                $results = mysqli_query($conn, $delete_query);
                // Se la query fallisce, salva l'errore  vulnerabile a raccolta dati
                if (!$results) {
                 $error = mysqli_error($conn);
				}
            } catch (Exception $e) {
                $error = $e->getMessage();
				}
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Query per ottenere le categorie per il menu a tendina
$categories_query = "SELECT DISTINCT category FROM products ORDER BY category";
try {
    $categories_result = mysqli_query($conn, $categories_query);
    // Se la query fallisce, salva l'errore vulnerabile a raccolta dati
    if (!$categories_result) {
     $error = mysqli_error($conn);
    }
    } catch (Exception $e) {
       $error = $e->getMessage();
      }

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ricerca Prodotti</title>
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
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .form-group label {
            width: 100px;
            font-weight: bold;
        }
        input[type="text"], select {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            flex: 1;
        }
        button {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        th a {
            color: #333;
            text-decoration: none;
        }
        th a:hover {
            text-decoration: underline;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .nav {
            display: flex;
            gap: 15px;
        }
        .nav a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .nav a:hover {
            color: #4CAF50;
        }
        .hint {
            margin-top: 30px;
            background: #e7f3fe;
            border-left: 3px solid #2196F3;
            padding: 10px;
            color: #555;
        }
        .query-debug {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .quantity-input {
            display: flex;
            align-items: right;
        }
        .quantity-input input {
            width: 150px;
            text-align: center;
            margin-right: 10px;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ricerca Prodotti</h1>
            <div class="nav">
                <a href="index.php">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Registrati</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="admin.php">Pannello Admin</a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Form di ricerca vulnerabile a SQL Injection -->
        <div class="search-form">
            <form action="search.php" method="GET">
                <div class="form-group">
                    <label for="search">Cerca:</label>
                    <input type="text" id="search" name="search" placeholder="Nome prodotto o descrizione..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="form-group">
                    <label for="category">Categoria:</label>
                    <select id="category" name="category">
                        <option value="">Tutte le categorie</option>
                        <?php
                        // Popolamento del menu a tendina con le categorie
                        if ($categories_result) {
                            while ($cat = mysqli_fetch_assoc($categories_result)) {
                                $selected = ($category == $cat['category']) ? 'selected' : '';
                                echo "<option value='" . $cat['category'] . "' $selected>" . $cat['category'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <button type="submit">Cerca</button>
            </form>
        </div>
        
        
        <?php if ($error): ?>
            <div class="error">
                <p>Si è verificato un errore durante la ricerca:</p>
                <pre><?php echo $error; ?></pre>
            </div>
        <?php endif; ?>
        
        <?php if ($buy_error): ?>
            <div class="error">
                <p><?php echo $buy_error; ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($results && mysqli_num_rows($results) > 0): ?>
            <h2>Risultati della ricerca</h2>
            <p>Trovati <?php echo mysqli_num_rows($results); ?> prodotti</p>
            
            <table>
                <thead>
                    <tr>
                        <th><a href="?search=<?php echo urlencode($search_term); ?>&category=<?php echo urlencode($category); ?>&order_by=id">ID</a></th>
                        <th><a href="?search=<?php echo urlencode($search_term); ?>&category=<?php echo urlencode($category); ?>&order_by=name">Nome</a></th>
                        <th>Descrizione</th>
                        <th><a href="?search=<?php echo urlencode($search_term); ?>&category=<?php echo urlencode($category); ?>&order_by=price">Prezzo</a></th>
                        <th><a href="?search=<?php echo urlencode($search_term); ?>&category=<?php echo urlencode($category); ?>&order_by=category">Categoria</a></th>
                        <th><a href="?search=<?php echo urlencode($search_term); ?>&category=<?php echo urlencode($category); ?>&order_by=stock">Disponibilità</a></th>
                        <th>Quantità</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = mysqli_fetch_assoc($results)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['id']); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($product['description'], 0, 100) . '...'); ?></td>
                            <td>€<?php echo htmlspecialchars(number_format($product['price'], 2, ',', '.')); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td><?php echo htmlspecialchars($product['stock']); ?></td>
                            <td>
                                <!-- Form di acquisto con quantità vulnerabile a SQL injection -->
                                <form method="POST" action="">
                                    <input type="hidden" name="buy_product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="current_stock" value="<?php echo $product['stock']; ?>">
                                    <div class="quantity-input">
                                        <label for="buy_quantity_<?php echo $product['id']; ?>"></label>
                                        <input type="text" id="buy_quantity_<?php echo $product['id']; ?>" name="buy_quantity" value="1" size="3">
                                        <button type="submit">BUY</button>
                                    </div>
                                </form>
                                <?php
								if (isset($_GET['link'])) {$selectedLink = $_GET['link'];} ?>
                                <a href="search.php?search=<?php echo urlencode($product['name']); ?>&link=product" class="btn" id="btn-product" 
                                <?php echo $selectedLink === 'product' ? 'style="display:none;"' : ''; ?>> Vedi prodotto</a>

                                <a href="search.php?category=<?php echo urlencode($product['category']); ?>&link=similar" class="btn" id="btn-similar" 
								<?php echo $selectedLink === 'similar' ? 'style="display:none;"' : ''; ?>> Vedi prodotti simili</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif (isset($_GET['search']) || isset($_GET['category'])): ?>
            <p>Nessun prodotto trovato per i criteri di ricerca specificati.</p>
        <?php endif; ?>
        
       
    </div>
</body>
</html>


