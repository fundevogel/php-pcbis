# PHPCBIS
[![Release](https://img.shields.io/github/release/Fundevogel/php-pcbis.svg)](https://github.com/Fundevogel/php-pcbis/releases) [![License](https://img.shields.io/github/license/Fundevogel/php-pcbis.svg)](https://github.com/Fundevogel/php-pcbis/blob/master/LICENSE) [![Issues](https://img.shields.io/github/issues/Fundevogel/php-pcbis.svg)](https://github.com/Fundevogel/php-pcbis/issues) [![Status](https://travis-ci.org/fundevogel/php-pcbis.svg?branch=master)](https://travis-ci.org/fundevogel/php-pcbis)

## What

This small library is a PHP wrapper for [pcbis.de](https://pcbis.de), gathering information about books through wholesale book distributor [KNV](http://knv.de)'s API. For the documentation on their [WSDL](https://en.wikipedia.org/wiki/Web_Services_Description_Language) interface, see [here](https://www.knv-zeitfracht.de/wp-content/uploads/2021/01/Webservice_ZF_2.0_21.pdf).


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

$object = new Pcbis\Webservice;

try {
    // After loading a book, you might want to ..
    $book = $object->load('978-3-522-20255-8');
    // (1) .. export its bibliographic data
    $data = $book->export();

    // (2) .. access specific information
     echo $book->title();

    // (3) .. download its cover
    $book->downloadCover();

    // (4) .. query its OLA status
    $book->ola()->isAvailable();

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage(), "\n";
}
```

If you want to load multiple books at once, you may pass their ISBNs to `loadBooks()`, like this:

```php
<?php

require_once('vendor/autoload.php');

$object = new Pcbis\Webservice;

try {
    $isbns = [
        '978-3-522-20255-8',
        '978-3-522-20072-1',
        '978-3-12-674104-0',
        '978-0-14-031753-4',
        '978-3-522-20210-7',
        '978-3-95751-338-0',
    ];

    $books = $object->loadBooks($isbns);

    foreach ($books as $book) {
        echo $book->title();
    }
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage(), "\n";
}
```


## Credits
Most of the helper functions were taken from [Kirby](https://getkirby.com)'s excellent [`toolkit`](https://github.com/getkirby-v2/toolkit) package by [Bastian Allgeier](https://github.com/bastianallgeier) (who's just awesome, btw).


**Happy coding!**


:copyright: Fundevogel Kinder- und Jugendbuchhandlung
