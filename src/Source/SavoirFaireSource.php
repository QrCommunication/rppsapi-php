<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Source;

use QrCommunication\RppsApi\Dto\SavoirFaire;

final class SavoirFaireSource extends TabularSource
{
    private const RESOURCE_ID = 'fb55f15f-bd61-4402-b551-51ef387f2fab';

    public function fetchByRpps(string $rpps): array
    {
        $rows = $this->queryAll(self::RESOURCE_ID, 'Identifiant PP', $rpps);
        return array_map(fn(array $row) => SavoirFaire::fromRow($row), $rows);
    }
}
