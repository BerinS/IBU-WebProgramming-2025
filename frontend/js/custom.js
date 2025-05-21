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

// Function to force close mobile menu using multiple methods
function forceCloseMobileMenu() {
    // Method 1: Using jQuery collapse
    $('.navbar-collapse').collapse('hide');
    
    // Method 2: Direct class manipulation
    $('.navbar-collapse').removeClass('show');
    
    // Method 3: Toggle button state
    $('.navbar-toggler').addClass('collapsed').attr('aria-expanded', 'false');
    
    // Method 4: Force body classes
    $('body').removeClass('modal-open');
    
    // Method 5: Trigger click on toggler if menu is open
    if ($('.navbar-collapse').hasClass('show')) {
        $('.navbar-toggler').trigger('click');
    }
}

// Close menu on any click outside
$(document).on('click', function(event) {
    if (!$(event.target).closest('.navbar').length) {
        forceCloseMobileMenu();
    }
});

// Close menu on navigation
$(document).ready(function() {
    // Close on nav link click
    $('.nav-link').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        forceCloseMobileMenu();
        setTimeout(function() {
            window.location.hash = href;
        }, 200);
    });
});

//Force scroll to top and close menu
window.addEventListener("hashchange", function() {
    window.scrollTo(0, 0);
    forceCloseMobileMenu();
});

//SPAPP code
var app = $.spapp({
    defaultView: "#page1",
    templateDir: "./views/"
});

// Additional SPAPP handlers
$(document).on('spapp.view.before', function() {
    forceCloseMobileMenu();
});

$(document).on('spapp.view.after', function() {
    forceCloseMobileMenu();
});

app.run();



