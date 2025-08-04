<?php
require('../config/connection.php');

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

if(isset($_POST['btn-out'])){
    session_destroy();
    header("Location: login.php");
}

$sql = "SELECT * FROM certificates ORDER BY created_at DESC";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Certificates</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/css/bootstrap.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />

    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .table-responsive {
            max-height: 80vh;
            overflow-y: auto;
        }

        table#certificatesTable {
            border-collapse: collapse;
            width: 100%;
            table-layout: auto;
        }

        table#certificatesTable th,
        table#certificatesTable td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }

        table#certificatesTable th {
            background-color: #f8f9fa;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        table#certificatesTable tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .truncate-cell {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
            display: inline-block;
            vertical-align: top;
        }

        .tooltip-wrap {
            position: relative;
            display: inline-block;
        }

        .tooltip-wrap .tooltip-text {
            visibility: hidden;
            width: 300px;
            background-color: #000;
            color: #fff;
            text-align: left;
            border-radius: 4px;
            padding: 10px;
            position: absolute;
            z-index: 100;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 13px;
        }

        .tooltip-wrap:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
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
            <h2 class="mb-4">All Certificates</h2>

            <input type="text" id="searchInput" class="form-control mb-3" placeholder="Search certificates..." onkeyup="searchTable()">

            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" id="certificatesTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Cat. No</th>
                                <th>Batch No</th>
                                <th>CAS No</th>
                                <th>Synonyms</th>
                                <th>Chemical Name</th>
                                <th>MG</th>
                                <th>Molecular Formula</th>
                                <th>Solubility</th>
                                <th>Storage</th>
                                <th>Shipping Condition</th>
                                <th>Specification</th>
                                <th>Purity</th>
                                <th>Lab Test Number</th>
                                <th>Note</th>
                                <th>Test Date</th>
                                <th>Retest Date</th>
                                <th>Created At</th>
                                <th>Update</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row["id"] ?></td>
                                <td><?= htmlspecialchars($row["product_name"]) ?></td>
                                <td><?= htmlspecialchars($row["cat_no"]) ?></td>
                                <td><?= htmlspecialchars($row["batch_no"]) ?></td>
                                <td><?= htmlspecialchars($row["cas_no"]) ?></td>
                                <td>
                                    <div class="tooltip-wrap">
                                        <span class="truncate-cell"><?= substr($row["synonyms"], 0, 20) ?>...</span>
                                        <div class="tooltip-text"><?= nl2br(htmlspecialchars($row["synonyms"])) ?></div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row["chemical_name"]) ?></td>
                                <td><?= htmlspecialchars($row["mg"]) ?></td>
                                <td><?= htmlspecialchars($row["molecular_formula"]) ?></td>
                                <td><?= htmlspecialchars($row["solubility"]) ?></td>
                                <td>
                                    <div class="tooltip-wrap">
                                        <span class="truncate-cell"><?= substr($row["storage"], 0, 20) ?>...</span>
                                        <div class="tooltip-text"><?= nl2br(htmlspecialchars($row["storage"])) ?></div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row["shipping_condition"]) ?></td>
                                <td><?= htmlspecialchars($row["specification"]) ?></td>
                                <td><?= htmlspecialchars($row["purity"]) ?></td>
                                <td><?= htmlspecialchars($row["lab_test_number"]) ?></td>
                                <td>
                                    <div class="tooltip-wrap">
                                        <span class="truncate-cell"><?= substr($row["note"], 0, 20) ?>...</span>
                                        <div class="tooltip-text"><?= nl2br(htmlspecialchars($row["note"])) ?></div>
                                    </div>
                                </td>
                                <td><?= $row["test_date"] ?></td>
                                <td><?= $row["retest_date"] ?></td>
                                <td><?= $row["created_at"] ?></td>
                                <td><a href="generate-certificate.php?id=<?php echo $row["id"]?>"><button class="btn btn-warning">Update</button></a></td>
                                <td><a href="delete-certificate.php?id=<?php echo $row["id"]?>"><button class="btn btn-danger">Delete</button></a></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No certificates found.</p>
            <?php endif; ?>
            <?php $con->close(); ?>
        </div>
    </div>
</div>

<script>
function searchTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("#certificatesTable tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}
</script>

</body>
</html>
