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

$username = $_SESSION['username'];

// Selbst gesendete E-Mails abrufen
$sql_receive = "
    SELECT emails.id, users.username AS recipient_username, emails.subject, emails.message, emails.sent_at 
    FROM emails 
    INNER JOIN users ON emails.receiver_id = users.id 
    WHERE emails.sender_id = (SELECT id FROM users WHERE username = ?) 
    ORDER BY emails.sent_at DESC";

$stmt_receive = $conn->prepare($sql_receive);
$stmt_receive->bind_param("s", $username);
$stmt_receive->execute();
$result_receive = $stmt_receive->get_result();

// E-Mail löschen
if (isset($_GET['delete'])) {
    $email_id = $_GET['delete'];

    // Sicherstellen, dass die E-Mail vom aktuellen Benutzer gesendet wurde
    $sql_delete = "DELETE FROM emails WHERE id = ? AND sender_id = (SELECT id FROM users WHERE username = ?)";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("is", $email_id, $username);

    if ($stmt_delete->execute()) {
        header("Location: sent_emails.php");
        exit();
    } else {
        echo "Fehler beim Löschen der E-Mail: " . $stmt_delete->error;
    }
    $stmt_delete->close();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gesendete E-Mails</title>
    <link rel="icon" href="images/logoicon.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=Shantell+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>
    <h1>DDS - Das Datenbanksystem</h1>

    <div class="received-emails">
        <h3>Gesendete E-Mails</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Empfänger</th>
                    <th>Betreff</th>
                    <th>Nachricht</th>
                    <th>Gesendet am</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row_receive = $result_receive->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row_receive['id']); ?></td>
                        <td><?php echo htmlspecialchars($row_receive['recipient_username']); ?></td>
                        <td><?php echo htmlspecialchars($row_receive['subject']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row_receive['message'])); ?></td>
                        <td><?php echo htmlspecialchars($row_receive['sent_at']); ?></td>
                        <td>
                            <a href="edit_email.php?id=<?php echo $row_receive['id']; ?>">Bearbeiten</a> |
                            <a href="?delete=<?php echo $row_receive['id']; ?>" onclick="return confirm('Sind Sie sicher, dass Sie diese E-Mail löschen möchten?');">Löschen</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <a href="home.php">Zurück</a>
                <a href="sent_emails_hash.php">Gesendete Hash E-Mails bearbeiten/löschen</a>
</body>
</html>
