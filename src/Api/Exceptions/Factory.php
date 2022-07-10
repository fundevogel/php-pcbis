<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Api\Exceptions;

use Fundevogel\Pcbis\Interfaces\KnvException;
use Fundevogel\Pcbis\Api\Exceptions\Types\BadRequestException;
use Fundevogel\Pcbis\Api\Exceptions\Types\ForbiddenException;
use Fundevogel\Pcbis\Api\Exceptions\Types\InternalServerErrorException;
use Fundevogel\Pcbis\Api\Exceptions\Types\OKException;
use Fundevogel\Pcbis\Api\Exceptions\Types\UnauthorizedException;

/**
 * Class Factory
 *
 * Creates 'KnvException' subclasses, factory-style
 */
class Factory
{
    /**
     * Available error codes
     *
     * @var array
     */
    public static $errors = [
        # (1) Resource 'login'
        '1' => 'Das Token, das übergeben wurde, ist nicht (mehr) gültig. Fordern Sie ein neues Token mit der Ressource "login" an',
        '2' => 'Die Login-Daten (VKN, Benutzer oder Passwort) sind nicht gültig',
        '3' => 'Der Zugriff auf diese Funktion ist Ihnen nicht erlaubt. Kontaktieren Sie bitte Ihren Ansprechpartner bei Zeitfracht',

        # (2) Resource 'suche'
        '101' => 'Im Aufruf an die Suche fehlt eine Suchanfrage',
        '102' => 'Es wurde kein oder ein falscher Datenbankname übergeben',
        '103' => 'Das übergebene Sortierfeld ist unbekannt',
        '104' => 'Bei der Leseanforderung ist der letzte zu lesende Satz kleiner als der erste',
        '105' => 'Es wurde ein unbekanntes Suchfeld übergeben',
        '106' => 'Bei den Feldern muss mindestens eines der Felder SuchWert1 und SuchWert2 befüllt sein',
        '107' => 'Bei den Feldern ist keine rechtsungenaue Suche möglich. Benutzen Sie bitte eine genaue Suche',
        '108' => 'Es ist ein unbekannter Verknüpfungsoperator bei den Suchen verwendet worden. Benutzen Sie bitte UND, UNDNICHT oder ODER',
        '109' => 'Bei einer komplexen Suche müssen genau zwei Suchen (einfach oder komplex) miteinander verknüpft werden',
        '110' => 'Die verschiedenen Formen der Suchanfragen dürfen nicht miteinander kombiniert werden',
        '111' => 'Bei der Einschränkung auf einen Filter wurde eine unbekannte ID übergeben',
        '112' => 'Interner Fehler bei der Suche',
        '113' => 'Eine Suchanfrage muss entweder einfach oder komplex sein',
        '114' => 'Bei einer einfachen Suche muss eine SuchArt angegeben sein (GENAU oder RECHTSUNGENAU)',
        '115' => 'Bei einer einfachen Suche muss ein SuchWert1 angegeben werden',
        '116' => 'Bei einer Von-Bis-Suche muss der Suchwert2 angegeben werden',
        '117' => 'Eine Suche oder ein Teil einer komplexen Suche ist nicht angegeben',
        '118' => 'Bei einem Suchstring sind mehr öffnende als schließende Klammern angegeben',
        '119' => 'Bei einem Suchstring ist ein unbekannter Vergleichsoperator angegeben',
        '120' => 'Bei einem Suchstring fehlt ein Suchwert oder ist nicht mit Anführungszeichen umgeben',
        '121' => 'Bei einer Einschränkung auf einen Cluster wurde ein unbekannter Cluster-Name oder Cluster-Wert übergeben',
        '122' => 'Beim Lesen eines bestimmten Multimediaobjektes fehlt Typ oder Format, bzw, es wurden dafür ungültige Werte übergeben',

        # (3) Resource 'register'
        '201' => 'Die Richtungsangabe für das Register ist unbekannt',

        # (4) Resource 'ola'
        '701' => 'Die in der Konfiguration bei Zeitfracht hinterlegten Daten für den OLA-Zugang sind fehlerhaft',
        '702' => 'Die Anmeldung bei der OLA mit den in der Konfiguration bei Zeitfracht hinterlegten Daten für den OLA-Zugang ist fehlgeschlagen',
        '703' => 'Bei der OLA ist ein Fehler aufgetreten',
        '704' => 'Eine OLA-Anfrage darf nicht mit einer Menge größer als 99 gemacht werden',
        '705' => 'Die OLA wurde mit einer ungültigen Zeitfracht-Titelnummer aufgerufen',
        '706' => 'Die OLA wurde mit einer ungültigen EAN aufgerufen',
        '707' => 'Die OLA wurde mit einer ungültigen ISBN aufgerufen',

        # (5) Resource 'ebook'
        '801' => 'Bei der E-Book-Bestellung ist ein Fehler aufgetreten',

        # (6) Resource 'filter'
        '901' => 'Die vorhandenen Filter konnten nicht eingelesen werden',
        '902' => 'Die beim Aufruf übergebene Filtergruppe ist unbekannt',

        # (7) Resource 'cmpaket'
        '1001' => 'Es konnten keine CM-Pakete gefunden werden',

        # (8) Generic 'Internal Server Error'
        '5001' => 'Allgemeiner interner Server Fehler',
    ];


    /**
     * Creates 'KnvException' instance matching given HTTP status
     *
     * @param \stdClass $data Response body as JSON object
     * @return \Fundevogel\Pcbis\Interfaces\KnvException
     */
    public static function create(\stdClass $data): KnvException
    {
        # Define fallback values
        $message = '';
        $code = 0;
        $description = '';

        # Determine exception message, code & description
        foreach (['error', 'fehlerText'] as $key) {
            if (isset($data->{$key})) {
                $message = $data->{$key};
            }
        }

        if (isset($data->fehlerNummer)) {
            $code = (int) $data->fehlerNummer;

            if (array_key_exists($data->fehlerNummer, static::$errors)) {
                $description = static::$errors[$data->fehlerNummer];
            }
        }

        if ($data->status == 'FAILED') {
            $code = 1;
        }

        if (isset($data->message)) {
            $description = $data->message;
        }

        # Create exception based on error code
        switch ($data->httpStatus) {
            # 'Unauthorized'
            case 'UNAUTHORIZED':
                return new UnauthorizedException($message, $code, $description, $data);

            # 'Forbidden'
            case 'FORBIDDEN':
                return new ForbiddenException($message, $code, $description, $data);

            # 'Bad Request'
            case 'BAD_REQUEST':
                return new BadRequestException($message, $code, $description, $data);

            # 'OK'
            case 'OK':
                return new OKException($message, $code, $description, $data);

            # 'Internal Server Error'
            case 'INTERNAL_SERVER_ERROR':
                return new InternalServerErrorException($message, $code, $description, $data);
        }

        # Provide fallback exception
        return new Exception($message, $code, $description, $data);
    }
}
