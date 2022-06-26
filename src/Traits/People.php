<?php

namespace Fundevogel\Pcbis\Traits;

use Pcbis\Exceptions\UnknownRoleException;
use Fundevogel\Pcbis\Helpers\Butler;


/**
 * Trait People
 *
 * Provides ability to extract people and build their respective roles
 */
trait People
{
    /**
     * Properties
     */

    /**
     * Available roles
     *
     * @var array
     */
    protected $roles = [
        'Vorlage'      => 'original',
        'Illustration' => 'illustrator',
        'Zeichnungen'  => 'drawer',
        'Fotos'        => 'photographer',
        'Übersetzung'  => 'translator',
        'Gesprochen'   => 'narrator',
        'Komposition'  => 'composer',
        'Regie'        => 'director',
        'Produktion'   => 'producer',
        'Besetzung'    => 'actor',
        'Mitarbeit'    => 'participant',
    ];


    /**
     * Involved people (all roles)
     *
     * @var array
     */
    protected $people;


    /**
     * Delimiter between people when exported as string
     *
     * @var string
     */
    protected $delimiter = '; ';


    /**
     * Methods
     */

    /**
     * Extracts involved people from source array
     *
     * This includes a wide variety, such as
     * - `author`
     * - `original`
     * - `illustrator`
     * - `drawer`
     * - `photographer`
     * - `translator`
     * - `narrator`
     * - `composer`
     * - `director`
     * - `producer`
     * - `actor`
     * - `participant`
     *
     * @return array
     */
    protected function separatePeople(): array
    {
        # Isolate author detection as this may vary for each product,
        # whereas all other roles are always part of the 'Mitarb' string
        $people = [
            'author'       => $this->buildAuthor(),
            'original'     => [],
            'illustrator'  => [],
            'drawer'       => [],
            'photographer' => [],
            'translator'   => [],
            'editor'       => [],
            'narrator'     => [],
            'composer'     => [],
            'director'     => [],
            'producer'     => [],
            'actor'        => [],
            'participant'  => [],
        ];

        if (!isset($this->source['Mitarb'])) {
            return $people;
        }

        # Default role
        $role = 'participant';

        # Alternative delimiters
        # (1) Participant
        # (2) Narrator
        # (3) Editor
        $delimiters = [
            # Includes edge case: 'Hrsg. v.'
            'Herausgegeben von ' => 'editor',
            'Gesprochen von '    => 'narrator',
            'Mit '               => 'participant',
            # Includes edge case: 'Illustr. v.'
            'Illustriert von '   => 'illustrator',

            # Edge cases: 'Aus d. Engl. v.', 'Aus d. Amerik. v.'
            'Aus dem Amerikanischen von ' => 'translator',
            'Aus dem Englischen von '     => 'translator',
        ];

        $data = $this->source['Mitarb'];

        # Take care of delimiters with two or more dots
        if (Butler::contains($data, 'Illustr. v. ')) {
            $data = Butler::replace($data, 'Illustr. v. ', 'Illustriert von ');
        }

        if (Butler::contains($data, 'Hrsg. v. ')) {
            $data = Butler::replace($data, 'Hrsg. v. ', 'Herausgegeben von ');
        }

        if (Butler::contains($data, 'Aus d. Amerik. v. ')) {
            $data = Butler::replace($data, 'Aus d. Amerik. v. ', 'Aus dem Amerikanischen von ');
        }

        if (Butler::contains($data, 'Aus d. Engl. v. ')) {
            $data = Butler::replace($data, 'Aus d. Engl. v. ', 'Aus dem Englischen von ');
        }

        # Check for names with two dots
        preg_match('/[A-Z]\.\s[A-Z]\./', $data, $matches);

        if (count($matches) > 0) {
            # Create replacements for each match, replacing the dots with sharps
            # For example, 'Tripp, F. J.' becomes 'Tripp, F# J#'
            $replacements = array_map(function ($string) {
                return Butler::replace(trim($string), ['. ', '.;', '.'], ['# ', '#;', '#.']);
            }, $matches);

            $data = Butler::replace($data, $matches, $replacements);
        }

        foreach (Butler::split($data, '.') as $string) {
            # If dots were replaced, change them back
            $string = Butler::replace($string, '#', '.');

            # First, see if there's a colon
            if (!Butler::contains($string, ':')) {
                # If not, the string is eligible for an alternative delimiter
                foreach ($delimiters as $delimiter => $role) {
                    if (Butler::startsWith($string, $delimiter)) {
                        # If so, remove it from the string, change role and end the loop
                        $group = Butler::replace($string, $delimiter, '');
                        $role = $delimiters[$delimiter];  # .. or $role

                        break;
                    }
                }

            } else {
                # Otherwise, split role & people as usual
                $array = Butler::split($string, ':');

                if (isset($this->roles[$array[0]])) {
                    $role = $this->roles[$array[0]];
                }

                $group = $array[1];
            }

            $people[$role] = $this->organizePeople($group);
        }

        return $people;
    }


    /**
     * Parses & organizes involved people by first & last name
     *
     * Example:
     * 'Doe, John; Doe, Jane'
     *
     * =>
     *
     * [
     *   [
     *     'firstName' => 'John',
     *     'lastName'  => 'Doe',
     *   ],
     *   [
     *     'firstName' => 'Jane',
     *     'lastName'  => 'Doe',
     *   ],
     * ]
     *
     * @param string $string - Involved people
     * @param string $groupDelimiter - Character between people
     * @param string $nameDelimiter - Character between first & last name
     * @return array
     */
    protected function organizePeople(string $string, string $groupDelimiter = ';', string $nameDelimiter = ','): array
    {
        $group = Butler::split($string, $groupDelimiter);

        $people = [];

        foreach ($group as $member) {
            $names = Butler::split($member, $nameDelimiter);

            # Edge case: single person entry, such as 'Diverse'
            $person = ['name' => $names[0]];

            if (count($names) > 1) {
                $person = [
                    'firstName' => $names[1],
                    'lastName'  => $names[0],
                ];
            }

            $people[] = $person;
        }

        return $people;
    }


    /**
     * Exports involved people of a given role as string (or array)
     *
     * @param string $role - Individual role
     * @param bool $asArray - Whether to export an array (rather than a string)
     * @throws \Pcbis\Exceptions\UnknownRoleException
     * @return string|array
     */
    public function getRole(string $role, bool $asArray = false)
    {
        if (!array_key_exists($role, $this->people)) {
            throw new UnknownRoleException('Unknown role: "' . $role . '"');
        }

        if (empty($this->people[$role])) {
            return $asArray ? [] : '';
        }

        if ($asArray) {
            return $this->people[$role];
        }

        $people = [];

        foreach (array_values($this->people[$role]) as $person) {
            $people[] = Butler::join($person, ' ');
        }

        return Butler::join($people, $this->delimiter);
    }


    /**
     * Builds author(s)
     *
     * @return array
     */
    protected function buildAuthor(): array
    {
        if (!isset($this->source['AutorSachtitel'])) {
            return [];
        }

        $string = $this->source['AutorSachtitel'];

        $groupDelimiter = ';';
        $personDelimiter = ',';

        # Edge case: `AutorSachtitel` contains something other than a person
        if (!Butler::contains($string, $groupDelimiter) && !Butler::contains($string, $personDelimiter)) {
            if (isset($this->source['IndexAutor'])) {
                if (is_array($this->source['IndexAutor'])) {
                    $string = Butler::join(array_map(function($string) {
                        return trim($string);
                    }, $this->source['IndexAutor']), ';');

                } elseif (is_string($this->source['IndexAutor'])) {
                    $string = trim($this->source['IndexAutor']);

                } else {
                    return [];
                }

            } else {
                return [];
            }
        }

        return $this->organizePeople($string);
    }


    /**
     * Exports all involved people as string (or array)
     *
     * @param bool $asArray - Whether to export an array (rather than a string)
     * @return string|array
     */

    public function people(bool $asArray = false)
    {
        if ($asArray) {
            return $this->people;
        }

        $all = $this->people;

        # Available role identifiers
        $roles = array_flip($this->roles);

        # Remove author
        unset($all['author']);

        $result = [];

        foreach ($all as $role => $people) {
            if ($people === []) {
                continue;
            }

            $array = [];

            foreach ($people as $person) {
                $array[] = Butler::join($person, ' ');
            }

            $result[] = $roles[$role] . ': ' . Butler::join($array, $this->delimiter);
        }

        return Butler::join($result, '. ');
    }


    /**
     * Shortcuts
     *
     * @return array|string
     */
    public function author(bool $asArray = false)
    {
        return $this->getRole('author', $asArray);
    }


    /**
     * @return array|string
     */
    public function original(bool $asArray = false)
    {
        return $this->getRole('original', $asArray);
    }


    /**
     * @return array|string
     */
    public function illustrator(bool $asArray = false)
    {
        return $this->getRole('illustrator', $asArray);
    }


    /**
     * @return array|string
     */
    public function drawer(bool $asArray = false)
    {
        return $this->getRole('drawer', $asArray);
    }


    /**
     * @return array|string
     */
    public function photographer(bool $asArray = false)
    {
        return $this->getRole('photographer', $asArray);
    }


    /**
     * @return array|string
     */
    public function translator(bool $asArray = false)
    {
        return $this->getRole('translator', $asArray);
    }


    /**
     * @return array|string
     */
    public function editor(bool $asArray = false)
    {
        return $this->getRole('editor', $asArray);
    }


    /**
     * @return array|string
     */
    public function narrator(bool $asArray = false)
    {
        return $this->getRole('narrator', $asArray);
    }


    /**
     * @return array|string
     */
    public function composer(bool $asArray = false)
    {
        return $this->getRole('composer', $asArray);
    }


    /**
     * @return array|string
     */
    public function director(bool $asArray = false)
    {
        return $this->getRole('director', $asArray);
    }


    /**
     * @return array|string
     */
    public function producer(bool $asArray = false)
    {
        return $this->getRole('producer', $asArray);
    }


    /**
     * @return array|string
     */
    public function actor(bool $asArray = false)
    {
        return $this->getRole('actor', $asArray);
    }


    /**
     * @return array|string
     */
    public function participant(bool $asArray = false)
    {
        return $this->getRole('participant', $asArray);
    }


    /**
     * Setters & getters
     *
     * @return void
     */
    public function setDelimiter(string $delimiter)
    {
        $this->delimiter = $delimiter;
    }


    public function getDelimiter(): string
    {
        return $this->delimiter;
    }
}
