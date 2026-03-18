<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Dto;

final readonly class FhirData
{
    public function __construct(
        public ?array $practitioner = null,
        public array $practitionerRoles = [],
    ) {}
}
