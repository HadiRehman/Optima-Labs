<?php
session_start();
require('../config/connection.php');

if(isset($_POST['btn-login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $encpass = md5($password);
    $query = "SELECT * FROM `users` WHERE `username` = BINARY '$username' and `password` = '$encpass'";
    $exec = mysqli_query($con, $query);
    $count = mysqli_num_rows($exec);

    if($count == 1){
        $_SESSION['username'] = $username;
        header("Location: index.php");
    }
    else{
        echo "Logged in failed!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/css/bootstrap.min.css" integrity="sha512-fw7f+TcMjTb7bpbLJZlP8g2Y4XcCyFZW8uy8HsRZsH/SwbMw0plKHFHr99DN3l04VsYNwvzicUX/6qurvIxbxw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/js/bootstrap.min.js" integrity="sha512-zKeerWHHuP3ar7kX2WKBSENzb+GJytFSBL6HrR2nPSR1kOX1qjm+oHooQtbDpDBSITgyl7QXZApvDfDWvKjkUw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
    <div class="container" style="margin-top: 120px;">
        <center>
            <h2>Admin Login</h2>
            <form method="POST">
                <div class="col-sm-6">
                    <div class="form-group">
                        <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter username" name="username">
                    </div>
                </div>
                <br>
                <div class="col-sm-6">
                    <div class="form-group">
                        <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password" name="password">
                    </div>
                </div>
                <br>
                <button type="submit" class="btn btn-primary" name="btn-login">Login</button>
            </form>
        </center>
    </div>
</body>
</html>