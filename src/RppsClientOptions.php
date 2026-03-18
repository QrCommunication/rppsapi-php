<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi;

final readonly class RppsClientOptions
{
    public function __construct(
        public string $fhirBaseUrl = 'https://gateway.api.esante.gouv.fr/fhir/v2',
        public ?string $fhirApiKey = null,
        public string $tabularBaseUrl = 'https://tabular-api.data.gouv.fr/api/resources',
        public float $timeout = 30.0,
        public int $tabularPageSize = 100,
        public array $disabledSources = [],
    ) {}
}
