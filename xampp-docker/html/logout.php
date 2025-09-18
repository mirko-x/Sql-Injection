<?php
// Avvia la sessione
session_start();

// Svuota tutte le variabili di sessione
$_SESSION = array();

// Distruggi la sessione
session_destroy();

// Reindirizza alla pagina di login
header("Location: index.php");
exit;
?>