-- Creazione del database
DROP DATABASE IF EXISTS info_log;
CREATE DATABASE IF NOT EXISTS info_log;
USE info_log;

-- Tabella dei cookie
CREATE TABLE IF NOT EXISTS login_log (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(255),
	success TINYINT(1) ,
    ip_address VARCHAR(255) NOT NULL,
    user_agent VARCHAR(255) NOT NULL,
    login_time DATETIME NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    request_uri TEXT
);


-- Creazione del database
DROP DATABASE IF EXISTS ecommerce;
CREATE DATABASE IF NOT EXISTS ecommerce;
USE ecommerce;

-- Tabella degli utenti
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Non crittografata per rendere il sistema vulnerabile
    email VARCHAR(100) NOT NULL,
    role ENUM('user', 'editor', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabella dei prodotti
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    stock INT DEFAULT 0,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabella degli ordini
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabella dei dettagli dell'ordine 
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Utenti
INSERT INTO users (username, password, email, role) VALUES
('admin', 'admin123', 'admin@example.com', 'admin'),
('editor', 'editor123', 'editor@example.com', 'editor'),
('user1', 'password123', 'user1@example.com', 'user'),
('user2', 'password123', 'user2@example.com', 'user');

-- Prodotti
INSERT INTO products (name, description, price, category, stock, image_url) VALUES
('Smartphone XYZ', 'Smartphone di ultima generazione con fotocamera da 48MP e 128GB di memoria.', 499.99, 'Elettronica', 1, 'images/phone1.jpg'),
('Laptop Pro', 'Laptop potente per professionisti con Intel i7 e 16GB RAM.', 1299.99, 'Elettronica', 25, 'images/laptop1.jpg'),
('Cuffie Wireless', 'Cuffie con cancellazione del rumore e 20 ore di autonomia.', 129.99, 'Accessori', 100, 'images/headphones1.jpg'),
('T-shirt Estiva', 'T-shirt in cotone 100% con design moderno.', 19.99, 'Abbigliamento', 200, 'images/tshirt1.jpg'),
('Jeans Classici', 'Jeans blu scuro in denim resistente.', 49.99, 'Abbigliamento', 150, 'images/jeans1.jpg'),
('Scarpe Sportive', 'Scarpe leggere ideali per la corsa.', 89.99, 'Calzature', 75, 'images/shoes1.jpg'),
('Orologio Smart', 'Orologio intelligente con monitoraggio attività e notifiche.', 159.99, 'Accessori', 40, 'images/watch1.jpg'),
('Tablet 10"', 'Tablet con display HD e processore veloce.', 299.99, 'Elettronica', 30, 'images/tablet1.jpg'),
('Fotocamera DSLR', 'Fotocamera professionale con obiettivo 18-55mm.', 699.99, 'Elettronica', 15, 'images/camera1.jpg'),
('Valigia Trolley', 'Valigia rigida con ruote silenziose e lucchetto TSA.', 79.99, 'Viaggio', 60, 'images/luggage1.jpg');

-- Ordini
INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES
(3, 649.98, 'delivered', 'Via Roma 123, Milano, Italia'),
(4, 139.98, 'shipped', 'Piazza Garibaldi 45, Roma, Italia'),
(3, 299.99, 'processing', 'Via Roma 123, Milano, Italia');

-- Dettagli ordini
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 499.99),
(1, 3, 1, 149.99),
(2, 4, 2, 19.99),
(2, 6, 1, 99.99),
(3, 8, 1, 299.99);

