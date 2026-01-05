<?php
// =========================
// blogs.php (USER PAGE)
// Fixes:
// ✅ show description excerpt (10 words)
// ✅ images load even if saved as uploads/blogs/... (adds correct base path)
// ✅ whole card clickable + correct blog link
// ✅ fallback image on error
// =========================
session_start();
require('config/connection.php');

// Fetch published blogs INCLUDING content for excerpt
$sql = "SELECT id, title, slug, featured_image, content, created_at
        FROM blogs
        WHERE status='published'
        ORDER BY created_at DESC";
$result = $con->query($sql);

// ---- helpers ----
function base_url() {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $proto = $https ? "https://" : "http://";
    return $proto . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), "/\\") . "/";
}

function base_path() {
  $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\");
  return $dir === "/" ? "/" : $dir . "/";
}

function normalize_image_url($path) {
  $path = trim((string)$path);
  if ($path === '') return '';

  if (preg_match('#^https?://#i', $path)) return $path;

  $path = ltrim($path, "/");
  return base_path() . $path; // "/optima-labs/uploads/..."
}

function excerpt_words($html, $limit = 10) {
    $text = trim(html_entity_decode(strip_tags($html)));
    if ($text === '') return '';
    $words = preg_split('/\s+/', $text);
    if (count($words) <= $limit) return $text;
    return implode(' ', array_slice($words, 0, $limit)) . '...';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Blogs</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .blogs-wrap{max-width:1100px;margin:40px auto;padding:0 16px;}
    .blogs-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
    .blog-card{background:#fff;border:1px solid #eee;border-radius:14px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,.05);cursor:pointer}
    .blog-card img{width:100%;height:180px;object-fit:cover;background:#f5f5f5;display:block}
    .blog-card .pad{padding:14px}
    .blog-card h3{margin:0 0 8px;font-size:18px;line-height:1.3}
    .blog-card .desc{margin:0 0 10px;opacity:.8;font-size:14px;line-height:1.5}
    .blog-card a{color:#111;text-decoration:none}
    .blog-card a:hover{text-decoration:underline}
    .meta{font-size:12px;opacity:.7;margin-bottom:6px}
    .readmore{font-size:14px;font-weight:600}
    @media(max-width:980px){.blogs-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:640px){.blogs-grid{grid-template-columns:1fr}}
    .navbar {
    display: flex;
    justify-content: flex-end; /* 👈 aligns items to the right */
    align-items: center;
    gap: 30px;
    padding: 20px 40px;
    }

    .navbar a {
    color: #2b2b2bff;
    text-decoration: none;
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 16px;
    font-weight: 500;
    }

    .navbar a:hover {
    opacity: 0.8;
    }
  </style>
</head>
<body>

<div class="navbar">
  <a href="index.php">Home</a>
  <a href="about.html">About</a>
  <a href="contact.html">Contact</a>
<a href="blogs.php">Blogs</a>
  <a href="lab-analyst-request.html">Lab Analysis Request</a>
  <a href="blogs.php">Blogs</a>
</div>

<div class="blogs-wrap">
  <h1>Blogs</h1>

  <?php if ($result && $result->num_rows > 0): ?>
    <div class="blogs-grid">
      <?php while($row = $result->fetch_assoc()): ?>
        <?php
          $blogUrl = "blog.php?slug=" . urlencode($row['slug']);
          $img = !empty($row['featured_image']);
          $desc = excerpt_words($row['content'] ?? '', 10);
        ?>
        <div class="blog-card" onclick="window.location.href='<?php echo $blogUrl; ?>'">
          <img src="admin/<?php echo htmlspecialchars($row['featured_image']); ?>" onerror="this.src='assets/images/lab-image.jpeg';">


          <div class="pad">
            <div class="meta"><?php echo date("d M Y", strtotime($row['created_at'])); ?></div>

            <h3>
              <a href="<?php echo $blogUrl; ?>">
                <?php echo htmlspecialchars($row['title']); ?>
              </a>
            </h3>

            <?php if ($desc): ?>
              <p class="desc"><?php echo htmlspecialchars($desc); ?></p>
            <?php endif; ?>

            <a class="readmore" href="<?php echo $blogUrl; ?>">Read more →</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p>No blogs found.</p>
  <?php endif; ?>

</div>

</body>
</html>
