# php-pcbis
[![License](https://badgen.net/badge/license/GPL/blue)](https://codeberg.org/fundevogel/php-pcbis/src/branch/main/LICENSE) [![Packagist](https://badgen.net/packagist/v/fundevogel/php-pcbis)](https://packagist.org/packages/fundevogel/php-pcbis) [![Build](https://ci.codeberg.org/api/badges/Fundevogel/php-pcbis/status.svg)](https://codeberg.org/fundevogel/php-pcbis/issues)


## What

This small library may be used to gather information about books by utilizing the JSON API powering [pcbis.de](https://pcbis.de), developed by [Zeitfracht](https://zeitfracht.de/en), a german wholesale book distributor. The official API documentation can be found [here](docs/2022-03-17_webservice_v3.0.0.pdf).


## Why

It powers [our book recommendations](https://fundevogel.de/en/recommendations) & downloads cover images from the [German National Library](https://www.dnb.de/EN/Home/home_node.html).


## How

It's available for [Composer](https://getcomposer.org):

```text
composer require fundevogel/php-pcbis
```


## Basic workflow

Getting started is pretty straight-forward:

```php
<?php

require_once('vendor/autoload.php');

use Fundevogel\Pcbis\Pcbis;

# Create object, passing credentials as first parameter (for caching, see below)
$object = new Pcbis([/* ... */]);

try {
    # After loading a book, you might want to ..
    $book = $object->load('978-3-522-20255-8');

    # (1) .. export its bibliographic data
    $data = $book->export();

    # (2) .. access specific information
    echo $book->title();

    # (3) .. download its cover
    if ($book->downloadCover()) {
        echo 'Cover downloaded!';
    }

    # (4) .. query its OLA status
    if ($book->isAvailable()) {
        # ...
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage(), "\n";
}
```

**Note**: While using this library during development, please consider using the API testing endpoint like so:

```php
# Initialize API wrapper
$api = new Webservice($credentials);

# Use 'testing' URL 
$api->url = 'https://wstest.pcbis.de/ws30';
```

If you want to load several EANs/ISBNs, use `loadAll(array $identifiers)` which returns a `Products` object.

**Note**: Starting with v3, ISBN validation is no longer enabled by default. If you want formatted (= hyphenated) ISBNs when calling `isbn()` (if available), `php-pcbis` takes care of this for you if [`nicebooks/isbn`](https://github.com/nicebooks-com/isbn) is installed.


### Caching

By default, `php-pcbis` doesn't implement any caching mechanism. If you want to store data - and you probably should - this could be achieved something like this:

```php
require_once('vendor/autoload.php');

use Fundevogel\Pcbis\Pcbis;

$obj = new Pcbis([/* ... */]);
$ean = 'identifier';

if ($myCache->has($ean)) {
    $data = $myCache->get($ean);
    $product = $obj->load($data);
}

else {
    $product = $obj->load($ean);
    $myCache->set($ean, $product->data);
}
```


## Credits

Most of the helper functions were taken from [Kirby](https://getkirby.com)'s excellent [`toolkit`](https://github.com/getkirby-v2/toolkit) package by [Bastian Allgeier](https://github.com/bastianallgeier) (who's just awesome, btw).

**Happy coding!**

:copyright: Fundevogel Kinder- und Jugendbuchhandlung
