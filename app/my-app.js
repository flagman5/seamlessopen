// Initialize app
var myApp = new Framework7();        

// If we need to use custom DOM library, let's save it to $$ variable:
var $$ = Dom7;

// Add view
var mainView = myApp.addView('.view-main', {
    // Because we want to use dynamic navbar, we need to enable it for this view:
    dynamicNavbar: true
});

//global?
var bgGeo;
var triggedFences = [];

// Handle Cordova Device Ready Event
$$(document).on('deviceready', function() {
    console.log("Device is ready!");
	//document.getElementById("getPosition").addEventListener("click", getPosition);
	//document.getElementById("watchPosition").addEventListener("click", watchPosition);
	var deviceID = device.uuid;
	
	//get initial location
	var currentPosition = getInitialLocation();

	//get relevant geofences for this location
	var geofences;
	$$.ajax({
		type: "POST",
		data:  { geoloc: currentPosition},
		url: 'http://localhost:8082/seamlessopen/getGeoFences.php',
		beforeSend: function() {
			myApp.showPreloader('Loading nearby listings...')
		}, 
		success: function(data) {
			myApp.hidePreloader();
			geofences = data;
		},
		error: function(xhr, textStatus, errorThrown){
			myApp.alert('Unable to load nearby listings, check your data connection', 'Error');
		}
	});
	
	//here is the gooey part
	bgGeo = window.BackgroundGeolocation;
	
	//remove any existing geofences
	bgGeo.removeGeofences(function() {
	  console.log("Successfully removed alll geofences");
	}, function(error) {
	  console.warn("Failed to remove geofence", error);
	});
	//add the geofences
	bgGeo.addGeofences(geofences);
	
	//config first time
	//need to configure the plugin for geofence only mode
	bgGeo.configure({
		// Geolocation config
		desiredAccuracy: 1000,
		distanceFilter: 10,
		// Activity Recognition config
		activityRecognitionInterval: 10000,
		stopTimeout: 5,
		// Application config
		debug: true,  // <-- Debug sounds & notifications.
		stopOnTerminate: false,
		startOnBoot: true,
		
		//geofence
		geofenceInitialTriggerEntry: false,
		}, function(state) {
			// This callback is executed when the plugin is ready to use.
			console.log("BackgroundGeolocation ready: ", state);
	});
	
	//redirect to home page
	mainView.loadPage("home.html");
	
});

myApp.onPageInit('home', function (page) {
	
	checkLogin();
	
	$$('#start_tracking').on('click', function() {
	
		if($$(this).text() == 'Start house hunting') {
			myApp.alert('Good luck! Remember to stop the app from tracking when you are done to avoid battery drain', 'Custom Title!');
			$$(this).text('Stop house hunting');
			$$(this).removeClass('color-green').addClass('color-red');
			
			//start geofence only tracking mode
			bgGeo.startGeofences(function(state) {
			    console.log('- Geofence-only monitoring started', state.trackingMode);
		    });
		}
		else {
			$$(this).text('Start house hunting');
			$$(this).removeClass('color-red').addClass('color-green');
			bgGeo.stop();
		}
	});
	
	//now listen to geofence crossings
	// Listen to geofences which is only set on dwell trigger with a long loiterdelay, so no false alarms
	
	bgGeo.on('geofence', function(params, taskId) {
		
		var geoFenceLocation    = params.location;
		var geoFenceidentifier  = params.identifier;
		var geoFenceAction 	= params.action;

		//add to list of trigged fences
		triggedFences.push(params);
		
		//start aggressive tracking
		if(checkAggressive() == 0) {
			//stop the geofenceonly mode?
			bgGeo.stop();

			//config location tracking with highest accuracy
			bgGeo.setConfig({
				desiredAccuracy: 0,
				distanceFilter: 5,
				activityRecognitionInterval: 0
			},function(){
			   console.log("- setConfig success");
			}, function(){
			   console.warn("- Failed to setConfig");
			});
			
			//turn on plugin normally
			bgGeo.start();
		}
		
		bgGeo.finish(taskId);
	});
	
	//here listen to location events, this function should only be used during aggressive tracking
	bgGeo.on('location', function(location, taskId) {
		
		if(checkAggressive() == 1) {
			var currentLocation = '';
			var distanceToListing = '';
			
			currentLocation = location;
			var index;
			for(index=0;index < triggedFences.length; index++) {
				var geoFence = triggedFences[index];
				distanceToListing = haversineDistance([geoFence.extras.center.latitude, geoFence.extras.center.longitude], [currentLocation.coords.latitude, currentLocation.coords.longitude], false);
				if(distanceToListing < 10) {
					//this is good enough, set it as the target and stop aggressive tracking, empty all trigged fences
					recordVisitAndStopAggressive(geoFence.identifier, deviceID);
					break;
				}
			}
		}
		bgGeo.finish(taskId);
	   }, function(errorCode) {
		console.log('An location error occurred: ' + errorCode);
	});
})

myApp.onPageInit('setup', function (page) {
    // Do something here for "about" page
	$$('#back_link').on('click', function() {
		//check if all fields have input
		if(checkInput() == 1) {
			myApp.alert("You must fill out all fields");
		}
		if(checkInput() == 2) {
			myApp.alert("You must put a valid email");
		}
		if(checkInput() == 3) {
			myApp.alert("You must put a valid zipcode");
		}
		if(checkInput() == 4) {
			myApp.alert("You must put a valid phone number");
		}
		
	});
});

//user history
myApp.onPageInit('history', function (page) {
   //make ajax call to populate initial set
   //var uuid = device.uuid;
   $$.ajax({
		type: "POST",
		url: 'http://localhost:8082/seamlessopen/getHistory.php',
		beforeSend: function() {
			myApp.showProgressbar();
		}, 
		success: function(data) {
			myApp.hideProgressbar();
			$$(".list-block").html(data);
		},
		error: function(xhr, textStatus, errorThrown){
			myApp.alert('Unable to load history, check your data connection', 'Error');
		}
	});
	
	$$(document).on('click', '.actionSheet', function () {
		var address = $$(this).children().children().html();
		var email_on_file = $$(this).children().children().data('agentEmail');
		var button_text = '';
		if(email_on_file == null) { 
			button_text = 'We were unable to get the realtor email address for this listing';
		}
		else {
			button_text = 'Your info was emailed to '+ email_on_file;
		}
		var buttons = [
			{
				
				text: button_text,
				label: true
				
			},
			{
				text: 'E-Mail your info to another address',
				onClick: function() {
					$$.ajax({
						type: "POST",
						data: { listing: address},
						url: 'http://localhost:8082/seamlessopen/emailAgent.php',
						success: function(data) {
							var subject = 'Hello, someone just signed in to your open house';
							window.location.href = 'mailto:?subject=' + subject + '&body=' + data;
						},
						error: function(xhr, textStatus, errorThrown){
							myApp.alert('Unable to get listing information, check your data connection', 'Error');
						}
					});
				}
			},
			{
				text: 'Cancel',
				color: 'red'
			},
		];
		myApp.actions(buttons);
	});
	
});


// Now we need to run the code that will be executed only for About page.
/*
// Option 1. Using page callback for page (for "about" page in this case) (recommended way):
myApp.onPageInit('about', function (page) {
    // Do something here for "about" page
	
})

// Option 2. Using one 'pageInit' event handler for all pages:
$$(document).on('pageInit', function (e) {
    // Get page data from event data
    var page = e.detail.page;
	
    if (page.name === 'about') {
        // Following code will be executed for page with data-page attribute equal to "about"
        myApp.alert('Here comes About page');
    }
})
*/
/*
// Option 2. Using live 'pageInit' event handlers for each page
$$(document).on('pageInit', '.page[data-page="about"]', function (e) {
    // Following code will be executed for page with data-page attribute equal to "about"
    myApp.alert('Here comes About page');
})
*/
function recordVisitAndStopAggressive(geoFenceIdentifier, deviceID) {
	//empty/reset the trigged fences array
	triggedFences = [];
	
	//send info to server
	$$.ajax({
		type: "POST",
		data: { visited: geoFenceIdentifier, deviceUUID: deviceID},
		url: 'http://localhost:8082/seamlessopen/recordVisit.php',
		success: function(data) {			
			//stop the service
			bgGeo.stop();
			
			//resume geofence tracking only, ideally pause would be best..
			bgGeo.setConfig({
				desiredAccuracy: 1000,
				distanceFilter: 10,
				activityRecognitionInterval: 10000,
				geofenceInitialTriggerEntry: false
			},function(){
				console.log("- setConfig success");
			}, function(){
				console.warn("- Failed to setConfig");
			});
			
			//start geofence only tracking mode
			bgGeo.startGeofences(function(state) {
				console.log('- Geofence-only monitoring started', state.trackingMode);
			});
			
		},
		error: function(xhr, textStatus, errorThrown){
			console.log("error in recording visit");
		}
	});

	return;
	
	
}
function checkAggressive() {

	 bgGeo.getState(function(state) {
		var currentAccuracy = state.desiredAccuracy;
		if(currentAccuracy == 0) {
			// already in aggressive state
			return 1;
		}
     });
	 
	 return 0;
		   
}

function haversineDistance(coords1, coords2, isMiles) {
  function toRad(x) {
    return x * Math.PI / 180;
  }

  var lon1 = coords1[0];
  var lat1 = coords1[1];

  var lon2 = coords2[0];
  var lat2 = coords2[1];

  var R = 6371; // km

  var x1 = lat2 - lat1;
  var dLat = toRad(x1);
  var x2 = lon2 - lon1;
  var dLon = toRad(x2)
  var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
    Math.sin(dLon / 2) * Math.sin(dLon / 2);
  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  var d = R * c;

  if(isMiles) d /= 1.60934;

  return d;
}

function getInitialLocation() {

	navigator.geolocation.getCurrentPosition((function(_this) {
	  return function(position) {
		// Do something here
		var currentPosition = 'Latitude=' + position.coords.latitude + ';Longitude=' + position.coords.longitude;
		return currentPosition;
	};
	})(this), function(error) {
	  var errorButton, errorMsg, errorTitle;
	  errorTitle = "Location Services";
	  errorButton = "Ok";
	  if (error.code === 1) {
		myApp.alert('The app needs access to your location. Please turn on Location Services in your device settings.', 'Error');
	  }
	  if (error.code === 2) {
		myApp.alert('This device is unable to retrieve a position. Make sure you are connected to a network', 'Error');
	  }
	  if (error.code === 3) {
		myApp.alert('This device is unable to retrieve a position. Make sure you have Location Services enabled', 'Error');
	  }
	  if (error.code === 1 || error.code === 2 || error.code === 3) {
		//throw ''; //this stops everything...
	  }
	}, {
	  enableHighAccuracy: false,
	  maximumAge: 20000,
	  timeout: 10000
	});

}
function validateEmail(email) {
  var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}
function validatePhone(phone) {
  var regex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im;
  return regex.test(phone);
}

function checkInput() {
	var storedData = myApp.formGetData('my-form');
	if(storedData) {
		for (var key in storedData) {
			if(empty(storedData[key])) {
				return 1;
			}
			else if(key == 'email' && !validateEmail(storedData[key])) {
				return 2;
			}
			else if(key == 'zipcode' && (storedData[key].toString().length != 5)) {
				return 3;
			}
			else if(key == 'phone' && !validatePhone(storedData[key])) {
				return 4;
			}
		}
		
		return 5;
	}
	else {
		return false;
	}
	
	
}
function empty(str)
{
    if (typeof str == 'undefined' || !str || str.length === 0 || str === "" || !/[^\s]/.test(str) || /^\s*$/.test(str) || str.replace(/\s/g,"") === "")
    {
        return true;
    }
    else
    {
        return false;
    }
}
function checkLogin() {
	var storedData = myApp.formGetData('my-form');
	
	if(checkInput() == 5) {
		//alert(JSON.stringify(storedData));
		$$('#logged_in').show();
		$$('#setup_link').hide();
		$$('#edit_profile').show();
		
	  }
	  else {
		//alert('There is no stored data for this form yet. Try to change any field')
		$$('#logged_in').hide();
		$$('#setup_link').show();
		$$('#edit_profile').hide();
	  }
}
/*
function getPosition() {

   var options = {
      enableHighAccuracy: true,
      maximumAge: 3000,
	  timeout: 5000
   }
	
   var watchID = navigator.geolocation.getCurrentPosition(onSuccess, onError, options);

   function onSuccess(position) {

      alert('Latitude: '          + position.coords.latitude          + '\n' +
         'Longitude: '         + position.coords.longitude         + '\n' +
         'Altitude: '          + position.coords.altitude          + '\n' +
         'Accuracy: '          + position.coords.accuracy          + '\n' +
         'Altitude Accuracy: ' + position.coords.altitudeAccuracy  + '\n' +
         'Heading: '           + position.coords.heading           + '\n' +
         'Speed: '             + position.coords.speed             + '\n' +
         'Timestamp: '         + position.timestamp                + '\n');
   };

   function onError(error) {
      alert('code: '    + error.code    + '\n' + 'message: ' + error.message + '\n');
   }
}
function watchPosition() {

   var options = {
      maximumAge: 3000,
      timeout: 5000,
      enableHighAccuracy: true,
   }

   var watchID = navigator.geolocation.watchPosition(onSuccess, onError, options);

   function onSuccess(position) {

      alert('Latitude: '          + position.coords.latitude          + '\n' +
         'Longitude: '         + position.coords.longitude         + '\n' +
         'Altitude: '          + position.coords.altitude          + '\n' +
         'Accuracy: '          + position.coords.accuracy          + '\n' +
         'Altitude Accuracy: ' + position.coords.altitudeAccuracy  + '\n' +
         'Heading: '           + position.coords.heading           + '\n' +
         'Speed: '             + position.coords.speed             + '\n' +
         'Timestamp: '         + position.timestamp                + '\n');
   };

   function onError(error) {
      alert('code: '    + error.code    + '\n' +'message: ' + error.message + '\n');
   }

}*/
