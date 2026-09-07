<?php
 require_once 'controller/common.php';
 $title='Find your next journey';
 require 'view/header.php';
?>
<section class="page-heading">
<div>
<span class="eyebrow">A BETTER WAY TO GET THERE</span>
<h1>Where are you heading?</h1>
<p>Find your ride. Pick your seat. Make it a journey.</p>
</div>
<span class="tag">Travel across Bangladesh</span>
</section>
<section class="card search-panel">
<form id="route-search" class="search-grid" data-validate>
<label>Transport<select name="type">
<option value="bus">Bus</option>
<option value="train">Train</option>
<option value="air">Air</option>
</select>
</label>
<label>From<input name="source" value="Dhaka" placeholder="Departure city" required maxlength="100" list="cities">
</label>
<label>To<input name="destination" value="Cox's Bazar" placeholder="Destination city" required maxlength="100" list="cities">
</label>
<label>Journey date<input type="date" name="date" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d',strtotime('+1 day')) ?>">
</label>
<button class="button">Search trips ↗</button>
</form>
<datalist id="cities">
<option>Dhaka</option>
<option>Cox's Bazar</option>
<option>Sylhet</option>
<option>Chattogram</option>
</datalist>
</section>
<div class="results-layout">
<section>
<div class="section-heading">
<h2>Available journeys</h2>
<span id="result-count" class="muted">
</span>
</div>
<div id="route-results" aria-live="polite">
<p class="card">Loading journeys…</p>
</div>
</section>
<aside>
<div class="travel-card">
<img src="assets/images/travel.jpg" alt="Landscape of Bangladesh">
<div>
<span class="eyebrow">A LITTLE FURTHER, A LITTLE FREER</span>
<h2>Make room for<br>a new view.</h2>
<a class="button light" href="hotel_search.php">Find a stay ↗</a>
</div>
</div>
<p class="photo-credit">Photo: <a href="https://commons.wikimedia.org/wiki/File:Tea_Garden_near_Srimangal,_Sylhet,_Bangladesh.jpg">Xahidur Reza</a> · <a href="https://creativecommons.org/licenses/by/2.0/">CC BY 2.0</a> (cropped)</p>
<div class="card compact">
<h3>Your trip, in three steps</h3>
<p>01 &nbsp; Find a journey</p>
<p>02 &nbsp; Choose an available seat</p>
<p>03 &nbsp; Confirm your booking</p>
</div>
</aside>
</div>
<script src="assets/js/search.js" defer>
</script>
<?php
 require 'view/footer.php';
?>
