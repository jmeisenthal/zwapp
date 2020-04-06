"use strict";

require('../less/zwapp.less');
require('@fortawesome/fontawesome-free/css/all.css');
// require('../node_modules/normalize.css/normalize.css');
var $ = require('jquery');
// var _ = require('lodash');


const STATE_INITIAL = 'nav-state--initial';
const STATE_ADD_PUBLISHER = 'nav-state--add-publisher';
const STATE_ADD_CHARACTER = 'nav-state--add-character';
const STATE_ADD_VOLUME = 'nav-state--add-volume';
const STATE_ADD_ISSUE = 'nav-state--add-issue';


let states = [STATE_INITIAL];
let urls = [];

let initialPromise = makePublishersPromise();

function makeServicePromise(url) {
    "use strict";
    return new Promise((resolve, reject) => {
        $.ajax({
            url: url,
        })
        .done(function(response) {
            resolve(response);
        })
        .fail(function(xhr) {
            console.log("error: " + xhr.responseText);
            reject(xhr);
        })
        .always(function() {
            console.log("complete");
        });
    });
}

function goToState(state, $this, url = null, service = null) {
    var $radial_nav = $this.closest('.radial_nav');
    $radial_nav.removeClass(states[states.length-1])
    $radial_nav.addClass(state);
    $radial_nav.addClass('ajax-loading');
    let servicePromise = service != null ? service : makeServicePromise(url);
    servicePromise.then((response) => {
        let backButton = $('#back_button_template').html();
        $('.radial_nav__choices').html(response + backButton);

        // Call via setTimeout with no <nav class=​"radial_nav nav-state--add-publisher fan--out">​…​</nav>​delay so render cycle completes first, allowing transistion to trigger:
        setTimeout(() => {
            $radial_nav.removeClass('ajax-loading');
            $radial_nav.addClass('fan--out');
        }, 0);

        states.push(state);
        urls.push(url);
    });

}

function makePublishersPromise() {
    return makeServicePromise('php/service/publishers.php');
}

$(function() {
    $('body').on('click', '.radial_nav__add_button', action__add);
    $('body').on('click', '.radial_nav__back_button', action__back);
    $('body').on('click', '.radial_nav__choice_button:not(.radial_nav__back_button)', radial_nav__choice_buttonClick)
    $('.header-menu__button').on('click', action__toggle_menu);
    $('.modal__close').on('click', action__toggle_menu);
    $('body').on('click', action__maybe_close_modal);
    $('.header-menu__option').on('click', action__open_dialog);
    $('.header-menu__dialog__title__back').on('click', action__close_dialog);
    $('body').on('mousedown', '.dial', dial__move_start);
    $('body').on('mousemove', '.dial', dial__move_drag);
    $('body').on('mouseup', '.dial', dial__move_end);
    $('body').on('mouseleave', '.dial', dial__move_end);
});

let action__close_dialog = function() {
    // $(this).closest('.modal-container').toggleClass('modal--expand modal--expand-start');
     $(this).closest('.modal-container').toggleClass('modal--expand modal--expand-stop');
        $(this).closest('.header-menu__group').removeClass('header-menu__group--show-dialog');
   
    setTimeout(() => {
        $(this).closest('.header-menu__pane').removeClass('header-menu__pane--hide-options');
        $(this).closest('.modal-container').removeClass('modal--hide-close modal--expand-stop');
    }, 1000);

    // setTimeout(() => {
    //     $(this).closest('.modal-container').addClass('modal--expand-stop');
    //     $(this).closest('.modal-container').removeClass('modal--expand');
    //     $(this).closest('.header-menu__group').removeClass('header-menu__group--show-dialog');
    // }, 0);
    // setTimeout(() => {
    //     $(this).closest('.header-menu__pane').removeClass('header-menu__pane--hide-options');
    //     $(this).closest('.modal-container').removeClass('modal--hide-close');
    //     $(this).closest('.modal-container').removeClass('modal--expand-stop');
    // }, 5000);
};

let action__open_dialog = function() {
    $(this).closest('.header-menu__group').addClass('header-menu__group--show-dialog');
    $(this).closest('.header-menu__pane').addClass('header-menu__pane--hide-options');
    $(this).closest('.modal-container').addClass('modal--hide-close modal--expand-start');
    
    setTimeout(() => {
        $(this).closest('.modal-container').toggleClass('modal--expand modal--expand-start');
    }, 0);
};

let action__maybe_close_modal = function(e) {
    let $modal = $(e.target).closest('.modal');
    // Close menu if click was not in the menu modal or on the menu button:
    if ($modal.length == 0 && $(e.target).closest('.header-menu__button').length == 0) {
        $('.header-menu__button').removeClass('header-menu__button--hide');
        $('.modal-container').removeClass('modal--show');
    }
};

let action__toggle_menu = function() {
    $('.header-menu__button').toggleClass('header-menu__button--hide');
    $('.modal-container').toggleClass('modal--show');
};

let action__add = function() {
    goToState(STATE_ADD_PUBLISHER, $(this), null, initialPromise);
};

let action__back = function() {
    removeLastDetail();
    urls.pop();
    let oldState = states.pop();

    let $radial_nav = $(this).closest('.radial_nav');
    $radial_nav.removeClass(oldState)
    $radial_nav.removeClass('fan--out');
    $radial_nav.addClass(states[states.length-1]);
    if (urls.length == 0) {
        return;
    }
    let url = urls[urls.length-1];
    let servicePromise = url == null ? makePublishersPromise() : makeServicePromise(url);
   //  if (url == null) {
   //      initialPromise = makePublishersPromise();
   //      initialPromise.then((response) => {
   //          $('.radial_nav__choices').html(response);
   //            // Call via setTimeout with no delay so render cycle completes first, allowing transistion to trigger:
   //          setTimeout(() => {
   //              $radial_nav.removeClass('ajax-loading');
   //              $radial_nav.addClass('fan--out');
   //          }, 10);
   //     });
   // } else {
        $radial_nav.addClass('ajax-loading');
        servicePromise.then((response) => {
            let backButton = $('#back_button_template').html();
            $('.radial_nav__choices').html(response + backButton);

            // Call via setTimeout with no delay so render cycle completes first, allowing transistion to trigger:
            setTimeout(() => {
                $radial_nav.removeClass('ajax-loading');
                $radial_nav.addClass('fan--out');
            }, 10);
        });
    // }
    // if ($radial_nav.is('.nav-state--add-character')) {
    //     // servicePromise = makePublishersPromise();
    //     action__add();
    //     $radial_nav.toggleClass('nav-state--add-character radial_nav--state-add-start');
    //     // $radial_nav.addClass('fan--out');
    // } else {
    //     $radial_nav.toggleClass('radial_nav--state-initial radial_nav--state-add-start fan--out');
    // }
};

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
let radial_nav__choice_buttonClick = function(e) {
    let nextState = null;
    let url = null;
    let button = $(e.target).closest('button');
    let id = button.data('id');
    let name = button.data('name');
    switch(states[states.length-1]) {
        case STATE_ADD_PUBLISHER: 
            nextState = STATE_ADD_CHARACTER;
            url = 'php/service/characters.php?publisher='+id;
            addDetail("Publisher", name);
            break;
        case STATE_ADD_CHARACTER:
            nextState = STATE_ADD_VOLUME;
            url = 'php/service/volumes.php?character='+id;
            addDetail("Character", name);
            break;
        case STATE_ADD_VOLUME:
            nextState = STATE_ADD_ISSUE;
            url = 'php/service/volumes.php?character='+id;
            addDetail("Volume", name);
            break;
    }
    goToState(nextState, $(this), url);
    // let $radial_nav = $(this).closest('.radial_nav');
    // let $button = $(this).closest('button');
    // let $choice = $button.closest('.radial_nav__choice');
    // let id = $button.data('id');
    // let nextState = null;
    // let url = null;
    // switch(states[states.length-1]) {
    //     case STATE_ADD_PUBLISHER: 
    //         nextState = STATE_ADD_CHARACTER;
    //         url = 'php/service/characters.php?publisher=';
    //         break;
    //     case STATE_ADD_CHARACTER:
    //         nextState = STATE_ADD_VOLUME;
    //         url = 'php/service/volumes.php?character=';
    //         break;
    // }

//    // 1. Fade out other choices:
    // new Promise((resolve) => {
    //     $radial_nav.find('.radial_nav__choice').addClass('fade-out-1s');
    //     $choice.removeClass('fade-out-1s');
    //     setTimeout(() => {
    //         $choice.addClass('fade-out-1s');
    //         $radial_nav.addClass('ajax-loading');
    //         setTimeout(function() {
    //             resolve();
    //         }, 1000);
    //     }, 1000);
    // }).then(() => {
    //     goToState(nextState, $(this), url+id);
    // });
};

let addDetail = function(name, value) {
    let $detailsPane = $('.details_pane');
    $detailsPane.removeClass("hidden");
    let $list = $detailsPane.find('.details_list');
    let $detail = $($detailsPane.find('._template').html());
    $list.append($detail);
    $detail.find('.detail__name').text(name);
    $detail.find('.detail__value').text(value);
}

let removeLastDetail = function() {
    let $details = $('.details_pane .details_list').find('.detail');
    $details.last().remove();
    if ($details.length <= 1) {
        $('.details_pane').addClass('hidden');
    }

}

// function drawDialTrack() {
//     var canvas = document.getElementById('dial__track');
//     if (canvas.getContext) {
//         var ctx = canvas.getContext('2d');
//         ctx.arc(120,120,120,Math.PI/4, -Math.PI/4);
//         ctx.strokeStyle = "#381A3F";
//         ctx.lineWidth = 20;
//         ctx.lineCap = 'round';
//         ctx.stroke();
//     }
// }
// 

let dial__move_start = function(e) {
    let $self = $(e.target);
    let $dial = $self.closest(".dial");
    $dial.data('mouse_down', true);
    dial__move(e);
}

let dial__move_drag = function(e) {
    let $self = $(e.target);
    let $dial = $self.closest(".dial");
    if ($dial.data('mouse_down')) {
        dial__move(e);        
    }
}

let dial__move = function(e) {
    let $self = $(e.target);
    let $dial = $self.closest(".dial");
    let dialOffset = $dial.offset();
    let x = e.pageX - dialOffset.left - $dial.width()/2;
    let y = -e.pageY + dialOffset.top + $dial.height()/2; 
    console.log("Info: height:" + $dial.height() + ", width: " + $dial.width() + ", x: " + x + ", y: " + y);
    let angle = Math.atan(y/x);
    if (x < 0 && y >= 0) {
        angle = Math.PI + angle;
    }
    if (x < 0 && y < 0) {
        angle = Math.PI + angle;
    }
    if (x >= 0 && y <= 0) {
        angle = 2*Math.PI + angle;
    }

    // angle less than 45deg treated as 45deg
    angle = Math.max(angle, Math.PI/4);
    // angle greater than 315deg treated as 315deg:
    angle = Math.min(angle, 1.75* Math.PI);

    let fraction = (angle - Math.PI/4) / (1.5 * Math.PI);

    console.log("Angle: " + angle/Math.PI/2*360 + ", fraction: " + fraction);

    dial__setValue(fraction, $dial) ;
}

let dial__move_end = function(e) {
    let $self = $(e.target);
    let $dial = $self.closest(".dial");
    $dial.data('mouse_down', false);
}

let dial__setValue = function(fraction, $dial) {
    let angle = (fraction * 1.5 * Math.PI + Math.PI/4) * 180 / Math.PI;
    let $thumb = $dial.find('.dial__thumb');
    $thumb.css('transform', 'rotate(-'+angle+'deg)');
}