var map,
    google_restaurants = [],
    my_current_location,
    marker,
    currentMarkersArray = {},
    circle,
    infowindow,
    routesDisplay,
    default_radius = 1000,
    cebuLat = 10.31672,
    cebuLng = 123.89071,
    defaultPlace = {lat: cebuLat, lng: cebuLng},
    defaultTravelMode = 'WALKING';

/*
    Initialize map
*/
function initMap(request = null) {
    // Cebu
    var location = defaultPlace;

    // map
    map = new google.maps.Map(document.getElementById('map'), {
        center: location,
        zoom: 15
    });

    // get user location
    getMyCurrentLocation();

    if (!request) {
        request = {
            location: my_current_location,
            radius: default_radius,
            type: ['restaurant'],
        };
    };
    
    console.log(request);
    getAllRestaurants(request);

    // init directions renderer
    routesDisplay = new google.maps.DirectionsRenderer({
        map: map
    });
}

/*
    Get default current location
*/
function getMyCurrentLocation() {
    // this is Cebu capitol
    my_current_location = new google.maps.LatLng(cebuLat, cebuLng); 

    // mark: Cebu Capitol
    var my_marker = new google.maps.Marker({
        map: map,
        position: my_current_location,
    });

    // add hover listener on marker
    google.maps.event.addListener(my_marker, 'mouseover', function() {
        infowindow.setContent('<div><b>My location</b><br>Cebu Provincial Capitol</div>');
        infowindow.open(map, this);
    });
}

// get restaurants from google
function getAllRestaurants(request) {
    //init Place service - get all type restaurants
    service = new google.maps.places.PlacesService(map);
    service.nearbySearch(
        request
        , function callback(results, status) {
        if (status === google.maps.places.PlacesServiceStatus.OK) {
            google_restaurants = results;

            for (var i = 0; i < results.length; i++) {
                createMarker(results[i]);
            }

            // getPlaces();
        }
    });
}

/*
    Create marker: points requested types/name
*/
function createMarker(place) {
    // restaurant icons
    var icon = {
        url: place.icon,
        scaledSize: new google.maps.Size(25, 25),
    };

    var specialty   = '',    
        rating      = '',
        report      = '',
        opening     = '';

    // init instance for marker
    marker = new google.maps.Marker({
        icon: icon,
        map: map,
        position: place.geometry.location,
    });

    marker.id = place.id;
    marker.name = place.name;

    // push marker to array
    currentMarkersArray[place.id] = marker;

    // restaurants list
    listAllRestaurants(marker);

    // init an instance of info window
    infoWindow = new google.maps.InfoWindow();

    // set place.* attributes
    if (place.specialty) {
        specialty = 'Specialty : ' + place.specialty + '<br>';
    }

    if (place.rating) {
        rating = 'Rating : ' + place.rating + '<br>';
    }

    if (place.opening_hours) {
        if (place.opening_hours.open_now) {
            opening = '<b>Status: <span style="color: #00b200;">Open</span></b><br>';
        } else {
            opening = '<b>Status: <span style="color: #ad000b;">Closed</span></b><br>';
        }
    }
    //end

    // show infoWindow with on hover event
    google.maps.event.addListener(marker, 'mouseover', function() {
        console.log(place);
        infoWindow.setContent(
            '<div><b>' + place.name + '</b><br>' +
            place.vicinity + '<br><br>' + opening +
            'Restaurant type : ' + place.types + '<br>' + specialty + rating +
            '<a href="#" id="get_waze_' + place.id + '" class="get_destination_route">Get directions to this restaurant</a>' +
            report
        );
        infoWindow.open(map, this);
    });

    // on click get direction
    $(document).off('click', '#get_waze_' + place.id).on('click', '#get_waze_' + place.id, function() {
        setWaze(place.geometry.location);
    });
}

/*
    Get a list of Restaurants
*/
function listAllRestaurants(this_marker) {
    document.getElementById('restaurants_list').innerHTML += '<input class="sub-body-cont" onclick="toggleMarker(\'' + this_marker.id + '\')" type="checkbox"  checked /> ' +
        this_marker.name + '<br>';
}

/*
    Toggle Marker
*/
function toggleMarker(id) {
    var mark = currentMarkersArray[id];
    console.log(currentMarkersArray);
    if (!mark.getVisible()) {
        mark.setVisible(true);
    } else {
        mark.setVisible(false);
    }
}

/*
    Set routes/directions
*/
function setWaze(destination) {
    var request = {
        destination: destination,
        origin: my_current_location,
        travelMode: defaultTravelMode
    };

    // init directions Service - library
    var directionsService = new google.maps.DirectionsService();
    directionsService.route(request, function(response, status) {
        if (status == 'OK') {
            waze = $('#waze');
            waze.html(''); // reset routes details

            var routes = response.routes[0].legs[0].steps;
            console.log(routes)
            if (routes) {
                for (var i = 1; i <= routes.length; i++) {
                    document.getElementById('waze').innerHTML += '<div class="sub-body-cont"><b>(' + i + ')</b><br>' + routes[i - 1].instructions + '</div><br>';
                }
            } else {
                waze.html('No direction found.');
            }

            routesDisplay.setDirections(response);
        }
    });
}

// available restaurant types on Cebu
var available_types = ['cafe','restaurant','bar'];
// deprecated ,'food'
/*
    Allow user to select restaurant type
    if current value is not exist in array use :name to search the area
*/
$('#types').on('change', function() {
    $("#restaurants_list").html('');
    
    // console.log(jQuery.inArray(this.value,available_types).length)
    if(jQuery.inArray(this.value,available_types) == '-1'){
        request = {
            location: my_current_location,
            radius: default_radius,
            name: this.value,
        };
    }else{
        request = {
            location: my_current_location,
            radius: default_radius,
            type: this.value,
        };
    }
        
    initMap(request);
});