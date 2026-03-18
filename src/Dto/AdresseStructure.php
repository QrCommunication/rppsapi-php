<?php

declare(strict_types=1);

namespace QrCommunication\RppsApi\Dto;

final readonly class AdresseStructure
{
    public function __construct(
        public string $complementDestinataire = '',
        public string $complementPointGeographique = '',
        public string $numeroVoie = '',
        public string $indiceRepetitionVoie = '',
        public string $codeTypeVoie = '',
        public string $libelleTypeVoie = '',
        public string $libelleVoie = '',
        public string $mentionDistribution = '',
        public string $bureauCedex = '',
        public string $codePostal = '',
        public string $codeCommune = '',
        public string $libelleCommune = '',
        public string $codePays = '',
        public string $libellePays = '',
    ) {}

    public static function fromRow(array $row): self
    {
        $s = fn(string $key): string => trim((string) ($row[$key] ?? ''));

        return new self(
            complementDestinataire: $s('Complément destinataire (coord. structure)'),
            complementPointGeographique: $s('Complément point géographique (coord. structure)'),
            numeroVoie: $s('Numéro Voie (coord. structure)'),
            indiceRepetitionVoie: $s('Indice répétition voie (coord. structure)'),
            codeTypeVoie: $s('Code type de voie (coord. structure)'),
            libelleTypeVoie: $s('Libellé type de voie (coord. structure)'),
            libelleVoie: $s('Libellé Voie (coord. structure)'),
            mentionDistribution: $s('Mention distribution (coord. structure)'),
            bureauCedex: $s('Bureau cedex (coord. structure)'),
            codePostal: $s('Code postal (coord. structure)'),
            codeCommune: $s('Code commune (coord. structure)'),
            libelleCommune: $s('Libellé commune (coord. structure)'),
            codePays: $s('Code pays (coord. structure)'),
            libellePays: $s('Libellé pays (coord. structure)'),
        );
    }
}
