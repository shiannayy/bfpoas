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
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      font-family: Arial, sans-serif;
      z-index: 5;
    }
  </style>
</head>
<body>
<div id="info">Click anywhere inside Oas to show route and get coordinates</div>
  <div id="map"></div>

  <!-- ✅ Load Google Maps JS API -->
  <script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAVal_YrBtEwQTFU6ianCaic2uVc6P_Jgc&libraries=places,geometry&callback=initMap"
    async
    defer>
  </script>

 <script>
    let map, directionsService, directionsRenderer, geocoder, destinationMarker;

    // 🏢 Fire station origin
    const bfpOas = { lat: 13.2574615, lng: 123.4997089 };

    // 🗺️ Boundary of Oas
    const oasBounds = {
      north: 13.3100,
      south: 13.2000,
      west: 123.4300,
      east: 123.5400
    };

    function initMap() {
      directionsService = new google.maps.DirectionsService();
           directionsRenderer = new google.maps.DirectionsRenderer({
        map,
        suppressMarkers: false,
        polylineOptions: {
          strokeColor: "#FF0000",
          strokeWeight: 8,
        },
          
      });

      geocoder = new google.maps.Geocoder();

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

      // 📍 Fire Station Marker
      new google.maps.Marker({
        position: bfpOas,
        map,
        title: "BFP Oas Fire Station",
        icon: "https://maps.google.com/mapfiles/ms/icons/red-dot.png"
      });

      // If GET vars exist, auto-load route
      const params = new URLSearchParams(window.location.search);
      const lat = parseFloat(params.get("lat"));
      const lng = parseFloat(params.get("lng"));
      const address = params.get("address");

      if (lat && lng) {
        const destination = { lat, lng };
        showRoute(bfpOas, destination);
        document.getElementById("info").innerHTML = `
          <b>Loaded from GET:</b><br>
          Address: ${decodeURIComponent(address || "")}<br>
          Lat: ${lat}<br>
          Lng: ${lng}
        `;
      }

      // When map is clicked
      map.addListener("click", (e) => {
        const clickedLatLng = e.latLng;
        showRoute(bfpOas, clickedLatLng);
        getAddress(clickedLatLng);
      });
    }

    // 🚗 Draw route
    function showRoute(origin, destination) {
      if (destinationMarker) {
        destinationMarker.setMap(null);
      }

      destinationMarker = new google.maps.Marker({
        position: destination,
        map: map,
        title: "Destination",
        icon: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png"
      });

      directionsService.route({
        origin,
        destination,
        travelMode: google.maps.TravelMode.DRIVING
      }).then(response => {
        directionsRenderer.setDirections(response);
      }).catch(err => console.error(err));
    }

    // 🏠 Get address & update URL
    function getAddress(latLng) {
      geocoder.geocode({ location: latLng }, (results, status) => {
        if (status === "OK" && results[0]) {
          const address = encodeURIComponent(results[0].formatted_address);
          const lat = latLng.lat();
          const lng = latLng.lng();

          // Update info box
          document.getElementById("info").innerHTML = `
            <b>Address:</b> ${decodeURIComponent(address)}<br>
            <b>Latitude:</b> ${lat}<br>
            <b>Longitude:</b> ${lng}
          `;

          // Change URL to include GET parameters
          const url = `map.php?address=${address}&lat=${lat}&lng=${lng}`;
          history.replaceState(null, '', url);
        }
      });
    }
  </script>

</body>
</html>
