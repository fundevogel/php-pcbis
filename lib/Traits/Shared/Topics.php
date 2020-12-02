<?php

namespace Pcbis\Traits\Shared;

use Pcbis\Helpers\Butler;


/**
 * Trait Topics
 *
 * Provides ability to extract topics (book/audiobook)
 *
 * @package PHPCBIS
 */

trait Topics
{
    /**
     * Properties
     */

    /**
     * List of blocked topics
     *
     * @var array
     */
    protected $blockList = [
        # Rather categories than tags
        'Hörbuch',
        'Papp-Bilderbuch',
        'Umwelt-Bilderbuch',
        'Vorlesebuch',

        # Highly sophisticated ways to say 'book for kids'
        # (1) Non-fiction for kids
        'Kinder-/Jugendsachbuch',
        'Kindersachbuch/Jugendsachbuch',
        'Kindersachbuch/Jugendsachbuch.',
        # (2) Literature for children & adolescents
        'Kinderliteratur/Jugendliteratur',
        'Kinder-/Jugendliteratur',
        'Kinder/Jugendliteratur',
        'Kinder-/Jugendlit.',
        'Kinder/Jugendlit.',
    ];


    /**
     * Setters & getters
     */

    public function setBlockList(array $blockList)
    {
        $this->blockList = $blockList;
    }


    public function getBlockList(): array
    {
        return $this->blockList;
    }


    /**
     * Methods
     */

    /**
     * Builds topics
     *
     * @return array
     */
    protected function buildTopics(): array
    {
        $tags = parent::buildTopics();

        $translations = [
            'Auto / Personenwagen / Pkw' => 'Autos',
            'Coming of Age / Erwachsenwerden' => 'Erwachsenwerden',
            'Demenz / Alzheimersche Krankheit' => 'Demenz',
            'Deutsche Demokratische Republik (DDR)' => 'DDR',
            'Flucht / Flüchtling' => 'Flucht',
            'IM (Staatssicherheitsdienst)' => 'Inoffizielle MitarbeiterInnen',
            'Klassenfahrt / Schulfahrt' => 'Klassenfahrt',
            'Klassiker (Literatur)' => 'Klassiker',
            'Klimaschutz, Klimawandel / Klimaveränderung' => 'Klimaschutz',
            'Klimawandel / Klimaveränderung' => 'Klimawandel',
            'Krebs (Krankheit) / Karzinom' => 'Krebserkrankung',
            'Leichte Sprache / Einfache Sprache' => 'Einfache Sprache',
            'Migration / Migrant' => 'Migration',
            'Regenwald / Dschungel' => 'Regenwald',
            'Schulanfang / Schulbeginn' => 'Schulanfang',
            'Selbstmord / Suizid / Freitod / Selbsttötung' => 'Selbsttötung',
            'Ski / Schi' => 'Skifahren',
            'Soziales Netzwerk (Internet) / Social Networking' => 'Social Media',
            'Spionage / Agent / Agentin / Spion / Spionin' => 'GeheimagentIn',
            'Staatssicherheitsdienst (Stasi)' => 'Stasi',
            'Traum / Träumen / Traumdeutung / Traumanalyse' => 'Traum',
            'Wolf / Wölfe (Tier)' => 'Wölfe',
        ];

        if (!empty($this->translations)) {
            $translations = $this->translations;
        }

        $topics = array_map(function ($topic) use ($translations) {
            # Skip blocklisted topics
            if (in_array($topic, $this->blockList)) {
                return '';
            }

            # Skip 'Antolin' rating
            if (Butler::startsWith($topic, 'Antolin')) {
                return '';
            }

            if (isset($translations[$topic])) {
                return $translations[$topic];
            }

            return $topic;
        }, $tags);

        return array_filter($topics);
    }
}
