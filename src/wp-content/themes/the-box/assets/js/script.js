/**
 * File main.js
 *
 */

// Mobile Navigation
;(function( $, window, document, undefined ){
	"use strict";

	$( document ).ready( function ($) {

		// toggle button
		var $toggleButton = $('#menu-toggle');
		var $menuMobile = $('#mobile-navigation');
		var $body = $('body');

		$('#menu-toggle').click( function( e ) {
			$menuMobile.toggleClass('toggled-on');
			$toggleButton.toggleClass('toggled-on');
			$body.toggleClass('mobile-nav-open');
			$( 'body,html' ).scrollTop( 0 );
			return false;
		} );

		var defaultWindowWidth = $(window).width();
		$(window).resize(function() {
			if ( defaultWindowWidth != $(window).width() ) {
				$menuMobile.removeClass('toggled-on');
				$toggleButton.removeClass('toggled-on');
				$body.removeClass('mobile-nav-open');
			}
		});

		// dropdown button
		var mainMenuDropdownLink = $('.mobile-nav-menu .menu-item-has-children > a, .mobile-nav-menu .page_item_has_children > a');
		var dropDownArrow = $('<span class="dropdown-toggle"></span>');

		mainMenuDropdownLink.after(dropDownArrow);

		// dropdown open on click
		var dropDownButton = mainMenuDropdownLink.next('span.dropdown-toggle');

		dropDownButton.on('click', function(){
			var $this = $(this);
			$this.parent('li').toggleClass('toggle-on').find('.toggle-on').removeClass('toggle-on');
			$this.parent('li').siblings().removeClass('toggle-on');
		});

	} );
})( jQuery, window , document );
