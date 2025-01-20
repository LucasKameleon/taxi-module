<!DOCTYPE html>
<html lang="en">
   <head>
      <title>Find Location</title>
      <meta charset='utf-8' />
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
      <script defer src="https://maps.googleapis.com/maps/api/js?libraries=places&key=API_KEY" type="text/javascript"></script>
      <link rel="shortcut icon" href="map.png" type="image/x-icon">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
      <style type="text/css">
         a:hover{
         cursor: pointer;
         text-decoration: unset;
         }

         .heading_anchor{
            background: #8142b1 !important; 
            color: #fff !important;
         }
      </style>
   </head>
   <body>
      <?php
   // Voeg de shortcode toe voor het formulier
add_shortcode('offerte_formulier', 'render_offerte_formulier');

function render_offerte_formulier() {
    ob_start();
    ?>
    <form id="distance_form" method="post" action="">
        <div class="form-group">
            <label for="name">Uw naam + voornaam</label>
            <input class="form-control" type="text" name="name" placeholder="Uw naam + voornaam" required>
        </div>
        <div class="form-group">
            <label for="email">Uw e-mailadres</label>
            <input class="form-control" type="email" name="email" placeholder="steven@voorbeeld.be" required>
        </div>
        <div class="form-group">
            <label for="phone">Uw telefoonnummer</label>
            <input class="form-control" type="tel" name="phone" placeholder="+32" required>
        </div>
        <div class="form-group">
            <label>Adres van ophaling</label>
            <input class="form-control" id="from_places" name="from_places" placeholder="Geef hier je adres in" required>
            <input type="hidden" id="origin" name="origin">
            <a href="javascript:void(0);" onclick="getCurrentPosition()">Gebruik huidige locatie</a>
        </div>
        <div id="stopover_container"></div>
        <button type="button" id="add_stopover_btn" class="btn btn-primary">Voeg een tussenstop toe</button>
        <div class="form-group">
            <label>Eind bestemming</label>
            <input class="form-control" id="to_places" name="to_places" placeholder="Geef hier je adres in" required>
            <input type="hidden" id="destination" name="destination">
        </div>
        <div class="form-group">
            <label for="persons">Aantal personen</label>
            <input class="form-control" type="number" name="persons" min="1" max="7" value="1" required>
        </div>
        <div class="form-group">
            <label for="date">Datum</label>
            <input class="form-control" type="date" name="date" required>
        </div>
        <div class="form-group">
            <label for="time">Aanvang uur ophaling</label>
            <input class="form-control" type="time" name="time" required>
        </div>
        <div class="form-group">
            <label>Travel Mode</label>
            <select class="form-control" name="travel_mode" required>
                <option value="DRIVING">Driving</option>
            </select>
        </div>
        <input type="submit" class="btn btn-primary" value="Verstuur aanvraag">
    </form>
    <div id="result">
        <p id="in_kilo"></p>
        <p id="in_mile"></p>
        <p id="duration_text"></p>
    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script>
        // JavaScript: Google API en berekeningen
        document.addEventListener("DOMContentLoaded", function () {
            var stopovers = [];
            function initializeAutocomplete() {
                new google.maps.places.Autocomplete(document.getElementById("from_places"));
                new google.maps.places.Autocomplete(document.getElementById("to_places"));
            }
            initializeAutocomplete();

            document.getElementById("add_stopover_btn").addEventListener("click", function () {
                var container = document.getElementById("stopover_container");
                var input = document.createElement("input");
                input.classList.add("form-control");
                input.placeholder = "Geef tussenstop in";
                container.appendChild(input);
                stopovers.push(input);
            });
        });

        function getCurrentPosition() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var geocoder = new google.maps.Geocoder();
                    var latlng = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    geocoder.geocode({ location: latlng }, function (results, status) {
                        if (status === "OK" && results[0]) {
                            document.getElementById("from_places").value = results[0].formatted_address;
                        }
                    });
                });
            } else {
                alert("Geolocatie wordt niet ondersteund.");
            }
        }
    </script>
    <?php
    return ob_get_clean();
}

// Verwerk het formulier en stuur een e-mail met PHPMailer
add_action('init', 'verwerk_offerte_formulier');

function verwerk_offerte_formulier() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
        // Informatie verzamelen
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $from = sanitize_text_field($_POST['from_places']);
        $to = sanitize_text_field($_POST['to_places']);
        $persons = intval($_POST['persons']);
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        $travel_mode = sanitize_text_field($_POST['travel_mode']);

        // Bericht opstellen
        $subject = "Nieuwe offerteaanvraag";
        $message = "Naam: $name\nE-mail: $email\nTelefoon: $phone\nVan: $from\nNaar: $to\n";
        $message .= "Aantal personen: $persons\nDatum: $date\nTijd: $time\nReismodus: $travel_mode\n";

        // PHPMailer gebruiken voor SMTP
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'lucas.kameleon@gmail.com';
            $mail->Password = 'hSl!5242@-';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('lucas.kameleon@gmail.com', 'Lucas');
            $mail->addAddress('heymanslucas@outlook.com');

            $mail->Subject = $subject;
            $mail->Body = $message;

            if ($mail->send()) {
                echo '<p style="color: green;">E-mail succesvol verzonden.</p>';
            } else {
                echo '<p style="color: red;">E-mail verzenden mislukt.</p>';
            }
        } catch (Exception $e) {
            echo '<p style="color: red;">Error: ' . $mail->ErrorInfo . '</p>';
        }
    }
}
?>
   </body>
</html>
