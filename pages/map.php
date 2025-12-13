<?php include_once "../includes/_init.php";

?>
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

    </style>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/color_pallette.css">
</head>

<body>
<div id="alerts" class="d-none" style="position: absolute;left: 45%;bottom: 10px;"></div>

    <div id="info" class="position-absolute top-0 start-0 m-2 z-5"></div>
    <div id="map"></div>
    <!-- Bootstrap 5 Collapse Legend -->
    <div class="position-absolute bottom-0 end-0 m-3 p-0" style="max-width:50%">

        <!-- Show Legend Button (appears when collapsed) -->
    

        <!-- Collapsible Legend -->
        <div class="collapse d-md-block p-0" id="legendCollapse">
            <div class="card shadow p-2 m-0 bg-white rounded" style="font-size: 13px; max-height: 50vh;">
                 <div class="card-header d-inline">
                     Scheduled for Inspection
                     <button class="btn p-1 btn-sm btn-outline-secondary float-end d-md-none m-0" type="button" data-bs-toggle="collapse" data-bs-target="#legendCollapse" aria-expanded="true" aria-controls="legendCollapse">
                        Hide
                    </button>
                 </div>
                <div class="card-body map-legend d-flex justify-content-between align-items-center mb-2 overflow-y-scroll">
                    <div class="map-legend" id="legend"></div>
                    <!-- Hide Legend Button (only for small screens) -->
                   
                </div>

            </div>
        </div>

    </div>



    <?php
    $path = '../index.php';
    if(isLoggedIn()){
        if( isClient() ){
            $path = '../client';
        }
        else if(isAdmin()){
            $path = '../admin';
        }
        else{
            $path = '../inspector';
        }
    }
   
    ?>

    <div style="position: absolute;left: 10px;bottom: 10px;z-index: 5;">
        <a href="<?php echo $path;?>" class="btn btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z" />
            </svg>
        </a>
        <button id="toggleOriginBtn" class="btn btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-crosshair" viewBox="0 0 16 16">
                <path d="M8.5.5a.5.5 0 0 0-1 0v.518A7 7 0 0 0 1.018 7.5H.5a.5.5 0 0 0 0 1h.518A7 7 0 0 0 7.5 14.982v.518a.5.5 0 0 0 1 0v-.518A7 7 0 0 0 14.982 8.5h.518a.5.5 0 0 0 0-1h-.518A7 7 0 0 0 8.5 1.018zm-6.48 7A6 6 0 0 1 7.5 2.02v.48a.5.5 0 0 0 1 0v-.48a6 6 0 0 1 5.48 5.48h-.48a.5.5 0 0 0 0 1h.48a6 6 0 0 1-5.48 5.48v-.48a.5.5 0 0 0-1 0v.48A6 6 0 0 1 2.02 8.5h.48a.5.5 0 0 0 0-1zM8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
            </svg>
        </button>
        <button class="btn btn-navy d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#legendCollapse" aria-expanded="false" aria-controls="legendCollapse">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
              <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2m0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2m0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14"/>
            </svg>
        </button>
    </div>


    <!-- ✅ Load Google Maps JS API -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        let API_KEY = null; // global variable for later use

        (async function loadGoogleMaps() {
            API_KEY = await getApiKey(); // Fetch key securely via AJAX
            if (!API_KEY) {
                console.error("Failed to load API key. Map cannot be initialized.");
                return;
            }

            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${API_KEY}&libraries=places,geometry&callback=initMap`;
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        })();



        let map, directionsService, directionsRenderer, geocoder, infoWindow, destinationMarker;
        let useMyLocationAsOrigin = false;
        let currentOrigin = null;
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

            // ✅ Initialize current origin as Fire Station by default
            currentOrigin = bfpOas;

            // ✅ Add toggle origin button functionality
            document.getElementById("toggleOriginBtn").addEventListener("click", () => {
                if (!useMyLocationAsOrigin) {
                    // Switch to user's current location
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(pos => {
                            currentOrigin = {
                                lat: pos.coords.latitude,
                                lng: pos.coords.longitude
                            };
                            useMyLocationAsOrigin = true;
                          document.getElementById("toggleOriginBtn").innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-buildings" viewBox="0 0 16 16">
                      <path d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V4.5a.5.5 0 0 1 .276-.447l8-4a.5.5 0 0 1 .487.022M6 8.694 1 10.36V15h5zM7 15h2v-1.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5V15h2V1.309l-7 3.5z"/>
                      <path d="M2 11h1v1H2zm2 0h1v1H4zm-2 2h1v1H2zm2 0h1v1H4zm4-4h1v1H8zm2 0h1v1h-1zm-2 2h1v1H8zm2 0h1v1h-1zm2-2h1v1h-1zm0 2h1v1h-1zM8 7h1v1H8zm2 0h1v1h-1zm2 0h1v1h-1zM8 5h1v1H8zm2 0h1v1h-1zm2 0h1v1h-1zm0-2h1v1h-1z"/>
                    </svg>`;
                            showAlert("✅ Origin set to your current location.");
                        }, () => showAlert("❌ Could not get your location."));
                    } else {
                        alert("Geolocation not supported.");
                    }
                } else {
                    // Switch back to Fire Station
                    currentOrigin = bfpOas;
                    useMyLocationAsOrigin = false;
                    document.getElementById("toggleOriginBtn").innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-crosshair" viewBox="0 0 16 16">
                                                                                <path d="M8.5.5a.5.5 0 0 0-1 0v.518A7 7 0 0 0 1.018 7.5H.5a.5.5 0 0 0 0 1h.518A7 7 0 0 0 7.5 14.982v.518a.5.5 0 0 0 1 0v-.518A7 7 0 0 0 14.982 8.5h.518a.5.5 0 0 0 0-1h-.518A7 7 0 0 0 8.5 1.018zm-6.48 7A6 6 0 0 1 7.5 2.02v.48a.5.5 0 0 0 1 0v-.48a6 6 0 0 1 5.48 5.48h-.48a.5.5 0 0 0 0 1h.48a6 6 0 0 1-5.48 5.48v-.48a.5.5 0 0 0-1 0v.48A6 6 0 0 1 2.02 8.5h.48a.5.5 0 0 0 0-1zM8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                                            </svg>`;
                    showAlert("✅ Origin set to Oas Fire Station.");
                }
            });

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
                      <b>Route</b><br>
                      Address: <b>${decodeURIComponent(address || "")}</b><br>
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
            // Always clear previous route
            directionsRenderer.set('directions', null);

            // Determine actual origin (based on toggle)
            const startPoint = currentOrigin || origin;

            if (destinationMarker) destinationMarker.setMap(null);
            destinationMarker = new google.maps.Marker({
                position: destination,
                map: map,
                title: "Destination",
                icon: "https://maps.google.com/mapfiles/ms/icons/green-dot.png"
            });

            directionsService.route({
                origin: startPoint,
                destination,
                travelMode: google.maps.TravelMode.DRIVING
            }).then(response => {
                directionsRenderer.setDirections(response);
            }).catch(err => console.error("Route error:", err));
        }



        // 🏠 Try to get a human-readable address
        function getReadableAddress(latLng) {
            geocoder.geocode({
                location: latLng
            }, (results, status) => {
                if (status === "OK" && results[0]) {
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

                    // Street View Static Image
                    const streetViewImg = `
                <img  class="card-img-top img-fluid"
                    src="https://maps.googleapis.com/maps/api/streetview?size=250x150&location=${lat},${lng}&fov=90&pitch=10&key=${API_KEY}"
                    alt="Street view preview"
                    style="width:100%;margin-top:6px;border-radius:6px;"
                    onerror="this.style.display='none';"
                />
            `;

                    const content = `
                <div class="card mb-0" style="max-width:220px;font-family:Roboto,Arial,sans-serif;font-size:13px;">
                    ${streetViewImg}
                    <div class="card-body">
                        <b>${bestAddress}</b><br>
                        Lat: ${lat.toFixed(6)}<br>
                        Lng: ${lng.toFixed(6)}<br>
                    </div>
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
                        showAlert(`✅ ${data.message}`, 'success');
                        loadSavedLocations(map);
                    } else {
                        showAlert(`❌ ${data.message}`, 'danger');
                    }
                })
                .catch(err => {
                    console.error("Error saving location:", err);
                    showAlert("❌ Error saving location.", 'danger');
                });
        }



        // 📥 Load saved locations from backend (via AJAX)
        // Helper: choose white or black text depending on background luminance
        function getTextColorForBg(hex) {
            hex = hex.replace('#', '');
            const r = parseInt(hex.substring(0, 2), 16);
            const g = parseInt(hex.substring(2, 4), 16);
            const b = parseInt(hex.substring(4, 6), 16);
            // luminance formula
            const luminance = 0.299 * r + 0.587 * g + 0.114 * b;
            return luminance > 186 ? '#000000' : '#FFFFFF';
        }

        // Create an SVG pin (pin shape + circle top + label) and return an icon object for Google Maps
        function getNumberedPinIcon(id, colorHex = '2196F3') {
            // Ensure color doesn’t have #
            const color = colorHex.replace('#', '');
            const textColor = '#000000'; // black text for better visibility

            // Convert ID to string (limit to 3 characters for readability)
            const label = String(id).substring(0, 3);

            // SVG pin — white circle at top, text centered inside
            const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="70" viewBox="0 0 50 70">
                      <!-- Pin shape -->
                      <path d="M25 0
                               C14 0 5 9 5 20
                               C5 33 22 57 25 70
                               C28 57 45 33 45 20
                               C45 9 36 0 25 0 Z"
                            fill="#${color}" />
                      <!-- White circle for label -->
                      <circle cx="25" cy="20" r="13" fill="#FFFFFF" stroke="#${color}" stroke-width="2"/>
                      <!-- Text (ID number) -->
                      <text x="25" y="25"
                            text-anchor="middle"
                            font-family="Arial, Helvetica, sans-serif"
                            font-size="13"
                            font-weight="bold"
                            fill="${textColor}">
                        ${label}
                      </text>
                    </svg>
  `.trim();

            // Encode SVG to base64 or URL encode
            const svgUrl = "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg);

            return {
                url: svgUrl,
                scaledSize: new google.maps.Size(40, 56), // adjust size on map
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(20, 56) // bottom center of pin
            };
        }


        // Replace your loadSavedLocations implementation with this:
        function loadSavedLocations(map) {
            fetch("../includes/get_saved_locations.php")
                .then(res => res.json())
                .then(data => {
                    const legend = document.getElementById("legend");
                    if (!legend) return; // just skip if not found
                    
                    const infoWindow = new google.maps.InfoWindow({
                        content: "HTML", // The HTML/text content displayed inside the info window
                        disableAutoPan: false, // If true, map won't pan automatically when opened
                        maxWidth: 200, // Maximum width of the info window in pixels
                        pixelOffset: new google.maps.Size(0, 0), // Offset from the marker in pixels (x, y)
                        position: {
                            lat: 0,
                            lng: 0
                        }, // LatLng position (used if you want to open without marker)
                        zIndex: 0, // Stack order of the window (higher = on top)
                        ariaLabel: "", // Optional accessibility label
                    })

                    data.forEach(loc => {
                        const locId = loc.loc_id;
                        const schedId = loc.sched_id;
                        const addr = loc.address || "No address";
                        const lat = parseFloat(loc.lat);
                        const lng = parseFloat(loc.lng);
                        const pos = {
                            lat,
                            lng
                        };
                        const pinColor = "2196F3";

                        // Create numbered marker
                        const marker = new google.maps.Marker({
                            position: pos,
                            map: map,
                            title: `${schedId}\n${addr}`,
                            icon: getNumberedPinIcon(loc.loc_id)
                        });

                        // Info window content
                        const content = `
                          <div style="min-width:200px;font-family: Roboto, Arial, sans-serif; font-size:13px;">
                            <strong>ID:</strong> ${schedId}<br>
                            <strong>Address:</strong> ${addr}<br>
                            ${loc.date_added ? `<small>Added: ${loc.date_added}</small>` : ""}
                          </div>`;

                        // Click on marker: open info + trace route
                        marker.addListener("click", () => {
                            const streetViewImg = `
                          <img 
                            src="https://maps.googleapis.com/maps/api/streetview?size=250x150&location=${lat},${lng}&fov=90&pitch=10&key=${API_KEY}" 
                            alt="Street view preview" 
                            style="width:100%;margin-top:6px;border-radius:6px;"
                            onerror="this.style.display='none';"
                          />
                        `;

                            const content = `
                          <div style="min-width:220px;font-family:Roboto,Arial,sans-serif;font-size:13px;">
                            <strong>ID:</strong> ${schedId}<br>
                            <strong>Address:</strong> ${addr}<br>
                            ${loc.date_added ? `<small>Added: ${loc.date_added}</small><br>` : ""}
                            ${streetViewImg}
                          </div>
                        `;

                            infoWindow.setContent(content);
                            infoWindow.open(map, marker);
                            showRoute(currentOrigin, pos);
                        });

                        // Legend entry
                        const item = document.createElement("div");
                        item.style.display = "flex";
                        item.style.alignItems = "center";
                        item.style.justifyContent = "space-between";
                        item.style.padding = "6px 4px";
                        item.style.borderBottom = "1px solid #f0f0f0";

                        // Info clickable area
                        const infoSpan = document.createElement("span");
                        infoSpan.style.flex = "1";
                        infoSpan.style.cursor = "pointer";
                        const today = new Date().toISOString().split("T")[0];
                        const isToday = loc.sched_date === today;
                        infoSpan.innerHTML = `<span style="display:inline-block;width:28px;text-align:center;margin-right:8px;">
                            <img src="${getNumberedPinIcon(locId, pinColor).url}" 
                                 style="width:20px;height:auto;vertical-align:middle" />
                          </span>

                          <div class="container-fluid ${isToday ? 'bg-danger text-white p-1 rounded' : ''}">
                              <strong>${loc.sched_date} ${loc.sched_time || '00:00:00'}</strong> 
                              <br> 
                              ${addr}
                          </div>
                        `;
                
                        infoSpan.addEventListener("click", () => {
                            map.panTo(pos);
                            map.setZoom(Math.max(map.getZoom(), 16));
                            google.maps.event.trigger(marker, "click");
                        });

//                        // Delete button
//                        const delBtn = document.createElement("button");
//                        delBtn.textContent = "🗑️";
//                        delBtn.style.border = "none";
//                        delBtn.style.cursor = "pointer";
//                        delBtn.style.fontSize = "16px";
//                        delBtn.title = "Delete this location";
//
//                        delBtn.addEventListener("click", (e) => {
//                            e.stopPropagation();
//                            if (confirm(`Delete location ID ${locId}?`)) {
//                                fetch("../includes/delete_saved_location.php", {
//                                        method: "POST",
//                                        headers: {
//                                            "Content-Type": "application/x-www-form-urlencoded"
//                                        },
//                                        body: `loc_id=${encodeURIComponent(locId)}`
//                                    })
//                                    .then(res => res.text())
//                                    .then(resp => {
//                                        console.log(resp);
//                                        showAlert("✅ Location deleted successfully!", "success");
//                                        loadSavedLocations(map); // reload list
//                                    })
//                                    .catch(err => {
//                                        console.error("Error deleting location:", err);
//                                        showAlert("❌ Error deleting location.", "error");
//                                    });
//                            }
//                        });

                        item.appendChild(infoSpan);
                        //item.appendChild(delBtn);
                        legend.appendChild(item);
                    });

                    // Add legend to map

                })
                .catch(err => {
                    console.error("Error loading saved locations:", err);
                    showAlert("⚠️ Failed to load saved locations.", "error");
                });
        }


//        // 📢 Custom alert handler (replaces annoying alert() popups)
//        function showAlert(message, type = "info", duration = 3000) {
//            const alerts = document.getElementById("alerts");
//            if (!alerts) return console.warn("⚠️ Missing #alerts element in HTML.");
//
//            // Choose Bootstrap-style alert color
//            let alertClass = "alert-info";
//            if (type === "success") alertClass = "alert-success";
//            if (type === "error") alertClass = "alert-danger";
//            if (type === "warning") alertClass = "alert-warning";
//
//            // Show the alert
//            alerts.innerHTML = `<div class="alert ${alertClass} mb-0">${message}</div>`;
//            alerts.classList.remove("d-none");
//
//            // Auto-hide after duration (default: 3s)
//            clearTimeout(alerts.hideTimeout);
//            alerts.hideTimeout = setTimeout(() => {
//                alerts.classList.add("d-none");
//                alerts.innerHTML = "";
//            }, duration);
//
//        }
    </script>

</body>

</html>