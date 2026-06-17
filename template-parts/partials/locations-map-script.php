<?php

if (! defined('ABSPATH')) {
    exit;
}

$section_id = isset($args['section_id']) ? (string) $args['section_id'] : '';

if ($section_id === '') {
    return;
}

static $matrix_locations_map_script_printed = false;

if ($matrix_locations_map_script_printed) {
    return;
}

$matrix_locations_map_script_printed = true;
?>

<script>
(function () {
  const LEAFLET_CSS = "https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css";
  const LEAFLET_JS = "https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js";
  const DESKTOP_BREAKPOINT = "(min-width: 1100px)";
  const registry = {};

  function loadLeafletIfNeeded(cb) {
    if (typeof window.L !== "undefined") return cb();

    if (!document.querySelector('link[data-leaflet-css]')) {
      const link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = LEAFLET_CSS;
      link.crossOrigin = "";
      link.setAttribute("data-leaflet-css", "1");
      document.head.appendChild(link);
    }

    const existing = document.querySelector('script[data-leaflet-js]');
    if (existing) {
      const timer = setInterval(() => {
        if (typeof window.L !== "undefined") {
          clearInterval(timer);
          cb();
        }
      }, 30);
      setTimeout(() => clearInterval(timer), 8000);
      return;
    }

    const script = document.createElement("script");
    script.src = LEAFLET_JS;
    script.defer = true;
    script.crossOrigin = "";
    script.setAttribute("data-leaflet-js", "1");
    script.onload = cb;
    document.body.appendChild(script);
  }

  function isDesktopMapViewport() {
    return window.matchMedia && window.matchMedia(DESKTOP_BREAKPOINT).matches;
  }

  function getIrelandBounds() {
    return window.L.latLngBounds([51.40, -10.50], [55.40, -5.95]);
  }

  function fitMapToIreland(map, container) {
    const bounds = getIrelandBounds();

    let paddingTopLeft = [12, 12];
    let paddingBottomRight = [12, 12];

    if (isDesktopMapViewport()) {
      // Keep Ireland clear to the right of the overlay panel on the left.
      const mapWidth = (container && container.clientWidth) || map.getSize().x;
      const contentWidth = 1018; // 63.625rem content column
      const panelWidth = 381;    // 23.8125rem panel
      const leftInset = Math.max(20, (mapWidth - contentWidth) / 2);
      const gap = 56;

      paddingTopLeft = [leftInset + panelWidth + gap, 24];
      paddingBottomRight = [56, 24];
    }

    map.setMinZoom(3);
    map.fitBounds(bounds, {
      animate: false,
      paddingTopLeft: paddingTopLeft,
      paddingBottomRight: paddingBottomRight,
    });

    // Lock to the fitted view so the UK and mainland Europe never appear.
    const fittedZoom = map.getZoom();
    map.setMinZoom(fittedZoom);
    map.setMaxZoom(Math.min(12, fittedZoom + 4));
    map.setMaxBounds(map.getBounds().pad(0.04));
  }

  function resolveTileLayer(provider, token) {
    const jawgAttribution = '&copy; <a href="https://www.jawg.io" target="_blank" rel="noopener">Jawg</a> &copy; OpenStreetMap';

    if (provider.indexOf("jawg-") === 0 && token) {
      return {
        url: "https://tile.jawg.io/" + provider + "/{z}/{x}/{y}{r}.png?access-token=" + encodeURIComponent(token),
        options: { maxZoom: 22, attribution: jawgAttribution },
      };
    }

    return {
      url: "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png",
      options: { maxZoom: 20, attribution: "&copy; OpenStreetMap &copy; CARTO" },
    };
  }

  function updatePanelScrollbar(panel) {
    const section = panel.closest(".locations-map-section");
    if (!section) return;

    const trackWrap = section.querySelector(".locations-map-panel__scrollbar");
    const track = section.querySelector(".locations-map-panel__scrollbar-track");
    const thumb = section.querySelector(".locations-map-panel__scrollbar-thumb");
    if (!trackWrap || !track || !thumb) return;

    const maxScroll = panel.scrollHeight - panel.clientHeight;
    if (maxScroll <= 0) {
      trackWrap.style.display = "none";
      return;
    }

    trackWrap.style.display = "";
    const trackHeight = track.clientHeight;
    const ratio = panel.clientHeight / panel.scrollHeight;
    const thumbHeight = Math.max(112, trackHeight * ratio);
    const maxThumbTop = trackHeight - thumbHeight;
    const thumbTop = (panel.scrollTop / maxScroll) * maxThumbTop;
    thumb.style.height = thumbHeight + "px";
    thumb.style.transform = "translateY(" + thumbTop + "px)";
  }

  function initLocationsMap(container) {
    if (!container || container.dataset.initialized === "1" || typeof window.L === "undefined") return;

    const sectionId = container.getAttribute("data-section-id") || container.id.replace(/-map$/, "");

    let markers = [];
    const jsonEl = document.getElementById(sectionId + "-markers");
    if (jsonEl) {
      try { markers = JSON.parse(jsonEl.textContent || "[]"); } catch (e) {}
    }

    const provider = container.getAttribute("data-provider") || "jawg-lagoon";
    const token = container.getAttribute("data-token") || "";

    const map = window.L.map(container, {
      scrollWheelZoom: !isDesktopMapViewport(),
      zoomControl: false,
      attributionControl: true,
    });

    const tileLayer = resolveTileLayer(provider, token);
    window.L.tileLayer(tileLayer.url, tileLayer.options).addTo(map);

    const customIcon = window.L.divIcon({
      className: "locations-map-marker",
      html: <?php echo wp_json_encode(matrix_get_locations_map_pin_icon_svg()); ?>,
      iconSize: [24, 29],
      iconAnchor: [12, 29],
    });

    const markerEntries = [];

    markers.forEach((location) => {
      if (!location) return;

      const glat = Number(location.lat);
      const glng = Number(location.lng);
      if (!Number.isFinite(glat) || !Number.isFinite(glng)) return;

      const markerTitle = String(location.title || "SPMHS location");
      const marker = window.L.marker([glat, glng], {
        icon: customIcon,
        keyboard: false,
        title: markerTitle,
      }).addTo(map);

      if (marker && marker._icon) {
        marker._icon.setAttribute("aria-label", markerTitle);
        marker._icon.setAttribute("title", markerTitle);
      }

      markerEntries.push({ marker, location });

      marker.on("click", () => {
        const locationId = Number(location.id);
        const section = document.getElementById(sectionId);
        if (!section || !Number.isFinite(locationId)) return;

        highlightMarker(locationId);

        section.dispatchEvent(new CustomEvent("matrix-location-focus", {
          bubbles: true,
          detail: { sectionId, locationId },
        }));

        const panelItem = document.getElementById(sectionId + "-location-" + locationId);
        const panel = document.getElementById(sectionId + "-panel");
        if (panelItem && panel) {
          panelItem.scrollIntoView({ behavior: "smooth", block: "nearest" });
        }
      });
    });

    function highlightMarker(locationId) {
      markerEntries.forEach(({ marker, location }) => {
        if (!marker._icon) return;
        const isActive = Number(location.id) === Number(locationId);
        marker._icon.classList.toggle("locations-map-marker--active", isActive);
        if (typeof marker.setZIndexOffset === "function") {
          marker.setZIndexOffset(isActive ? 1000 : 0);
        }
      });
    }

    function focusLocation(locationId) {
      const entry = markerEntries.find(({ location }) => Number(location.id) === Number(locationId));
      if (!entry) return;
      highlightMarker(locationId);
      map.panTo(entry.marker.getLatLng(), { animate: true });
    }

    registry[sectionId] = { map, focusLocation, markerEntries };
    fitMapToIreland(map, container);

    const panel = document.getElementById(sectionId + "-panel");
    if (panel) {
      panel.addEventListener("scroll", () => updatePanelScrollbar(panel), { passive: true });
      updatePanelScrollbar(panel);
      window.addEventListener("resize", () => {
        updatePanelScrollbar(panel);
        map.invalidateSize();
        fitMapToIreland(map, container);
      });
    }

    container.dataset.initialized = "1";
    setTimeout(() => {
      map.invalidateSize();
      fitMapToIreland(map, container);
    }, 50);
    setTimeout(() => {
      map.invalidateSize();
      fitMapToIreland(map, container);
    }, 300);
  }

  window.matrixLocationsMapFocus = function (sectionId, locationId) {
    const entry = registry[sectionId];
    if (entry && typeof entry.focusLocation === "function") {
      entry.focusLocation(locationId);
    }
  };

  function boot() {
    document.querySelectorAll("[data-locations-leaflet]").forEach((container) => {
      loadLeafletIfNeeded(() => initLocationsMap(container));
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
</script>
