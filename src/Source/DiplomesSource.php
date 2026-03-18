<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Source;

use QrCommunication\RppsApi\Dto\DiplomeEtAutorisation;

final class DiplomesSource extends TabularSource
{
    private const RESOURCE_ID = '41ae70ac-90c8-4c4e-8644-4ef1b100f045';

    public function fetchByRpps(string $rpps): array
    {
        $rows = $this->queryAll(self::RESOURCE_ID, 'Identifiant PP', $rpps);
        return array_map(fn(array $row) => DiplomeEtAutorisation::fromRow($row), $rows);
    }
}
