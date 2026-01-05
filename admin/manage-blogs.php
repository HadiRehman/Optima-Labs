<?php
session_start();

// ✅ Admin Auth
if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header("Location: login.php");
    exit();
}

require('../config/connection.php');

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

if(isset($_POST['btn-out'])){
    session_destroy();
    header("Location: login.php");
    exit();
}

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

    // block javascript: links
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

// IMPORTANT: admin page is in /admin, so show images with ../
function img_url_admin($path){
    $path = trim((string)$path);
    if ($path === '') return '';
    if (preg_match('#^https?://#i', $path)) return $path;

    $path = ltrim($path, "/");     // "uploads/blogs/xx.jpg"
    return "../" . $path;          // "../uploads/blogs/xx.jpg"
}

// -------------------------------
// Upload featured image
// -------------------------------
$uploadError = "";
$uploadedPath = "";

if (isset($_FILES['featured_image']) && !empty($_FILES['featured_image']['name'])) {
    $file = $_FILES['featured_image'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg','image/png','image/webp'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes)) {
            $uploadError = "Only JPG, PNG, WEBP allowed.";
        } else {
            // Save into project root uploads/blogs
            // __DIR__ is /admin -> go one level up
            $dir = dirname(__DIR__) . "/uploads/blogs";
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            $ext = ($mime === 'image/png') ? 'png' : (($mime === 'image/webp') ? 'webp' : 'jpg');
            $name = "blog_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;

            $dest = $dir . "/" . $name;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                // store in DB relative path from root
                $uploadedPath = "uploads/blogs/" . $name;
            } else {
                $uploadError = "Image upload failed.";
            }
        }
    } else {
        $uploadError = "Image upload failed.";
    }
}

// -------------------------------
// Add / Update Blog
// -------------------------------
if (isset($_POST['save_blog'])) {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $status = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
    $content = sanitize_blog_html($_POST['content'] ?? '');

    $featured_image = trim($_POST['featured_image_old'] ?? '');
    if ($uploadedPath !== "") {
        $featured_image = $uploadedPath;
    }

    if ($title !== '' && trim(strip_tags($content)) !== '') {
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
// Delete Blog
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
// Edit Blog
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

// List all
$resAll = $con->query("SELECT id, title, slug, featured_image, status, created_at FROM blogs ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Blogs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/css/bootstrap.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />

    <!-- ✅ Free TinyMCE (no API key) -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>

    <style>
        body { font-family: Arial, sans-serif; }
        .thumb{
          width: 90px; height: 60px; object-fit: cover;
          border-radius: 8px; border: 1px solid #eee; background: #fafafa;
        }
        .editor-wrap .tox{ border-radius: 10px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <!-- ✅ Sidebar -->
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-light">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                <a href="index.php" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
                    <span class="fs-5 d-none d-sm-inline">Portal</span>
                </a>

                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link align-middle px-0 text-dark">
                            <i class="fs-4 bi-house"></i> <span class="ms-1 d-none d-sm-inline">Home</span>
                        </a>
                    </li>

                    <li>
                        <a href="generate-certificate.php" class="nav-link px-0 align-middle text-dark">
                            <i class="bi bi-cloud-arrow-up"></i> <span class="ms-1 d-none d-sm-inline">Generate Certificate</span>
                        </a>
                    </li>

                    <li>
                        <a href="certificates.php" class="nav-link px-0 align-middle text-dark">
                            <i class="bi bi-map"></i> <span class="ms-1 d-none d-sm-inline">Certificates</span>
                        </a>
                    </li>

                    <li>
                        <a href="manage-blogs.php" class="nav-link px-0 align-middle text-dark active">
                            <i class="bi bi-journal-text"></i> <span class="ms-1 d-none d-sm-inline">Manage Blogs</span>
                        </a>
                    </li>
                </ul>

                <hr />

                <!-- ✅ User dropdown -->
                <div class="dropdown pb-4">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                       id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRvts5aHBstDkR8PigS4RmZkbZy78zpZoSuOw&s"
                             alt="user" width="30" height="30" class="rounded-circle" />
                        <span class="d-none d-sm-inline mx-1">Admin</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                        <li>
                            <form method="post">
                                <button class="dropdown-item" name="btn-out">Sign out</button>
                            </form>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- ✅ Main Content -->
        <div class="col py-3 bg-light">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="m-0">Manage Blogs</h2>
                <a href="../blogs.php" class="btn btn-outline-secondary">View Blogs</a>
            </div>

            <?php if ($uploadError): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($uploadError); ?></div>
            <?php endif; ?>

            <div class="row g-3">

                <!-- ✅ Left: Add/Edit -->
                <div class="col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3"><?php echo $edit ? "Edit Blog" : "Add Blog"; ?></h5>

                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo $edit['id'] ?? 0; ?>">

                                <div class="mb-2">
                                    <label class="form-label">Title</label>
                                    <input class="form-control" name="title" required
                                           value="<?php echo htmlspecialchars($edit['title'] ?? ''); ?>">
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
                                    <input type="hidden" name="featured_image_old" value="<?php echo htmlspecialchars($edit['featured_image'] ?? ''); ?>">

                                    <?php if (!empty($edit['featured_image'])): ?>
                                        <div class="mt-2">
                                            <img class="thumb" src="<?php echo htmlspecialchars(img_url_admin($edit['featured_image'])); ?>"
                                                 onerror="this.onerror=null;this.src='../assets/images/lab-image.jpeg';" alt="">
                                        </div>
                                    <?php endif; ?>

                                    <div class="form-text">Uploading a new image replaces the old one.</div>
                                </div>

                                <div class="mb-2 editor-wrap">
                                    <label class="form-label">Description</label>
                                    <textarea id="content" name="content" class="form-control" rows="10"><?php
                                        echo htmlspecialchars($edit['content'] ?? '');
                                    ?></textarea>
                                </div>

                                <div class="d-flex gap-2 mt-3">
                                    <button class="btn btn-primary" name="save_blog" type="submit">Save</button>
                                    <a class="btn btn-outline-secondary" href="manage-blogs.php">Clear</a>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                <!-- ✅ Right: List -->
                <div class="col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-0">All Blogs</h5>
                                <input class="form-control" style="max-width:260px" id="search"
                                       placeholder="Search..." oninput="searchTable()"/>
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
                                                        <img class="thumb"
                                                             src="<?php echo htmlspecialchars(img_url_admin($b['featured_image'])); ?>"
                                                             onerror="this.onerror=null;this.src='../assets/images/lab-image.jpeg';"
                                                             alt="">
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

                                                <td class="small text-muted">
                                                    <?php echo date("d M Y H:i", strtotime($b['created_at'])); ?>
                                                </td>

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

            </div><!-- row -->

        </div><!-- col main -->
    </div><!-- row -->
</div><!-- container -->

<script>
tinymce.init({
  selector: '#content',
  height: 320,
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