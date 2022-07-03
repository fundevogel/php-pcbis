<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Products\Nonbook\Types;

use Fundevogel\Pcbis\Helpers\Str;
use Fundevogel\Pcbis\Products\Nonbook\Item;

/**
 * Class Boardgame
 *
 * KNV product category 'Spiel'
 */
class Boardgame extends Item
{
    /**
     * Dataset methods
     */

    /**
     * Exports number of players
     *
     * @return string
     */
    public function playerCount(): string
    {
        # TODO: Prevent subtitle containing player count!
        if (!isset($this->data['Utitel'])) {
            return '';
        }

        $playerCount = '';

        if (preg_match('/Für\s(.*)\sSpieler/', $this->data['Utitel'], $matches)) {
            $playerCount = $matches[1];
        }

        return $playerCount;
    }


    /**
     * Exports estimated playing time (in minutes)
     *
     * @return string
     */
    public function playingTime(): string
    {
        if (!isset($this->data['Utitel'])) {
            return '';
        }

        $playingTime = '';

        if (preg_match('/Spieldauer:\s(.*)\sMin/', $this->data['Utitel'], $matches)) {
            $playingTime = $matches[1];
        }

        # Edge case: Subtitle string too long, playing could not be found
        if (empty($playingTime)) {
            # (1) Try looping over tags
            if (isset($this->data['IndexStichw']) && is_array($this->data['IndexStichw'])) {
                foreach ($this->data['IndexStichw'] as $index => $tag) {
                    # Match each tag for term 'playing time' ..
                    if (Str::contains(Str::lower($tag), 'spieldauer')) {
                        # .. which means the next entry contains playing time ..
                        $playingTime = $this->data['IndexStichw'][$index + 1];

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
     * Exports all data
     *
     * @return array
     */
    public function export(): array
    {
        # Build dataset
        return array_merge(parent::export(), [
            # 'Boardgame' specific data
            'Spieleranzahl' => $this->playerCount(),
            'Spieldauer'    => $this->playingTime(),
        ]);
    }
}
