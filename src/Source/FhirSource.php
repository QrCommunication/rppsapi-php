<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Source;

use GuzzleHttp\ClientInterface;
use QrCommunication\RppsApi\Dto\FhirData;

final class FhirSource
{
    private const BASE_URL = 'https://gateway.api.esante.gouv.fr/fhir/v2';

    public function __construct(
        private readonly ClientInterface $http,
        private readonly string $apiKey,
        private readonly string $baseUrl = self::BASE_URL,
    ) {}

    public function fetchByRpps(string $rpps): FhirData
    {
        $response = $this->http->request('GET', $this->baseUrl . '/Practitioner', [
            'query' => ['identifier' => $rpps, '_count' => 1],
            'headers' => ['ESANTE-API-KEY' => $this->apiKey, 'Accept' => 'application/json'],
        ]);

        $bundle = json_decode($response->getBody()->getContents(), true);
        $practitioner = $bundle['entry'][0]['resource'] ?? null;

        $practitionerRoles = [];
        if ($practitioner && isset($practitioner['id'])) {
            $roleResponse = $this->http->request('GET', $this->baseUrl . '/PractitionerRole', [
                'query' => ['practitioner' => $practitioner['id'], '_count' => 50],
                'headers' => ['ESANTE-API-KEY' => $this->apiKey, 'Accept' => 'application/json'],
            ]);
            $roleBundle = json_decode($roleResponse->getBody()->getContents(), true);
            $practitionerRoles = array_map(
                fn(array $entry) => $entry['resource'],
                $roleBundle['entry'] ?? [],
            );
        }

        return new FhirData(practitioner: $practitioner, practitionerRoles: $practitionerRoles);
    }
}
