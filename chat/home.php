<?php
session_start();

// Datenbankverbindung herstellen
$db_host = 'localhost';
$db_name = 'phpmyadmin';
$db_user = 'phpmyadmin';
$db_pass = 'nils';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['username'])) {
    // Wenn nicht angemeldet, zur Anmeldeseite weiterleiten
    header("Location: index.php");
    exit();
}

// E-Mails empfangen
$username = $_SESSION['username'];
$sql_receive = "SELECT * FROM emails WHERE receiver_id = (SELECT id FROM users WHERE username = '$username') ORDER BY sent_at DESC";
$result_receive = mysqli_query($conn, $sql_receive);

// E-Mails senden
if (isset($_POST['send_email'])) {
    $sender_username = $_SESSION['username'];
    $receiver_username = $_POST['receiver'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $iv = $_POST['iv'];  // Stellen Sie sicher, dass der IV-Wert korrekt übergeben wird
    
    // Empfänger-ID abrufen
    $sql_receiver_id = "SELECT id FROM users WHERE username = '$receiver_username'";
    $result_receiver_id = mysqli_query($conn, $sql_receiver_id);
    $row = mysqli_fetch_assoc($result_receiver_id);
    $receiver_id = $row['id'];

    // Eigene ID abrufen
    $sql_own_id = "SELECT id FROM users WHERE username = '$sender_username'";
    $result_own_id = mysqli_query($conn, $sql_own_id);
    $row_own_id = mysqli_fetch_assoc($result_own_id);
    $sender_id = $row_own_id['id'];

	

    // E-Mail in die Datenbank einfügen
    $sql_send = "INSERT INTO emails (sender_id, receiver_id, subject, message, iv) VALUES ('$sender_id', '$receiver_id', '$subject', '$message', '$iv')";
    if (!mysqli_query($conn, $sql_send)) {
        die("Error: " . mysqli_error($conn));
    }
}

// Antwort-Formular
if (isset($_POST['reply'])) {
    $reply_message = $_POST['reply_message'];
    $original_email_id = $_POST['email_id'];
    $iv = $_POST['iv'];  // Stellen Sie sicher, dass der IV-Wert korrekt übergeben wird

    // Ermitteln des ursprünglichen E-Mail-Absenders und Betreffs
    $sql_original_email = "SELECT sender_id, subject FROM emails WHERE id = '$original_email_id'";
    $result_original_email = mysqli_query($conn, $sql_original_email);
    if ($row_original_email = mysqli_fetch_assoc($result_original_email)) {
        $original_sender_id = $row_original_email['sender_id'];
        $original_subject = $row_original_email['subject'];

        // Ermitteln des Benutzernamens des ursprünglichen Absenders
        $sql_original_sender = "SELECT username FROM users WHERE id = '$original_sender_id'";
        $result_original_sender = mysqli_query($conn, $sql_original_sender);
        if ($row_original_sender = mysqli_fetch_assoc($result_original_sender)) {
            $original_sender_username = $row_original_sender['username'];

            // Eigene ID abrufen
            $sql_sender_id = "SELECT id FROM users WHERE username = '$username'";
            $result_sender_id = mysqli_query($conn, $sql_sender_id);
            if ($row_sender_id = mysqli_fetch_assoc($result_sender_id)) {
                $sender_id = $row_sender_id['id'];

                // E-Mail antworten
                $sql_reply = "INSERT INTO emails (sender_id, receiver_id, subject, message, iv) VALUES (
                    '$sender_id',
                    (SELECT id FROM users WHERE username = '$original_sender_username'),
                    CONCAT('Re: ', '$original_subject'),
                    '$reply_message',
                    '$iv'
                )";
                if (!mysqli_query($conn, $sql_reply)) {
                    die("Error: " . mysqli_error($conn));
                }
            } else {
                die("Error retrieving sender ID.");
            }
        } else {
            die("Error retrieving original sender username.");
        }
    } else {
        die("Error retrieving original email.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SentinelBox</title>
    <link rel="icon" href="images/logoicon.png">
    <link rel="stylesheet" href="secure.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=Shantell+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>

<div class="menu-button" onclick="LOGO.style.transform = 'translate(0, 0)'">
<span class="material-symbols-outlined">menu</span>
</div>

<div class="logo" id="LOGO">
    <div class="menu-button-close" onclick="LOGO.style.transform = 'translate(-100%, 0)'">
        <span class="material-symbols-outlined">menu</span>
    </div>
    <h2> SentinelBox</h2>
    <form action=""><button>Neu laden</button></form>
    <form action="home.php"><button class="main" disabled="disabled">Übersicht</button></form>
    <form action="send.php"><button>E-Mail verfassen</button></form>
    <form action="send-hash-mail.php"><button>Hash E-Mail verfassen</button></form>
    <form action="sent_emails.php"><button>Gesendendete E-Mails</button></form>
    <form action="came_emails_hash.php"><button>Empfangene E-Mails</button></form>
    <form action="change_pw.php"><button>Passwort ändern</button></form>
    <form action="index.php"><button>Abmelden</button></form>
</div>

<!-- E-Mail-Bereich -->
<div class="content">
    <div class="email">
        <!-- E-Mails anzeigen -->
        <h2>Hallo, <?php echo $_SESSION['username']; ?>!</h2>
 		
        <h3>E-Mail Postfach</h3>
        <ul>
            <?php
                while ($row = mysqli_fetch_assoc($result_receive)) {
                    echo "<li>
                        <strong>ID:</strong> " . $row['id'] . " <br>
                        <strong>Von:</strong> " . $row['sender_id'] . " <br>
                        <strong>Betreff:</strong> " . $row['subject'] . " <br>
                        <strong>Nachricht:</strong> " . $row['message'] . " <br>
                        <form action='' method='POST' style='display:inline;'>
                            <input type='hidden' name='email_id' value='" . $row['id'] . "'>
                            <input type='text' name='reply_message' placeholder='Antworten...' required>
                            <input type='hidden' name='iv' value='0' required>
                            <button type='submit' name='reply'>Antworten</button>
                        </form>
                    </li>";
                }
            ?>
        </ul>
    </div>
</div>

</body>
</html>
