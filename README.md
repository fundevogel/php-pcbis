# PHPCBIS
[![Release](https://img.shields.io/github/release/Fundevogel/php-pcbis.svg)](https://github.com/Fundevogel/php-pcbis/releases) [![License](https://img.shields.io/github/license/Fundevogel/php-pcbis.svg)](https://github.com/Fundevogel/php-pcbis/blob/master/LICENSE) [![Issues](https://img.shields.io/github/issues/Fundevogel/php-pcbis.svg)](https://github.com/Fundevogel/php-pcbis/issues) [![Status](https://travis-ci.org/fundevogel/php-pcbis.svg?branch=master)](https://travis-ci.org/fundevogel/php-pcbis)

## What
This small library is a PHP wrapper for [pcbis.de](https://pcbis.de), gathering information about books through wholesale book distributor [KNV](http://knv.de)'s API. For the documentation on their [WSDL](https://en.wikipedia.org/wiki/Web_Services_Description_Language) interface, see [here](http://www.knv-zeitfracht.de/wp-content/uploads/2020/07/Webservice_2.0.pdf).


## Why
It powers [our book recommendations](https://fundevogel.de/en/recommendations) & downloads cover images from the [German National Library](https://www.dnb.de/EN/Home/home_node.html).


## How
It's available for [Composer](https://getcomposer.org):

```text
composer require fundevogel/php-pcbis
```


## Basic workflow
.. to be continued.

### Loading translations
This library provides minimal translations for german strings out-of-the-box. However, you may want to bring your own - for example, you could load a JSON file, looking like this:

```json
{
    "BUCH": "gebunden",
    "CD": "CD",
    "HL": "Halbleinen",
    "KT": "kartoniert",
    "LN": "Leinen",
    "PP": "Pappband",
    "CRD": "Nonbook",
    "GEB": "gebunden",
    "GEH": "geheftet",
    "NON": "Nonbook",
    "SPL": "Spiel"
}
```

.. and load its contents with `setTranslations()`:

```php
<?php

$file = file_get_contents('/path/to/translations.json');
$translations = json_decode($file, true);

// For providing login credentials, see above
$book = new PHPCBIS()->loadBook('some-isbn');
$book->setTranslations($translations);
```


## Credits
Most of the helper functions were taken from [Kirby](https://getkirby.com)'s excellent [`toolkit`](https://github.com/getkirby-v2/toolkit) package by [Bastian Allgeier](https://github.com/bastianallgeier) (who's just awesome, btw).


**Happy coding!**


:copyright: Fundevogel Kinder- und Jugendbuchhandlung
