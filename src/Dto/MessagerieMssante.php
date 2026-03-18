<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Dto;

final readonly class MessagerieMssante
{
    public function __construct(
        public string $typeBal = '',
        public string $adresseBal = '',
        public bool $dematerialisation = false,
        public CodeLabel $savoirFaire = new CodeLabel(),
        public string $structureIdentifiant = '',
        public string $structureTypeIdentifiant = '',
        public string $serviceRattachement = '',
        public string $raisonSociale = '',
        public string $codePostal = '',
        public string $departement = '',
    ) {}

    public static function fromRow(array $row): self
    {
        $s = fn(string $k): string => trim((string) ($row[$k] ?? ''));
        $demat = $row['Dématérialisation'] ?? false;
        return new self(
            typeBal: $s('Type de BAL'),
            adresseBal: $s('Adresse BAL'),
            dematerialisation: $demat === true || $demat === 'True' || $demat === 'true' || $demat === '1',
            savoirFaire: CodeLabel::fromRow($row, 'Code savoir-faire', 'Libellé savoir-faire'),
            structureIdentifiant: $s('Identification Structure'),
            structureTypeIdentifiant: $s('Type identifiant structure'),
            serviceRattachement: $s('Service de rattachement'),
            raisonSociale: $s('Raison Sociale structure BAL'),
            codePostal: $s('Code postal structure BAL'),
            departement: $s('Département structure BAL'),
        );
    }
}
