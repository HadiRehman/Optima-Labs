<?php
require('../config/connection.php');

if (!isset($_GET['id'])) {
    echo "Certificate ID is missing.";
    exit;
}

$id = intval($_GET['id']);

// Show confirmation if not confirmed yet
if (!isset($_GET['confirm'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Confirm Deletion</title>
        <style>
            body {
                font-family: Arial;
                background: #f4f4f4;
                text-align: center;
                padding-top: 100px;
            }
            .box {
                background: white;
                padding: 30px;
                margin: auto;
                width: 400px;
                box-shadow: 0 0 10px rgba(0,0,0,0.2);
                border-radius: 8px;
            }
            .btn {
                padding: 10px 20px;
                margin: 10px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
            }
            .btn-confirm {
                background-color: #e74c3c;
                color: white;
            }
            .btn-cancel {
                background-color: #3498db;
                color: white;
            }
        </style>
    </head>
    <body>
        <div class="box">
            <h2>Are you sure you want to delete this certificate?</h2>
            <a href="?id=<?= $id ?>&confirm=yes" class="btn btn-confirm">Yes, Delete</a>
            <a href="certificates.php" class="btn btn-cancel">Cancel</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Perform delete
$stmt = $con->prepare("DELETE FROM certificates WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    header("Location: certificates.php?deleted=1");
    exit;
} else {
    echo "Error deleting certificate: " . $con->error;
}
?>
