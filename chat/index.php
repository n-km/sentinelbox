<?php
session_start();

// Datenbankverbindung herstellen
$db_host = 'localhost'; 
$db_user = 'phpmyadmin';
$db_password = 'nils';
$db_name = 'phpmyadmin';
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);

if (!$conn) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . mysqli_connect_error());
}

// Funktion zur Benutzeranmeldung
function loginUser($username, $password) {
    global $conn;
    $sql = "SELECT password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Passwort überprüfen
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;
            return true;
        }
    }
    return false;
}

// Funktion zur Benutzerregistrierung
function registerUser($username, $password) {
    global $conn;
    $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $hashed_pw);

    return $stmt->execute();
}

// Verarbeitung der Anmeldedaten
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if (loginUser($username, $password)) {
        header("Location: home.php");
        exit();
    } else {
        $message = "<p>Anmeldung fehlgeschlagen.</p>";
    }
}

// Verarbeitung der Registrierungsdaten
if (isset($_POST['register'])) {
    $username = $_POST['username'] . '@sentinelbox.de';
    $password = $_POST['password'];
    if (registerUser($username, $password)) {
        $message = "<p>Registrierung erfolgreich. </p>";
    } else {
        $message = "<p>Registrierung fehlgeschlagen.</p>";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/logoicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=Shantell+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <title>SentinelBox - Login</title>
</head>
<body>
    <!-- Anmeldeformular -->
    <div class="content">
        <div class="email">
            <!-- E-Mail-Bereich hier -->
            <h2>Anmeldung</h2>
            <?php if(isset($message)) echo $message; ?> <!-- Rückmeldungsnachricht anzeigen -->
            <form action="" method="post">
                <input type="email" name="username" placeholder="Email" required>
                <br>
                <input type="password" name="password" placeholder="Passwort" required>
                <br>
                <button type="submit" name="login">Anmelden</button>
            </form>
        </div>
        <div class="registration">
            <!-- Registrierungsbereich hier -->
            <h2>Registrieren</h2>
            <form action="" method="post">
                <input type="text" name="username" placeholder="Benutzername" required>@sentinelbox.de
                <br>
                <input type="password" name="password" placeholder="Passwort" required>
                <br>
                <button type="submit" name="register">Registrieren</button>
            </form>
        </div>
    </div>
    <div class="logo">
        SentinelBox
    </div>
</body>
</html>
