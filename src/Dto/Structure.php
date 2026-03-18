<?php

declare(strict_types=1);

namespace QrCommunication\RppsApi\Dto;

final readonly class Structure
{
    public function __construct(
        public string $identifiantTechnique = '',
        public string $raisonSociale = '',
        public string $enseigneCommerciale = '',
        public string $numeroSiret = '',
        public string $numeroSiren = '',
        public string $numeroFinessSite = '',
        public string $numeroFinessEtablissementJuridique = '',
        public string $telephone = '',
        public string $telephone2 = '',
        public string $telecopie = '',
        public string $email = '',
        public string $codeDepartement = '',
        public string $libelleDepartement = '',
        public string $ancienIdentifiant = '',
        public AdresseStructure $adresse = new AdresseStructure(),
    ) {}

    public static function fromRow(array $row): self
    {
        $s = fn(string $key): string => trim((string) ($row[$key] ?? ''));

        return new self(
            identifiantTechnique: $s('Identifiant technique de la structure'),
            raisonSociale: $s('Raison sociale site'),
            enseigneCommerciale: $s('Enseigne commerciale site'),
            numeroSiret: $s('Numéro SIRET site'),
            numeroSiren: $s('Numéro SIREN site'),
            numeroFinessSite: $s('Numéro FINESS site'),
            numeroFinessEtablissementJuridique: $s('Numéro FINESS établissement juridique'),
            telephone: $s('Téléphone (coord. structure)'),
            telephone2: $s('Téléphone 2 (coord. structure)'),
            telecopie: $s('Télécopie (coord. structure)'),
            email: $s('Adresse e-mail (coord. structure)'),
            codeDepartement: $s('Code Département (structure)'),
            libelleDepartement: $s('Libellé Département (structure)'),
            ancienIdentifiant: $s('Ancien identifiant de la structure'),
            adresse: AdresseStructure::fromRow($row),
        );
    }
}
