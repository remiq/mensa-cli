<?php

include_once "mensa.php";

function set_color($color = 'white') {
    $colors = array(
        'white' =>  "\033[0m",
        'gruen'  =>  "\033[32m",
        'orange'  =>  "\033[33m",
        'rot'  =>  "\033[31m",
        ""
    );
    return $colors[$color];
}

$mensa = new Mensa();
$date = date('Y-m-d');
$data = $mensa->parse($date);
$fav_foods = array();
echo '=== Mensa ', $date ,' ===', "\n";
foreach ($data as $r) {
    echo set_color($r['color']);
    echo '[', $r['type'], '] ', $r['name'], set_color(), "\n";
    if ($r['type'] === '!') {
        $fav_foods[] = $r['name'];
    }
}
echo '===', "\n";
if (!empty($fav_foods)) {
    echo 'Food alert triggered for:', "\n";
    foreach ($fav_foods as $fav_food) {
        echo $fav_food, "\n";
    }
    echo '===', "\n";
}

