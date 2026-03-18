<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Source;

use QrCommunication\RppsApi\Dto\CarteCps;

final class CarteCpsSource extends TabularSource
{
    private const RESOURCE_ID = '210eb05e-564b-42be-994a-d1800b63e9b7';

    public function fetchByRpps(string $rpps): array
    {
        $rows = $this->queryAll(self::RESOURCE_ID, 'Identifiant PP', $rpps);
        return array_map(fn(array $row) => CarteCps::fromRow($row), $rows);
    }
}
