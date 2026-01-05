<?php
session_start();

// ✅ Admin Auth (redirect if not logged in)
if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header("Location: login.php");
    exit();
}
require('../config/connection.php');

if (isset($_POST['btn-out'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$editing = false;
$certificate = [
    "product_name" => "",
    "cat_no" => "",
    "batch_no" => "",
    "cas_no" => "",
    "synonyms" => "",
    "chemical_name" => "",
    "mg" => "",
    "molecular_formula" => "",
    "solubility" => "",
    "storage" => "",
    "shipping_condition" => "",
    "specification" => "",
    "purity" => "",
    "lab_test_number" => "",
    "note" => "",
    "test_date" => "",
    "retest_date" => "",
];

if (isset($_GET['id'])) {
    $editing = true;
    $id = intval($_GET['id']);
    $stmt = $con->prepare("SELECT * FROM certificates WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $certificate = $result->fetch_assoc();
    } else {
        echo "<script>alert('Certificate not found.'); window.location.href = 'certificates.php';</script>";
        exit;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $editing ? "Update" : "Generate" ?> Certificate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/css/bootstrap.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />
    <style>
        form { max-width: 700px; }
        label { font-weight: bold; display: block; margin-top: 15px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; }
        input[type="submit"] {
            width: auto; background-color: #2c3e50; color: #fff;
            border: none; padding: 10px 20px; margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-light">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
                <a href="/" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
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
                        <a href="manage-blogs.php" class="nav-link px-0 align-middle text-dark">
                            <i class="bi bi-journal-text"></i> <span class="ms-1 d-none d-sm-inline">Manage Blogs</span>
                        </a>
                    </li>
                </ul>
                <hr />
                <div class="dropdown pb-4">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRvts5aHBstDkR8PigS4RmZkbZy78zpZoSuOw&s" alt="user" width="30" height="30" class="rounded-circle" />
                        <span class="d-none d-sm-inline mx-1">Admin</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                        <!-- <li><a href="updateprofile" class="dropdown-item">Profile</a></li>
                        <li><hr class="dropdown-divider" /></li> -->
                        <li>
                            <form method="post">
                                <button class="dropdown-item" name="btn-out">Sign out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col py-3 bg-light">
            <h2><?= $editing ? "Update" : "Generate" ?> Certificate</h2>
            <form method="POST" action="submit.php">
                <?php if ($editing): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($certificate['id']) ?>">
                    <input type="hidden" name="action" value="update">
                <?php else: ?>
                    <input type="hidden" name="action" value="create">
                <?php endif; ?>

                <label>Product Name:</label>
                <input type="text" name="product_name" value="<?= htmlspecialchars($certificate['product_name']) ?>" required>

                <label>Product ID:</label>
                <input type="text" name="cat_no" value="<?= htmlspecialchars($certificate['cat_no']) ?>" required>

                <label>Batch No:</label>
                <input type="text" name="batch_no" value="<?= htmlspecialchars($certificate['batch_no']) ?>" required>

                <label>CAS No:</label>
                <input type="text" name="cas_no" value="<?= htmlspecialchars($certificate['cas_no']) ?>">

                <label>Synonyms:</label>
                <input type="text" name="synonyms" value="<?= htmlspecialchars($certificate['synonyms']) ?>">

                <label>Chemical Name:</label>
                <input type="text" name="chemical_name" value="<?= htmlspecialchars($certificate['chemical_name']) ?>">

                <label>MG:</label>
                <input type="text" name="mg" value="<?= htmlspecialchars($certificate['mg']) ?>">

                <label>Molecular Formula:</label>
                <input type="text" name="molecular_formula" value="<?= htmlspecialchars($certificate['molecular_formula']) ?>">

                <label>Solubility:</label>
                <input type="text" name="solubility" value="<?= htmlspecialchars($certificate['solubility']) ?>">

                <label>Storage Conditions:</label>
                <textarea name="storage" rows="4"><?= htmlspecialchars($certificate['storage']) ?></textarea>

                <label>Shipping Condition:</label>
                <textarea name="shipping_condition" rows="2"><?= htmlspecialchars($certificate['shipping_condition']) ?></textarea>

                <label>Specification:</label>
                <input type="text" name="specification" value="<?= htmlspecialchars($certificate['specification']) ?>">

                <label>Purity (HPLC):</label>
                <input type="text" name="purity" value="<?= htmlspecialchars($certificate['purity']) ?>">

                <label>Lab Test Number:</label>
                <input type="text" name="lab_test_number" value="<?= htmlspecialchars($certificate['lab_test_number']) ?>">

                <label>Note:</label>
                <textarea name="note" rows="2"><?= htmlspecialchars($certificate['note']) ?></textarea>

                <label>Test Date:</label>
                <input type="date" name="test_date" value="<?= htmlspecialchars($certificate['test_date']) ?>">

                <label>Retest Date:</label>
                <input type="date" name="retest_date" value="<?= htmlspecialchars($certificate['retest_date']) ?>">

                <input type="submit" value="<?= $editing ? "Update Certificate" : "Submit Certificate" ?>">
            </form>
        </div>
    </div>
</div>
</body>
</html>
