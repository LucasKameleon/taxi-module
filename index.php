<!DOCTYPE html>
<html lang="en">
   <head>
      <title>Find Location</title>
      <meta charset='utf-8' />
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
      <script defer src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyB0gHMQbdBqYTf8dXl5MhiAr0MTt-i_TyE" type="text/javascript"></script>
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
      <div class='container-fluid'>
         <div class='row'>
            <div class='col-md'>
               <div class='well define_height'>
                  <form id="distance_form" method="post" action=""> <!-- Action verwijst naar dezelfde pagina -->
                     <div class="form-group">
                        <label for="">Uw naam + voornaam</label>
                        <input class="form-control" type="text" placeholder="Uw naam + voornaam" name="name" required>
                     </div>
                     <div class="form-group">
                        <label for="">Uw e-mailadres</label>
                        <input class="form-control" type="email" name="email" placeholder="steven@voorbeeld.be" required>
                     </div>
                     <div class="form-group">
                        <label for="">Uw telefoonnummer</label>
                        <input class="form-control" type="tel" name="phone" placeholder="+32" required>
                     </div>
                     <div class="form-group">
                        <label>Adres van ophaling</label>
                        <input class="form-control ophaling" id="from_places" name="from_places" placeholder="Geef hier je adres in" required/>                        
                        <input id="origin" name="origin" type="hidden"/>
                        <a onclick="getCurrentPosition()">Set Current Location</a>
                     </div>
                     <div id="stopover_container"></div>
                     <button type="button" id="add_stopover_btn" class="btn btn-primary">Voeg een tussenstop toe</button>
                     <div class="form-group">
                        <label>Eind bestemming</label>
                        <input class="form-control" id="to_places" name="to_places" placeholder="Geef hier je adres in" required/>
                        <input id="destination" name="destination" type="hidden"/>
                     </div>
                     <div class="form-group">
                        <label for="">Aantal personen</label>
                        <input class="form-control" type="number" min="1" max="7" name="persons" value="1">
                     </div>
                     <div class="form-group">
                        <label for="">Datum</label>
                        <input class="form-control" type="date" name="date" required>
                     </div>
                     <div class="form-group">
                        <label for="">Aanvang uur ophaling</label>
                        <input class="form-control" type="time" name="time" required>
                     </div>
                     <div class="form-group">
                        <label>Travel Mode</label>
                        <select class="form-control" id="travel_mode" name="travel_mode">
                           <option value="DRIVING">Driving</option>
                        </select>
                     </div>
                     <input class="btn btn-primary" type="submit" name="submit" value="Verstuur aanvraag" style="background: #8142b1; width: 100%; border: 0px;" />
                  </form>
                  <div class="row" style="padding-top: 20px;">
                     <div class="container">
                        <p id="in_kilo"></p>
                        <p id="in_mile"></p>
                        <p id="duration_text"></p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <script>
         document.addEventListener("DOMContentLoaded", function() {
            var origin, destination, map;
            var stopover = []; // Array to store multiple stopover addresses

            // add input listeners
            google.maps.event.addDomListener(window, 'load', function (listener) {
               setDestination();
               initMap();
            });

            // init or load map
            function initMap() {
               var myLatLng = {
                  lat: 50.887691,
                  lng: 4.470130
               };
               map = new google.maps.Map(document.getElementById('map'), {zoom: 10, center: myLatLng});
            }

            function setDestination() {
               var from_places = new google.maps.places.Autocomplete(document.getElementById('from_places'));
               var to_places = new google.maps.places.Autocomplete(document.getElementById('to_places'));
               var add_stopover_btn = document.getElementById('add_stopover_btn');

               google.maps.event.addListener(from_places, 'place_changed', function () {
                  var from_place = from_places.getPlace();
                  var from_address = from_place.formatted_address;
                  $('#origin').val(from_address);
               });

               google.maps.event.addListener(to_places, 'place_changed', function () {
                  var to_place = to_places.getPlace();
                  var to_address = to_place.formatted_address;
                  $('#destination').val(to_address);
               });

               // Event listener to add stopover
               add_stopover_btn.addEventListener('click', function () {
                  var stopover_container = document.getElementById('stopover_container');
                  var new_stopover_input = document.createElement('input');
                  new_stopover_input.classList.add('form-control', 'between_places');
                  new_stopover_input.setAttribute('placeholder', 'Enter stopover address');
                  stopover_container.appendChild(new_stopover_input);

                  var new_stopover_autocomplete = new google.maps.places.Autocomplete(new_stopover_input);
                  google.maps.event.addListener(new_stopover_autocomplete, 'place_changed', function () {
                     var between_place = new_stopover_autocomplete.getPlace();
                     if (between_place && between_place.formatted_address) {
                        stopover.push(between_place.formatted_address);
                     }
                  });
               });
            }
         });

         // get current Position
         function getCurrentPosition() {
            if (navigator.geolocation) {
               navigator.geolocation.getCurrentPosition(setCurrentPosition);
            } else {
               alert("Geolocation is not supported by this browser.")
            }
         }

         // get formatted address based on current position and set it to input
         function setCurrentPosition(pos) {
            var geocoder = new google.maps.Geocoder();
            var latlng = {lat: parseFloat(pos.coords.latitude), lng: parseFloat(pos.coords.longitude)};
            geocoder.geocode({'location': latlng}, function (responses) {
               if (responses && responses.length > 0) {
                  $("#origin").val(responses[1].formatted_address);
                  $("#from_places").val(responses[1].formatted_address);
               } else {
                  alert("Cannot determine address at this location.")
               }
            });
         }
      </script>

      <?php
         if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Valideer en schoon de invoer
            $name = htmlspecialchars($_POST['name']);
            $email = htmlspecialchars($_POST['email']);
            $phone = htmlspecialchars($_POST['phone']);
            $from = htmlspecialchars($_POST['from_places']);
            $to = htmlspecialchars($_POST['to_places']);
            $persons = intval($_POST['persons']);
            $date = htmlspecialchars($_POST['date']);
            $time = htmlspecialchars($_POST['time']);
            $travel_mode = htmlspecialchars($_POST['travel_mode']);

            // Onderwerp en bericht opstellen
            $subject = "Offerte Aanvraag";
            $message = "Naam: " . $name . "\n";
            $message .= "E-mailadres: " . $email . "\n";
            $message .= "Telefoonnummer: " . $phone . "\n";
            $message .= "Adres van ophaling: " . $from . "\n";
            $message .= "Eind bestemming: " . $to . "\n";
            $message .= "Aantal personen: " . $persons . "\n";
            $message .= "Datum: " . $date . "\n";
            $message .= "Aanvang uur ophaling: " . $time . "\n";
            $message .= "Reismodus: " . $travel_mode . "\n";

            // Het e-mailadres waarnaar de aanvraag wordt verzonden
            $to_email = "lucas.kameleon@gmail.com";

            // Headers instellen voor de e-mail
            $headers = "From: noreply@jouwdomein.com\r\n";
            $headers .= "Reply-To: " . $email . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            // E-mail verzenden
            if (mail($to_email, $subject, $message, $headers)) {
               echo "<p style='color: green;'>Bedankt! Uw aanvraag is succesvol verzonden.</p>";
            } else {
               echo "<p style='color: red;'>Er is een probleem opgetreden bij het versturen van uw aanvraag. Probeer het later opnieuw.</p>";
            }
         }
      ?>
   </body>
</html>
