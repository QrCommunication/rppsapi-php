<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Dto;

final readonly class DiplomeEtAutorisation
{
    public function __construct(
        public ?CodeLabel $typeDiplome = null,
        public ?CodeLabel $diplome = null,
        public ?CodeLabel $typeAutorisation = null,
        public ?CodeLabel $disciplineAutorisation = null,
    ) {}

    public static function fromRow(array $row): self
    {
        $s = fn(string $k): string => trim((string) ($row[$k] ?? ''));
        $hasDiplome = $s('Code diplôme obtenu') !== '';
        $hasAutorisation = $s('Code type autorisation') !== '';

        return new self(
            typeDiplome: $hasDiplome ? CodeLabel::fromRow($row, 'Code type diplôme obtenu', 'Libellé type diplôme obtenu') : null,
            diplome: $hasDiplome ? CodeLabel::fromRow($row, 'Code diplôme obtenu', 'Libellé diplôme obtenu') : null,
            typeAutorisation: $hasAutorisation ? CodeLabel::fromRow($row, 'Code type autorisation', 'Libellé type autorisation') : null,
            disciplineAutorisation: $hasAutorisation ? CodeLabel::fromRow($row, 'Code discipline autorisation', 'Libellé discipline autorisation') : null,
        );
    }
}
