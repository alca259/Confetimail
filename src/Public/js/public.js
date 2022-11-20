
/**
 * Esta funcion se encarga de realizar animaciones en la web cuando se hace click sobre un ancla
 */
$(function() {
	$("a[href*=#]").click(function() {
		if (location.pathname.replace(/^\//,"") == this.pathname.replace(/^\//,"") && location.hostname == this.hostname) {
			var $target = $(this.hash);
			$target = $target.length && $target || $("[name=\"" + this.hash.slice(1) + "\"]");

		    var targetOffset = 0;
			if ($target.length) {
			    targetOffset = $target.offset().top;
			}

			$("html,body").animate({ scrollTop: targetOffset }, 1000);
		}
		return false;
	});
});

$(function () {

    /* set variables locally for increased performance */
    var scroll_timer = 1;
    var displayed = false;
    var $message = $("a#FlechitaLinda");
    var $window = $(window);
    var top = 0; // Siempre desde arriba

    /* react to scroll event on window */
    $window.scroll(function () {
        window.clearTimeout(scroll_timer);
        scroll_timer = window.setTimeout(function () {
            if($window.scrollTop() <= top)
            {
                displayed = false;
                $message.fadeOut(500);
            }
            else if(displayed == false)
            {
                displayed = true;
                $message.stop(true, true).fadeIn(500).click(function () { $message.fadeOut(500); });
            }
        }, 100);
    });
});

function submitForm(IDForm) {
	document.getElementById(IDForm).submit();
}

function htmlStarsFor(value) {
    // Min 0 - Max 5
    var emptyStar = "<img src='/Public/img/ui/star-empty-icon-16.png' alt='Empty star' title='Empty' />";
    var middleStar = "<img src='/Public/img/ui/star-middle-icon.png' alt='Empty star' title='Empty' />";
    var fullStar = "<img src='/Public/img/ui/star-full-icon-16.png' alt='Empty star' title='Empty' />";

    var currentValue;
    var dataReturn = "<div style='display: inline-block;'>";

    for (currentValue = 0; currentValue < 5; currentValue++) {
        if (currentValue < value && value >= currentValue + 1) {
            // Si 0 < 1.7 y 1.7 >= 1 - OK Full star
            dataReturn += fullStar;
        } else if (currentValue < value && value < currentValue + 1) {
            // Si 1 < 1.7 y 1.7 < 2 - OK Middle star
            dataReturn += middleStar;
        }
        else {
            // Si 2 > 1.7 o 1.7 < 3 - OK Empty star
            dataReturn += emptyStar;
        }
    }

    dataReturn += "</div>";

    return dataReturn;
}

function showLoading(active, divName) {
    if (divName == null || divName == "")
        divName = "loading";
    if (active != null)
        kendo.ui.progress($("#" + divName), active);
}

$(document).ready(function() {
    $(".dropdown-toggle").dropdown();
});

$.extend({
    // Returns a range object
    // Author: Matthias Miller
    // Site:   http://blog.outofhanwell.com/2006/03/29/javascript-range-function/
    range: function () {
        if (!arguments.length) { return []; }
        var min, max, step;
        if (arguments.length == 1) {
            min = 0;
            max = arguments[0] - 1;
            step = 1;
        }
        else {
            // default step to 1 if it's zero or undefined
            min = arguments[0];
            max = arguments[1];// - 1;
            step = arguments[2] || 1;
        }
        // convert negative steps to positive and reverse min/max
        if (step < 0 && min >= max) {
            step *= -1;
            var tmp = min;
            min = max;
            max = tmp;
            min += ((max - min) % step);
        }
        var a = [];
        for (var i = min; i <= max; i += step) { a.push(i); }
        return a;
    }
});

function replaceAll(find, replace, str) {
    return str.replace(new RegExp(find, 'g'), replace);
}