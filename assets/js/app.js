// assets/js/app.js
"use strict";
import '../css/app.css';

// (Optional: your jQuery/WP helpers here; DO NOT import or start Alpine here.)
document.addEventListener('DOMContentLoaded', () => {
  const checkbox = document.querySelector('#ship-to-different-address-checkbox');
  const shipping = document.querySelector('.shipping_address');
  if (checkbox && shipping) {
    const sync = () => { shipping.style.display = checkbox.checked ? 'block' : 'none'; };
    checkbox.addEventListener('change', sync);
    sync();
  }
});
