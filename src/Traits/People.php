<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Traits;

use Fundevogel\Pcbis\Classes\Fields\Types\Role;
use Fundevogel\Pcbis\Classes\Fields\Types\Roles;
use Fundevogel\Pcbis\Exceptions\UnknownRoleException;
use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Helpers\Str;

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
     * Involved people (all roles)
     *
     * @var array
     */
    public array $people;


    /**
     * Methods
     */

    /**
     * Extracts involved people from raw data
     *
     * This includes the following roles by default:
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
    protected function setUpPeople(): array
    {
        # Isolate author detection as this may vary for each product,
        # whereas all other roles are always part of the 'Mitarb' string
        $people = [
            'author'       => $this->buildAuthor(),
            'original'     => $this->buildOriginal(),
            'illustrator'  => $this->buildIllustrator(),
            'drawer'       => $this->buildDrawer(),
            'photographer' => $this->buildPhotographer(),
            'translator'   => $this->buildTranslator(),
            'editor'       => $this->buildEditor(),
            'narrator'     => $this->buildNarrator(),
            'composer'     => $this->buildComposer(),
            'director'     => $this->buildDirector(),
            'producer'     => $this->buildProducer(),
            'actor'        => $this->buildActor(),
            'participant'  => $this->buildParticipant(),
        ];

        if (!isset($this->data['Mitarb'])) {
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

        $data = $this->data['Mitarb'];

        # Take care of delimiters with two or more dots
        if (Str::contains($data, 'Illustr. v. ')) {
            $data = Str::replace($data, 'Illustr. v. ', 'Illustriert von ');
        }

        if (Str::contains($data, 'Hrsg. v. ')) {
            $data = Str::replace($data, 'Hrsg. v. ', 'Herausgegeben von ');
        }

        if (Str::contains($data, 'Aus d. Amerik. v. ')) {
            $data = Str::replace($data, 'Aus d. Amerik. v. ', 'Aus dem Amerikanischen von ');
        }

        if (Str::contains($data, 'Aus d. Engl. v. ')) {
            $data = Str::replace($data, 'Aus d. Engl. v. ', 'Aus dem Englischen von ');
        }

        # Check for names with two dots
        preg_match('/[A-Z]\.\s[A-Z]\./', $data, $matches);

        if (count($matches) > 0) {
            # Create replacements for each match, replacing the dots with sharps
            # For example, 'Tripp, F. J.' becomes 'Tripp, F# J#'
            $replacements = array_map(function ($string) {
                return Str::replace(trim($string), ['. ', '.;', '.'], ['# ', '#;', '#.']);
            }, $matches);

            $data = Str::replace($data, $matches, $replacements);
        }

        # Define types of involvement
        $roles = [
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

        foreach (Str::split($data, '.') as $string) {
            # Define fallback to check against later
            $group = null;

            # If dots were replaced, change them back
            $string = Str::replace($string, '#', '.');

            # First, see if there's a colon
            if (!Str::contains($string, ':')) {
                # If not, the Str is eligible for an alternative delimiter
                foreach ($delimiters as $delimiter => $role) {
                    if (Str::startsWith($string, $delimiter)) {
                        # If so, remove it from the string, change role and end the loop
                        $group = Str::replace($string, $delimiter, '');
                        $role = $delimiters[$delimiter];  # .. or $role

                        break;
                    }
                }
            } else {
                # Otherwise, split role & people as usual
                $array = Str::split($string, ':');

                if (isset($roles[$array[0]])) {
                    $role = $roles[$array[0]];
                }

                $group = $array[1];
            }

            # Skip undefined group
            if (is_null($group)) {
                continue;
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
     * .. becomes ..
     *
     * [
     *     [
     *         'firstName' => 'John',
     *         'lastName'  => 'Doe',
     *     ],
     *     [
     *         'firstName' => 'Jane',
     *         'lastName'  => 'Doe',
     *     ],
     * ]
     *
     * @param string $string Involved people
     * @param string $groupDelimiter Character between people
     * @param string $nameDelimiter Character between first & last name
     * @return array
     */
    protected function organizePeople(string $string, string $groupDelimiter = ';', string $nameDelimiter = ','): array
    {
        $group = Str::split($string, $groupDelimiter);

        $people = [];

        foreach ($group as $member) {
            $names = Str::split($member, $nameDelimiter);

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
     * Exports people of given role
     *
     * @param string $role Role (= involvement)
     * @throws \Fundevogel\Pcbis\Exceptions\UnknownRoleException
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function getRole(string $role): Role
    {
        if (!array_key_exists($role, $this->people)) {
            throw new UnknownRoleException(sprintf('Unknown role: "%s"', $role));
        }

        return new Role($this->people[$role]);
    }


    /**
     * Builds author(s)
     *
     * @return array
     */
    protected function buildAuthor(): array
    {
        if (!isset($this->data['AutorSachtitel'])) {
            return [];
        }

        $string = $this->data['AutorSachtitel'];

        $groupDelimiter = ';';
        $personDelimiter = ',';

        # Edge case: `AutorSachtitel` contains something other than a person
        if (!Str::contains($string, $groupDelimiter) && !Str::contains($string, $personDelimiter)) {
            if (isset($this->data['IndexAutor'])) {
                if (is_array($this->data['IndexAutor'])) {
                    $string = A::join(array_map('trim', $this->data['IndexAutor']), ';');
                } elseif (is_string($this->data['IndexAutor'])) {
                    $string = trim($this->data['IndexAutor']);
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
     * Builds original author(s)
     *
     * @return array
     */
    protected function buildOriginal(): array
    {
        return [];
    }


    /**
     * Builds illustrator(s)
     *
     * @return array
     */
    protected function buildIllustrator(): array
    {
        return [];
    }


    /**
     * Builds drawer(s)
     *
     * @return array
     */
    protected function buildDrawer(): array
    {
        return [];
    }


    /**
     * Builds photographer(s)
     *
     * @return array
     */
    protected function buildPhotographer(): array
    {
        return [];
    }


    /**
     * Builds translator(s)
     *
     * @return array
     */
    protected function buildTranslator(): array
    {
        return [];
    }


    /**
     * Builds editor(s)
     *
     * @return array
     */
    protected function buildEditor(): array
    {
        return [];
    }


    /**
     * Builds narrator(s)
     *
     * @return array
     */
    protected function buildNarrator(): array
    {
        return [];
    }


    /**
     * Builds composer(s)
     *
     * @return array
     */
    protected function buildComposer(): array
    {
        return [];
    }


    /**
     * Builds director(s)
     *
     * @return array
     */
    protected function buildDirector(): array
    {
        return [];
    }


    /**
     * Builds producer(s)
     *
     * @return array
     */
    protected function buildProducer(): array
    {
        return [];
    }


    /**
     * Builds actor(s)
     *
     * @return array
     */
    protected function buildActor(): array
    {
        return [];
    }


    /**
     * Builds participant(s)
     *
     * @return array
     */
    protected function buildParticipant(): array
    {
        return [];
    }


    /**
     * Dataset methods
     */

    /**
     * Exports author(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function author(): Role
    {
        return $this->getRole('author');
    }


    /**
     * Exports original author(s
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function original(): Role
    {
        return $this->getRole('original');
    }


    /**
     * Exports illustrator(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function illustrator(): Role
    {
        return $this->getRole('illustrator');
    }


    /**
     * Exports drawer(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function drawer(): Role
    {
        return $this->getRole('drawer');
    }


    /**
     * Exports photographer(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function photographer(): Role
    {
        return $this->getRole('photographer');
    }


    /**
     * Exports translator(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function translator(): Role
    {
        return $this->getRole('translator');
    }


    /**
     * Exports editor(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function editor(): Role
    {
        return $this->getRole('editor');
    }


    /**
     * Exports narrator(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function narrator(): Role
    {
        return $this->getRole('narrator');
    }


    /**
     * Export composer(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function composer(): Role
    {
        return $this->getRole('composer');
    }


    /**
     * Exports director(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function director(): Role
    {
        return $this->getRole('director');
    }


    /**
     * Exports producer(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function producer(): Role
    {
        return $this->getRole('producer');
    }


    /**
     * Exports actor(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function actor(): Role
    {
        return $this->getRole('actor');
    }


    /**
     * Exports participant(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Role
     */
    public function participant(): Role
    {
        return $this->getRole('participant');
    }


    /**
     * Exports (just) involved people
     *
     * @return \Fundevogel\Pcbis\Classes\Fields\Types\Roles
     */
    public function people(): Roles
    {
        return new Roles($this->people);
    }
}
