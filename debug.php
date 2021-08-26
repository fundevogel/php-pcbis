<?php

require_once('vendor/autoload.php');

/**
 * TODO:
 *
 * KNV doesn't list 'BUCH', 'CRD', 'EBO', 'DIGI', 'LIZ', 'AUD', 'TRDE', 'ONL', 'FILM'
 */

$isbns = [
    '5010993411504',  # Monopoly Classic
    '4002051693602',  # Die Siedler von Catan
    '4002051694104',  # Die Siedler von Catan, Seefahrer
    '4002051695101',  # Die Siedler von Catan, Städte & Ritter
    '4002051693305',  # Die Siedler von Catan, Händler & Barbaren
    '4002051694111',  # Die Siedler von Catan, Entdecker & Piraten
    '4015566000964',  # Maus und Mystik
    '0681706117010',  # Maus und Mystik, Herz des Glürm
    '4015566033108',  # Maus und Mystik, Geschichten aus dem Dunkelwald
];


$login = json_decode(file_get_contents('login.json'), true);

try {
    $object = new Pcbis\Webservice($login);

    foreach ($isbns as $isbn) {
        echo $isbn . "\n";
        $book = $object->load($isbn);

        // var_dump($book->showSource());
        var_dump($book->playerCount());
        var_dump($book->playingTime());
        var_dump($book->age());
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage(), "\n";
}
