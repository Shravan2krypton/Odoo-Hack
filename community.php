<?php
include 'includes/db_connect.php';
session_start();

$message = '';

// Handle New Post Creation (Requires Auth)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_post') {
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php?auth=required");
        exit();
    }
    
    $userId  = $_SESSION['user_id'];
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $tripId  = !empty($_POST['trip_id']) ? intval($_POST['trip_id']) : null;
    $imgUrl  = trim($_POST['image_url'] ?? '');

    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO community_posts (user_id, trip_id, title, content, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $userId, $tripId, $title, $content, $imgUrl);
        if ($stmt->execute()) {
            $message = "Your travel story has been published to the community! ✨";
        }
    }
}

// Handle Like Action
if (isset($_POST['like_post_id'])) {
    $postId = intval($_POST['like_post_id']);
    $conn->query("UPDATE community_posts SET likes_count = likes_count + 1 WHERE id = {$postId}");
    header("Location: community.php?liked=1");
    exit();
}

// Fetch all public community posts with user details
$postsQuery = $conn->query("SELECT cp.*, u.first_name, u.last_name, u.photo_url, t.name AS trip_name, t.share_slug 
    FROM community_posts cp 
    JOIN users u ON cp.user_id = u.id 
    LEFT JOIN trips t ON cp.trip_id = t.id 
    WHERE cp.public = TRUE 
    ORDER BY cp.created_at DESC");
$posts = $postsQuery->fetch_all(MYSQLI_ASSOC);

// Fetch user's trips if logged in
$userTrips = [];
if (isset($_SESSION['user_id'])) {
    $uTripsQuery = $conn->query("SELECT id, name FROM trips WHERE user_id = {$_SESSION['user_id']} ORDER BY start_date DESC");
    $userTrips = $uTripsQuery->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = "GlobeTrotter India Community & Stories";
include 'includes/header.php';
?>

<div class="main-content">
  <div class="container">
    
    <!-- Top Header -->
    <div style="text-align:center; max-width:700px; margin:0 auto 3rem;">
      <span class="badge badge-gold" style="margin-bottom:0.5rem;">Indian Travel Community</span>
      <h1 style="font-size:2.6rem; font-weight:900;">Traveler Stories &amp; Insights</h1>
      <p class="text-muted" style="font-size:1.05rem;">
        Discover insider tips, itineraries, and stories shared by explorers traveling through Rajasthan, Ladakh, Kerala, and across India.
      </p>
    </div>

    <?php if (!empty($message)): ?>
      <div class="alert alert-success" style="max-width:800px; margin:0 auto 2rem;">
        <span>🎉</span>
        <span><?php echo htmlspecialchars($message); ?></span>
      </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 2fr 1.2fr; gap:2.5rem; align-items:start;">
      
      <!-- Left Column: Community Feed -->
      <div style="display:flex; flex-direction:column; gap:2rem;">
        <?php if (empty($posts)): ?>
          <div class="card" style="text-align:center; padding:3rem;">
            <p class="text-muted">No community posts yet. Be the first explorer to share your story!</p>
          </div>
        <?php else: ?>
          <?php foreach ($posts as $post): ?>
            <div class="card" style="padding:2rem;">
              <!-- Author Header -->
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                <div style="display:flex; align-items:center; gap:0.85rem;">
                  <img
                    src="<?php echo htmlspecialchars($post['photo_url']); ?>"
                    alt="<?php echo htmlspecialchars($post['first_name']); ?>"
                    style="width:48px; height:48px; border-radius:50%; object-fit:cover; border:2px solid var(--primary);"
                  >
                  <div>
                    <div style="font-weight:700; color:#fff; font-size:1.05rem;">
                      <?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?>
                    </div>
                    <div style="font-size:0.8rem; color:var(--text-dim);">
                      Published <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                    </div>
                  </div>
                </div>

                <?php if (!empty($post['trip_name'])): ?>
                  <a href="itinerary_view.php?trip_id=<?php echo $post['trip_id']; ?>" class="badge badge-teal" style="text-decoration:none;">
                    🗺️ <?php echo htmlspecialchars($post['trip_name']); ?>
                  </a>
                <?php endif; ?>
              </div>

              <!-- Post Title & Content -->
              <h2 style="font-size:1.45rem; font-weight:800; margin-bottom:0.75rem; color:#fff;">
                <?php echo htmlspecialchars($post['title']); ?>
              </h2>
              
              <p style="font-size:0.95rem; color:var(--text-muted); line-height:1.65; margin-bottom:1.5rem;">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
              </p>

              <?php if (!empty($post['image_url'])): ?>
                <div style="border-radius:var(--radius); overflow:hidden; margin-bottom:1.5rem; max-height:300px;">
                  <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Story Photo" style="width:100%; height:100%; object-fit:cover;">
                </div>
              <?php endif; ?>

              <!-- Footer Toolbar (Like & Comment) -->
              <div style="display:flex; justify-content:space-between; align-items:center; padding-top:1rem; border-top:1px solid var(--border);">
                <form method="POST" action="community.php" style="display:inline;">
                  <input type="hidden" name="like_post_id" value="<?php echo $post['id']; ?>">
                  <button type="submit" class="btn btn-secondary btn-sm" style="border-radius:var(--radius-full);">
                    <span>❤️</span>
                    <span><?php echo $post['likes_count']; ?> Helpful Likes</span>
                  </button>
                </form>

                <?php if (!empty($post['share_slug'])): ?>
                  <a href="share_trip.php?slug=<?php echo $post['share_slug']; ?>" class="btn btn-primary btn-sm">
                    View Full Itinerary →
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Right Column: Share Your Story Form -->
      <div class="card" style="padding:2rem; position:sticky; top:5.5rem;">
        <span class="badge badge-primary" style="margin-bottom:0.5rem;">Community Voice</span>
        <h3 style="font-size:1.35rem; margin-bottom:1rem;">Share Your Experience ✍️</h3>

        <?php if (isset($_SESSION['user_id'])): ?>
          <form method="POST" action="community.php">
            <input type="hidden" name="action" value="new_post">

            <div class="form-group">
              <label class="form-label">Story Headline *</label>
              <input type="text" name="title" placeholder="e.g. 5 Hidden Gems in Udaipur or Leh Packing Tips" required>
            </div>

            <?php if (!empty($userTrips)): ?>
              <div class="form-group">
                <label class="form-label">Link to Your Trip (Optional)</label>
                <select name="trip_id">
                  <option value="">-- General Indian Travel Tip --</option>
                  <?php foreach ($userTrips as $ut): ?>
                    <option value="<?php echo $ut['id']; ?>"><?php echo htmlspecialchars($ut['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endif; ?>

            <div class="form-group">
              <label class="form-label">Story / Advice *</label>
              <textarea name="content" placeholder="Share your experience, favourite restaurants, things to avoid, or seasonal tips..." rows="5" required></textarea>
            </div>

            <div class="form-group">
              <label class="form-label">Photo URL (Optional)</label>
              <input type="text" name="image_url" placeholder="https://images.unsplash.com/...">
            </div>

            <button type="submit" class="btn btn-sunset btn-block">
              <span>Publish Story to Community 🌟</span>
            </button>
          </form>
        <?php else: ?>
          <div style="text-align:center; padding:2rem 1rem;">
            <p class="text-muted" style="margin-bottom:1.5rem;">Sign in to join the discussion and share your travels.</p>
            <a href="login.php" class="btn btn-primary btn-block">Sign In to Post</a>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
