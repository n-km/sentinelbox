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
    	$iv = $_POST["iv"];
        
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
        mysqli_query($conn, $sql_send);
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
        <form action="home.php"><button>Übersicht</button></form>
        <form action="send.php"><button class="main" disabled="disabled">E-Mail verfassen</button></form>
	<form action="sent_emails.php"><button>Gesendendete E-Mails</button></form>
<form action="change_pw.php"><button>Passwort ändern</button></form>
        <form action="index.php"><button>Abmelden</button></form>
        
    </div>
    <!-- E-Mail-Bereich -->
    <div class="content">
        <div class="email">
            <!-- E-Mails anzeigen -->
            <h2>Hallo, <?php echo $_SESSION['username']; ?>!</h2>
        </div>
        <!-- E-Mail senden -->
        
    
    <div class="send" id="SEND">
            <h3>E-Mail senden</h3>
            <form action="" method="post">
                <input type="email" name="receiver" placeholder="Empfänger" required>
                <br>
                <input type="text" name="subject" placeholder="Betreff" required>
                <br>
                <textarea name="message" cols="30" rows="10" placeholder="Nachricht" required></textarea>
                <br>
				<input type="number" name="iv" placeholder="1" value="1" disabled="disabled" style="display: none;" required>
                <button type="submit" name="send_email">Senden</button>
            </form>
        </div>
        <div class="usertable">
        <h3>Benutzertabelle</h3>
<form action="" method="GET">
    <input type="text" name="search" placeholder="Benutzername oder ID eingeben" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
    <button type="submit">Suchen</button>
</form>

<table>
    <tr>
        <th>ID</th>
        <th>Benutzername</th>
    </tr>
    <?php
        // SQL-Abfrage für die Benutzertabelle mit Suchfunktion nach Benutzernamen oder ID
        $sql_users = "SELECT id, username FROM users";

        // Wenn eine Suchanfrage gestellt wurde
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql_users .= " WHERE username LIKE '%$search%' OR id LIKE '%$search%'";
        }

        $result_users = mysqli_query($conn, $sql_users);

        // Überprüfen, ob Datensätze vorhanden sind
        if (mysqli_num_rows($result_users) > 0) {
            // Ausgabe der Daten in der Tabelle
            while ($row_users = mysqli_fetch_assoc($result_users)) {
                echo "<tr>";
                echo "<td>" . $row_users['id'] . "</td>";
                echo "<td>" . $row_users['username'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='2'>Keine Benutzer gefunden</td></tr>";
        }
    ?>
</table>
</div>
</div>
</body>
</html>
