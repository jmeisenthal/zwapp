require('../less/zwapp.less');
require('@fortawesome/fontawesome-free/css/all.css')
// require('../node_modules/normalize.css/normalize.css');
window.$ = require('jquery');
window._ = require('lodash');

// ====================
// ComicVine
// 
const ComicVine_API = 'https://comicvine.gamespot.com/api/';
const ComicVine_KEY = 'api_key=a587d36b7356338e04d0b132d105c9384a11691a';
function comicVineQuery(queryType, params, callback) {
	let url = ComicVine_API + queryType + '/?' + ComicVine_KEY + _.join(params, '&');
	$.ajax({
		url: url,
	})
	.done(callback)
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
	
};

$(function() {
	$('.radial_nav').addClass('ajax-loading');
	$.ajax({
		// url: 'php/service/publisher.php?id=10,31,364,101,521',
		url: 'php/service/publishers.php',
	})
	.done(function(response) {
		// console.log("success: " + response); 
		$('.radial_nav__choices').html(response);
		// Call via setTimeout with no delay so render cycle completes first, allowing transistion to trigger:
		setTimeout(function() {$('.radial_nav').removeClass('ajax-loading');}, 0);
	})
	.fail(function(xhr) {
		console.log("error: " + xhr.responseText);
	})
	.always(function() {
		console.log("complete");
	});

	$('body').on('click', '.radial_nav__button--add', action__add);
	// $('body').on('click', '.radial_nav__button--back', action__back);
	$('body').on('click', '.radial_nav__choice_button', radial_nav__choice_buttonClick)
});

action__add = function(e) {
	// console.log("click!");
	// debugger;
	let $radial_nav = $(this).closest('.radial_nav');
	if ($radial_nav.is('.nav-state--initial')) {
		$radial_nav.toggleClass('nav-state--initial nav-state--add-publisher');
		$radial_nav.addClass('fan--out');
	} 
	// Cancel add:
	else {
		$radial_nav.removeClass('nav-state--add-publisher nav-state--add-character fan--out');
		$radial_nav.addClass('nav-state--initial');
	}
};

// action__back = function(e) {
// 	// console.log("click!");
// 	// debugger;
// 	let $radial_nav = $(this).closest('.radial_nav');
// 	if ($radial_nav.is('.radial_nav--state-add-middle')) {
// 		$radial_nav.toggleClass('radial_nav--state-add-middle radial_nav--state-add-start');
// 		// $radial_nav.addClass('fan--out');
// 	} else {
// 		$radial_nav.toggleClass('radial_nav--state-initial radial_nav--state-add-start fan--out');
// 	}
// };

/**
 * What happens when a nav choice is selected: 
 *   1a. The other nav choices fade.
 *   1b. Start getting the child choices. They are hidden initially, in case they are available before other steps completed.
 *   2. This choice fades.
 *   3a. The background burst flickers (i.e. "Loading...")
 *   3b. The old now hidden choices leave the layout (by setting display none?)
 *   4a. Fade-in the new choices as soon as they are available once old choices gone.
 *   4b. Stop the backgrounf flickering
 *   5. Make back button visible.
 *   
 * @param  {[type]} e [description]
 * @return {[type]}   [description]
 */
radial_nav__choice_buttonClick = function(e) {
	let $radial_nav = $(this).closest('.radial_nav');
	let $button = $(e.target).closest('button');
	let $choice = $button.closest('.radial_nav__choice');
	console.log("Type: " + $button.data('type'));

	// 1. Fade out other choices:
	$radial_nav.find('.radial_nav__choice').addClass('fade-out-1s');
	$choice.removeClass('fade-out-1s');
	$.ajax({
		// url: 'php/service/characters.php',
		url: 'php/service/characters.php?publisher=' + $button.data('id'),
	})
	.done(function(response) {
		// console.log("success: " + response); 
		$('.radial_nav__choices').html(response);
		// Call via setTimeout with no delay so render cycle completes first, allowing transistion to trigger:
		setTimeout(function() {$('.radial_nav').removeClass('ajax-loading');}, 0);
	})
	.fail(function(xhr) {
		console.log("error: " + xhr.responseText);
	})
	.always(function() {
		console.log("complete");
	});

	setTimeout(function() {
		$choice.addClass('fade-out-1s');

		setTimeout(function() {
			// $radial_nav.find('.radial_nav__background').removeClass("background-loading");

			// if ($radial_nav.is('.radial_nav--state-add-start')) {
				$radial_nav.toggleClass('nav-state--add-publisher nav-state--add-character');
				$radial_nav.addClass("ajax-loading");
			// }
		}, 1000);
	}, 1000);
};