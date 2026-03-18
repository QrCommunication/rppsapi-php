<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Dto;

final readonly class Activite
{
    public function __construct(
        public CodeLabel $modeExercice = new CodeLabel(),
        public CodeLabel $savoirFaire = new CodeLabel(),
        public CodeLabel $typeSavoirFaire = new CodeLabel(),
        public CodeLabel $secteurActivite = new CodeLabel(),
        public CodeLabel $sectionTableauPharmaciens = new CodeLabel(),
        public CodeLabel $role = new CodeLabel(),
        public CodeLabel $genreActivite = new CodeLabel(),
        public string $autoriteEnregistrement = '',
        public Structure $structure = new Structure(),
    ) {}

    public static function fromRow(array $row): self
    {
        $cl = fn(string $c, string $l) => CodeLabel::fromRow($row, $c, $l);
        $s = fn(string $k): string => trim((string) ($row[$k] ?? ''));
        return new self(
            modeExercice: $cl('Code mode exercice', 'Libellé mode exercice'),
            savoirFaire: $cl('Code savoir-faire', 'Libellé savoir-faire'),
            typeSavoirFaire: $cl('Code type savoir-faire', 'Libellé type savoir-faire'),
            secteurActivite: $cl("Code secteur d'activité", "Libellé secteur d'activité"),
            sectionTableauPharmaciens: $cl('Code section tableau pharmaciens', 'Libellé section tableau pharmaciens'),
            role: $cl('Code rôle', 'Libellé rôle'),
            genreActivite: $cl('Code genre activité', 'Libellé genre activité'),
            autoriteEnregistrement: $s("Autorité d'enregistrement"),
            structure: Structure::fromRow($row),
        );
    }
}
