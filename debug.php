<?php

require_once('vendor/autoload.php');

$isbns = [
    # Nonbook
    '5055964778583',
    # Hardcover
    '978-3-96664-127-2',
    # Softcover
    '978-3-551-35401-3',
    # Ebook
    '978-3-96664-324-5',
    # Stationary
    '978-1-78370-594-8',
    # Calendar
    '978-3-8401-8256-3',
    # Games
    // '3558380068891',
    # Movie
    '5051890326966',
    # Audiobook
    '978-3-8445-3053-7',
    # Sound
    '0738572138127',
    # Schoolbook
    '978-3-640-51139-6',
];


$login = json_decode(file_get_contents('login.json'), true);

try {
    $object = new Pcbis\Webservice($login);

    foreach ($isbns as $isbn) {
        echo $isbn . "\n";
        $book = $object->load($isbn);

        var_dump($book->showSource());
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage(), "\n";
}
