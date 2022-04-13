<?php
require_once "./db.php";
$db = new DB();
session_start();
if (isset($_POST['login'])) {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $loginQuery = $db->run("SELECT * FROM customer WHERE name = ?", [strtoupper($email)]);
    if ($loginQuery->rowCount() > 0) {
        $customer = $loginQuery->fetch();
        $_SESSION['user'] = $customer->name;
        header("location:index.php");
    } else {
        $_SESSION['error'] = "Invalid email or password";
        header("location:auth.php");
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("location:auth.php");
}
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css"
          integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">

    <title>Login</title>
    <style>
        html, body {
            height: 100vh;
            margin: 0;
        }
    </style>
</head>
<body>
<div class="d-flex align-items-center justify-content-center h-100">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error'] ?>
        </div>
    <?php endif; ?>
    <div class="w-50">
        <div class="col-md-6 offset-md-3">
            <h1>Login</h1>
            <form action="auth.php?login=true" method="post">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="text" class="form-control" id="email" name="email" aria-describedby="emailHelp"
                           placeholder="Enter email">
                    <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone
                        else.
                    </small>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Password">
                </div>
                <button type="submit" name="login" class="btn btn-primary float-right">Submit</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF"
        crossorigin="anonymous"></script>

</body>
</html>