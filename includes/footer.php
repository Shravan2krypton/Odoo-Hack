  <!-- ========================================================
       GlobeTrotter — Footer
  ========================================================= -->
  <footer style="background:var(--surface);border-top:1px solid var(--border);margin-top:4rem;padding:2.5rem 0;">
    <div class="container">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;">
        <div>
          <div class="navbar-brand" style="font-size:1.2rem;margin-bottom:0.5rem;">
            <div class="brand-icon" style="width:28px;height:28px;font-size:0.9rem;">🌍</div>
            Globe<span style="color:var(--primary);">Trotter</span>
          </div>
          <p style="font-size:0.82rem;color:var(--text-dim);max-width:280px;">
            Plan smarter, travel better. Your personalized multi-city trip planner.
          </p>
        </div>
        <div style="display:flex;gap:2rem;flex-wrap:wrap;">
          <div>
            <div style="font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.75rem;">Navigate</div>
            <div style="display:flex;flex-direction:column;gap:0.4rem;">
              <a href="index.php"        style="font-size:0.85rem;color:var(--text-dim);">Home</a>
              <a href="my_trips.php"     style="font-size:0.85rem;color:var(--text-dim);">My Trips</a>
              <a href="city_search.php"  style="font-size:0.85rem;color:var(--text-dim);">Explore Cities</a>
              <a href="community.php"    style="font-size:0.85rem;color:var(--text-dim);">Community</a>
            </div>
          </div>
          <div>
            <div style="font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.75rem;">Account</div>
            <div style="display:flex;flex-direction:column;gap:0.4rem;">
              <a href="profile.php"      style="font-size:0.85rem;color:var(--text-dim);">Profile</a>
              <a href="budget.php"       style="font-size:0.85rem;color:var(--text-dim);">Budget</a>
              <a href="calendar.php"     style="font-size:0.85rem;color:var(--text-dim);">Calendar</a>
              <a href="logout.php"       style="font-size:0.85rem;color:var(--accent);">Logout</a>
            </div>
          </div>
        </div>
      </div>
      <hr style="border:none;border-top:1px solid var(--border);margin:1.5rem 0;">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
        <p style="font-size:0.8rem;color:var(--text-dim);">&copy; <?php echo date('Y'); ?> GlobeTrotter. Built for explorers. 🌏</p>
        <p style="font-size:0.8rem;color:var(--text-dim);">Powered by PHP &amp; MySQL on XAMPP</p>
      </div>
    </div>
  </footer>

  <div id="toast-container"></div>
  <script src="assets/js/api.js"></script>
  <?php if (isset($extraScript)) echo $extraScript; ?>
</body>
</html>
