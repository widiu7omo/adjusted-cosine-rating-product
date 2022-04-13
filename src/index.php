<?php require_once "./db.php";
$db = new DB();
session_start();
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

    <title>Rekomendasi Produk</title>
    <style>
        .scrolling-wrapper {
            overflow-x: scroll;
            scroll-behavior: smooth;

        }

        /* width */
        ::-webkit-scrollbar {
            width: 5px;
            height: 10px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            background-color: #f5f5f5;
            border-radius: 10px;
            border: none;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: #dedede;
            border-radius: 10px;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
            background: #d2d2d2;
        }

        .card-block {
            height: 300px;
            background-color: #fff;
            border: none;
            background-position: center;
            background-size: cover;
            transition: all 0.2s ease-in-out !important;
            border-radius: 24px;
        }

        .card-block:hover {
            transform: translateY(-5px);
            box-shadow: none;
            opacity: 0.9;
        }

        .card-color {
            background-color: #ffe6e6;
        }

    </style>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
<div class="container">
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="#">Cakiest Sweet</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#"><i data-feather="home"></i> Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i data-feather="heart"></i> Favourites</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i data-feather="shopping-bag"></i> Pesananmu <span
                                class="badge badge-primary">4</span></a>
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0">
                <input class="form-control rounded-pill mr-sm-2" type="search" placeholder="Cari Produk"
                       aria-label="Cari Produk">
                <button class="btn my-2 my-sm-0" type="submit"><i data-feather="search"></i>
                </button>
            </form>
            <div class="position-relative">
                <i data-feather="shopping-cart"></i>
                <div class="badge badge-danger position-absolute rounded-circle"
                     style="right: -5px;top: -5px;height: 18px;width: 18px; display: flex;align-items: center;justify-content: center">
                    <small>1</small>
                </div>
            </div>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="#" data-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name=<?= $_SESSION['user'] ?>"
                         style="height: 34px;width: 34px;margin-left: 16px;"
                         class="rounded-circle" alt="Avatar"/>
                    <div class="dropdown-menu dropdown-menu-right">
                        <button class="dropdown-item margin-auto" type="button">
                            <i data-feather="settings" style="height: 16px"></i>
                            Pengaturan
                        </button>
                        <a href="auth.php?logout=true" class="dropdown-item margin-auto">
                            <i data-feather="log-out" style="height: 16px"></i>
                            Keluar
                        </a>
                    </div>
                </a>
            <?php else: ?>
                <a href="auth.php" style="margin-left: 16px;" class="btn btn-sm btn-outline-primary">Masuk</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="content">
        <div class="jumbotron bg-white">
            <h1 class="display-4">Selamat datang cakiers!</h1>
            <p class="lead">This is a simple hero unit, a simple jumbotron-style component for calling extra attention
                to featured content or information.</p>
        </div>
        <h5>Rekomendasi Produk</h5>
        <div class="d-flex justify-content-between">
            <small class="text-muted">Rekomendasi makanan buat kamu. Enak ini</small>
            <a href="detail_recommendation.php">Detail</a>
        </div>
        <div class="scrolling-wrapper row flex-row flex-nowrap mt-4 pb-4 pt-2">
            <?php require_once "./recommendation.php" ?>
        </div>
        <hr>
        <h5>Snack Terbaru</h5>
        <small class="text-muted">Hey, ada yang baru ini</small>
        <div class="scrolling-wrapper row flex-row flex-nowrap mt-4 pb-4 pt-2">
            <?php
            $result = $db->run("SELECT * FROM product");
            foreach ($result as $key => $value):
                ?>
                <div class="col-5">
                    <div class="card card-block card-color">
                        <div class="d-flex align-items-center justify-content-center h1" style="height: 100%">
                            Product <?php echo $value->name; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF"
        crossorigin="anonymous"></script>
<script>
    feather.replace()
    $(document).ready(function () {
        $.ajax({
            url: 'ajax.php?produk',
            dataType: "json",
            success: function (data) {
                console.log(data);
            }
        });
    });
</script>

</body>
</html>