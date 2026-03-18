<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Dto;

final readonly class RppsSearchCriteria
{
    public function __construct(
        public ?string $nom = null,
        public ?string $prenom = null,
        public ?string $codePostal = null,
    ) {}
}
