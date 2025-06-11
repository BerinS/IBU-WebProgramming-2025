(function() {
	'use strict';

	var tinyslider = function() {
		var el = document.querySelectorAll('.testimonial-slider');

		if (el.length > 0) {
			var slider = tns({
				container: '.testimonial-slider',
				items: 1,
				axis: "horizontal",
				controlsContainer: "#testimonial-nav",
				swipeAngle: false,
				speed: 700,
				nav: true,
				controls: true,
				autoplay: true,
				autoplayHoverPause: true,
				autoplayTimeout: 3500,
				autoplayButtonOutput: false
			});
		}
	};
	tinyslider();

	var sitePlusMinus = function() {
		var value,
    		quantity = document.getElementsByClassName('quantity-container');

		function createBindings(quantityContainer) {
	      var quantityAmount = quantityContainer.getElementsByClassName('quantity-amount')[0];
	      var increase = quantityContainer.getElementsByClassName('increase')[0];
	      var decrease = quantityContainer.getElementsByClassName('decrease')[0];
	      increase.addEventListener('click', function (e) { increaseValue(e, quantityAmount); });
	      decrease.addEventListener('click', function (e) { decreaseValue(e, quantityAmount); });
	    }

	    function init() {
	        for (var i = 0; i < quantity.length; i++ ) {
						createBindings(quantity[i]);
	        }
	    };

	    function increaseValue(event, quantityAmount) {
	        value = parseInt(quantityAmount.value, 10);
	        console.log(quantityAmount, quantityAmount.value);
	        value = isNaN(value) ? 0 : value;
	        value++;
	        quantityAmount.value = value;
	    }

	    function decreaseValue(event, quantityAmount) {
	        value = parseInt(quantityAmount.value, 10);
	        value = isNaN(value) ? 0 : value;
	        if (value > 0) value--;
	        quantityAmount.value = value;
	    }
	    
	    init();
	};
	sitePlusMinus();

})();

// Simple close menu on navigation
$(document).ready(function() {
    // Close on nav link click
    $('.nav-link').on('click', function() {
        // Let Bootstrap handle the collapse
        $('.navbar-collapse').collapse('hide');
    });
});

//SPAPP code
var app = $.spapp({
    defaultView: "#page1",
    templateDir: "./views/",
    cache: true // Enable view caching
});

// Additional SPAPP handlers
$(document).on('spapp.view.before', function(e, view) {
    // Check access to dashboard
    if (view === '#dashboard') {
        const user = Utils.getCurrentUser();
        if (!user || (user.role !== Constants.ROLES.ADMIN && user.role !== Constants.ROLES.EMPLOYEE)) {
            e.preventDefault();
            window.location.hash = 'page1';
            Utils.showError('Access denied. You need admin or employee privileges to access the dashboard.');
            return false;
        }
    }
});

$(document).on('spapp.view.after', function() {
    // Close menu on navigation
    $('.navbar-collapse').collapse('hide');
});

// Hide/show dashboard link based on user role
function updateDashboardVisibility() {
    const user = Utils.getCurrentUser();
    if (user && (user.role === Constants.ROLES.ADMIN || user.role === Constants.ROLES.EMPLOYEE)) {
        $('#dashboard_link').show();
    } else {
        $('#dashboard_link').hide();
    }
}

// Update dashboard visibility on page load and after login/logout
$(document).ready(function() {
    updateDashboardVisibility();
});

// Listen for auth changes
$(document).on('auth.changed', function() {
    updateDashboardVisibility();
});

app.run();



