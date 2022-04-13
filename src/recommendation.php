<?php
require_once './db.php';
$db = new DB();
//Mengambil produk yang sudah dirating/review
$queryProduct = $db->run("SELECT * FROM product WHERE id IN (SELECT product_id from review group by product_id)");
$products = $queryProduct->fetchAll();
$prevProduct = null;
$count = 1;
//Mencari rata-rata rating produk dan insert ke database
$db->run("TRUNCATE review_average;");
$db->run("INSERT INTO review_average SELECT 0, SUM(rating) / COUNT(rating) as rata2, customer_id from review GROUP BY customer_id;");
//Looping Produk vertical
foreach ($products as $key => $product) {
    $productId = $product->id;
    //Looping Produk horizontal
    foreach ($products as $subProduct) {
        $subProductId = $subProduct->id;
        if ($subProductId > $prevProduct && $subProductId != $productId) {
            $querySimilarity = $db->run("
                        SELECT (SUM((item1.rating - item1.average_rating) * (item2.rating - item2.average_rating)) /
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
            //Hanya mengambil produk dengan nilai similarity > 0.5
            if ($sim->similarity > 0.5) {
                $queryCheck = $db->run("SELECT * FROM similarity WHERE product1_id = $productId AND product2_id = $subProductId");
                if ($queryCheck->rowCount() == 0) {
                    $db->run("INSERT INTO similarity VALUES (?,?,?,?)", [0, $productId, $subProductId, $sim->similarity]);
                } else {
                    $existed = $queryCheck->fetch();
                    $db->run("UPDATE similarity SET sim = ? WHERE product1_id = ? AND product2_id = ?", [$sim->similarity, $productId, $subProductId]);
                }
//                echo "No.$count SIM(" . $product->name . "," . $subProduct->name . ") = " . $sim->similarity . "<br>";
                $count++;
            }
        }
    }
    $prevProduct = $productId;
}
if (isset($_SESSION['user'])) {
    $queryCustomer = $db->run("SELECT * FROM customer WHERE name = ?", [strtoupper($_SESSION['user'])]);
    $users = $queryCustomer->fetchAll();
    foreach ($users as $user) {
//   Check jika user belum melakukan rating dari produk yang bersangkutan
        $queryUnratedProduct = $db->run("SELECT * FROM product WHERE id NOT IN (SELECT product_id FROM review WHERE customer_id = ?)", [$user->id]);
//    $queryUnratedProduct = $db->run("SELECT * FROM similarity");
        $unratedProducts = $queryUnratedProduct->fetchAll();

//    echo "<h3>Customer " . $user->name . " belum melakukan rating di " . $queryUnratedProduct->rowCount() . " produk</h3>";
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
                       s.sim                                     as sim,
                       r.name                                    as productName    
                FROM (SELECT product1_id, product2_id, sim FROM similarity) s,
                     (SELECT customer_id, product_id, rating, name
                      FROM review INNER JOIN product p on review.product_id = p.id
                      WHERE customer_id = ?) r
                WHERE (s.product1_id = ?
                    AND s.product2_id = r.product_id)
                          XOR (s.product1_id = r.product_id AND s.product2_id = ?)", [$user->id, $unratedProductId, $unratedProductId]);
                $prediction = $queryPrediction->fetch();
                if ($prediction->prediction != null) {
//                    echo "<p style='color:green;'> [ Customer " . $user->name . " - Product <i>j</i> " . $unratedProductId . " - Product <i>i</i> " . $unratedProductId . " ] P(" . $user->name . "," . $unratedProductId . ") = TOTAL(Rating produk " . $unratedProductId . " X similarity " . $prediction->sim . ")/ TOTAL(similar " . $prediction->sim . ") = " . $prediction->prediction . "</p>";
                    echo '<div class="col-5">
                            <div class="card card-block card-color">
                                <div class="d-flex align-items-center justify-content-center h1" style="height: 100%">
                                    Product ' . $unratedProduct->name . '</div>
                            </div>
                        </div>';
                }
//              else {
//                echo "<p style='color: red'> [ Customer " . $user->name . " - Product <i>j</i> " . $unratedProductId . " - Product <i>i</i> " . $unratedProductId . " ] Tidak terprediksi karena produk tidak memiliki kesamaan dengan produk yang lain </p>";
//            }
            }
//        else {
//            echo "<p style='color: red'> [ Customer " . $user->name . " - Product <i>j</i> " . $unratedProductId . " - Product <i>i</i> NULL ] Tidak terprediksi karena produk tidak memiliki kesamaan dengan produk yang lain </p>";
//        }
        }
    }
}
