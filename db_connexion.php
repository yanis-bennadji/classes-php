<?php
// db_connexion.php
$host = 'localhost';
$dbname = "classes";  // Le nom de ta base de données
$username = 'root';   // Ton identifiant de base de données
$password = '';       // Ton mot de passe de base de données (laisse vide si aucun mot de passe)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
