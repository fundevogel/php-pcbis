<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Products;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Api\Ola;
use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Traits\OlaStatus;
use Fundevogel\Pcbis\Traits\People;
use Fundevogel\Pcbis\Traits\Tags;

use Exception;

/**
 * Class ProductAbstract
 *
 * Serves as template for products
 */
abstract class ProductAbstract
{
    /**
     * Traits
     */

    use OlaStatus;
    use People;
    use Tags;


    /**
     * Properties
     */

    /**
     * Product EAN/ISBN
     *
     * @var string
     */
    protected $identifier;


    /**
     * Constructor
     *
     * @param array $data Source data fetched from KNV's API
     * @param \Fundevogel\Pcbis\Webservice $api Object granting access to KNV's API
     */
    public function __construct(public array $data, protected Webservice $api)
    {
        # Store product EAN/ISBN
        $this->identifier = $this->data['EAN'];

        # If present ..
        if (class_exists('Nicebooks\Isbn\Isbn')) {
            # .. attempt to ..
            try {
                # .. format product EAN/ISBN using third-party tools
                $this->identifier = \Nicebooks\Isbn\Isbn::of($this->identifier)->format();
            } catch (Exception $e) {
            }
        }

        # Add startup hook
        $this->setup();
    }


    /**
     * Setup hook
     *
     * @return void
     */
    public function setup(): void
    {
        $this->people = $this->setUpPeople();
        $this->tags   = $this->setUpTags();
    }


    /**
     * Methods
     */

    /**
     * Checks whether product has a predecessor
     *
     * @return bool
     */
    public function hasDowngrade(): bool
    {
        return isset($this->data['VorherigeAuflageGtin']);
    }


    /**
     * Loads & returns predecessor
     *
     * @return self
     */
    public function downgrade()
    {
        if (!isset($this->data['VorherigeAuflageGtin'])) {
            return $this;
        }

        return $this->api->load($this->data['VorherigeAuflageGtin']);
    }


    /**
     * Checks whether product has a successor
     *
     * @return bool
     */
    public function hasUpgrade(): bool
    {
        return isset($this->data['NeueAuflageGtin']);
    }


    /**
     * Loads & returns successor
     *
     * @return self
     */
    public function upgrade()
    {
        if (!isset($this->data['NeueAuflageGtin'])) {
            return $this;
        }

        return $this->api->load($this->data['NeueAuflageGtin']);
    }


    /**
     * Exports OLA record
     *
     * @param int $quantity Number of products to be delivered
     * @return \Fundevogel\Pcbis\Api\Ola
     */
    public function ola(int $quantity = 1): Ola
    {
        return $this->api->ola($this->identifier, $quantity);
    }


    /**
     * Type detection helper
     *
     * @return string
     */
    private function type(): string
    {
        # Extract class name (= product type)
        return A::last(explode('\\', get_class($this)));
    }


    /**
     * Checks whether this is a book
     *
     * @return bool
     */
    public function isBook(): bool
    {
        return in_array($this->type(), [
            # Base
            'Book',

            # Subset
            'Ebook',
            'Hardcover',
            'Schoolbook',
            'Softcover',
        ]);
    }


    /**
     * Checks whether this is an eBook
     *
     * @return bool
     */
    public function isEbook(): bool
    {
        return $this->type() == 'Ebook';
    }


    /**
     * Checks whether this is a hardcover book
     *
     * @return bool
     */
    public function isHardcover(): bool
    {
        return $this->type() == 'Hardcover';
    }


    /**
     * Checks whether this is a schoolbook
     *
     * @return bool
     */
    public function isSchoolbook(): bool
    {
        return $this->type() == 'Schoolbook';
    }


    /**
     * Checks whether this is a softcover book
     *
     * @return bool
     */
    public function isSoftcover(): bool
    {
        return $this->type() == 'Softcover';
    }


    /**
     * Checks whether this is a medium
     *
     * @return bool
     */
    public function isMedia(): bool
    {
        return in_array($this->type(), [
            # Base
            'Medium',

            # Subset
            'Audiobook',
            'Movie',
            'Music',
            'Sound',
        ]);
    }


    /**
     * Checks whether this is an audiobook
     *
     * @return bool
     */
    public function isAudiobook(): bool
    {
        return $this->type() == 'Audiobook';
    }


    /**
     * Checks whether this is a movie
     *
     * @return bool
     */
    public function isMovie(): bool
    {
        return $this->type() == 'Movie';
    }


    /**
     * Checks whether this is music
     *
     * @return bool
     */
    public function isMusic(): bool
    {
        return $this->type() == 'Music';
    }


    /**
     * Checks whether this is a sound storage medium
     *
     * @return bool
     */
    public function isSound(): bool
    {
        return $this->type() == 'Sound';
    }


    /**
     * Checks whether this is an item
     *
     * @return bool
     */
    public function isItem(): bool
    {
        return in_array($this->type(), [
            # Base
            'Item',

            # Subset
            'Boardgame',
            'Calendar',
            'Map',
            'Nonbook',
            'Notes',
            'Software',
            'Stationery',
            'Toy',
            'Videogame',
        ]);
    }


    /**
     * Checks whether this is a boardgame
     *
     * @return bool
     */
    public function isBoardgame(): bool
    {
        return $this->type() == 'Boardgame';
    }


    /**
     * Checks whether this is a calendar
     *
     * @return bool
     */
    public function isCalendar(): bool
    {
        return $this->type() == 'Calendar';
    }


    /**
     * Checks whether this is a map
     *
     * @return bool
     */
    public function isMap(): bool
    {
        return $this->type() == 'Map';
    }


    /**
     * Checks whether this is a generic item
     *
     * @return bool
     */
    public function isNonbook(): bool
    {
        return $this->type() == 'Nonbook';
    }


    /**
     * Checks whether these are notes
     *
     * @return bool
     */
    public function isNotes(): bool
    {
        return $this->type() == 'Notes';
    }


    /**
     * Checks whether this is software
     *
     * @return bool
     */
    public function isSoftware(): bool
    {
        return $this->type() == 'Software';
    }


    /**
     * Checks whether this is stationery
     *
     * @return bool
     */
    public function isStationery(): bool
    {
        return $this->type() == 'Stationery';
    }


    /**
     * Checks whether this is a toy
     *
     * @return bool
     */
    public function isToy(): bool
    {
        return $this->type() == 'Toy';
    }


    /**
     * Checks whether this is a videogame
     *
     * @return bool
     */
    public function isVideogame(): bool
    {
        return $this->type() == 'Videogame';
    }



    /**
     * Exports OLA code
     *
     * @return string
     */
    public function olaCode(): string
    {
        if (isset($this->data['Mnr'])) {
            return $this->data['Mnr'];
        }

        return '';
    }


    /**
     * Exports OLA message
     *
     * @return string
     */
    public function olaMessage(): string
    {
        if (array_key_exists($this->olaCode, $this->olaMessages)) {
            return $this->olaMessages[$this->olaCode];
        }

        return '';
    }


    /**
     * Checks whether product is available (= purchasable)
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if ($this->hasOlaCode()) {
            return in_array($this->olaCode, $this->available);
        }

        return $this->ola()->isAvailable();
    }


    /**
     * Checks whether product is permanently unavailable
     *
     * @return bool
     */
    public function isUnavailable(): bool
    {
        if ($this->hasOlaCode()) {
            return in_array($this->olaCode, $this->unavailable);
        }

        return !$this->isAvailable();
    }
}
