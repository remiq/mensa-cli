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
$data = $mensa->parse();
echo '=== Mensa ===', "\n";
foreach ($data as $r) {
    echo set_color($r['color']);
    echo '[', $r['type'], '] ', $r['name'], set_color(), "\n";
}
echo '===', "\n";

