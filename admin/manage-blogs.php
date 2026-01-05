<?php
// =====================================
// 2) manage-blogs.php  (ADMIN PAGE)
// =====================================
session_start();

// ✅ Admin Auth (redirect if not logged in)
if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header("Location: login.php");
    exit();
}

require('../config/connection.php'); // update path if different

// -------------------------------
// Helpers
// -------------------------------
function make_slug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'post';
}
function sanitize_blog_html($html) {
    $allowed = '<p><br><b><strong><i><em><u><ul><ol><li><h2><h3><blockquote><a>';
    $html = strip_tags($html, $allowed);

    // prevent javascript: links
    $html = preg_replace_callback('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>/i', function($m) {
        $href = $m[1];
        if (preg_match('/^\s*javascript:/i', $href)) return '<a href="#">';
        return $m[0];
    }, $html);

    return $html;
}
function unique_slug($con, $baseSlug, $excludeId = 0) {
    $slug = $baseSlug;
    $i = 1;

    while (true) {
        if ($excludeId > 0) {
            $stmt = $con->prepare("SELECT id FROM blogs WHERE slug=? AND id!=? LIMIT 1");
            $stmt->bind_param("si", $slug, $excludeId);
        } else {
            $stmt = $con->prepare("SELECT id FROM blogs WHERE slug=? LIMIT 1");
            $stmt->bind_param("s", $slug);
        }
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if (!$exists) return $slug;
        $slug = $baseSlug . "-" . $i;
        $i++;
    }
}

// -------------------------------
// Upload featured image (POST)
// -------------------------------
$uploadError = "";
if (isset($_POST['upload_image']) && isset($_FILES['featured_image'])) {
    $file = $_FILES['featured_image'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg','image/png','image/webp'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes)) {
            $uploadError = "Only JPG, PNG, WEBP allowed.";
        } else {
            $dir = __DIR__ . "/uploads/blogs";
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            $ext = ($mime === 'image/png') ? 'png' : (($mime === 'image/webp') ? 'webp' : 'jpg');
            $name = "blog_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;

            $dest = $dir . "/" . $name;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $_POST['featured_image_path'] = "uploads/blogs/" . $name;
            } else {
                $uploadError = "Image upload failed.";
            }
        }
    } else {
        $uploadError = "Image upload failed.";
    }
}

// -------------------------------
// Add / Update Blog (POST)
// -------------------------------
if (isset($_POST['save_blog'])) {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $status = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
    $content = sanitize_blog_html($_POST['content'] ?? '');
    $featured_image = trim($_POST['featured_image'] ?? '');

    // If image uploaded in this request, use it
    if (!empty($_POST['featured_image_path'])) {
        $featured_image = $_POST['featured_image_path'];
    }

    if ($title === '' || trim(strip_tags($content)) === '') {
        // ignore (or show message)
    } else {
        $baseSlug = make_slug($title);

        if ($id > 0) {
            $slug = unique_slug($con, $baseSlug, $id);
            $stmt = $con->prepare("UPDATE blogs SET title=?, slug=?, content=?, featured_image=?, status=? WHERE id=?");
            $stmt->bind_param("sssssi", $title, $slug, $content, $featured_image, $status, $id);
            $stmt->execute();
            $stmt->close();
        } else {
            $slug = unique_slug($con, $baseSlug);
            $stmt = $con->prepare("INSERT INTO blogs (title, slug, content, featured_image, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $title, $slug, $content, $featured_image, $status);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: manage-blogs.php");
    exit();
}

// -------------------------------
// Delete Blog (GET)
// -------------------------------
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $con->prepare("DELETE FROM blogs WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage-blogs.php");
    exit();
}

// -------------------------------
// Edit (GET)
// -------------------------------
$edit = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $con->prepare("SELECT * FROM blogs WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $edit = $res->fetch_assoc();
    $stmt->close();
}

// List all blogs
$resAll = $con->query("SELECT id, title, slug, featured_image, status, created_at FROM blogs ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Blogs</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/css/bootstrap.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/js/bootstrap.bundle.min.js"></script>

  <!-- TinyMCE like WP editor -->
  <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>

  <style>
    .wrap{max-width:1200px;margin:30px auto;padding:0 14px;}
    .thumb{width:90px;height:60px;object-fit:cover;border-radius:8px;border:1px solid #eee;background:#fafafa}
  </style>
</head>
<body class="bg-light">

<div class="wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="m-0">Manage Blogs</h2>
    <div class="d-flex gap-2">
      <a href="blogs.php" class="btn btn-outline-secondary">View Blogs</a>
      <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
  </div>

  <?php if ($uploadError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($uploadError); ?></div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5><?php echo $edit ? "Edit Blog" : "Add Blog"; ?></h5>

          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $edit['id'] ?? 0; ?>">

            <div class="mb-2">
              <label class="form-label">Title</label>
              <input class="form-control" name="title" required value="<?php echo htmlspecialchars($edit['title'] ?? ''); ?>">
            </div>

            <div class="mb-2">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="published" <?php echo (($edit['status'] ?? '')==='published')?'selected':''; ?>>Published</option>
                <option value="draft" <?php echo (($edit['status'] ?? '')==='draft')?'selected':''; ?>>Draft</option>
              </select>
            </div>

            <div class="mb-2">
              <label class="form-label">Featured Image</label>
              <input type="file" class="form-control" name="featured_image" accept="image/*">
              <input type="hidden" name="featured_image" value="<?php echo htmlspecialchars($edit['featured_image'] ?? ''); ?>">
              <input type="hidden" name="upload_image" value="1">

              <?php if (!empty($edit['featured_image'])): ?>
                <div class="mt-2">
                  <img class="thumb" src="<?php echo htmlspecialchars($edit['featured_image']); ?>" alt="">
                </div>
              <?php endif; ?>
              <div class="form-text">Uploading a new image will replace old image.</div>
            </div>

            <div class="mb-2">
              <label class="form-label">Description</label>
              <textarea id="content" name="content" class="form-control" rows="10"><?php echo htmlspecialchars($edit['content'] ?? ''); ?></textarea>
            </div>

            <div class="d-flex gap-2">
              <button class="btn btn-primary" name="save_blog" type="submit">Save</button>
              <a class="btn btn-outline-secondary" href="manage-blogs.php">Clear</a>
            </div>
          </form>

        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="m-0">All Blogs</h5>
            <input class="form-control" style="max-width:260px" id="search" placeholder="Search..." oninput="searchTable()"/>
          </div>

          <div class="table-responsive mt-3">
            <table class="table table-striped align-middle" id="blogsTable">
              <thead>
                <tr>
                  <th>Image</th>
                  <th>Title</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th style="width:170px">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($resAll && $resAll->num_rows > 0): ?>
                  <?php while($b = $resAll->fetch_assoc()): ?>
                    <tr>
                      <td>
                        <?php if(!empty($b['featured_image'])): ?>
                          <img class="thumb" src="<?php echo htmlspecialchars($b['featured_image']); ?>" alt="">
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="fw-semibold"><?php echo htmlspecialchars($b['title']); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($b['slug']); ?></div>
                      </td>
                      <td>
                        <span class="badge <?php echo $b['status']==='published'?'bg-success':'bg-secondary'; ?>">
                          <?php echo htmlspecialchars($b['status']); ?>
                        </span>
                      </td>
                      <td class="small text-muted"><?php echo date("d M Y H:i", strtotime($b['created_at'])); ?></td>
                      <td>
                        <a class="btn btn-sm btn-warning" href="manage-blogs.php?edit=<?php echo $b['id']; ?>">Edit</a>
                        <a class="btn btn-sm btn-danger"
                           href="manage-blogs.php?delete=<?php echo $b['id']; ?>"
                           onclick="return confirm('Delete this blog?')">Delete</a>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="5">No blogs found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>

  </div>
</div>

<script>
tinymce.init({
  selector: '#content',
  height: 280,
  menubar: false,
  plugins: 'link lists',
  toolbar: 'undo redo | bold italic underline | bullist numlist | link',
});

function searchTable() {
  const input = document.getElementById("search").value.toLowerCase();
  const rows = document.querySelectorAll("#blogsTable tbody tr");
  rows.forEach(r => {
    const text = r.innerText.toLowerCase();
    r.style.display = text.includes(input) ? "" : "none";
  });
}
</script>

</body>
</html>