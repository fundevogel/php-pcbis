<?php

namespace Pcbis\Products\Nonbook\Types;

use Pcbis\Helpers\Butler;
use Pcbis\Products\Nonbook\Item;


/**
 * Class Boardgame
 *
 * KNV product category 'Spiel'
 *
 * @package PHPCBIS
 */

class Boardgame extends Item {
    /**
     * Properties
     */

    /**
     * Number of players
     *
     * @var string
     */
    protected $playerCount;


    /**
     * Estimated playing time (in minutes)
     *
     * @var string
     */
    protected $playingTime;


    /**
     * Constructor
     */

    public function __construct(array $source, array $props)
    {
        parent::__construct($source, $props);

        # Extend dataset
        $this->playerCount = $this->buildPlayerCount();
        $this->playingTime = $this->buildPlayingTime();
    }


    /**
     * Methods
     */

    /**
     * Builds number of players
     *
     * @return string
     */
    protected function buildPlayerCount(): string
    {
        if (!isset($this->source['Utitel'])) {
            return '';
        }

        $playerCount = '';

        if (preg_match('/Für\s(.*)\sSpieler/', $this->source['Utitel'], $matches)) {
            $playerCount = $matches[1];
        }

        return $playerCount;
    }


    /**
     * Exports number of players
     *
     * @return string
     */
    public function playerCount(): string
    {
        return $this->playerCount;
    }


    /**
     * Builds estimated playing time
     *
     * @return string
     */
    protected function buildPlayingTime(): string
    {
        if (!isset($this->source['Utitel'])) {
            return '';
        }

        $playingTime = '';

        if (preg_match('/Spieldauer:\s(.*)\sMin/', $this->source['Utitel'], $matches)) {
            $playingTime = $matches[1];
        }

        # Edge case: Subtitle string too long, playing could not be found
        if (empty($playingTime)) {
            # (1) Try looping over tags
            if (isset($this->source['IndexStichw']) && is_array($this->source['IndexStichw']) === true) {
                foreach ($this->source['IndexStichw'] as $index => $tag) {
                    # Match each tag for term 'playing time' ..
                    if (Butler::contains(Butler::lower($tag), 'spieldauer')) {
                        # .. which means the next entry contains playing time ..
                        $playingTime = $this->source['IndexStichw'][$index + 1];

                        # .. so stop looping
                        break;
                    }
                }
            }
        }

        # Be safe, trim strings
        return trim($playingTime);
    }


    /**
     * Exports estimated playing time
     *
     * @return string
     */
    public function playingTime()
    {
        return $this->playingTime;
    }


    /**
     * Overrides
     */

    /**
     * Exports all data
     *
     * @param bool $asArray - Whether to export an array (rather than a string)
     * @return array
     */
    public function export(bool $asArray = false): array
    {
        # Build dataset
        return array_merge(
            # (1) 'Item' dataset
            parent::export($asArray), [
            # (2) 'Boardgame' specific data
            'Spieleranzahl' => $this->playerCount(),
            'Spieldauer'    => $this->playingTime(),
        ]);
    }
}
