<?php

namespace PHPCBIS\KNV;

use PHPCBIS\Exceptions\IncompatibleClientException;
use PHPCBIS\Exceptions\InvalidLoginException;

use PHPCBIS\KNV\Responses\OLA;
use PHPCBIS\Helpers\Butler;

use SoapClient;
use SoapFault;


/**
 * Class Api
 *
 * Provides methods to interact with KNV's API
 *
 * @package PHPCBIS
 */

class Api
{
    /**
     * Properties
     */

    /**
     * Whether to work offline (cached books only)
     *
     * @var bool
     */
    private $offlineMode = false;


    /**
     * Session identifier retrieved when first connecting to KNV's API
     *
     * @var string
     */
    private $sessionID = null;


    /**
     * SOAP client used when connecting to KNV's API
     *
     * @var \SoapClient
     */
    private $client = null;


    /**
     * Constructor
     */

    public function __construct($credentials)
    {
        try {
            # Fire up SOAP client
            $this->client = new SoapClient('http://ws.pcbis.de/knv-2.0/services/KNVWebService?wsdl', [
                'soap_version' => SOAP_1_2,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                'cache_wsdl' => WSDL_CACHE_BOTH,
                'trace' => true,
                'exceptions' => true,
            ]);
        } catch (SoapFault $e) {
            # Activate offline mode on network error
            $this->offlineMode = true;
        }

        if (!$this->offlineMode) {
            # Check compatibility
            if ($this->client->CheckVersion('2.0') === '2') {
                throw new IncompatibleClientException('Your client is outdated, please update to newer version.');
            }

            # Initialise API driver
            if ($credentials !== null) {
                $this->sessionID = $this->logIn($credentials);
            }
        }
    }


    /**
     * Destructor
     */

    public function __destruct()
    {
        if (!$this->offlineMode) {
            $this->logOut();
        }
    }


    /**
     * Methods
     */

    /**
     * Uses credentials to log into KNV's API & generates a sessionID
     *
     * @param array $credentials
     * @throws \PHPCBIS\Exceptions\InvalidLoginException
     * @return string
     */
    private function logIn(array $credentials): string
    {
        try {
            $query = $this->client->WSCall(['LoginInfo' => $credentials]);
        } catch (SoapFault $e) {
            throw new InvalidLoginException($e->getMessage());
        }

        return $query->SessionID;
    }


    /**
     * Uses sessionID to log out of KNV's API
     *
     * @return void
     */
    private function logOut(): void
    {
        $this->client->WSCall([
            'SessionID' => $this->sessionID,
            'Logout' => true,
        ]);
    }


    /**
     * Fetches raw product data from KNV
     *
     * .. if product for given EAN/ISBN exists
     *
     * @param string $isbn
     * @throws \PHPCBIS\Exceptions\InvalidLoginException
     * @return array
     */
    private function query(string $isbn)
    {
        if ($this->offlineMode) {
            throw new InvalidLoginException('Offline mode enabled, API calls are not allowed.');
        }

        # For getting started with KNV's (surprisingly well documented) german API,
        # see http://www.knv-zeitfracht.de/wp-content/uploads/2020/07/Webservice_2.0.pdf
        $query = $this->client->WSCall([
            # Log in using sessionID
            'SessionID' => $this->sessionID,

            # Start new database query
            'Suchen' => [
                # Search across all databases
                'Datenbank' => [
                    'KNV',
                    'KNVBG',
                    'BakerTaylor',
                    'Gardners',
                ],
                'Suche' => [
                    'SimpleTerm' => [
                        # Simple search suffices for querying single ISBN
                        'Suchfeld' => 'ISBN',
                        'Suchwert' => $isbn,
                        'Schwert2' => '',
                        'Suchart' => 'Genau',
                    ],
                ],
            ],
            # Read results of the query & return first result
            'Lesen' => [
                'SatzVon' => 1,
                'SatzBis' => 1,
                'Format' => 'KNVXMLLangText',
            ],
        ]);

        if ($query->Suchergebnis->TrefferGesamt > 0) {
            $result = $query->Daten->Datensaetze->Record->ArtikelDaten;
            $array = Butler::loadXML($result);

            return Butler::last($array);
        }

        return [];
    }


    /**
     * Checks if product is available for delivery via OLA query
     *
     * @param string $isbn - A given product's EAN/ISBN
     * @param int $quantity - Number of products to be delivered
     * @return \stdObject
     */
    public function ola(string $isbn, int $quantity)
    {
        $ola = $this->client->WSCall([
            # Log in using sessionID
            'SessionID' => $this->sessionID,
            'OLA' => [
                'Art' => 'Abfrage',
                'OLAItem' => [
                    'Bestellnummer' => [
                        'ISBN' => $isbn,
                    ],
                    'Menge' => $quantity,
                ],
            ],
        ]);

        return new OLA($ola->OLAResponse->OLAResponseRecord);
    }
}
