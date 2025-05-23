<?php include 'header.php'; ?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css">
    <title>Location | MYBlood</title>
    <style>
      body {
        font-family: 'Roboto', sans-serif;
        color: #333;
      }
      .jumbotron {
        background-color: light-grey;
        color: #000000;
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }
      .contact-locator-container {
        padding: 20px;
        background-color: #fff;
      }
	  
	  .contact-locator-container {
        padding: 20px;
        background-color: #fff;
      }

      .card {
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
      }
	  h2.blood-donation-centers {
		  margin-left: 200px;
		  color: #000000;
		}


      .map-container {
        width: 100%;
        height: 400px;
        border-radius: 10px;
        border: 2px solid #000000;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      }
      .list-group-item {
        cursor: pointer;
        transition: background-color 0.3s ease;
      }
      .list-group-item:hover {
        background-color: grey;
        color: #000000;
      }
      .btn-locator {
        background: linear-gradient(90deg, #000000, #B71C1C);
        color: #fff;
        border: none;
        transition: background 0.3s ease;
      }
      .btn-locator:hover {
        background: linear-gradient(90deg, #B71C1C, #000000);
      }
      #searchBox {
        border: 2px solid #000000;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 5px;
        width: 300px;
        margin: 10px;
      }
      #searchBox:focus {
        outline: none;
        box-shadow: 0 0 10px rgba(211, 47, 47, 0.5);
      }
	  footer {
		background-color: #000000; /* Black background */
		color: #ffffff; /* White text */
		text-align: center;
		padding: 20px;
		font-family: Arial, sans-serif;
		margin-top: auto;
	}

	footer p {
		margin: 0;
		font-size: 14px;
	}
    </style>
  </head>
  <body>
    <div class="jumbotron text-center">
      <h1>Donate Blood, Save Lives</h1>
      <p>Find the nearest blood donation center and make a difference today!</p>
    </div>

    <div class="contact-locator-container container">
      <h2 class="mb-4 blood-donation-centers">Blood Donation Centers</h2>
      <div class="row">
        <!-- Map Card -->
        <div class="col-md-8">
          <div class="card">
            <div class="card-body">
              <div id="map" class="map-container"></div>
            </div>
          </div>
        </div>

        <!-- Center List -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <h4>Available Centers</h4>
              <ul id="centerList" class="list-group">
                <li class="list-group-item" onclick="focusOnCenter(0)">Hospital Shah Alam</li>
                <li class="list-group-item" onclick="focusOnCenter(1)">Hospital Tengku Ampuan Rahimah</li>
                <li class="list-group-item" onclick="focusOnCenter(2)">Hospital Serdang</li>
                <li class="list-group-item" onclick="focusOnCenter(3)">Hospital Kajang</li>
                <li class="list-group-item" onclick="focusOnCenter(4)">Hospital Sungai Buloh</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
	<footer>
        <p>&copy; 2025 MYBlood. All Rights Reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDefcDgWQrKM52A41_1XFcZ6-Bb_h_enLU&libraries=places"></script>
    <script>
      // List of blood donation centers
      const centers = [
        { name: "Hospital Shah Alam", lat: 3.0738, lng: 101.5183 },
        { name: "Hospital Tengku Ampuan Rahimah", lat: 3.0319, lng: 101.4450 },
        { name: "Hospital Serdang", lat: 2.9765, lng: 101.7175 },
        { name: "Hospital Kajang", lat: 2.9939, lng: 101.7873 },
        { name: "Hospital Sungai Buloh", lat: 3.2146, lng: 101.5769 }
      ];

      let map;

      function initMap() {
        // Initialize the map
        map = new google.maps.Map(document.getElementById("map"), {
          zoom: 10,
          center: { lat: 3.0738, lng: 101.5183 } // Center around Shah Alam
        });

        // Add hospital markers
        centers.forEach((center, index) => {
          const marker = new google.maps.Marker({
            position: { lat: center.lat, lng: center.lng },
            map: map,
            title: center.name,
          });

          const infoWindow = new google.maps.InfoWindow({
            content: `<strong>${center.name}</strong>`,
          });

          marker.addListener("click", () => {
            infoWindow.open(map, marker);
          });

          // Link marker to center list
          const listItem = document.querySelector(`#centerList li:nth-child(${index + 1})`);
          listItem.addEventListener("click", () => {
            map.setCenter(marker.getPosition());
            map.setZoom(14);
            infoWindow.open(map, marker);
          });
        });

        // Add search box
        const input = document.createElement("input");
        input.type = "text";
        input.placeholder = "Search for location...";
        input.id = "searchBox";
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        const searchBox = new google.maps.places.SearchBox(input);

        searchBox.addListener("places_changed", () => {
          const places = searchBox.getPlaces();
          if (places.length === 0) return;

          const bounds = new google.maps.LatLngBounds();
          places.forEach(place => {
            if (!place.geometry) return;

            bounds.extend(place.geometry.location);
          });

          map.fitBounds(bounds);
        });
      }

      google.maps.event.addDomListener(window, "load", initMap);
    </script>
  </body>
</html>
