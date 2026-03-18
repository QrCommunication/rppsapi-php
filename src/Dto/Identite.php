<?php

declare(strict_types=1);

namespace QrCommunication\RppsApi\Dto;

final readonly class Identite
{
    public function __construct(
        public CodeLabel $civilite = new CodeLabel(),
        public CodeLabel $civiliteExercice = new CodeLabel(),
        public string $nomExercice = '',
        public string $prenomExercice = '',
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            civilite: CodeLabel::fromRow($row, 'Code civilité', 'Libellé civilité'),
            civiliteExercice: CodeLabel::fromRow($row, "Code civilité d'exercice", "Libellé civilité d'exercice"),
            nomExercice: trim((string) ($row["Nom d'exercice"] ?? '')),
            prenomExercice: trim((string) ($row["Prénom d'exercice"] ?? '')),
        );
    }
}
