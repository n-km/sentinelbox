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

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Passwort aktualisieren
if (isset($_POST['update_pw'])) {
    $old_pw = $_POST['old_password'];
    $new_pw = $_POST['new_password'];
    $confirm_pw = $_POST['confirm_password'];

    // Benutzername des aktuell angemeldeten Benutzers holen
    $username = $_SESSION['username'];

    // Altes Passwort überprüfen
    $sql_check = "SELECT password FROM users WHERE username = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->store_result();
    $stmt_check->bind_result($hashed_old_pw);
    $stmt_check->fetch();

    if ($stmt_check->num_rows == 0 || !password_verify($old_pw, $hashed_old_pw)) {
        echo "Das alte Passwort ist falsch.";
    } else {
        // Überprüfen, ob die neuen Passwörter übereinstimmen
        if ($new_pw !== $confirm_pw) {
            echo "Die neuen Passwörter stimmen nicht überein.";
        } else {
            $hashed_new_pw = password_hash($new_pw, PASSWORD_DEFAULT);
 
            // Passwort aktualisieren
            $sql_update = "UPDATE users SET password = ? WHERE username = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ss", $hashed_new_pw, $username);

            if ($stmt_update->execute()) {
            	echo "<div style='display: flex; justify-content: center; font-family: arial; margin-top: 30vh;'>";
            	echo "<form style='justify-content: center;'>";
            	echo "<img src='https://cdn-icons-png.flaticon.com/512/6195/6195699.png' height='150px'>";
                echo "<br><br>Passwort erfolgreich aktualisiert. <br> <br>";
                echo "<a href='home.php'>Zurück</a>";
            	echo "</form>";
            	echo "</div>";
                exit();
            } else {
                echo "Fehler beim Aktualisieren des Passworts: " . $stmt_update->error;
            }
            $stmt_update->close();
        }
    }
    $stmt_check->close();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort ändern</title>
    <link rel="icon" href="images/logoicon.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=Shantell+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>
    <h2>Passwort ändern</h2>
    <form action="" method="post">
        <label for="old_password">Altes Passwort:</label>
        <input type="password" name="old_password" id="old_password" required>
        <br>
        <label for="new_password">Neues Passwort:</label>
        <input type="password" name="new_password" id="new_password" required>
        <br>
        <label for="confirm_password">Neues Passwort bestätigen:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        <br>
        <button type="submit" name="update_pw">Speichern</button>
    </form>
    <a href="home.php">Zurück</a>
</body>
</html>
