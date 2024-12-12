<?php
session_start();

// Datenbankverbindung herstellen
$db_host = 'localhost';
$db_name = 'phpmyadmin';
$conn = mysqli_connect($db_host, 'phpmyadmin', 'nils', $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Überprüfen, ob die ID der zu bearbeitenden E-Mail vorhanden ist
if (!isset($_GET['id'])) {
    die('Ungültige Anfrage');
}

$email_id = $_GET['id'];

// E-Mail-Daten abrufen
$sql_get_email = "SELECT * FROM emails WHERE id = ? AND sender_id = (SELECT id FROM users WHERE username = ?)";
$stmt_get_email = $conn->prepare($sql_get_email);
$stmt_get_email->bind_param("is", $email_id, $_SESSION['username']);
$stmt_get_email->execute();
$result_get_email = $stmt_get_email->get_result();
$email = $result_get_email->fetch_assoc();

if (!$email) {
    die('E-Mail nicht gefunden');
}

// E-Mail bearbeiten
if (isset($_POST['update_email'])) {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    $sql_update = "UPDATE emails SET subject = ?, message = ? WHERE id = ? AND sender_id = (SELECT id FROM users WHERE username = ?)";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssis", $subject, $message, $email_id, $_SESSION['username']);
    
    if ($stmt_update->execute()) {
        header("Location: sent_emails.php");
        exit();
    } else {
        echo "Fehler beim Aktualisieren der E-Mail: " . $stmt_update->error;
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Mail bearbeiten</title>
    <link rel="icon" href="images/logoicon.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=Shantell+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>
    <h2>E-Mail bearbeiten</h2>
    <form action="" method="post">
        <input type="text" name="subject" value="<?php echo htmlspecialchars($email['subject']); ?>" required>
        <br>
        <textarea name="message" cols="30" rows="10" required><?php echo htmlspecialchars($email['message']); ?></textarea>
        <br>
        <button type="submit" name="update_email">Speichern</button>
    </form>
    <a href="sent_emails.php">Zurück</a>
</body>
</html>
