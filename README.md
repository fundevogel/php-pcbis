# php-pcbis
[![License](https://badgen.net/badge/license/GPL/blue)](https://codeberg.org/fundevogel/php-pcbis/src/branch/main/LICENSE) [![Packagist](https://badgen.net/packagist/v/fundevogel/php-pcbis)](https://packagist.org/packages/fundevogel/php-pcbis) [![Build](https://ci.codeberg.org/api/badges/Fundevogel/php-pcbis/status.svg)](https://codeberg.org/fundevogel/php-pcbis/issues)


## What

This small library is a PHP wrapper for [pcbis.de](https://pcbis.de), gathering information about books through wholesale book distributor [KNV](http://knv.de)'s API. For the documentation on their [WSDL](https://en.wikipedia.org/wiki/Web_Services_Description_Language) interface, see [here](docs/Webservice_2.0.19.pdf).


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

# Create API object, passing credentials as first parameter (for caching, see below)
$object = new Pcbis([/* ... */]);

try {
    # After loading a book, you might want to ..
    $book = $object->load('978-3-522-20255-8');
    # (1) .. export its bibliographic data
    $data = $book->export();

    # (2) .. access specific information
     echo $book->title();

    # (3) .. download its cover
    $book->downloadCover();
    # Note: For this to work, you have to install 'Guzzle':
    # composer require guzzlehttp/guzzle

    # (4) .. query its OLA status
    $book->ola()->isAvailable();

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage(), "\n";
}
```

**Note**: Starting with v3, ISBN validation is no longer enabled by default. If you want formatted (= hyphenated) ISBNs, `php-pcbis` takes care of this for you if [`nicebooks/isbn`](https://github.com/nicebooks-com/isbn) is installed via `composer require nicebooks/isbn`.


### Caching

For caching data fetched from KNV's API, you may use any [`symfony/cache`](https://symfony.com/doc/current/components/cache) object:

```text
composer require symfony/cache
```

For a standard file cache, your setup might then look something like this:

```php
use Fundevogel\Pcbis\Pcbis;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

# Initialize cache object
$cache = new FilesystemAdapter();

# Create API object, passing credentials & cache object as parameters
$object = new Pcbis([/* ... */], $cache);
```

**Note**: Since we use [KirbyCMS](https://getkirby.com/docs/guide/cache) on our website, we included a cache driver to make it work nicely with `php-pcbis`:

```php
use Fundevogel\Pcbis\Pcbis;
use Fundevogel\Pcbis\Cache\KirbyCache;

# Initialize cache object
$cache = new KirbyCache(kirby()->cache('my-cache'));

# Create API object, passing credentials & cache object as parameters
$object = new Pcbis([/* ... */], $cache);
```


## Credits

Most of the helper functions were taken from [Kirby](https://getkirby.com)'s excellent [`toolkit`](https://github.com/getkirby-v2/toolkit) package by [Bastian Allgeier](https://github.com/bastianallgeier) (who's just awesome, btw).

**Happy coding!**

:copyright: Fundevogel Kinder- und Jugendbuchhandlung
