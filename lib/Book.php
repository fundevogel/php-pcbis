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
     * Whether source data was fetched from cache
     *
     * @var bool
     */
    protected $fromCache;


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
     * Tags (category & topics)
     *
     * @var array
     */
    private $tags;


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
     * Involved people (all roles)
     *
     * @var array
     */
    private $people;


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
     * Editor
     *
     * @var array
     */
    protected $editor;


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

    public function __construct(string $isbn, array $source, string $imagePath, bool $fromCache) {
        # Store valid ISBN
        $this->isbn = $isbn;

        # Store source data, fetched from KNV's API ..
        $this->source = $source;

        # .. or from cache?
        $this->fromCache = $fromCache;

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
        $this->tags        = $this->separateTags();
        $this->category    = $this->buildCategory();
        $this->topics      = $this->buildTopics();

        # Extract involved people
        $this->people      = $this->separatePeople();
        $this->illustrator = $this->buildIllustrator();
        $this->translator  = $this->buildTranslator();
        $this->director    = $this->buildDirector();
        $this->narrator    = $this->buildNarrator();
        $this->narrator    = $this->buildEditor();
        $this->participant = $this->buildParticipant();

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

    public function setISBN($isbn)
    {
        $this->isbn = $isbn;
    }

    public function getISBN()
    {
        return $this->isbn;
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
     * Checks whether source data was fetched from cache
     *
     * @return bool
     */
    public function fromCache(): bool
    {
        return $this->fromCache;
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
     * Extracts people from exploded strings (= arrays) & organizes them by first & last name
     *
     * @param array $people - Involved people
     * @return array
     */
    private function organizePeople(array $people): array
    {
        # Edge case: single person entry, such as 'Diverse'
        $array = [$people[0]];

        if (count($people) > 1) {
            $array = [
                'firstName' => $people[1],
                'lastName'  => $people[0],
            ];
        }

        return $array;
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

        $delimiter = ';';

        # When `AutorSachtitel` contains the title, try `IndexAutor`
        # TODO: Check `IndexAutor`: string, always array, whaddup?
        if (!Butler::contains($string, $delimiter) || is_array($string)) {
            $string = Butler::join($this->source['IndexAutor'], $delimiter);
        }

        $array = Butler::split($string, $delimiter);
        $authors = [];

        foreach ($array as $author) {
            $group = Butler::split($author, ',');

            $authors[] = $this->organizePeople($group);
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
            $authors[] = Butler::join($author, ' ');
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
            'editor' => [],
            'participant' => [],
        ];

        if (!isset($this->source['Mitarb'])) {
            return $people;
        }

        $string = $this->source['Mitarb'];

        # Editor role
        $publishedBy = 'Herausgegeben von';

        if (Butler::startsWith($string, $publishedBy)) {
            $case1 = Butler::replace($string, $publishedBy, '');
            $group = Butler::split($case1, ',');

            $people['editor'][] = $this->organizePeople($group);

            # Case 1 yields only a single editor
            return $people;
        }

        # Narrator role
        $delimiter1 = 'Gesprochen von';
        $delimiter2 = 'Gesprochen:';

        foreach ([$delimiter1, $delimiter2] as $delimiter) {
            # Case 1: 'Gesprochen von / Gesprochen: XY'
            if (Butler::startsWith($string, $delimiter)) {
                $case1 = Butler::replace($string, $delimiter, '');
                $group = Butler::split($case1, ',');

                $people['narrator'][] = $this->organizePeople($group);

                # Case 1 yields only a single narrator
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

        foreach (Butler::split($string, '.') as $array) {
            $array = Butler::split($array, ':');

            # Determine role
            # TODO: Not covered stuff, like 'Vorlage' (see 978-88-6312-438-5)
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


    /**
     * Extracts involved people from array created by `separatePeople()`
     *
     * @param string $role - Individual role
     * @return array
     */
    private function extractRole(string $role): array
    {
        return $this->people[$role];
    }


    /**
     * Exports involved people as string
     *
     * @param string $role - Individual role
     * @return string
     */
    private function exportRole(string $role): string
    {
        if (empty($this->people[$role])) {
            return '';
        }

        $array = [];

        foreach (array_values($this->people[$role]) as $person) {
            $array[] = Butler::join($person, ' ');
        }

        return Butler::join($array, '; ');
    }


    /**
     * Exports all involved people as (large) string
     *
     * @return string
     */
    public function exportPeople(): string
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


    /**
     * Builds illustrator
     *
     * @return array
     */
    protected function buildIllustrator(): array
    {
        return $this->extractRole('illustrator');
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

        return $this->exportRole('illustrator');
    }


    /**
     * Builds translator
     *
     * @return array
     */
    protected function buildTranslator(): array
    {
        return $this->extractRole('translator');
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

        return $this->exportRole('translator');
    }


    /**
     * Builds director
     *
     * @return array
     */
    protected function buildDirector(): array
    {
        return $this->extractRole('director');
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

        return $this->exportRole('director');
    }


    /**
     * Builds narrator
     *
     * @return array
     */
    protected function buildNarrator(): array
    {
        return $this->extractRole('narrator');
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

        return $this->exportRole('narrator');
    }


    /**
     * Builds editor
     *
     * @return array
     */
    protected function buildEditor(): array
    {
        return $this->extractRole('editor');
    }


    public function setEditor($editor)
    {
        $this->editor = $editor;
    }


    public function getEditor(bool $formatted = false)
    {
        if (!$formatted) {
            return $this->editor;
        }

        return $this->exportRole('editor');
    }


    /**
     * Builds participant
     *
     * @return array
     */
    protected function buildParticipant(): array
    {
        return $this->extractRole('participant');
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

        return $this->exportRole('participant');
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
            # TODO: See if this needs `trim()`
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
     * @return string
     */
    protected function buildCategory(): string
    {
        if ($this->isAudiobook) {
            return 'Hörbuch';
        }

        if (!isset($this->tags['category']) || $this->tags === false) {
            return '';
        }

        $category = $this->tags['category'];

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
     * @return array
     */
    protected function buildTopics(): array
    {
        if (!isset($this->tags['topics']) || $this->tags === false) {
            return [];
        }

        $topics = $this->tags['topics'];

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
            'ISBN'             => $this->getISBN(),
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
}
