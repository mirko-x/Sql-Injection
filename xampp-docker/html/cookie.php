<?php
$host = "db";
$username = "root";
$password = "";
$db_name = "info_log";


$conn2 = new mysqli($host, $username, $password, $db_name);
if ($conn2->connect_error) {
    die("Connessione fallita: " . $conn2->connect_error);
}

//Funzione che costruisce i cookie in base all'utente per ogni tentativo di accesso 
function log_login_attempt($conn2, $username, $password, $role , $success) {
    if (!isset($_COOKIE['my_cookie_id'])) {
    $my_cookie_id = bin2hex(random_bytes(2));
    setcookie('my_cookie_id', $my_cookie_id, time() + 2592000 , "/");
    $_COOKIE['my_cookie_id'] = $my_cookie_id;
    } else { $my_cookie_id = $_COOKIE['my_cookie_id'];}


    $php_session_id = session_id();

    $session_id = $my_cookie_id . '_' . $php_session_id;
	
    $ip = $_SERVER['REMOTE_ADDR'];
    $browser = $_SERVER['HTTP_USER_AGENT'];
    $current_time = date("Y-m-d H:i:s");
	$page = $_SERVER['REQUEST_URI'];
	
	//Sanificazione permette di leggere correttamente i log senza errori quindi evita un possibile sqli su insert ma log ancora vulnerabili...
    $username = mysqli_real_escape_string($conn2, $username);    
    $password = mysqli_real_escape_string($conn2, $password);
	
    $sql = "INSERT INTO login_log (username, password, role, success, ip_address, user_agent, login_time, session_id, request_uri  ) 
            VALUES ('$username', '$password', '$role','$success','$ip', '$browser', '$current_time', '$session_id', '$page')";
			
    //echo $query;
	
    if (!$conn2->query($sql)) {
        die("Errore nel salvataggio dei dati di login: " . $conn2->error);
    }
}

//log_login_attempt($conn2, $username, $password, $role , $success);   QUESTO VA ESCLUSIVAMENTE NEL TENTATIVO DI LOGIN NEL FILE login.php (AVVIA LA FUNZIONE)  */

?>