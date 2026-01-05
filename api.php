<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");

// Set API key (Use environment variables in production)
$API_KEY = "65468dggfyhmasbfk937";

// Check API Key
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }
}

$headers = getallheaders();
// More robust header access
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

// Compare raw value directly
if (trim($authHeader) !== $API_KEY) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Connect to database (prefer environment variables in production)
$conn = mysqli_connect("localhost", "u829380813_optimalabs", "Optimalabs@123", "u829380813_optimalabs_db");
if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "DB Connection failed: " . mysqli_connect_error()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

function esc($conn, $str) {
    return $conn->real_escape_string(trim($str));
}

function make_slug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'post';
}

function sanitize_blog_html($html) {
    // allow basic WP-like tags
    $allowed = '<p><br><b><strong><i><em><u><ul><ol><li><h2><h3><blockquote><a>';
    $html = strip_tags($html, $allowed);

    // prevent javascript: links
    $html = preg_replace_callback('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>/i', function($m) {
        $href = $m[1];
        if (preg_match('/^\s*javascript:/i', $href)) {
            return '<a href="#">';
        }
        return $m[0];
    }, $html);

    return $html;
}

function unique_slug($conn, $baseSlug, $excludeId = 0) {
    $slug = $baseSlug;
    $i = 1;

    while (true) {
        if ($excludeId > 0) {
            $stmt = $conn->prepare("SELECT id FROM blogs WHERE slug = ? AND id != ? LIMIT 1");
            $stmt->bind_param("si", $slug, $excludeId);
        } else {
            $stmt = $conn->prepare("SELECT id FROM blogs WHERE slug = ? LIMIT 1");
            $stmt->bind_param("s", $slug);
        }
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if (!$exists) return $slug;

        $slug = $baseSlug . '-' . $i;
        $i++;
    }
}


switch ($action) {
    case 'getcertificates':
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(["error" => "Method Not Allowed"]);
            exit;
        }

        $result = $conn->query("SELECT * FROM certificates");
        $certificates = [];

        while ($row = $result->fetch_assoc()) {
            $certificates[] = $row;
        }

        echo json_encode($certificates);
        exit;
    
    case 'uploadBlogImage':
        if ($method !== 'POST') { http_response_code(405); echo json_encode(["error"=>"Method Not Allowed"]); break; }

        if (!isset($_FILES['image'])) {
            http_response_code(400);
            echo json_encode(["error" => "image file is required"]);
            break;
        }

        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(["error" => "Upload failed"]);
            break;
        }

        $allowedTypes = ['image/jpeg','image/png','image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes)) {
            http_response_code(415);
            echo json_encode(["error" => "Only JPG, PNG, WEBP allowed"]);
            break;
        }

        $uploadDir = __DIR__ . "/uploads/blogs";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = ($mime === 'image/png') ? 'png' : (($mime === 'image/webp') ? 'webp' : 'jpg');
        $name = "blog_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;

        $dest = $uploadDir . "/" . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            http_response_code(500);
            echo json_encode(["error" => "Could not save file"]);
            break;
        }

        // return a public path (adjust if needed)
        $publicPath = "uploads/blogs/" . $name;
        echo json_encode(["success" => true, "path" => $publicPath]);
        break;

    case 'addblog':
        if ($method !== 'POST') break;

        $title = esc($conn, $input['title'] ?? '');
        $contentRaw = $input['content'] ?? '';
        $content = sanitize_blog_html($contentRaw);
        $featured = esc($conn, $input['featured_image'] ?? '');
        $status = esc($conn, $input['status'] ?? 'published');

        if (!$title || !$content) {
            http_response_code(400);
            echo json_encode(["error" => "Required fields: title, content"]);
            break;
        }

        $baseSlug = make_slug($title);
        $slug = unique_slug($conn, $baseSlug);

        $stmt = $conn->prepare("INSERT INTO blogs (title, slug, content, featured_image, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $slug, $content, $featured, $status);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "id" => $stmt->insert_id, "slug" => $slug]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to add blog"]);
        }
        $stmt->close();
        break;

    case 'getblogs':
        if ($method !== 'GET') break;

        $status = isset($_GET['status']) ? esc($conn, $_GET['status']) : 'published';

        $stmt = $conn->prepare("SELECT id, title, slug, featured_image, status, created_at FROM blogs WHERE status = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $res = $stmt->get_result();

        $blogs = [];
        while ($row = $res->fetch_assoc()) $blogs[] = $row;

        echo json_encode(["success" => true, "blogs" => $blogs]);
        $stmt->close();
        break;

    case 'getblog':
        if ($method !== 'GET') break;

        $slug = isset($_GET['slug']) ? esc($conn, $_GET['slug']) : '';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if (!$slug && !$id) {
            http_response_code(400);
            echo json_encode(["error" => "Provide slug or id"]);
            break;
        }

        if ($slug) {
            $stmt = $conn->prepare("SELECT * FROM blogs WHERE slug = ? LIMIT 1");
            $stmt->bind_param("s", $slug);
        } else {
            $stmt = $conn->prepare("SELECT * FROM blogs WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $id);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $blog = $res->fetch_assoc();

        if (!$blog) {
            http_response_code(404);
            echo json_encode(["error" => "Blog not found"]);
        } else {
            echo json_encode(["success" => true, "blog" => $blog]);
        }
        $stmt->close();
        break;

    case 'updateblog':
        if ($method !== 'PUT') break;

        $id = intval($input['id'] ?? 0);
        $title = esc($conn, $input['title'] ?? '');
        $contentRaw = $input['content'] ?? '';
        $content = sanitize_blog_html($contentRaw);
        $featured = esc($conn, $input['featured_image'] ?? '');
        $status = esc($conn, $input['status'] ?? 'published');

        if (!$id || !$title || !$content) {
            http_response_code(400);
            echo json_encode(["error" => "Required: id, title, content"]);
            break;
        }

        $baseSlug = make_slug($title);
        $slug = unique_slug($conn, $baseSlug, $id);

        $stmt = $conn->prepare("UPDATE blogs SET title=?, slug=?, content=?, featured_image=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $title, $slug, $content, $featured, $status, $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "slug" => $slug]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update blog"]);
        }
        $stmt->close();
        break;

    case 'deleteblog':
        if ($method !== 'DELETE') break;

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "id is required"]);
            break;
        }

        $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) echo json_encode(["success" => true]);
        else { http_response_code(500); echo json_encode(["error" => "Failed to delete blog"]); }

        $stmt->close();
        break;

    default:
        http_response_code(400);
        echo json_encode(["error" => "Invalid action"]);
        exit;
}
