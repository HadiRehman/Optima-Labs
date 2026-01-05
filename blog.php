<?php
session_start();
require('config/connection.php');

function e($str){ return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

function base_path() {
  // returns "/optima-labs/" (folder name auto)
  $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\");
  return $dir === "/" ? "/" : $dir . "/";
}

function normalize_image_url($path) {
  $path = trim((string)$path);
  if ($path === '') return '';

  // already absolute
  if (preg_match('#^https?://#i', $path)) return $path;

  $path = ltrim($path, "/"); // "uploads/blogs/..."
  return base_path() . $path; // "/optima-labs/uploads/blogs/..."
}

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
  http_response_code(404);
  echo "Blog not found";
  exit;
}

$stmt = $con->prepare("SELECT title, content, featured_image, created_at 
                       FROM blogs 
                       WHERE slug=? AND status='published'
                       LIMIT 1");
$stmt->bind_param("s", $slug);
$stmt->execute();
$res = $stmt->get_result();
$blog = $res->fetch_assoc();
$stmt->close();

if (!$blog) {
  http_response_code(404);
  echo "Blog not found";
  exit;
}

$img = !empty($blog['featured_image'])
  ? normalize_image_url($blog['featured_image'])
  : "assets/images/lab-image.jpeg";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo e($blog['title']); ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .wrap{max-width:900px;margin:40px auto;padding:0 16px;}
    .hero{border-radius:16px;overflow:hidden;border:1px solid #eee;background:#f5f5f5}
    .hero img{width:100%;height:360px;object-fit:cover;display:block}
    .meta{opacity:.7;font-size:13px;margin:14px 0}
    .content{background:#fff;border:1px solid #eee;border-radius:16px;padding:18px;box-shadow:0 8px 20px rgba(0,0,0,.04)}
    .content a{word-break:break-word}
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

<div class="wrap">
  <h1><?php echo e($blog['title']); ?></h1>
  <div class="meta"><?php echo date("d M Y", strtotime($blog['created_at'])); ?></div>

  <div class="hero">
    <img src="admin/<?php echo $blog['featured_image']; ?>" alt=""
         onerror="this.onerror=null;this.src='assets/images/lab-image.jpeg';">
  </div>

  <div class="content" style="margin-top:16px;">
    <!-- content saved from editor already contains <b><i><u><a> etc -->
    <?php echo $blog['content']; ?>
  </div>
</div>

</body>
</html>
