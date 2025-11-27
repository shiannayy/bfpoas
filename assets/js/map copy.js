let map, directionsService, directionsRenderer, autocomplete;

async function initMap() {
    const { Map } = await google.maps.importLibrary("maps");
    const { Places } = await google.maps.importLibrary("places");
    const { DirectionsService, DirectionsRenderer } = await google.maps.importLibrary("routes");

    // 📍 Center map on BFP Oas, Albay
    const bfpOas = { lat: 13.2553, lng: 123.4968 };

    map = new Map(document.getElementById("map"), {
        zoom: 15,
        center: bfpOas,
    });

    // Set up Directions API
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({ suppressMarkers: false });
    directionsRenderer.setMap(map);

    // Add a marker for BFP Oas
    new google.maps.Marker({
        position: bfpOas,
        map: map,
        title: "BFP Oas, Albay",
    });

    // Create Autocomplete for destination input
    const input = document.getElementById("autocomplete");
    autocomplete = new google.maps.places.Autocomplete(input, {
        componentRestrictions: { country: "ph" }, // 🇵🇭 limit to Philippines
        fields: ["geometry", "name"],
    });
    autocomplete.bindTo("bounds", map);

    // When user selects a place
    autocomplete.addListener("place_changed", () => {
        const place = autocomplete.getPlace();
        if (!place.geometry || !place.geometry.location) {
            showError("No details available for input: '" + place.name + "'");
            return;
        }

        // Get current location (origin)
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const origin = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    calculateRoute(origin, place.geometry.location);
                },
                () => showError("Unable to access your location.")
            );
        } else {
            showError("Geolocation not supported by this browser.");
        }
    });
}

function calculateRoute(origin, destination) {
    const request = {
        origin,
        destination,
        travelMode: google.maps.TravelMode.DRIVING,
    };

    directionsService.route(request, (result, status) => {
        if (status === "OK") {
            directionsRenderer.setDirections(result);
            const route = result.routes[0].legs[0];
            document.getElementById("route-info").innerHTML =
                `<b>From:</b> ${route.start_address}<br>
                 <b>To:</b> ${route.end_address}<br>
                 <b>Distance:</b> ${route.distance.text}<br>
                 <b>Duration:</b> ${route.duration.text}`;
        } else {
            showError("Directions request failed due to " + status);
        }
    });
}

function showError(message) {
    const errorDisplay = document.getElementById("error-display");
    errorDisplay.innerText = message;
    errorDisplay.style.display = "block";
}

// Initialize map
initMap();
