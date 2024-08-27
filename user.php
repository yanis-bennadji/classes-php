<?php
// Démarrage de la session
session_start();

// Inclusion du fichier de connexion à la base de données
include 'db_connexion.php';

class Userpdo
{
    public $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;

    public function register($login, $password, $email, $firstname, $lastname)
    {
        global $pdo;
        $sql = "INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$login, password_hash($password, PASSWORD_DEFAULT), $email, $firstname, $lastname]);

        $this->id = $pdo->lastInsertId();
        $this->login = $login;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;

        return [
            'id' => $this->id,
            'login' => $this->login,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname
        ];
    }

    public function connect($login, $password)
    {
        global $pdo;
        $sql = "SELECT * FROM utilisateurs WHERE login = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Stocker les informations de l'utilisateur dans la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_login'] = $user['login'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_firstname'] = $user['firstname'];
            $_SESSION['user_lastname'] = $user['lastname'];
            return true;
        } else {
            return false;
        }
    }

    public function disconnect()
    {
        // Supprimer les informations de l'utilisateur de la session
        session_unset();
        session_destroy();
    }

    public function update($login, $password, $email, $firstname, $lastname)
    {
        global $pdo;
        $sql = "UPDATE utilisateurs SET login = ?, password = ?, email = ?, firstname = ?, lastname = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$login, password_hash($password, PASSWORD_DEFAULT), $email, $firstname, $lastname, $_SESSION['user_id']]);

        $_SESSION['user_login'] = $login;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_firstname'] = $firstname;
        $_SESSION['user_lastname'] = $lastname;
    }

    public function delete($id)
    {
        global $pdo;
        $sql = "DELETE FROM utilisateurs WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    public function getAllUsers()
    {
        global $pdo;
        $sql = "SELECT * FROM utilisateurs";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }
}

$user = new Userpdo();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'register':
                $login = $_POST['login'];
                $password = $_POST['password'];
                $email = $_POST['email'];
                $firstname = $_POST['firstname'];
                $lastname = $_POST['lastname'];
                $user->register($login, $password, $email, $firstname, $lastname);
                echo "Utilisateur enregistré avec succès !";
                break;

            case 'connect':
                $login = $_POST['login'];
                $password = $_POST['password'];
                if ($user->connect($login, $password)) {
                    echo "Utilisateur connecté : " . $_SESSION['user_login'];
                } else {
                    echo "Erreur de connexion.";
                }
                break;

            case 'disconnect':
                $user->disconnect();
                echo "Vous avez été déconnecté.";
                break;

            case 'update':
                $login = $_POST['login'];
                $password = $_POST['password'];
                $email = $_POST['email'];
                $firstname = $_POST['firstname'];
                $lastname = $_POST['lastname'];
                $user->update($login, $password, $email, $firstname, $lastname);
                echo "Utilisateur mis à jour avec succès !";
                break;

            case 'delete':
                $id = $_POST['user_id'];
                $user->delete($id);
                echo "Utilisateur supprimé avec succès !";
                break;
        }
    }
}

// Récupération de tous les utilisateurs pour les afficher
$allUsers = $user->getAllUsers();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
</head>
<body>
    <h1>Gestion des utilisateurs</h1>

    <!-- Si l'utilisateur n'est pas connecté, afficher le formulaire de connexion -->
    <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- Formulaire d'enregistrement -->
        <h2>Créer un utilisateur</h2>
        <form method="post">
            <input type="hidden" name="action" value="register">
            <label>Login:</label><input type="text" name="login" required><br>
            <label>Password:</label><input type="password" name="password" required><br>
            <label>Email:</label><input type="email" name="email" required><br>
            <label>Firstname:</label><input type="text" name="firstname" required><br>
            <label>Lastname:</label><input type="text" name="lastname" required><br>
            <button type="submit">Créer l'utilisateur</button>
        </form>

        <!-- Formulaire de connexion -->
        <h2>Connexion d'un utilisateur</h2>
        <form method="post">
            <input type="hidden" name="action" value="connect">
            <label>Login:</label><input type="text" name="login" required><br>
            <label>Password:</label><input type="password" name="password" required><br>
            <button type="submit">Se connecter</button>
        </form>

    <?php else: ?>
        <!-- Si l'utilisateur est connecté, afficher le bouton de déconnexion -->
        <h2>Bienvenue, <?= $_SESSION['user_login'] ?></h2>
        <form method="post">
            <input type="hidden" name="action" value="disconnect">
            <button type="submit">Se déconnecter</button>
        </form>
    <?php endif; ?>

    <!-- Formulaire de mise à jour -->
    <h2>Mettre à jour un utilisateur</h2>
    <form method="post">
        <input type="hidden" name="action" value="update">
        <label>Nouveau Login:</label><input type="text" name="login" required><br>
        <label>Nouveau Password:</label><input type="password" name="password"><br>
        <label>Nouveau Email:</label><input type="email" name="email" required><br>
        <label>Nouveau Firstname:</label><input type="text" name="firstname" required><br>
        <label>Nouveau Lastname:</label><input type="text" name="lastname" required><br>
        <button type="submit">Mettre à jour l'utilisateur</button>
    </form>

    <!-- Affichage des utilisateurs et formulaire de suppression -->
    <h2>Liste des utilisateurs</h2>
    <form method="post">
        <input type="hidden" name="action" value="delete">
        <label>Sélectionnez un utilisateur à supprimer:</label>
        <select name="user_id" required>
            <?php foreach ($allUsers as $userData): ?>
                <option value="<?= $userData['id'] ?>"><?= $userData['login'] ?> (ID: <?= $userData['id'] ?>)</option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Supprimer l'utilisateur</button>
    </form>
</body>
</html>
