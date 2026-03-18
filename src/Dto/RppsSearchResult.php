<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Dto;

final readonly class RppsSearchResult
{
    public function __construct(
        public string $rpps = '',
        public string $identificationNationale = '',
        public string $civilite = '',
        public string $nomExercice = '',
        public string $prenomExercice = '',
        public CodeLabel $profession = new CodeLabel(),
        public CodeLabel $categorieProfessionnelle = new CodeLabel(),
        public CodeLabel $modeExercice = new CodeLabel(),
        public CodeLabel $savoirFaire = new CodeLabel(),
        public string $raisonSociale = '',
        public string $codePostal = '',
        public string $libelleCommune = '',
        public string $departement = '',
    ) {}

    public static function fromRow(array $row): self
    {
        $s = fn(string $k): string => trim((string) ($row[$k] ?? ''));
        $cl = fn(string $c, string $l) => CodeLabel::fromRow($row, $c, $l);
        return new self(
            rpps: $s('Identifiant PP'),
            identificationNationale: $s('Identification nationale PP'),
            civilite: $s("Libellé civilité d'exercice"),
            nomExercice: $s("Nom d'exercice"),
            prenomExercice: $s("Prénom d'exercice"),
            profession: $cl('Code profession', 'Libellé profession'),
            categorieProfessionnelle: $cl('Code catégorie professionnelle', 'Libellé catégorie professionnelle'),
            modeExercice: $cl('Code mode exercice', 'Libellé mode exercice'),
            savoirFaire: $cl('Code savoir-faire', 'Libellé savoir-faire'),
            raisonSociale: $s('Raison sociale site'),
            codePostal: $s('Code postal (coord. structure)'),
            libelleCommune: $s('Libellé commune (coord. structure)'),
            departement: $s('Libellé Département (structure)'),
        );
    }
}
