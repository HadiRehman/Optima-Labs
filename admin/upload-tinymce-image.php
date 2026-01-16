<?php
session_start();

// Admin auth (match your pattern)
if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit();
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload error']);
    exit();
}

// Validate mime
$allowedTypes = ['image/jpeg','image/png','image/webp'];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowedTypes, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Only JPG, PNG, WEBP allowed']);
    exit();
}

// Optional: size limit (e.g. 4MB)
$maxBytes = 4 * 1024 * 1024;
if ($file['size'] > $maxBytes) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large (max 4MB)']);
    exit();
}

// Determine paths
// This file is in /admin (based on your previous pattern using dirname(__DIR__))
$rootDir  = dirname(__DIR__);              // project root
$publicDir = $rootDir . "/uploads/blogs";  // /uploads/blogs
$adminDir  = __DIR__ . "/uploads/blogs";   // /admin/uploads/blogs

// Ensure both dirs exist
foreach ([$publicDir, $adminDir] as $d) {
    if (!is_dir($d) && !mkdir($d, 0777, true)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create upload directory']);
        exit();
    }
}

// Build filename once (so both copies match)
$ext  = ($mime === 'image/png') ? 'png' : (($mime === 'image/webp') ? 'webp' : 'jpg');
$name = "content_" . time() . "_" . bin2hex(random_bytes(6)) . "." . $ext;

$destPublic = $publicDir . "/" . $name;
$destAdmin  = $adminDir . "/" . $name;

// Move to first folder, then copy to second
if (!move_uploaded_file($file['tmp_name'], $destPublic)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file to uploads/blogs']);
    exit();
}

// Copy into /admin/uploads/blogs
if (!copy($destPublic, $destAdmin)) {
    // If you want strict behavior, you can also unlink($destPublic) here.
    http_response_code(500);
    echo json_encode(['error' => 'Saved to uploads/blogs but failed to copy to admin/uploads/blogs']);
    exit();
}

// Optional: lock down permissions a bit
@chmod($destPublic, 0644);
@chmod($destAdmin, 0644);

// Return a public URL (root-absolute)
echo json_encode([
    'location' => "uploads/blogs/" . $name
]);