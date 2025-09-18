<?php
// Inclusione del file di configurazione
require_once 'config.php';

// Avvia la sessione
session_start();

// Recupero di alcuni prodotti casuali per la homepage
$query = "SELECT * FROM products ORDER BY RAND() LIMIT 4";
try {
        $featured_products = mysqli_query($conn, $query);
        
        // Se la query fallisce, salva l'errore vulnerabile a raccolta dati 
        if (!$featured_products) {
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
    <title>eCommerce - Home</title>
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
        h1, h2 {
            color: #333;
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
        .welcome {
            background: #e7f3fe;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .search-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
        }
        .search-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
        }
        .search-box button {
            padding: 10px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        .search-box button:hover {
            background: #45a049;
        }
        .featured-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            transition: transform 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-card h3 {
            margin-top: 0;
            color: #333;
        }
        .product-card p {
            color: #666;
        }
        .product-card .price {
            font-weight: bold;
            color: #e91e63;
            font-size: 1.2em;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
            margin-right: 5px;
        }
        .btn:hover {
            background: #45a049;
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
        <div class="header">
            <h1>eCommerce Demo</h1>
            <div class="nav">
                <a href="index.php">Home</a>
                <a href="search.php">Ricerca</a>
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
        
        <div class="welcome">
            <h2>Benvenuto nel nostro eCommerce</h2>
            <p>Questo è un sito dimostrativo per illustrare le vulnerabilità SQL Injection. Puoi utilizzare varie pagine per testare diversi tipi di attacchi.</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <p>Benvenuto, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> (<?php echo htmlspecialchars($_SESSION['user_role']); ?>)</p>
            <?php endif; ?>
        </div>
        
        <form action="search.php" method="GET" class="search-box">
            <input type="text" name="search" placeholder="Cerca prodotti...">
            <button type="submit">Cerca</button>
        </form>
        
        <h2>Prodotti in evidenza</h2>
        <div class="featured-products">
            <?php while ($product = mysqli_fetch_assoc($featured_products)): ?>
                <div class="product-card">
                    <h3><?php echo $product['name']; ?></h3>
                    <p><?php echo substr($product['description'], 0, 100) . '...'; ?></p>
                    <p class="price">€<?php echo number_format($product['price'], 2, ',', '.'); ?></p>
                    <p><strong>Categoria:</strong> <?php echo $product['category']; ?></p>
                    
                    <a href="search.php?search=<?php echo urlencode($product['name']); ?>&link=product" class="btn" id="btn-product" > Vedi prodotto</a>
                    <a href="search.php?category=<?php echo urlencode($product['category']); ?>&link=similar" class="btn" id="btn-similar" > Vedi prodotti simili</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>