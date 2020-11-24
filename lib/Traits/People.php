<?php

namespace PHPCBIS\Traits;

use PHPCBIS\Exceptions\UnknownRoleException;
use PHPCBIS\Helpers\Butler;


/**
 * Trait People
 *
 * Provides ability to extract people and build their respective roles
 *
 * @package PHPCBIS
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
     * - `illustrator`
     * - `drawer`
     * - `photographer`
     * - `translator`
     * - `narrator`
     * - `director`
     * - `producer`
     * - `participant`
     *
     * @return array
     */
    private function separatePeople(): array
    {
        $people = [
            'illustrator'  => [],
            'drawer'       => [],
            'photographer' => [],
            'translator'   => [],
            'editor'       => [],
            'narrator'     => [],
            'director'     => [],
            'producer'     => [],
            'participant'  => [],
        ];

        if (!isset($this->source['Mitarb'])) {
            return $people;
        }

        # Available roles
        $roles = [
            'Illustration' => 'illustrator',
            'Zeichnungen'  => 'drawer',
            'Fotos'        => 'photographer',
            'Übersetzung'  => 'translator',
            'Gesprochen'   => 'narrator',
            'Regie'        => 'director',
            'Produktion'   => 'producer',
            'Mitarbeit'    => 'participant',
            # Edge case: author of original works
            'Vorlage'      => 'original',
        ];

        # Default role
        $role = 'participant';

        # Alternative delimiters
        # (1) Participant
        # (2) Narrator
        # (3) Editor
        $delimiters = [
            'Mit '               => 'participant',
            'Gesprochen von '    => 'narrator',
            'Herausgegeben von ' => 'editor',
        ];

        foreach (Butler::split($this->source['Mitarb'], '.') as $string) {
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

                if (isset($roles[$array[0]])) {
                    $role = $roles[$array[0]];
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
     * @throws \PHPCBIS\Exceptions\UnknownRoleException
     * @return string|array
     */
    public function getRole(string $role, bool $asArray = false)
    {
        $roles = [
            'illustrator',
            'drawer',
            'photographer',
            'translator',
            'editor',
            'narrator',
            'director',
            'producer',
            'participant',
        ];

        if (!in_array($role, $roles)) {
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
     * Setters & getters
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
