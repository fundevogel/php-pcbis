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
     * Source data fetched from KNV's API
     *
     * @var array
     */
    private $source;


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
     * Duration (audiobook only)
     *
     * @var string
     */
    protected $duration;


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
     * Illustrator
     *
     * @var array
     */
    protected $illustrator;


    /**
     * Translator
     *
     * @var array
     */
    protected $translator;


    /**
     * Director
     *
     * @var array
     */
    protected $director;


    /**
     * Narrator
     *
     * @var array
     */
    protected $narrator;


    /**
     * Participant
     *
     * @var array
     */
    protected $participant;


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
    protected $translations = [];


    /**
     * User-Agent used when downloading book cover images
     *
     * @var string
     */
    protected $userAgent = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0';


    /**
     * Constructor
     */

    public function __construct(string $isbn, array $source, string $imagePath) {
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
        $this->description = $this->buildDescription();
        $this->retailPrice = $this->buildretailPrice();
        $this->releaseYear = $this->buildreleaseYear();
        $this->age         = $this->buildAge();
        $this->binding     = $this->buildBinding();
        $this->pageCount   = $this->buildPageCount();
        $this->dimensions  = $this->buildDimensions();
        $this->duration    = $this->buildDuration();

        # Extract category & topics
        $tags = $this->separateTags();
        $this->category    = $this->buildCategory($tags);
        $this->topics      = $this->buildTopics($tags);

        # Extract involved people
        $people = $this->separatePeople();
        $this->illustrator = $this->buildIllustrator($people);
        $this->translator  = $this->buildTranslator($people);
        $this->director    = $this->buildDirector($people);
        $this->narrator    = $this->buildNarrator($people);
        $this->participant = $this->buildParticipant($people);

        # Import image path & translations
        $this->imagePath = $imagePath;
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

    public function setTranslations(array $translations)
    {
        $this->translations = $translations;
    }

    public function getTranslations()
    {
        return $this->translations;
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
     * Shows source data fetched from KNV's API
     *
     * @return array
     */
    public function showSource(): array
    {
        return $this->source;
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
                'lastName'  => $authorArray[0],
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
     * Extracts involved people from source array
     *
     * This includes `illustrator`, `translator`, `director`, `narrator` & `participant`
     *
     * @return array
     */
    private function separatePeople(): array
    {
        $people = [
            'illustrator' => [],
            'translator' => [],
            'director' => [],
            'narrator' => [],
            'participant' => [],
        ];

        if (!isset($this->source['Mitarb'])) {
            return $people;
        }

        $string = $this->source['Mitarb'];

        # Narrator role
        $delimiter1 = 'Gesprochen von';
        $delimiter2 = 'Gesprochen:';

        foreach ([$delimiter1, $delimiter2] as $delimiter) {
            # Case 1: 'Gesprochen von / Gesprochen: XY'
            if (Butler::startsWith($string, $delimiter)) {
                $case1 = Butler::replace($string, $delimiter, '');
                $array = Butler::split($case1, ',');

                # Edge case: single entry, such as 'Diverse'
                $lastName = '';

                if (count($array) > 1) {
                    $firstName = $array[1];
                }

                $people['narrator'][] = [
                    'firstName' => $firstName,
                    'lastName'  => $array[0],
                ];

                # Case 1 yields only a single narrator
                // break;
                return $people;
            }

            # Case 2: '... Gesprochen von / Gesprochen: XY'
            if (Butler::contains($string, $delimiter)) {
                $array = Butler::split($string, '.');
                $case2 = Butler::replace(Butler::last($array), $delimiter, '');

                foreach (Butler::split($case2, ';') as $narrator) {
                    $narratorArray = Butler::split($narrator, ',');

                    # Edge case: single entry, such as 'Diverse'
                    $firstName = '';

                    if (count($narratorArray) > 1) {
                        $firstName = $narratorArray[1];
                    }

                    $people['narrator'][] = [
                        'firstName' => $firstName,
                        'lastName'  => $narratorArray[0],
                    ];
                }

                # Case 2 yields more participants
                $string = Butler::split($string, $delimiter)[0];
                break;
            }
        }

        # Remaining roles
        $tasks = [
            'Illustration' => 'illustrator',
            'Übersetzung'  => 'translator',
            'Regie'        => 'director',
            'Mitarbeit'    => 'participant',
        ];

        // if (empty($array)) {
        //     continue;
        // }
        foreach (Butler::split($string, '.') as $array) {
            $array = Butler::split($array, ':');

            # Determine role
            $task = 'participant';

            if (isset($tasks[$array[0]])) {
                $task = $tasks[$array[0]];
            }

            $array = Butler::split($array[1], ';');

            foreach ($array as $case3) {
                $person = Butler::split($case3, ',');

                $people[$task][] = [
                    'firstName' => $person[1],
                    'lastName'  => $person[0],
                ];
            }
        }

        return $people;
    }


    private function exportRole(array $array, string $role): string
    {
        if (!$array) {
            return '';
        }

        $people = [];

        foreach ($array as $person) {
            $people[] = $person['firstName'] . ' ' . $person['lastName'];
        }

        return Butler::join($people, '; ');
    }


    private function exportPeople(array $people)
    {
        // $participants = [];

        // foreach ($this->participants as $task => $group) {
        //     if (empty($group)) {
        //         continue;
        //     }

        //     $data = [];

        //     foreach ($group as $participant) {
        //         $data[] = $participant['firstName'] . ' ' . $participant['lastName'];
        //     }

        //     $translations = [
        //         'illustrator' => 'IllustratorIn',
        //         'translator' => 'ÜbersetzerIn',
        //         'director' => 'RegisseurIn',
        //         'narrator' => 'SprecherIn',
        //         'participant' => 'Mitwirkende',
        //     ];

        //     if (!empty($this->translations)) {
        //         $translations = $this->translations;
        //     }

        //     if (!isset($translations[$task])) {
        //         throw new \Exception('No translation found: ' . $task);
        //     }

        //     $participants[] = $translations[$task] . ': ' . Butler::join($data, '; ');
        // }

        // return Butler::join($participants, '; ');
    }


    private function extractRole(array $people, string $role): array
    {
        return $people[$role];
    }


    /**
     * Builds illustrator
     *
     * @param array $people - People involved
     * @return string
     */
    protected function buildIllustrator(array $people): array
    {
        return $this->extractRole($people, 'illustrator');
    }


    public function setIllustrator($illustrator)
    {
        $this->illustrator = $illustrator;
    }


    public function getIllustrator(bool $formatted = false)
    {
        if (!$formatted) {
            return $this->illustrator;
        }

        $this->exportRole($this->illustrator, 'illustrator');
    }


    /**
     * Builds translator
     *
     * @param array $people - People involved
     * @return string
     */
    protected function buildTranslator(array $people): array
    {
        return $this->extractRole($people, 'translator');
    }


    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }


    public function getTranslator(bool $formatted = false)
    {
        if (!$formatted) {
            return $this->translator;
        }

        $this->exportRole($this->translator, 'translator');
    }


    /**
     * Builds director
     *
     * @param array $people - People involved
     * @return string
     */
    protected function buildDirector(array $people): array
    {
        return $this->extractRole($people, 'director');
    }


    public function setDirector($director)
    {
        $this->director = $director;
    }


    public function getDirector(bool $formatted = false)
    {
        if (!$formatted) {
            return $this->director;
        }

        $this->exportRole($this->director, 'director');
    }


    /**
     * Builds narrator
     *
     * @param array $people - People involved
     * @return string
     */
    protected function buildNarrator(array $people): array
    {
        return $this->extractRole($people, 'narrator');
    }


    public function setNarrator($narrator)
    {
        $this->narrator = $narrator;
    }


    public function getNarrator(bool $formatted = false)
    {
        if (!$formatted) {
            return $this->narrator;
        }

        $this->exportRole($this->narrator, 'narrator');
    }


    /**
     * Builds participant
     *
     * @param array $people - People involved
     * @return string
     */
    protected function buildParticipant(array $people): array
    {
        return $this->extractRole($people, 'participant');
    }


    public function setParticipant($participant)
    {
        $this->participant = $participant;
    }


    public function getParticipant(bool $formatted = false)
    {
        if (!$formatted) {
            return $this->participant;
        }

        $this->exportRole($this->participant, 'participant');
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

        $translations = [
            'BUCH' => 'gebunden',
            'CD'   => 'CD',
            'CRD'  => 'Nonbook',
            'GEB'  => 'gebunden',
            'GEH'  => 'geheftet',
            'HL'   => 'Halbleinen',
            'KT'   => 'kartoniert',
            'LN'   => 'Leinen',
            'NON'  => 'Nonbook',
            'PP'   => 'Pappband',
            'SPL'  => 'Spiel',
        ];

        if (!empty($this->translations)) {
            $translations = $this->translations;
        }

        if (!isset($translations[$binding])) {
            return $binding;
        }

        return $translations[$binding];
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
            $topics = (array) $topics;
        }

        $topics = array_filter($topics, function ($topic) {
            if (!in_array($topic, $this->blockedTopics, true)) {
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
     * Exports all information, optionally as pre-formatted strings
     *
     * @return array
     */
    public function export(bool $formatted = false): array
    {
        $data = [
            'AutorIn'          => $this->getAuthor($formatted),
            'Titel'            => $this->getTitle(),
            'Untertitel'       => $this->getSubtitle(),
            'Verlag'           => $this->getPublisher(),
            'Preis'            => $this->getRetailPrice(),
            'Erscheinungsjahr' => $this->getReleaseYear(),
            'Altersempfehlung' => $this->getAge(),
            'Inhaltsangabe'    => $this->getDescription(),
            'Einband'          => $this->getBinding(),
            'Seitenzahl'       => $this->getPageCount(),
            'Abmessungen'      => $this->getDimensions(),
            'Dauer'            => $this->getDuration(),
            'IllustratorIn'    => $this->getIllustrator($formatted),
            'ÜbersetzerIn'     => $this->getTranslator($formatted),
            'RegisseurIn'      => $this->getDirector($formatted),
            'SprecherIn'       => $this->getNarrator($formatted),
            'Mitwirkende'      => $this->getParticipant($formatted),
            'Kategorie'        => $this->getCategory(),
            'Themen'           => $this->getTopics($formatted),
        ];

        return $data;
    }




    /**
     * TODO: Illustrator, translator, director, narrator + partaking people
     */

}