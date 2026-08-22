  <!-- ========================================================
       GlobeTrotter India — Footer
  ========================================================= -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <div class="navbar-brand" style="margin-bottom: 0.85rem;">
            <div class="brand-icon">🇮🇳</div>
            Globe<span style="background:var(--grad-sunset);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Trotter</span>
          </div>
          <p style="color:var(--text-muted); font-size:0.9rem; max-width:320px; line-height:1.6;">
            Your personal AI-powered travel planner for discovering the timeless magic, heritage, and landscapes of Incredible India.
          </p>
          <div style="display:flex; gap:0.75rem; margin-top:1.25rem;">
            <span class="badge badge-gold">✨ Made for Explorers</span>
            <span class="badge badge-teal">🇮🇳 100% Indian Journeys</span>
          </div>
        </div>

        <div class="footer-col">
          <h4>Destinations</h4>
          <ul class="footer-links">
            <li><a href="city_search.php?region=1">North India &amp; Himalayas</a></li>
            <li><a href="city_search.php?region=2">South India &amp; Backwaters</a></li>
            <li><a href="city_search.php?region=3">Western Palaces &amp; Deserts</a></li>
            <li><a href="city_search.php?region=4">East &amp; North-East</a></li>
            <li><a href="city_search.php?region=5">Andaman &amp; Islands</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Trip Planner</h4>
          <ul class="footer-links">
            <li><a href="create_trip.php">Plan a New Journey</a></li>
            <li><a href="my_trips.php">My Saved Itineraries</a></li>
            <li><a href="budget.php">Expense Tracker (₹)</a></li>
            <li><a href="activity_search.php">Adventure &amp; Culture</a></li>
            <li><a href="community.php">Traveler Stories</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Account</h4>
          <ul class="footer-links">
            <?php if (isset($_SESSION['user_id'])): ?>
              <li><a href="dashboard.php">User Dashboard</a></li>
              <li><a href="profile.php">Travel Profile</a></li>
              <li><a href="calendar.php">Journey Calendar</a></li>
              <li><a href="logout.php" style="color:var(--danger);">Sign Out</a></li>
            <?php else: ?>
              <li><a href="login.php">Sign In</a></li>
              <li><a href="register.php">Create Account</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <div>
          &copy; <?php echo date('Y'); ?> <strong>GlobeTrotter India</strong>. Designed for the adventurous spirit.
        </div>
        <div style="display:flex; gap:1.5rem;">
          <span style="color:var(--text-dim);">Privacy Policy</span>
          <span style="color:var(--text-dim);">Terms of Service</span>
          <span style="color:var(--gold);">Curated in India 🪷</span>
        </div>
      </div>
    </div>
  </footer>

  <div id="toast-container"></div>
  <script src="assets/js/api.js"></script>
  <?php if (isset($extraScript)) echo $extraScript; ?>
</body>
</html>
