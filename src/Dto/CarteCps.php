<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Dto;

final readonly class CarteCps
{
    public function __construct(
        public CodeLabel $typeCarte = new CodeLabel(),
        public string $numeroCarte = '',
        public string $identifiantNationalCarte = '',
        public string $dateDebutValidite = '',
        public string $dateFinValidite = '',
        public string $dateOpposition = '',
        public string $dateMiseAJour = '',
    ) {}

    public static function fromRow(array $row): self
    {
        $s = fn(string $k): string => trim((string) ($row[$k] ?? ''));
        return new self(
            typeCarte: CodeLabel::fromRow($row, 'Code type de carte', 'Libellé type de carte'),
            numeroCarte: $s('Numéro carte'),
            identifiantNationalCarte: $s('Identifiant national contenu dans la carte'),
            dateDebutValidite: $s('Date début validité'),
            dateFinValidite: $s('Date fin validité'),
            dateOpposition: $s('Date opposition'),
            dateMiseAJour: $s('Date de mise à jour'),
        );
    }
}
