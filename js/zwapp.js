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
	$.ajax({
		url: 'php/service/publisher.php?id=10,31,364,101,521',
	})
	.done(function(response) {
		console.log("success");
		$('.radial_nav__choices').html(response)
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});

	$('body').on('click', '.radial_nav__button', radial_nav__buttonClick);
});

radial_nav__buttonClick = function(e) {
		// console.log("click!");
		// debugger;
		let $radial_nav = $(this).closest('.radial_nav');
		if ($radial_nav.is('.radial_nav--state-initial')) {
			$radial_nav.toggleClass('radial_nav--state-initial radial_nav--state-add-start');
			$radial_nav.addClass('fan--out');
		}
};