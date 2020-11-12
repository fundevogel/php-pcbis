<?php

require_once('vendor/autoload.php');

$object = new PHPCBIS\PHPCBIS;

// Do it like this ..
try {
    // Retrieve data
    $dataRaw = $object->loadBook('978-3-407-81238-4');
    $data = $object->processData($dataRaw);

    // Download cover
    $object->downloadCover('978-3-407-81238-4');
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage(), "\n";
}
