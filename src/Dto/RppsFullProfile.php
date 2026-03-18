<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Dto;

final readonly class RppsFullProfile
{
    public function __construct(
        public string $rpps = '',
        public string $identificationNationale = '',
        public Identite $identite = new Identite(),
        public Profession $profession = new Profession(),
        /** @var Activite[] */
        public array $activites = [],
        /** @var DiplomeEtAutorisation[] */
        public array $diplomesEtAutorisations = [],
        /** @var SavoirFaire[] */
        public array $savoirFaire = [],
        /** @var CarteCps[] */
        public array $cartesCps = [],
        /** @var MessagerieMssante[] */
        public array $messageriesMssante = [],
        public FhirData $fhir = new FhirData(),
        /** @var SourceStatus[] */
        public array $sources = [],
        public string $queriedAt = '',
        public float $totalDurationMs = 0,
    ) {}
}
