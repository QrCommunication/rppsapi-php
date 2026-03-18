<?php

declare(strict_types=1);

namespace QrCommunication\RppsApi\Dto;

final readonly class Profession
{
    public function __construct(
        public string $code = '',
        public string $libelle = '',
        public CodeLabel $categorie = new CodeLabel(),
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            code: trim((string) ($row['Code profession'] ?? '')),
            libelle: trim((string) ($row['Libellé profession'] ?? '')),
            categorie: CodeLabel::fromRow($row, 'Code catégorie professionnelle', 'Libellé catégorie professionnelle'),
        );
    }
}
