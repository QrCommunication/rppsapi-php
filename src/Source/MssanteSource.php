<?php
declare(strict_types=1);
namespace QrCommunication\RppsApi\Source;

use QrCommunication\RppsApi\Dto\MessagerieMssante;

final class MssanteSource extends TabularSource
{
    private const RESOURCE_ID = 'afe01105-d9a1-41fe-921f-e40ea48b2ba6';

    public function fetchByRpps(string $rpps): array
    {
        $rows = $this->queryAll(self::RESOURCE_ID, 'Identifiant PP', $rpps);
        return array_map(fn(array $row) => MessagerieMssante::fromRow($row), $rows);
    }
}
