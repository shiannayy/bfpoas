<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Oas Map (Bounded)</title>
    <style>
        #map {
            height: 100vh;
            width: 100%;
        }

        #info {
            position: absolute;
            top: 10px;
            left: 10px;
            background: white;
            padding: 10px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            font-family: Arial, sans-serif;
            z-index: 5;
        }
    </style>
</head>

<body>
    <div id="info">Click anywhere inside Oas to show route and get coordinates</div>
    <div id="map"></div>
    <div id="legend" style="
  background: white;
  padding: 10px;
  margin: 10px;
  font-size: 13px;
  border: 1px solid #999;
  max-height: 150px;
  overflow-y: auto;
  border-radius: 8px;
"></div>

    <!-- ✅ Load Google Maps JS API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAVal_YrBtEwQTFU6ianCaic2uVc6P_Jgc&libraries=places,geometry&callback=initMap" async defer>
    </script>
    <script>
        let map, directionsService, directionsRenderer, geocoder, infoWindow, destinationMarker;

        const bfpOas = {
            lat: 13.2574615,
            lng: 123.4997089
        };
        const oasBounds = {
            north: 13.3100,
            south: 13.2000,
            west: 123.4300,
            east: 123.5400
        };

        async function initMap() {
            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                map,
                suppressMarkers: false,
                polylineOptions: {
                    strokeColor: "#FF0000",
                    strokeWeight: 5,
                },

            });

            geocoder = new google.maps.Geocoder();
            infoWindow = new google.maps.InfoWindow();

            map = new google.maps.Map(document.getElementById("map"), {
                center: bfpOas,
                zoom: 13,
                restriction: {
                    latLngBounds: oasBounds,
                    strictBounds: true
                },
                mapTypeId: "roadmap",
                streetViewControl: false
            });

            directionsRenderer.setMap(map);

            // 🔥 Fire Station Marker
            new google.maps.Marker({
                position: bfpOas,
                map,
                title: "BFP Oas Fire Station",
                icon: "https://maps.google.com/mapfiles/ms/icons/red-dot.png"
            });

            // 🚩 Load Saved Locations
            loadSavedLocations(map);

            // 🧭 Load route if GET variables exist
            const params = new URLSearchParams(window.location.search);
            const lat = parseFloat(params.get("lat"));
            const lng = parseFloat(params.get("lng"));
            const address = params.get("address");

            if (lat && lng) {
                const destination = {
                    lat,
                    lng
                };
                showRoute(bfpOas, destination);
                document.getElementById("info").innerHTML = `
          <b>Loaded from GET:</b><br>
          Address: ${decodeURIComponent(address || "")}<br>
          Lat: ${lat}<br>
          Lng: ${lng}
        `;
            }

            // 📍 Map Click Event
            map.addListener("click", (e) => {
                const clickedLatLng = e.latLng;
                showRoute(bfpOas, clickedLatLng);
                getReadableAddress(clickedLatLng);
            });
        }

        // 🚗 Draw route between fire station and clicked location
        function showRoute(origin, destination) {
            if (destinationMarker) destinationMarker.setMap(null);
            destinationMarker = new google.maps.Marker({
                position: destination,
                map: map,
                title: "Destination",
                icon: "https://maps.google.com/mapfiles/ms/icons/green-dot.png"
            });

            directionsService.route({
                origin,
                destination,
                travelMode: google.maps.TravelMode.DRIVING
            }).then(response => {
                directionsRenderer.setDirections(response);
            }).catch(err => console.error(err));
        }

        // 🏠 Try to get a human-readable address
        function getReadableAddress(latLng) {
            geocoder.geocode({
                location: latLng
            }, (results, status) => {
                if (status === "OK" && results[0]) {
                    // Try to get the best human-readable label
                    let readableName = "";
                    for (let result of results) {
                        if (result.types.includes("point_of_interest") || result.types.includes("establishment")) {
                            readableName = result.formatted_address.split(",")[0];
                            break;
                        }
                    }
                    const bestAddress = readableName || results[0].formatted_address;

                    const encodedAddress = encodeURIComponent(bestAddress);
                    const lat = latLng.lat();
                    const lng = latLng.lng();

                    const content = `
            <div style="max-width:200px;">
              <b>${bestAddress}</b><br>
              Lat: ${lat.toFixed(6)}<br>
              Lng: ${lng.toFixed(6)}<br>
              <button class="save-btn" onclick="saveLocation('${encodedAddress}', ${lat}, ${lng})">
                Save this location
              </button>
            </div>
          `;

                    infoWindow.setContent(content);
                    infoWindow.setPosition(latLng);
                    infoWindow.open(map);
                }
            });
        }

        // 💾 Save Button → Update URL
        function saveLocation(address, lat, lng) {
            const url = `map.php?address=${encodeURIComponent(address)}&lat=${lat}&lng=${lng}&marked_loc=1`;
            history.replaceState(null, '', url);

            document.getElementById("info").innerHTML = `
    ✅ Location marked and ready to save.<br>
    <b>Address:</b> ${address}<br>
    <b>Lat:</b> ${lat}<br>
    <b>Lng:</b> ${lng}
  `;

            fetch("../includes/save_location.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `address=${encodeURIComponent(address)}&lat=${lat}&lng=${lng}`,
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert("✅ " + data.message);
                    } else {
                        alert("❌ " + data.message);
                    }
                })
                .catch(err => {
                    console.error("Error saving location:", err);
                    alert("❌ Error saving location.");
                });
        }


        // 📥 Load saved locations from backend (via AJAX)
        function loadSavedLocations(map) {
            fetch("../includes/get_saved_locations.php")
                .then(res => res.json())
                .then(data => {
                    const legend = document.getElementById("legend");
                    legend.innerHTML = "<h3>📍 Saved Locations</h3>";

                    data.forEach(location => {
                        const {
                            loc_id,
                            address,
                            lat,
                            lng
                        } = location;
                        const position = {
                            lat: parseFloat(lat),
                            lng: parseFloat(lng)
                        };

                        // Use a custom marker (can replace with your own pin image)
                        const marker = new google.maps.Marker({
                            position,
                            map,
                            title: `ID: ${loc_id}\n${address}`,
                            icon: {
                                url: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png",
                            },
                        });

                        // Tooltip on click
                        const infowindow = new google.maps.InfoWindow({
                            content: `<div style="font-size:14px;">
                      <b>ID:</b> ${loc_id}<br>
                      <b>Address:</b> ${address}
                    </div>`,
                        });
                        marker.addListener("click", () => {
                            infowindow.open(map, marker);
                        });

                        // Add to legend
                        const legendItem = document.createElement("div");
                        legendItem.innerHTML = `<b>${loc_id}:</b> ${address}`;
                        legend.appendChild(legendItem);
                    });

                    map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);
                })
                .catch(err => console.error("Error loading saved locations:", err));
        }
    </script>

</body>

</html>