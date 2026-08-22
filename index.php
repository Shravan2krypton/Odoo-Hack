<?php
include 'includes/db_connect.php';
session_start();

$pageTitle = "Incredible India Travel Planner";
include 'includes/header.php';

// Fetch regions
$regionsQuery = $conn->query("SELECT id, name, tagline, description, image_url FROM regions ORDER BY id ASC LIMIT 6");

// Fetch trending featured cities
$citiesQuery = $conn->query("SELECT c.id, c.name, c.state, c.popularity_score, c.avg_daily_cost, c.image_url, c.description, c.best_time_to_visit, r.name AS region_name 
    FROM cities c 
    LEFT JOIN regions r ON c.region_id = r.id 
    ORDER BY c.popularity_score DESC 
    LIMIT 6");

// Fetch top curated activities
$activitiesQuery = $conn->query("SELECT a.id, a.name, a.category, a.cost, a.duration, a.image_url, a.description, c.name AS city_name 
    FROM activities a 
    LEFT JOIN cities c ON a.city_id = c.id 
    ORDER BY a.cost DESC 
    LIMIT 6");

// Fetch recent community posts
$postsQuery = $conn->query("SELECT cp.id, cp.title, cp.content, cp.image_url, cp.likes_count, cp.created_at, u.first_name, u.last_name, u.photo_url 
    FROM community_posts cp 
    JOIN users u ON cp.user_id = u.id 
    ORDER BY cp.likes_count DESC 
    LIMIT 3");
?>

<!-- ============================================================
     Hero Section with Interactive Search
============================================================= -->
<section class="hero-section">
  <div class="container">
    <div style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.4rem 1rem; background:rgba(99,102,241,0.15); border:1px solid rgba(99,102,241,0.3); border-radius:var(--radius-full); margin-bottom:1.5rem;">
      <span>🪷</span>
      <span style="font-size:0.85rem; font-weight:700; color:var(--text); letter-spacing:0.5px; text-transform:uppercase;">
        Incredible India — 2025/2026 Travel Guide
      </span>
    </div>

    <h1 class="hero-title">
      Explore the Soul of India, <br>
      <span class="text-gradient">One Majestic Journey at a Time</span>
    </h1>

    <p class="hero-subtitle">
      Plan seamless multi-city circuits across Rajasthan, Ladakh, Kerala, and Himachal. Track travel expenses in Indian Rupees (₹) and discover hand-crafted cultural experiences.
    </p>

    <!-- Interactive Travel Search Box -->
    <div class="search-box-card">
      <form action="city_search.php" method="GET" class="search-grid">
        <div class="input-float" style="margin-bottom:0;">
          <input type="search" name="q" id="searchDest" placeholder=" " autocomplete="off">
          <label for="searchDest">🔍 Search Destination or City (e.g. Jaipur, Manali...)</label>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <select name="region" id="searchRegion" style="padding: 0.95rem 1rem; background: rgba(13,19,34,0.9);">
            <option value="">🗺️ All Indian Regions</option>
            <option value="1">North India &amp; Himalayas</option>
            <option value="2">South India &amp; Backwaters</option>
            <option value="3">Western Palaces &amp; Deserts</option>
            <option value="4">East &amp; North-East</option>
            <option value="5">Islands &amp; Coral Paradises</option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <select name="budget_max" id="searchBudget" style="padding: 0.95rem 1rem; background: rgba(13,19,34,0.9);">
            <option value="">💰 Any Budget (₹)</option>
            <option value="2000">Under ₹2,000 / day</option>
            <option value="3500">₹2,000 - ₹3,500 / day</option>
            <option value="5000">₹3,500+ / day (Luxury)</option>
          </select>
        </div>

        <button type="submit" class="btn btn-sunset btn-lg" style="height: 100%;">
          <span>Find Trips →</span>
        </button>
      </form>
    </div>

    <!-- Quick Stats Metric Ticker -->
    <div style="display:flex; justify-content:center; gap:2.5rem; flex-wrap:wrap; margin-top:3.5rem;">
      <div style="text-align:center;">
        <div style="font-family:'Outfit',sans-serif; font-size:1.8rem; font-weight:800; color:var(--text);" class="text-gradient">18+</div>
        <div style="font-size:0.82rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Curated Cities</div>
      </div>
      <div style="text-align:center;">
        <div style="font-family:'Outfit',sans-serif; font-size:1.8rem; font-weight:800; color:var(--teal);">36+</div>
        <div style="font-size:0.82rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Adventure &amp; Culture</div>
      </div>
      <div style="text-align:center;">
        <div style="font-family:'Outfit',sans-serif; font-size:1.8rem; font-weight:800; color:var(--gold);">₹ INR</div>
        <div style="font-size:0.82rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Live Budget Tracker</div>
      </div>
      <div style="text-align:center;">
        <div style="font-family:'Outfit',sans-serif; font-size:1.8rem; font-weight:800; color:var(--accent);">50,000+</div>
        <div style="font-size:0.82rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Indian Explorers</div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     Top Regional Selections
============================================================= -->
<section style="padding: 4rem 0 3rem; background: var(--bg-subtle);">
  <div class="container">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2.5rem; flex-wrap:wrap; gap:1rem;">
      <div>
        <span class="badge badge-gold" style="margin-bottom:0.5rem;">Regional Highlights</span>
        <h2 style="font-size:2.2rem; font-weight:800;">Top Regional Selections</h2>
        <p class="text-muted">Explore the diverse geographies and cultures across India.</p>
      </div>
      <a href="city_search.php" class="btn btn-secondary btn-sm">View All Destinations →</a>
    </div>

    <div class="grid grid-3">
      <?php if ($regionsQuery && $regionsQuery->num_rows > 0): ?>
        <?php while ($r = $regionsQuery->fetch_assoc()): ?>
          <a href="city_search.php?region=<?php echo $r['id']; ?>" class="dest-card card-interactive" style="text-decoration:none;">
            <div class="dest-img-wrap" style="height: 220px;">
              <img src="<?php echo htmlspecialchars($r['image_url']); ?>" alt="<?php echo htmlspecialchars($r['name']); ?>" loading="lazy">
              <div class="dest-overlay">
                <span class="badge badge-primary">Region #<?php echo $r['id']; ?></span>
                <div>
                  <h3 style="color:#fff; font-size:1.3rem; margin-bottom:0.25rem;"><?php echo htmlspecialchars($r['name']); ?></h3>
                  <div style="font-size:0.82rem; color:rgba(255,255,255,0.8);"><?php echo htmlspecialchars($r['tagline']); ?></div>
                </div>
              </div>
            </div>
            <div class="dest-body" style="padding: 1.25rem;">
              <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:1rem; line-height:1.5;">
                <?php echo htmlspecialchars($r['description']); ?>
              </p>
              <div style="margin-top:auto; font-size:0.85rem; font-weight:700; color:var(--primary); display:flex; align-items:center; justify-content:space-between;">
                <span>Explore Region</span>
                <span>→</span>
              </div>
            </div>
          </a>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     Trending Destinations in India
============================================================= -->
<section style="padding: 4.5rem 0;">
  <div class="container">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2.5rem; flex-wrap:wrap; gap:1rem;">
      <div>
        <span class="badge badge-teal" style="margin-bottom:0.5rem;">Iconic Places</span>
        <h2 style="font-size:2.2rem; font-weight:800;">Featured Indian Destinations</h2>
        <p class="text-muted">Hand-picked destinations offering authentic heritage and adventure.</p>
      </div>
      <a href="city_search.php" class="btn btn-secondary btn-sm">Explore All 18 Cities →</a>
    </div>

    <div class="grid grid-3">
      <?php if ($citiesQuery && $citiesQuery->num_rows > 0): ?>
        <?php while ($city = $citiesQuery->fetch_assoc()): ?>
          <div class="dest-card card-interactive">
            <div class="dest-img-wrap">
              <img src="<?php echo htmlspecialchars($city['image_url']); ?>" alt="<?php echo htmlspecialchars($city['name']); ?>" loading="lazy">
              <div class="dest-overlay">
                <span class="badge badge-gold">★ <?php echo $city['popularity_score']; ?>/100 Score</span>
                <span class="badge badge-teal"><?php echo htmlspecialchars($city['best_time_to_visit'] ?? 'Best All Year'); ?></span>
              </div>
            </div>
            <div class="dest-body">
              <div style="font-size:0.8rem; text-transform:uppercase; font-weight:700; color:var(--text-dim); margin-bottom:0.25rem;">
                <?php echo htmlspecialchars($city['state']); ?> • <?php echo htmlspecialchars($city['region_name']); ?>
              </div>
              <h3 class="dest-title"><?php echo htmlspecialchars($city['name']); ?></h3>
              <p style="font-size:0.86rem; color:var(--text-muted); margin-bottom:1.25rem; line-height:1.5;">
                <?php echo htmlspecialchars($city['description']); ?>
              </p>
              <div class="dest-meta">
                <div>
                  <div style="font-size:0.75rem; color:var(--text-dim);">Avg Daily Cost</div>
                  <div class="dest-price">₹<?php echo number_format($city['avg_daily_cost']); ?> <span style="font-size:0.75rem; font-weight:normal; color:var(--text-muted);">/ day</span></div>
                </div>
                <a href="create_trip.php?city_id=<?php echo $city['id']; ?>" class="btn btn-primary btn-sm">+ Plan Here</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     Top Curated Experiences & Activities
============================================================= -->
<section style="padding: 4rem 0; background: var(--bg-subtle);">
  <div class="container">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2.5rem; flex-wrap:wrap; gap:1rem;">
      <div>
        <span class="badge badge-accent" style="margin-bottom:0.5rem;">Unforgettable Moments</span>
        <h2 style="font-size:2.2rem; font-weight:800;">Curated Activities &amp; Adventures</h2>
        <p class="text-muted">Must-do experiences across forts, rivers, coral reefs, and peaks.</p>
      </div>
      <a href="activity_search.php" class="btn btn-secondary btn-sm">Browse 36+ Activities →</a>
    </div>

    <div class="grid grid-3">
      <?php if ($activitiesQuery && $activitiesQuery->num_rows > 0): ?>
        <?php while ($act = $activitiesQuery->fetch_assoc()): ?>
          <div class="card card-interactive" style="padding:1.35rem; display:flex; flex-direction:column;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
              <span class="badge badge-primary"><?php echo htmlspecialchars($act['category']); ?></span>
              <span style="font-size:0.8rem; color:var(--text-dim); font-weight:600;">⏱️ <?php echo round($act['duration']/60, 1); ?> hrs</span>
            </div>
            
            <h4 style="font-size:1.15rem; margin-bottom:0.5rem; color:#fff;"><?php echo htmlspecialchars($act['name']); ?></h4>
            <div style="font-size:0.82rem; color:var(--teal); font-weight:700; margin-bottom:0.75rem;">
              📍 <?php echo htmlspecialchars($act['city_name']); ?>
            </div>
            
            <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.5; margin-bottom:1.25rem;">
              <?php echo htmlspecialchars($act['description']); ?>
            </p>

            <div style="margin-top:auto; display:flex; justify-content:space-between; align-items:center; padding-top:1rem; border-top:1px solid var(--border);">
              <div>
                <span style="font-size:0.75rem; color:var(--text-dim);">Estimated Price</span>
                <div style="font-family:'Outfit',sans-serif; font-size:1.15rem; font-weight:800; color:var(--gold);">
                  <?php echo ($act['cost'] > 0) ? '₹' . number_format($act['cost']) : '<span style="color:var(--success);">Free Entry</span>'; ?>
                </div>
              </div>
              <a href="activity_search.php?q=<?php echo urlencode($act['name']); ?>" class="btn btn-secondary btn-sm">Details →</a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     Traveler Stories & Community
============================================================= -->
<section style="padding: 4.5rem 0;">
  <div class="container">
    <div style="text-align:center; max-width:650px; margin:0 auto 3rem;">
      <span class="badge badge-gold" style="margin-bottom:0.5rem;">Real Explorers</span>
      <h2 style="font-size:2.2rem; font-weight:800;">Stories From The Road</h2>
      <p class="text-muted">Read genuine reflections and pro-tips from globetrotters traveling across India.</p>
    </div>

    <div class="grid grid-3">
      <?php if ($postsQuery && $postsQuery->num_rows > 0): ?>
        <?php while ($post = $postsQuery->fetch_assoc()): ?>
          <div class="card" style="display:flex; flex-direction:column; padding:1.5rem;">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
              <img
                src="<?php echo htmlspecialchars($post['photo_url']); ?>"
                alt="<?php echo htmlspecialchars($post['first_name']); ?>"
                style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid var(--primary);"
              >
              <div>
                <div style="font-size:0.95rem; font-weight:700; color:#fff;">
                  <?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?>
                </div>
                <div style="font-size:0.78rem; color:var(--text-dim);">
                  <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                </div>
              </div>
            </div>

            <h4 style="font-size:1.1rem; margin-bottom:0.5rem;"><?php echo htmlspecialchars($post['title']); ?></h4>
            <p style="font-size:0.86rem; color:var(--text-muted); line-height:1.6; margin-bottom:1.25rem;">
              <?php echo htmlspecialchars($post['content']); ?>
            </p>

            <div style="margin-top:auto; display:flex; justify-content:space-between; align-items:center; font-size:0.82rem; color:var(--text-dim); padding-top:0.75rem; border-top:1px solid var(--border);">
              <span>❤️ <?php echo $post['likes_count']; ?> Likes</span>
              <a href="community.php" style="color:var(--primary); font-weight:600;">Join Conversation →</a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     CTA Banner
============================================================= -->
<section style="padding: 4.5rem 0; background: linear-gradient(135deg, rgba(99,102,241,0.15) 0%, rgba(236,72,153,0.1) 100%), var(--surface-card); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); text-align:center;">
  <div class="container container-narrow">
    <span class="badge badge-sunset" style="margin-bottom:1rem;">Your Next Adventure Awaits</span>
    <h2 style="font-size:2.5rem; font-weight:900; margin-bottom:1rem;">Ready to Experience Incredible India?</h2>
    <p class="text-muted" style="font-size:1.1rem; margin-bottom:2rem; max-width:600px; margin-left:auto; margin-right:auto;">
      Create custom itineraries, manage multi-city stops, track your expenses in Rupees, and share your journey with fellow travelers.
    </p>

    <div style="display:flex; justify-content:center; gap:1rem; flex-wrap:wrap;">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="create_trip.php" class="btn btn-primary btn-lg">+ Plan a New Journey</a>
        <a href="dashboard.php" class="btn btn-secondary btn-lg">Go to Dashboard →</a>
      <?php else: ?>
        <a href="register.php" class="btn btn-sunset btn-lg">Create Free Account 🚀</a>
        <a href="login.php" class="btn btn-secondary btn-lg">Sign In →</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
