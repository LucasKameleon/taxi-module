<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require 'vendor/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/src/Exception.php';
require 'vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars($_POST['phone']);
    $from = htmlspecialchars($_POST['from_places']);
    $to = htmlspecialchars($_POST['to_places']);
    $persons = intval($_POST['persons']);
    $date = htmlspecialchars($_POST['date']);
    $time = htmlspecialchars($_POST['time']);
    $travel_mode = htmlspecialchars($_POST['travel_mode']);

    $subject = "Nieuwe offerteaanvraag";
    $message = "Naam: $name\nE-mail: $email\nTelefoon: $phone\nVan: $from\nNaar: $to\n";
    $message .= "Aantal personen: $persons\nDatum: $date\nTijd: $time\nReismodus: $travel_mode\n";

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lucas.kameleon@gmail.com'; // Vervang door je eigen e-mail
        $mail->Password = 'wachtwoord'; // Zorg voor een app-wachtwoord of veilige authenticatie
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('lucas.kameleon@gmail.com', 'Offerteformulier');
        $mail->addAddress('heymanslucas@outlook.com'); // Vervang door het ontvangende e-mailadres

        $mail->Subject = $subject;
        $mail->Body = $message;

        if ($mail->send()) {
            echo '<p style="color: green;">E-mail succesvol verzonden.</p>';
        } else {
            echo '<p style="color: red;">E-mail verzenden mislukt.</p>';
        }
    } catch (Exception $e) {
        echo '<p style="color: red;">Fout: ' . $mail->ErrorInfo . '</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Find Location</title>
    <meta charset='utf-8' />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <script defer src="https://maps.googleapis.com/maps/api/js?key=API_KEY&libraries=places"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        a:hover {
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Offerte Aanvraag</h2>
        <form method="post">
            <div class="form-group">
                <label>Uw naam + voornaam</label>
                <input class="form-control" type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Uw e-mailadres</label>
                <input class="form-control" type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Uw telefoonnummer</label>
                <input class="form-control" type="tel" name="phone" required>
            </div>
            <div class="form-group">
                <label>Adres van ophaling</label>
                <input class="form-control" id="from_places" name="from_places" required>
            </div>
            <div class="form-group">
                <label>Eind bestemming</label>
                <input class="form-control" id="to_places" name="to_places" required>
            </div>
            <div class="form-group">
                <label>Aantal personen</label>
                <input class="form-control" type="number" name="persons" min="1" max="7" value="1" required>
            </div>
            <div class="form-group">
                <label>Datum</label>
                <input class="form-control" type="date" name="date" required>
            </div>
            <div class="form-group">
                <label>Aanvang uur ophaling</label>
                <input class="form-control" type="time" name="time" required>
            </div>
            <div class="form-group">
                <label>Reismodus</label>
                <select class="form-control" name="travel_mode" required>
                    <option value="DRIVING">Driving</option>
                </select>
            </div>
            <input type="submit" class="btn btn-primary" value="Verstuur aanvraag">
        </form>
    </div>
    <script>
        function initializeAutocomplete() {
            new google.maps.places.Autocomplete(document.getElementById("from_places"));
            new google.maps.places.Autocomplete(document.getElementById("to_places"));
        }
        document.addEventListener("DOMContentLoaded", initializeAutocomplete);
    </script>
</body>
</html>
