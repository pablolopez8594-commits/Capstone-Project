<?php include "partials/header.php"; ?>

<main class="container py-4">
  <h1 class="text-center mb-4">Campus Map</h1>

  <div class="row justify-content-center mb-4">
  <div class="col-12 col-xl-10">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          
          <div class="col-12 col-md-6 col-lg-4">
            <label for="fromRoom" class="form-label">From</label>
            <input
              id="fromRoom"
              class="form-control"
              list="roomsList"
              placeholder="e.g. M3520"
              autocomplete="off"
            >
          </div>

          <div class="col-12 col-md-6 col-lg-4">
            <label for="toRoom" class="form-label">To</label>
            <input
              id="toRoom"
              class="form-control"
              list="roomsList"
              placeholder="e.g. Library"
              autocomplete="off"
            >
          </div>

          <div class="col-12 col-lg-4">
            <div class="d-grid d-sm-flex gap-2">
              <button id="btnRoute" type="button" class="btn btn-primary flex-fill">
                Draw route
              </button>
              <button id="btnClear" type="button" class="btn btn-outline-secondary flex-fill">
                Clear
              </button>
            </div>
          </div>

        </div>

        <datalist id="roomsList"></datalist>
      </div>
    </div>
  </div>
</div>

  <div class="row justify-content-center">
    <div class="col-12 col-xl-10">
      <div id="map-stage" class="d-flex align-items-center justify-content-center gap-2 gap-md-3">
        
        <button
          id="map-prev-btn"
          class="btn btn-outline-dark map-side-btn disabled"
          type="button"
          disabled
          aria-label="Previous map"
        >
          &#9664;
        </button>

        <div id="map-center" class="flex-grow-1">
          <div class="map-wrap border rounded overflow-hidden bg-light">
            <div id="map-container" class="w-100"></div>
          </div>
        </div>

        <button
          id="map-next-btn"
          class="btn btn-outline-dark map-side-btn disabled"
          type="button"
          disabled
          aria-label="Next map"
        >
          &#9654;
        </button>
      </div>
    </div>
  </div>
</main>

<script type="module" src="./js/map-route.js"></script>

<?php
include "./partials/nav.php";
include "./partials/footer.php";
?>