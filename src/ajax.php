<?php
require_once "./db.php";
$db_instance = DB::getInstance();
if (isset($_GET['produk'])) {
    $query = "SELECT * FROM product";
    $statement = $db_instance->prepare($query);
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    echo json_encode($statement->fetchAll());
}