<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Dto;

final readonly class SavoirFaire
{
    public function __construct(
        public CodeLabel $profession = new CodeLabel(),
        public CodeLabel $categorieProfessionnelle = new CodeLabel(),
        public CodeLabel $typeSavoirFaire = new CodeLabel(),
        public CodeLabel $savoirFaire = new CodeLabel(),
    ) {}

    public static function fromRow(array $row): self
    {
        $cl = fn(string $c, string $l) => CodeLabel::fromRow($row, $c, $l);
        return new self(
            profession: $cl('Code profession', 'Libellé profession'),
            categorieProfessionnelle: $cl('Code catégorie professionnelle', 'Libellé catégorie professionnelle'),
            typeSavoirFaire: $cl('Code type savoir-faire', 'Libellé type savoir-faire'),
            savoirFaire: $cl('Code savoir-faire', 'Libellé savoir-faire'),
        );
    }
}
