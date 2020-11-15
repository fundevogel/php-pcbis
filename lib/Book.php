<?php

namespace PHPCBIS;

use PHPCBIS\Helpers\Butler;

/**
 * Class Book
 *
 * Holds information from KNV's API in a human-readable form &
 * downloads book covers from the German National Library
 *
 * @package PHPCBIS
 */

class Book
{
    /**
     * International Standard Book Number
     *
     * @var string
     */
    private $isbn;


    /**
     * Whether it's an audiobook
     *
     * @var bool
     */
    protected $isAudiobook = false;


    /**
     * Author
     *
     * @var array
     */
    protected $author;


    /**
     * Title
     *
     * @var string
     */
    protected $title;


    /**
     * Subtitle
     *
     * @var string
     */
    protected $subtitle;


    /**
     * Publisher
     *
     * @var string
     */
    protected $publisher;


    /**
     * Description
     *
     * @var string
     */
    protected $description;


    /**
     * Retail price (in €)
     *
     * @var string
     */
    protected $retailPrice;


    /**
     * Release year
     *
     * @var string
     */
    protected $releaseYear;


    /**
     * Minimum age recommendation
     *
     * @var string
     */
    protected $age;


    /**
     * Binding
     *
     * @var string
     */
    protected $binding;


    /**
     * Page count
     *
     * @var string
     */
    protected $pageCount;


    /**
     * Dimensions (width x height)
     *
     * @var string
     */
    protected $dimensions;


    /**
     * Category
     *
     * @var string
     */
    protected $category;


    /**
     * Topics
     *
     * @var array
     */
    protected $topics;


    /**
     * Duration (audiobook only)
     *
     * @var string
     */
    protected $duration;


    /**
     * Blocked topics
     *
     * @var array
     */
    protected $blockedTopics = [
        'Hörbuch',
        'Papp-Bilderbuch',
    ];


    /**
     * Path to downloaded book cover images
     *
     * @var string
     */
    protected $imagePath;


    /**
     * Translatable strings
     *
     * @var array
     */
    protected $translations;


    /**
     * User-Agent used when downloading book cover images
     *
     * @var string
     */
    protected $userAgent = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0';


    /**
     * Constructor
     */

    public function __construct(string $isbn, array $source, string $imagePath, array $translations) {
        # Store valid ISBN
        $this->isbn = $isbn;

        # Store data fetched from KNV's API
        $this->source = $source;

        if (isset($source['Einband']) && $source['Einband'] === 'CD') {
            $this->isAudiobook = true;
        }

        # Build bibliographic dataset
        $this->author      = $this->buildAuthor();
        $this->title       = $this->buildTitle();
        $this->subtitle    = $this->buildSubtitle();
        $this->publisher   = $this->buildPublisher();
        // $this->participants = $this->buildParticipants();
        // $this->illustrator = $this->buildIllustrator();
        // $this->translator = $this->buildTranslator();
        $this->description = $this->buildDescription();
        $this->retailPrice = $this->buildretailPrice();
        $this->releaseYear = $this->buildreleaseYear();
        $this->age         = $this->buildAge();
        $this->binding     = $this->buildBinding();
        $this->pageCount   = $this->buildPageCount();
        $this->dimensions  = $this->buildDimensions();

        $tags = $this->separateTags();
        $this->category    = $this->buildCategory($tags);
        $this->topics      = $this->buildTopics($tags);

        // # Build audiobook specifics
        $this->duration = $this->buildDuration();
        // $this->director = $this->buildDirector();
        // $this->narrator = $this->buildNarrator();

        # Import image path & translations
        $this->imagePath = $imagePath;
        $this->translations = $translations;
    }


    /**
     * Setters & getters
     */

    public function setBlockedTopics(string $blockedTopics)
    {
        $this->blockedTopics = $blockedTopics;
    }

    public function getBlockedTopics()
    {
        return $this->blockedTopics;
    }

    public function setUserAgent(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }


    /**
     * Methods
     */

    /**
     * Downloads book cover from DNB
     *
     * @param string $fileName - Filename for the image to be downloaded
     * @param bool $overwrite - Whether existing file should be overwritten
     * @return bool
     */
    public function downloadCover(string $fileName = null, bool $overwrite = false): bool
    {
        if ($fileName == null) {
            $fileName = $this->isbn;
        }

        $file = realpath($this->imagePath . '/' . $fileName . '.jpg');

        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        if (file_exists($file) && !$overwrite) {
            return true;
        }

        $success = false;

        if ($handle = fopen($file, 'w')) {
            $client = new \GuzzleHttp\Client();
            $url = 'https://portal.dnb.de/opac/mvb/cover.htm?isbn=' . $this->isbn;

            try {
                $response = $client->get($url, ['sink' => $handle]);
                $success = true;
            } catch (\GuzzleHttp\Exception\ClientException $e) {}
        }

        return $success;
    }


    /**
     * Checks whether this is an audiobook
     *
     * @return bool
     */
    public function isAudiobook(): bool
    {
        return $this->isAudiobook;
    }


    /**
     * Builds author(s)
     *
     * @return array
     */
    protected function buildAuthor(): array
    {
        if (!isset($this->source['AutorSachtitel'])) {
            return '';
        }

        $string = $this->source['AutorSachtitel'];

        $array = Butler::split($string, ';');
        $authors = [];

        foreach ($array as $author) {
            $authorArray = Butler::split($author, ',');

            $authors[] = [
                'firstName' => $authorArray[1],
                'lastName' => $authorArray[0],
            ];
        }

        return $authors;
    }


    public function setAuthor($author)
    {
        $this->author = $author;
    }


    public function getAuthor(bool $formatted = false)
    {
        if (!$formatted) {
            return $this->author;
        }

        $authors = [];

        foreach ($this->author as $author) {
            $authors[] = $author['firstName'] . ' ' . $author['lastName'];
        }

        return Butler::join($authors, '; ');
    }


    /**
     * Builds title
     *
     * @return string
     */
    protected function buildTitle(): string
    {
        if (!isset($this->source['Titel'])) {
            return '';
        }

        return $this->source['Titel'];
    }


    public function setTitle($title)
    {
        $this->title = $title;
    }


    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Builds subtitle
     *
     * @return string
     */
    protected function buildSubtitle(): string
    {
        if (!isset($this->source['Utitel']) || $this->source['Utitel'] == null) {
            return '';
        }

        return $this->source['Utitel'];
    }


    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }


    public function getSubtitle()
    {
        return $this->subtitle;
    }


    /**
     * Builds publisher
     *
     * @return string
     */
    protected function buildPublisher(): string
    {
        if (!isset($this->source['IndexVerlag'])) {
            return '';
        }

        $publisher = $this->source['IndexVerlag'];

        if (is_array($publisher)) {
            $publisher = Butler::first($publisher);
        }

        return trim($publisher);
    }


    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }


    public function getPublisher()
    {
        return $this->publisher;
    }





    /**
     * Builds description
     *
     * @return string
     */
    protected function buildDescription(): string
    {
        if (!isset($array['Text1'])) {
            return '';
        }

        $string = $array['Text1'];
        $description = Butler::split($string, 'º');

        foreach ($description as $index => $text) {
            $text = htmlspecialchars_decode($text);
            $text = Butler::replace($text, '<br><br>', '. ');
            $text = Butler::unhtml($text);
            $description[$index] = $text;

            if (Butler::length($description[$index]) < 130 && count($description) > 1) {
                unset($description[array_search($text, $description)]);
            }
        }

        return Butler::first($description);
    }


    public function setDescription($description)
    {
        $this->description = $description;
    }


    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Builds retail price (in €)
     *
     * @return string
     */
    protected function buildRetailPrice(): string
    {
        // Input: XX(.YY)
        // Output: XX,YY
        if (!isset($this->source['PreisEurD'])) {
            return '';
        }

        $retailPrice = (float) $this->source['PreisEurD'];

        return number_format($retailPrice, 2, ',', '');
    }


    public function setRetailPrice($retailPrice)
    {
        $this->retailPrice = $retailPrice;
    }


    public function getRetailPrice()
    {
        return $this->retailPrice;
    }


    /**
     * Builds release year
     *
     * @return string
     */
    protected function buildReleaseYear(): string
    {
        if (!isset($this->source['Erschjahr'])) {
            return '';
        }

        return $this->source['Erschjahr'];
    }


    public function setReleaseYear($releaseYear)
    {
        $this->releaseYear = $releaseYear;
    }


    public function getReleaseYear()
    {
        return $this->releaseYear;
    }


    /**
     * Builds minimum age recommendation (in years)
     * TODO: Cater for months
     *
     * @return string
     */
    protected function buildAge(): string
    {
        if (!isset($this->source['Alter'])) {
            return '';
        }

        $age = Butler::substr($this->source['Alter'], 0, 2);

        if (Butler::substr($age, 0, 1) === '0') {
            $age = Butler::substr($age, 1, 1);
        }

      	return 'ab ' . $age . ' Jahren';
    }


    public function setAge($age)
    {
        $this->age = $age;
    }


    public function getAge()
    {
        return $this->age;
    }


    /**
     * Builds binding
     *
     * @return string
     */
    protected function buildBinding(): string
    {
        if (!isset($this->source['Einband'])) {
            return '';
        }

        $binding = $this->source['Einband'];

        if (!isset($this->translations[$binding])) {
            return $binding;
        }

        return $this->translations[$binding];
    }


    public function setBinding($binding)
    {
        $this->binding = $binding;
    }


    public function getBinding()
    {
        return $this->binding;
    }


    /**
     * Builds page count
     *
     * @return string
     */
    protected function buildPageCount(): string
    {
        if (!isset($this->source['Abb'])) {
            return '';
        }

        $string = $this->source['Abb'];
        $array = Butler::split($string, '.');

        foreach ($array as $line) {
            if (Butler::substr($line, -1) === 'S') {
                return Butler::split($line, ' ')[0];
            }
        }

        return $string;
    }


    public function setPageCount($pageCount)
    {
        $this->pageCount = $pageCount;
    }


    public function getPageCount()
    {
        return $this->pageCount;
    }


    /**
     * Builds dimensions (width x height)
     * TODO: Cover unconventional cases
     *
     * @return string
     */
    protected function buildDimensions(): string
    {
        if (!isset($this->source['Breite'])) {
            return '';
        }

        if (!isset($this->source['Hoehe'])) {
            return '';
        }

        $width = Butler::convertMM($this->source['Breite']);
        $height = Butler::convertMM($this->source['Hoehe']);

        return $width . ' x ' . $height;
    }


    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;
    }


    public function getDimensions()
    {
        return $this->dimensions;
    }


    /**
     * Extracts category & topics from source array
     *
     * @return array
     */
    private function separateTags(): array
    {
        if (!isset($this->source['IndexSchlagw'])) {
            return [];
        }

        $string = $this->source['IndexSchlagw'];

        if (is_string($string)) {
            $array = Butler::split(trim($string), ';');

            $category = count($array) === 2 ? $array[1] : '';
            $topics = Butler::contains($array[0], 'Antolin') ? '' : $array[0];
        } else {
            $category = [];
            $topics = [];

            foreach ($string as $tag) {
                $array = Butler::split(trim($tag), ';');

                // We don't need no .. Antolin
                if (count($array) === 1) {
                    if (Butler::contains($array[0], 'Antolin')) {
                        continue;
                    }

                    $topics[] = $array[0];
                }

                if (count($array) > 1) {
                    $topics[] = $array[0];
                    $category[] = $array[1];
                }
            }
        }

        return [
            'category' => $category,
            'topics' => $topics,
        ];
    }


    /**
     * Builds category
     * TODO: Check if `$array === false` is really necessary
     *
     * @param array $array - Separated tags (category + topcs)
     * @return string
     */
    protected function buildCategory(array $array): string
    {
        if ($this->isAudiobook) {
            return 'Hörbuch';
        }

        if (!isset($array['category']) || $array === false) {
            return '';
        }

        $category = $array['category'];

        if (is_string($category)) {
            if (Butler::contains(Butler::lower($category), 'sachbuch')) {
                return 'Sachbuch';
            }

            return $category;
        }

        if (empty($category)) {
            return '';
        }

        return Butler::join(array_unique($category), ', ');
    }


    public function setCategory($category)
    {
        $this->category = $category;
    }


    public function getCategory()
    {
        return $this->category;
    }


    /**
     * Builds topic(s)
     *
     * @param array $array - Separated tags (category + topcs)
     * @return string
     */
    protected function buildTopics(array $array): array
    {
        if (!isset($array['topics']) || $array === false) {
            return [];
        }

        $topics = $array['topics'];

        if (is_string($topics)) {
            return $topics;
        }

        $topics = array_filter($topics, function ($topic) {
            if (!in_array($topic, $this->blockedTopics)) {
                return $topic;
            }
        });

        return array_unique($topics);
    }


    public function setTopics($topics)
    {
        $this->topics = $topics;
    }


    public function getTopics(bool $formatted = false)
    {
        if (!$formatted) {
            return $this->topics;
        }

        return Butler::join($this->topics, ', ');
    }


    /**
     * Builds duration (audiobook only)
     * TODO: Use regex voodoo
     *
     * @return string
     */
    protected function buildDuration(): string
    {
        if (!isset($this->source['Utitel']) || !$this->isAudiobook()) {
            return '';
        }

        $string = $this->source['Utitel'];
        $array = Butler::split($string, '.');

        return Butler::replace(Butler::last($array), ' Min', '');
    }


    public function setDuration($duration)
    {
        $this->duration = $duration;
    }


    public function getDuration()
    {
        return $this->duration;
    }


    /**
     * Exports all information, optionally as preformatted strings
     *
     * @return array
     */
    public function export(bool $formatted = false)
    {
        $data = [
            'AutorIn' => $this->getAuthor($formatted),
            'Titel' => $this->title,
            'Untertitel' => $this->subtitle,
            'Verlag' => $this->publisher,
            // 'Mitwirkende' => $this->participants,
            // 'IllustratorIn' => $this->illustrator,
            // 'ÜbersetzerIn' => $this->translator,
            'Preis' => $this->retailPrice,
            'Erscheinungsjahr' => $this->releaseYear,
            'Altersempfehlung' => $this->age,
            'Inhaltsbeschreibung' => $this->description,
            'Einband' => $this->binding,
            'Seitenzahl' => $this->pageCount,
            'Abmessungen' => $this->dimensions,
            'Kategorie' => $this->category,
            'Themen' => $this->getTopics($formatted),
        ];

        // // If it's an audiobook ..
        // if ($this->isAudiobook) {
        //     // .. add entries exclusive to audiobooks
        //     $dataOutput = Butler::update($dataOutput, [
        //         'Dauer' => $this->Duration,
        //         'RegisseurIn' => $this->Participants($dataInput, 'Regie'),
        //         'SprecherIn' => $this->Participants($dataInput, 'Gesprochen von'),
        //     ]);
        // }

        return $data;
    }




    /**
     * TODO
     */

    /**
     * Builds participants
     *
     * This may also be used to retrieve illustrator(s) & translator(s)
     *
     * @return string
     */
    protected function buildParticipants(array $array, string $groupTask = ''): string
    {
        if (!isset($array['Mitarb'])) {
            return '';
        }

        $participants = $array['Mitarb'];

        $spoken1 = 'Gesprochen von';
        $spoken2 = 'Gesprochen:';

        // 'Mitarbeit: ... Regie: ... Gesprochen von XY'
        if (Butler::contains($array['Mitarb'], $spoken1) && $groupTask !== '') {
            $participantArray = Butler::split($array['Mitarb'], $spoken1);
            $participants = $participantArray[0];

            if ($groupTask === $spoken1) {
                $speakers = Butler::last($participantArray);

                $result = [];

                foreach (Butler::split($speakers, ';') as $speaker) {
                    $result[] = Butler::reverseName($speaker);
                }

                return Butler::join($result, ', ');
            }
        }

        // 'Mitarbeit: ... Regie: ... Gesprochen: XY'
        if (Butler::contains($participants, $spoken2) && $groupTask !== '') {
            $participantArray = Butler::split($participants, $spoken2);
            $speaker = Butler::last($participantArray);

            if ($groupTask === $spoken2 = $spoken1) {
                return Butler::reverseName($speaker);
            }

            return '';
        }

        // 'Gesprochen von XY' & 'Gesprochen: XY'
        foreach ([$spoken1, $spoken2] as $spoken) {
            if (Butler::startsWith($array['Mitarb'], $spoken)) {
                if ($groupTask === $spoken2 = $spoken1) {
                    $string = Butler::replace($array['Mitarb'], $spoken, '');

                    return Butler::reverseName($string);
                }

                return '';
            }
        }

        $result = [];

        foreach (Butler::split($participants, '.') as $group) {
            $groupArray = Butler::split($group, ':');
            $task = $groupArray[0];

            $delimiter = $this->isAudiobook($array) ? '.' : ';';

            $people = Butler::split($groupArray[1], $delimiter);
            $peopleArray = [];

            foreach ($people as $person) {
                $peopleArray[] = Butler::reverseName($person);
            }

            $peopleString = Butler::join($peopleArray, ' & ');


            if ($groupTask !== '') {
                if ($groupTask === $task) {
                    return $peopleString;
                }

                continue;
            }

            $result[] = Butler::join([$task, $peopleString], ': ');
        }

        return Butler::join($result, '; ');
    }
}
