<?php
session_start();

// ✅ Admin Auth (redirect if not logged in)
if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header("Location: login.php");
    exit();
}
require('../config/connection.php');

if($_SESSION['username'] == null){
    header("Location: login.php");
}

if(isset($_POST['btn-out'])){
    session_destroy();
    header("Location: login.php");
}

// Check DB connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Get certificate count
$sql = "SELECT COUNT(*) AS total FROM certificates";
$result = $con->query($sql);
$row = $result->fetch_assoc();
$total_certificates = $row['total'];

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/css/bootstrap.min.css" integrity="sha512-fw7f+TcMjTb7bpbLJZlP8g2Y4XcCyFZW8uy8HsRZsH/SwbMw0plKHFHr99DN3l04VsYNwvzicUX/6qurvIxbxw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/js/bootstrap.bundle.min.js" integrity="sha512-Tc0i+vRogmX4NN7tuLbQfBxa8JkfUSAxSFVzmU31nVdHyiHElPPy2cWfFacmCJKw0VqovrzKhdd2TSTMdAxp2g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                                        <i class="bi bi-cloud-arrow-up"></i> <span class="ms-1 d-none d-sm-inline">Generate Certificate</span></a>
                                </li>

                                <li>
                                    <a href="certificates.php" class="nav-link px-0 align-middle text-dark">
                                        <i class="bi bi-map"></i> <span class="ms-1 d-none d-sm-inline">Certificates</span></a>
                                </li>

                                <!-- <li>
                                    <a href="allleads" class="nav-link px-0 align-middle text-dark">
                                        <i class="bi bi-clipboard-data"></i> <span class="ms-1 d-none d-sm-inline">All Data</span></a>
                                </li>
                            
                                <li>
                                    <a href="/users" class="nav-link px-0 align-middle text-dark">
                                        <i class="fs-4 bi-people"></i> <span class="ms-1 d-none d-sm-inline">Users</span> </a>
                                </li> -->

                                <!-- <li>
                                    <a href="/emails" class="nav-link px-0 align-middle text-dark">
                                        <i class="fs-4 bi-envelope"></i> <span class="ms-1 d-none d-sm-inline">Emails</span> </a>
                                </li>  -->
                    </ul>
                    <hr />
                    <div class="dropdown pb-4">
                        <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRvts5aHBstDkR8PigS4RmZkbZy78zpZoSuOw&s" alt="hugenerd" width="30" height="30" class="rounded-circle" />
                            <span class="d-none d-sm-inline mx-1">Admin</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <!-- <li><a href="updateprofile" class="dropdown-item">Profile</a></li>
                            <li>
                                <hr class="dropdown-divider" />
                            </li> -->
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
                <div class="row">
                    <div class="col-md-5">

                        <!-- Bootstrap Card -->
                        <div class="card shadow">
                            <div class="card-body">
                                <h5 class="card-title">Certificates</h5>
                                <p class="display-5 text-primary"><?= $total_certificates ?></p>
                                <p class="card-text">Total Certificates in the Database</p>
                                <a href="certificates.php" class="btn btn-outline-primary">Manage Certificates</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>