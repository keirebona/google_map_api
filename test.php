<!DOCTYPE html>
<html>
  <head>
    <title>Place Autocomplete</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      .controls {
        margin-top: 10px;
        border: 1px solid transparent;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        height: 32px;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
      }
    
      #for_use_default_place{
        top: 10%;
      }
      #use_default_place {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 50px;
        height: 25px;
        top: 10%;
      }

      #origin-input,
      #destination-input {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 200px;
      }

      #origin-input:focus,
      #destination-input:focus {
        border-color: #4d90fe;
      }

      #mode-selector {
        color: #fff;
        background-color: #4d90fe;
        margin-left: 12px;
        padding: 5px 11px 0px 11px;
      }

      #mode-selector label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
      }
  
      #pac-input{
        width:50%;
      }

      #floating-panel {
        position: absolute;
        top: 20%;
        z-index: 5;
        background-color: #fff;
        padding: 5px;
        border: 1px solid #999;
        text-align: left;
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }
    </style>
  </head>
  <body>
    <div id="search_inputs">
      <span id="for_use_default_place" class="controls" >
        <label  for="#use_default_place">Use Default Place
        </label>
        <input id="use_default_place" type="checkbox" > 
      </span>
          
      <input id="origin-input" class="controls" type="text"
          placeholder="Enter an origin location">

      <input id="destination-input" class="controls" type="text"
          placeholder="Enter a destination location">
    </div>

    <div id="mode-selector" class="controls">
      <input type="radio" name="type" id="changemode-walking" checked="checked">
      <label for="changemode-walking">Walking</label>

      <input type="radio" name="type" id="changemode-transit">
      <label for="changemode-transit">Transit</label>

      <input type="radio" name="type" id="changemode-driving">
      <label for="changemode-driving">Driving</label>
    </div>
    
    <div id="floating-panel" >
      <b>Restaurants:</b>
      <div>
        <label for="#pizza">Pizza</label><input id="pizza" type="radio" name="restaurants">
        <label for="#cafe">Cafe</label><input id="cafe" type="radio" name="restaurants">
        <label for="#bar">Bar</label><input id="bar" type="radio" name="restaurants">
      </div>
    </div>

    <div id="map"></div>

    <script>
      
      
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCoi-pmZeN1lmVnBspppsLjF7_VbFTmr84&libraries=places&callback=initMap"
        async defer></script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  </body>
</html>
<script>
  // This example requires the Places library. Include the libraries=places
      // parameter when you first load the API. For example:
      // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

      var cebuLat = 10.31672;
      var cebuLng = 123.89071;
      var infoWindow;
      var prev_infowindow =false;
      var myPlace = {lat: cebuLat, lng: cebuLng};
      var request;
      var directionsService;
      var directionsDisplay;
      var defaultPlaceId = "ChIJ2TImteCbqTMRtRck6dNcgEM";
      var map;
      function initMap(request = null) {
        map = new google.maps.Map(document.getElementById('map'), {
          // mapTypeControl: false,
          center: myPlace,
          zoom: 15
        });

        var service = new google.maps.places.PlacesService(map);
        new AutocompleteDirectionsHandler(map);

        if(!request){
          request = {
            location : myPlace,
            radius : 5000,
            type : [ 'restaurant' ]
          };
        }

        // console.log(request);
        
        // //initialize pointing of restaurants
        service.nearbySearch(request, callback);

      }

      function callback(results, status) {
          console.log(results)
          //displaying of array of datas
          if (status === google.maps.places.PlacesServiceStatus.OK) {
              for (var i = 0; i < results.length; i++) {
                  createMarker(results[i]);
              }
          }
      }

      function createMarker(place) {
          var placeLoc = place.geometry.location;
          var marker = new google.maps.Marker({
              map : map,
              position : place.geometry.location
          });

          var infoWindow = new google.maps.InfoWindow({ map: map });
          google.maps.event.addListener(marker, 'click', function() {
              if(prev_infowindow){
                prev_infowindow.close();
              }
              if(place.vicinity){
                var place_localtion = place.vicinity;
              }else{
                place_localtion="";
              }
              console.log(place);
              var window_content = "<div> <p> Name: "+place.name+"</p><p>Address:"+place_localtion+"</p><p> Ratings:"+place.rating+"</p></div>";

              prev_infowindow = infoWindow;
              infoWindow.setContent(window_content);
              infoWindow.open(map, marker);
          });
      }

       /**
        * @constructor
       */
      function AutocompleteDirectionsHandler(map) {
        this.map = map;
        this.originPlaceId = null;
        this.destinationPlaceId = null;
        this.travelMode = 'WALKING';
        var originInput = document.getElementById('origin-input');
        var destinationInput = document.getElementById('destination-input');
        var modeSelector = document.getElementById('mode-selector');
        this.directionsService = new google.maps.DirectionsService;
        this.directionsDisplay = new google.maps.DirectionsRenderer;
        this.directionsDisplay.setMap(map);

        var originAutocomplete = new google.maps.places.Autocomplete(
            originInput, {placeIdOnly: true});
        var destinationAutocomplete = new google.maps.places.Autocomplete(
            destinationInput, {placeIdOnly: true});

        this.setupClickListener('changemode-walking', 'WALKING');
        this.setupClickListener('changemode-transit', 'TRANSIT');
        this.setupClickListener('changemode-driving', 'DRIVING');
        this.setupClickListener('pizza', 'not_travel_mode');
        this.setupClickListener('cafe', 'not_travel_mode');
        this.setupClickListener('bar', 'not_travel_mode');

        this.setupPlaceChangedListener(originAutocomplete, 'ORIG');
        this.setupPlaceChangedListener(destinationAutocomplete, 'DEST');

        // this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(originInput);
        // this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(destinationInput);
        // this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(modeSelector);
      }

      // Sets a listener on a radio button to change the filter type on Places
      // Autocomplete.
      AutocompleteDirectionsHandler.prototype.setupClickListener = function(id, mode) {
        var radioButton = document.getElementById(id);
        var me = this;

        if(!radioButton){
            me.route();
        }else{
          radioButton.addEventListener('click', function() {
            if (mode != 'not_travel_mode') {
              me.travelMode = mode;
            };
            me.route();
          });
        }
      };

      AutocompleteDirectionsHandler.prototype.setupPlaceChangedListener = function(autocomplete, mode) {
        var me = this;
        autocomplete.bindTo('bounds', this.map);
        autocomplete.addListener('place_changed', function() {
          var place = autocomplete.getPlace();
          if (!place.place_id) {
            window.alert("Please select an option from the dropdown list.");
            return;
          }
          if (mode === 'ORIG') {
            me.originPlaceId = place.place_id;
          } else {
            me.destinationPlaceId = place.place_id;
          }
          me.route();
        });
      };

      AutocompleteDirectionsHandler.prototype.route = function() {
        if (!this.originPlaceId || !this.destinationPlaceId) {
          return;
        }
        var me = this;

        if ($('input[id=use_default_place]:checked').length) {
          this.originPlaceId = defaultPlaceId
        };
        
        this.directionsService.route({
          origin: {'placeId': this.originPlaceId},
          destination: {'placeId': this.destinationPlaceId},
          travelMode: this.travelMode
        }, function(response, status) {
          if (status === 'OK') {
            me.directionsDisplay.setDirections(response);
          } else {
            window.alert('Directions request failed due to ' + status);
          }
        });
      };

  $(":radio").on("change", function() {
          //============= Note ============
          //base on nearby search docs: name/type value can't be multiple
          //============= end =============
          
          //add name in array if not exist and if the checkbox is ticked
          // if ( jQuery.inArray(this.id,names) == "-1" && this.checked) {
          //   names.push(this.id);
          //   console.log(122)
          // };

          // //remove name in array if exist and if checkbox is not ticked
          // if (jQuery.inArray(this.id,names) && !this.checked) {
          //   names = jQuery.grep(names, function(value) {
          //     return value != this.id;
          //   });
          // };

          if(this.checked){
              request = {
              location : myPlace,
              radius : 500,
              // type : [ 'food','restaurants','cafe' ],
              name : this.id,
              openNow: true
            };
            initMap(request);
          }
      });
</script>