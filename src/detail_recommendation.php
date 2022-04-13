<?php
require_once './db.php';
$db = new DB();
$queryProduct = $db->run("SELECT * FROM product WHERE id IN (SELECT product_id from review group by product_id)");
$products = $queryProduct->fetchAll();
$prevProduct = null;
$count = 1;
//Mencari rata-rata rating produk dan insert ke database
$db->run("TRUNCATE review_average;");
$db->run("INSERT INTO review_average SELECT 0, SUM(rating) / COUNT(rating) as rata2, customer_id from review GROUP BY customer_id;");
$simiars = array();
foreach ($products as $key => $product) {
    $productId = $product->id;
    foreach ($products as $subProduct) {
        $subProductId = $subProduct->id;
        if ($subProductId > $prevProduct && $subProductId != $productId) {
            $querySimilarity = $db->run("SELECT (SUM((item1.rating - item1.average_rating) * (item2.rating - item2.average_rating)) /
                                (SQRT(SUM(POW((item1.rating - item1.average_rating), 2))) *
                                 SQRT(SUM(POW((item2.rating - item2.average_rating), 2))))) AS similarity,
                               SUM((item1.rating - item1.average_rating) * (item2.rating - item2.average_rating)) AS pembilang,
                               SQRT(SUM(POW((item1.rating - item1.average_rating), 2)))                           as item1_sqrt,
                               SQRT(SUM(POW((item2.rating - item2.average_rating), 2)))                           as item2_sqrt,
                               (SQRT(SUM(POW((item1.rating - item1.average_rating), 2))) *
                                SQRT(SUM(POW((item2.rating - item2.average_rating), 2))))                         AS penyebut_total
                        FROM (SELECT r.customer_id, r.product_id, r.rating, ra.average_rating FROM review r, review_average ra WHERE r.customer_id = ra.customer_id AND r.product_id = ?) AS item1,
                             (SELECT r.customer_id, r.product_id, r.rating, ra.average_rating FROM review r, review_average ra WHERE r.customer_id = ra.customer_id AND r.product_id = ?) AS item2
                        WHERE item1.customer_id = item2.customer_id;", [$productId, $subProductId]);
            $sim = $querySimilarity->fetch();
            if ($sim->similarity > 0.5) {
                $queryCheck = $db->run("SELECT * FROM similarity WHERE product1_id = $productId AND product2_id = $subProductId");
                if ($queryCheck->rowCount() == 0) {
                    $db->run("INSERT INTO similarity VALUES (?,?,?,?)", [0, $productId, $subProductId, $sim->similarity]);
                } else {
                    $existed = $queryCheck->fetch();
                    $db->run("UPDATE similarity SET sim = ? WHERE product1_id = ? AND product2_id = ?", [$sim->similarity, $productId, $subProductId]);
                }
//                    echo "Pasangan Similar (Produk " . $product->name . ", Produk" . $subProduct->name . ") = " . $sim->similarity . "<br>";
                $similars[] = (object)['product1' => $product->name, 'product2' => $subProduct->name, "sim" => $sim->similarity];
                $count++;
            }
        }
    }
    $prevProduct = $productId;
}

$queryCustomer = $db->run("SELECT * FROM customer");
$users = $queryCustomer->fetchAll();
$weightedSums = array();
foreach ($users as $user) {
//   Check jika user belum melakukan rating dari produk yang bersangkutan
    $queryUnratedProduct = $db->run("SELECT * FROM product WHERE id NOT IN (SELECT product_id FROM review WHERE customer_id = ?)", [$user->id]);
//    $queryUnratedProduct = $db->run("SELECT * FROM similarity");
    $unratedProducts = $queryUnratedProduct->fetchAll();

//    echo "<h3>Customer " . $user->name . " belum melakukan rating di " . $queryUnratedProduct->rowCount() . " produk</h3>";
    $totalNotRated = $queryUnratedProduct->rowCount();
    $weights = array();
    foreach ($unratedProducts as $key => $unratedProduct) {
//        P(userA,productJ) = TOTAL(Rating productI, similarity productI,productJ)/ TOTAL(similar productI,productJ)
        $unratedProductId = $unratedProduct->id;
        $querySimilarity = $db->run("SELECT * FROM similarity WHERE product1_id = ? XOR product2_id = ?", [$unratedProductId, $unratedProductId]);
        $simProducts = $querySimilarity->fetchAll();
        if ($querySimilarity->rowCount() > 0) {
            $queryPrediction = $db->run("
                SELECT r.product_id                              as product,
                       r.rating                                  as rating_product,
                       r.rating * s.sim                          as rating_sim,
                       (SUM(r.rating * s.sim) / SUM(ABS(s.sim))) AS prediction,
                       s.sim                                     as sim
                FROM (SELECT product1_id, product2_id, sim FROM similarity) s,
                     (SELECT customer_id, product_id, rating
                      FROM review
                      WHERE customer_id = ?) r
                WHERE (s.product1_id = ?
                    AND s.product2_id = r.product_id)
                          XOR (s.product1_id = r.product_id AND s.product2_id = ?)", [$user->id, $unratedProductId, $unratedProductId]);
            $prediction = $queryPrediction->fetch();
            $weights[] = (object)['user' => $user->name, 'product' => $unratedProduct->name, 'prediction' => $prediction->prediction != null ? $prediction->prediction : "Tidak terprediksi"];
            if ($prediction->prediction != null) {
//                echo "<p style='color:green;'> [ Customer " . $user->name . " - Product <i>j</i> " . $unratedProductId . " - Product <i>i</i> " . $unratedProductId . " ] P(" . $user->name . "," . $unratedProductId . ") = TOTAL(Rating produk " . $unratedProductId . " X similarity " . $prediction->sim . ")/ TOTAL(similar " . $prediction->sim . ") = " . $prediction->prediction . "</p>";
            } else {
//                echo "<p style='color: red'> [ Customer " . $user->name . " - Product <i>j</i> " . $unratedProductId . " - Product <i>i</i> " . $unratedProductId . " ] Tidak terprediksi karena produk tidak memiliki kesamaan dengan produk yang lain </p>";
            }
        } else {
//            echo "<p style='color: red'> [ Customer " . $user->name . " - Product <i>j</i> " . $unratedProductId . " - Product <i>i</i> NULL ] Tidak terprediksi karena produk tidak memiliki kesamaan dengan produk yang lain </p>";
        }
    }
    $weightedSums[] = (object)['weights' => $weights, 'totalNotRated' => $totalNotRated, 'user' => $user->name];
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

    <title>Detail Perhitungan Rekomendasi Produk</title>
</head>
<body>
<div class="container" style="margin-top:59px">
    <h4>Pasangan produk (similar) bernilai lebih dari 0.5</h4>
    <div class="row">
        <div class="col-md-6">
            <ul class="list-group">
                <?php foreach ($similars as $similar): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Produk <?php echo $similar->product1 ?> - <?php echo $similar->product2 ?>
                        <span class="badge badge-primary badge-pill"><?php echo $similar->sim ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <hr>
    <h4>Detail</h4>
    <div class="row">
        <div class="col-md-12">
            <?php foreach ($weightedSums as $weightedSum): ?>
                <table class="table">
                    <tbody>
                    <tr>
                        <th colspan="3">Produk belum terate oleh user <?php echo $weightedSum->user ?></th>
                    </tr>
                    <tr>
                        <th colspan="3"><span
                                    class="badge badge-info"><?php echo $weightedSum->totalNotRated ?> Produk</span>
                        </th>
                    </tr>
                    <tr>
                        <th style="width: 1%">No</th>
                        <th>Produk</th>
                        <th>Prediksi Rating</th>
                    </tr>
                    <?php foreach ($weightedSum->weights as $key => $weight): ?>
                        <tr>
                            <td><?php echo $key + 1 ?></td>
                            <td>Produk <?php echo $weight->product ?></td>
                            <td><span class="badge badge-danger"><?php echo $weight->prediction ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>