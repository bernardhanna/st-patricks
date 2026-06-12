/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
// assets/js/app.js




// (Optional: your jQuery/WP helpers here; DO NOT import or start Alpine here.)
document.addEventListener('DOMContentLoaded', function () {
  var checkbox = document.querySelector('#ship-to-different-address-checkbox');
  var shipping = document.querySelector('.shipping_address');
  if (checkbox && shipping) {
    var sync = function sync() {
      shipping.style.display = checkbox.checked ? 'block' : 'none';
    };
    checkbox.addEventListener('change', sync);
    sync();
  }
});
/******/ })()
;