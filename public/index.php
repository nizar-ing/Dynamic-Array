<?php

use App\NumberHelper;
use App\QueryBuilder;
use App\Table;
//use App\TableHelper;
//use App\UrlHelper;

define('PER_PAGE', 20);
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
$pdo = new PDO('sqlite:../data.sql', null, null, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
$query = (new QueryBuilder($pdo))->from("products");
/* if (!empty($_GET['q'])) {
    $query .= " WHERE city LIKE \"%" . $_GET['q'] . "%\"";
} else {
    $query .= ' LIMIT 20';
} */
// recherche par ville via une requete preparée afin d'eliminer les injections maladroits :) 
if (!empty($_GET['q'])) {
    $query->where("city LIKE :city")
        ->setParam("city", "%" . $_GET['q'] . "%");
}

$table = (new Table($query, $_GET))
    ->sortable("id", "city", "price")
    ->format("price", function($value){
        return NumberHelper::price($value);
    })
    ->columns([
    'id' => "ID",
    'name' => "Name",
    'city' => "Ville",
    'price' => "Prix"
]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biens Immobilier</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>

<body class="p-4">
    <h1>Les biens immobiliers</h1>
    <form action="" class="mb-4">
        <div class="form-group">
            <input type="text" class="form-control" name="q" placeholder="Rechercher par ville"
                value="<?= htmlentities($_GET['q'] ?? null) ?>">
        </div>
        <button class="btn btn-primary">Rechercher</button>
    </form>

    <?php $table->render() ?>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
        integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous">
    </script>
</body>

</html>